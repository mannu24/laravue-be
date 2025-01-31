<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Str;

class PostController extends Controller
{
    public function index(Request $request) {
        $query = Post::query()->with(['user:id,name,username']);

        $loggedInCheck = auth()->guard('api')->check() ;
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'LIKE', "%$search%")->orWhere('content', 'LIKE', "%$search%");
        }

        $posts = $query->latest()->get()->each(function ($q) use($loggedInCheck) {
            if($loggedInCheck) {
                $q->owner = $q->user_id === auth()->guard('api')->id();
            } else {
                $q->owner = false;
            }
            $q->append('media_urls')->makeHidden('media','id','user_id','meta_content');
            $q->user->makeHidden('id','media');
        });

        $page = $_GET['page'];
        $perPage = 2;
        $paginatedData = new LengthAwarePaginator($posts->forPage($page, $perPage)->values(), $posts->count(), $perPage);

        return response()->json(['status' => 'success', 'posts' => $paginatedData]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'mentions' => 'array',
            'mentions.*.id' => 'required|string|exists:users,id',
            'mentions.*.username' => 'required|string|exists:users,username',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:5092',
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
        return response()->json($post);
    }

    public function update(Request $request, $post_code) {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:1000',
            'content' => 'nullable',
            'meta_content' => 'nullable|string',
            'is_ai_generated' => 'boolean',
            'is_blocked' => 'boolean',
        ]);

        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $post = Post::where('post_code', $post_code)->firstOrFail();
        $post->update($request->only([
            'title',
            'content',
            'meta_content',
            'post_code',
            'is_ai_generated',
            'is_blocked',
        ]));

        return response()->json(['message' => 'Post updated successfully', 'post' => $post]);
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

    public function like_unlike($postCode) {
        $post = Post::where('post_code', $postCode)->firstOrFail();
        $post->toggleLike(auth()->id());
        $status = $post->auth_like() ? 'Liked' : 'Unliked' ;
        
        return response()->json(['message' => 'Post '.$status.' successfully']);
    }

    public function destroy($postCode) {
        $post = Post::where('post_code', $postCode)->firstOrFail();
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
