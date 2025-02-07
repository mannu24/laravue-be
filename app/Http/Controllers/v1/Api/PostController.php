<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Str;

class PostController extends Controller
{
    public function index(Request $request) {
        $query = Post::query()->with(['user:id,name,username'])->withCount('likes','comments') ;

        if ($request->has('username') && $request->username) {
            $query->whereHas('user', function ($q) {
                $q->where('username', request()->input('username'));
            }) ;
        }

        $posts = $query->latest()->get()->each(function ($q) {
            $q->views_count = 10;
            $q->makeHidden('media','id','user_id','meta_content');
            $q->user->makeHidden('id','media');
        });

        $page = $_GET['page'];
        $perPage = 2;
        $paginatedData = new LengthAwarePaginator($posts->forPage($page, $perPage)->values(), $posts->count(), $perPage);

        return response()->json(['status' => 'success', 'records' => $paginatedData]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'title' => 'nullable|string|max:500',
            'content' => 'required|string',
            'mentions' => 'array',
            'mentions.*.id' => 'required|string|exists:users,id',
            'mentions.*.username' => 'required|string|exists:users,username',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png|max:2048',
        ]);
    
        DB::beginTransaction();
        try {
            $post = Post::create([
                'user_id' => auth()->id(),
                'post_code' => uniqid('post_'),
                'title' => $data['title'],
                'content' => $this->processContent($data['content'], $data['mentions']),
                'meta_content' => json_encode($data['mentions']),
                'is_ai_generated' => $request->is_ai_generated ?? 0,
                'is_blocked' => $request->is_blocked ?? 0,
            ]);
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $post->addMedia($file)->toMediaCollection('images');
                }
            }
            // $this->handleMentionNotifications($post, $data['mentions']);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Post created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error Occured']);
        }    
    }

    private function processContent($content, $mentions) {
        $cleanContent = Str::of($content)
        // ->stripTags(config('feed.allowed_tags'))
        ->replaceMatches('/<script.*?>.*?<\/script>/si', '');

        return preg_replace_callback('/@\[([^\]]+)\]\((\d+)\)/', function ($matches) use ($mentions) {
            $userId = $matches[2];
            $mention = collect($mentions)->firstWhere('id', $userId);
            return $mention ? "<mention data-user-id='{$userId}'>@{$mention['username']}</mention>" : $matches[0];
        }, $cleanContent);
    }

    public function mentionSuggestions(Request $request) {
        $query = $request->input('q', '');

        return User::where('username', 'like', $query.'%')
            // ->whereDoesntHave('blockers', function ($q) use ($request) {
            //     $q->where('blocked_user_id', $request->user()->id);
            // })
            ->select('id', 'username', 'name')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'display' => "@{$user->username}",
                    'avatar' => $user->getMedia('photos')->first() ? $user->getMedia('photos')->first()->getUrl() : null,
                    'name' => $user->name
                ];
            });
    }

    public function show($post_code) {
        $post = Post::where('post_code', $post_code)->firstOrFail();
        if($post) {
            $post->load(['user:id,name,username','comments' => function ($q) {
                $q->withCount('likes');
            },'comments.user:id,name,username'])->loadCount('likes','comments') ;
            $post->views_count = 10;
            $post->makeHidden('media','id','user_id','meta_content');
            $post->user->makeHidden('id','media');

            return response()->json($post);
        }
    }

    public function update(Request $request, $post_code) {
        $data = $request->validate([
            'title' => 'nullable|string|max:500',
            'content' => 'required|string',
            'mentions' => 'array',
            'mentions.*.id' => 'required|string|exists:users,id',
            'mentions.*.username' => 'required|string|exists:users,username',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png|max:2048',
            'deleted' => 'nullable|array',
        ]);
        
        if(!$request->has('mentions')) $data['mentions'] = [] ;

        DB::beginTransaction();
        try {

            $post = Post::where('post_code', $post_code)->firstOrFail();
            $post->update([
                'title' => $data['title'],
                'content' => $this->processContent($data['content'], $data['mentions']),
                'meta_content' => json_encode($data['mentions']),
            ]);

            if ($request->has('deleted')) {
                foreach ($request->deleted as $index) {
                    $mediaItem = $post->getMedia('images')->get($index);
                    if ($mediaItem) {
                        $mediaItem->delete();
                    }
                }
            }

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $post->addMedia($file)->toMediaCollection('images');
                }
            }
            // $this->handleMentionNotifications($post, $data['mentions']);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Post updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e) ;
            return response()->json(['status' => 'error', 'message' => 'Error Occured']);
        }    
    }

    public function duplicate($postCode) {
        $post = Post::where('post_code', $postCode)->firstOrFail();
        
        DB::beginTransaction();
        try {
            $newPost = Post::create([
                'user_id' => request()->user()->id,
                'post_code' => $post->post_code . '-copy',
                'title' => $post->title . '-copy',
                'content' => $post->content . '-copy',
                'meta_content' => '',
                'is_ai_generated' => 0,
                'is_blocked' => 0,
            ]);
            if ($post->hasMedia('images')) {
                foreach ($post->getMedia('images') as $media) {
                    $newPost->addMedia($media->getPath())->preservingOriginal()->toMediaCollection('images');
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Post duplicated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error Occured']);
        }    
    }

    public function add_comment (Request $request) {
        $data = $request->validate([
            'code' => 'required|string|exists:posts,post_code',
            'content' => 'required|string',
        ]);

        $post = Post::where('post_code', $data['code'])->firstOrFail();
        $comment = Comment::create([
            'user_id' => auth()->id(),
            'record_type' => 'post',
            'record_id' => $post->id,
            'content' => $data['content'],
        ]);

        $comment->load('user:id,name,username');

        return response()->json(['status' => 'success', 'message' => 'Comment added successfully', 'comment' => $comment]);

    }

    public function delete_comment($comment_id) {
        $comment = Comment::where('id', $comment_id)->firstOrFail();
        $comment->delete();

        return response()->json(['status' => 'success', 'message' => 'Comment deleted successfully']);
    }

    public function like_unlike($postCode) {
        $post = Post::where('post_code', $postCode)->firstOrFail();
        $post->toggleLike();

        return response()->json(['status' => 'success', 'liked' => $post->liked]);
    }

    public function like_unlike_comment($comment_id) {
        $comment = Comment::where('id', $comment_id)->firstOrFail();
        $comment->toggleLike();

        return response()->json(['status' => 'success', 'liked' => $comment->liked]);
    }

    public function destroy($postCode) {
        $post = Post::where('post_code', $postCode)->firstOrFail();
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
