<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query()->with(['user:id,name,username'])->withCount('likes', 'comments');

        if ($request->has('username') && $request->username) {
            $query->whereHas('user', function ($q) {
                $q->where('username', request()->input('username'));
            });
        }

        $posts = $query->latest()->get()->each(function ($q) {
            $q->views_count = 10;
            $q->makeHidden('media', 'id', 'user_id', 'meta_content');
            $q->user->makeHidden('id', 'media');
        });

        $page = $_GET['page'];
        $perPage = 2;
        $paginatedData = new LengthAwarePaginator($posts->forPage($page, $perPage)->values(), $posts->count(), $perPage);

        return response()->json(['status' => 'success', 'records' => $paginatedData]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:500',
            'content' => 'required|string',
            'mentions' => 'array',
            'mentions.*.id' => 'required|string|exists:users,id',
            'mentions.*.username' => 'required|string|exists:users,username',
            'media' => [
                'nullable',
                'array',
                'max:5'
            ],
            'media.*' => [
                'file',
                'mimes:jpg,jpeg,png,svg,webp',
                'max:2048'
            ]
        ]);

        DB::beginTransaction();
        try {
            $mentions = isset($data['mentions']) ? $data['mentions'] : [];
            $post = Post::create([
                'user_id' => auth()->id(),
                'post_code' => uniqid('post_'),
                'title' => $data['title'],
                'content' => $this->processContent($data['content'], $mentions),
                'meta_content' => json_encode($mentions),
                'is_ai_generated' => $request->is_ai_generated ?? 0,
                'is_blocked' => $request->is_blocked ?? 0,
            ]);
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $post->addMedia($file)->toMediaCollection('images');
                }
            }
            
            // Send notifications to mentioned users
            if (!empty($mentions)) {
                $postUrl = url("/posts/{$post->post_code}");
                foreach ($mentions as $mention) {
                    NotificationService::create(
                        userId: $mention['id'],
                        type: Notification::TYPE_MENTIONED,
                        title: 'You were mentioned in a post',
                        message: auth()->user()->name . ' mentioned you in a post',
                        subject: $post,
                        notifiableId: auth()->id(),
                        data: ['url' => $postUrl, 'post_title' => $post->title],
                        sendEmail: true,
                        emailBlade: 'emails.notification',
                        emailSubject: 'Someone mentioned you in a post'
                    );
                }
            }
            
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Post created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error Occured']);
        }
    }

    private function processContent($content, $mentions)
    {
        $cleanContent = Str::of($content)
            // ->stripTags(config('feed.allowed_tags'))
            ->replaceMatches('/<script.*?>.*?<\/script>/si', '');

        return preg_replace_callback('/@\[([^\]]+)\]\((\d+)\)/', function ($matches) use ($mentions) {
            $userId = $matches[2];
            $mention = collect($mentions)->firstWhere('id', $userId);
            return $mention ? "<mention data-user-id='{$userId}'>@{$mention['username']}</mention>" : $matches[0];
        }, $cleanContent);
    }

    public function mentionSuggestions(Request $request)
    {
        $query = $request->input('q', '');

        return User::where('username', 'like', $query . '%')
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

    public function show($post_code)
    {
        $post = Post::where('post_code', $post_code)->firstOrFail();
        if ($post) {
            $post->load(['user:id,name,username', 'comments' => function ($q) {
                $q->withCount('likes');
            }, 'comments.user:id,name,username'])->loadCount('likes', 'comments');
            $post->views_count = 10;
            $post->makeHidden('media', 'id', 'user_id', 'meta_content');
            $post->user->makeHidden('id', 'media');

            return response()->json($post);
        }
    }

    public function update(Request $request, $post_code)
    {
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

        if (!$request->has('mentions')) $data['mentions'] = [];

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
            
            // Send notifications to newly mentioned users
            if (!empty($data['mentions'])) {
                $existingMentions = json_decode($post->meta_content, true) ?? [];
                $existingUserIds = collect($existingMentions)->pluck('id')->toArray();
                $newMentions = collect($data['mentions'])->filter(function ($mention) use ($existingUserIds) {
                    return !in_array($mention['id'], $existingUserIds);
                });
                
                $postUrl = url("/posts/{$post->post_code}");
                foreach ($newMentions as $mention) {
                    NotificationService::create(
                        userId: $mention['id'],
                        type: Notification::TYPE_MENTIONED,
                        title: 'You were mentioned in a post',
                        message: auth()->user()->name . ' mentioned you in a post',
                        subject: $post,
                        notifiableId: auth()->id(),
                        data: ['url' => $postUrl, 'post_title' => $post->title],
                        sendEmail: true,
                        emailBlade: 'emails.notification',
                        emailSubject: 'Someone mentioned you in a post'
                    );
                }
            }
            
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Post updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return response()->json(['status' => 'error', 'message' => 'Error Occured']);
        }
    }

    public function duplicate($postCode)
    {
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

    public function add_comment(Request $request)
    {
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

        // Notify post owner (if not commenting on own post)
        if ($post->user_id !== auth()->id()) {
            $postUrl = url("/posts/{$post->post_code}");
            NotificationService::create(
                userId: $post->user_id,
                type: Notification::TYPE_POST_COMMENTED,
                title: 'New comment on your post',
                message: auth()->user()->name . ' commented on your post',
                subject: $comment,
                notifiableId: auth()->id(),
                data: ['url' => $postUrl, 'post_title' => $post->title],
                sendEmail: true,
                emailBlade: 'emails.notification',
                emailSubject: 'You have a new comment on your post'
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Comment added successfully', 'comment' => $comment]);
    }

    public function delete_comment($comment_id)
    {
        $comment = Comment::where('id', $comment_id)->firstOrFail();
        $comment->delete();

        return response()->json(['status' => 'success', 'message' => 'Comment deleted successfully']);
    }

    public function like_unlike($postCode)
    {
        $post = Post::where('post_code', $postCode)->firstOrFail();
        $wasLiked = $post->liked;
        $isLiked = $post->toggleLike();
        $post->refresh();
        $post->liked = $isLiked;

        // Notify post owner when liked (if not liking own post)
        if ($isLiked && !$wasLiked && $post->user_id !== auth()->id()) {
            $postUrl = url("/posts/{$post->post_code}");
            NotificationService::create(
                userId: $post->user_id,
                type: Notification::TYPE_POST_LIKED,
                title: 'Your post was liked',
                message: auth()->user()->name . ' liked your post',
                subject: $post,
                notifiableId: auth()->id(),
                data: ['url' => $postUrl, 'post_title' => $post->title],
                sendEmail: true,
                emailBlade: 'emails.notification',
                emailSubject: 'Someone liked your post'
            );
        }

        return response()->json(['status' => 'success', 'liked' => $post->liked]);
    }

    public function like_unlike_comment($comment_id)
    {
        $comment = Comment::where('id', $comment_id)->firstOrFail();
        $wasLiked = $comment->liked;
        $isLiked = $comment->toggleLike();
        $comment->refresh();
        $comment->liked = $isLiked;

        // Notify comment owner when liked (if not liking own comment)
        if ($isLiked && !$wasLiked && $comment->user_id !== auth()->id()) {
            $post = Post::find($comment->record_id);
            $postUrl = $post ? url("/posts/{$post->post_code}") : '#';
            NotificationService::create(
                userId: $comment->user_id,
                type: Notification::TYPE_COMMENT_LIKED,
                title: 'Your comment was liked',
                message: auth()->user()->name . ' liked your comment',
                subject: $comment,
                notifiableId: auth()->id(),
                data: ['url' => $postUrl],
                sendEmail: true,
                emailBlade: 'emails.notification',
                emailSubject: 'Someone liked your comment'
            );
        }

        return response()->json(['status' => 'success', 'liked' => $comment->liked]);
    }

    public function destroy($postCode)
    {
        $post = Post::where('post_code', $postCode)->firstOrFail();
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
