<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Str;

class PostController extends Controller
{
    public function index(Request $request) {
        $query = Post::query()->with(['user:id,name,username']);
        $user = auth()->guard('api')->user() ;
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('title', 'LIKE', "%$search%")->orWhere('content', 'LIKE', "%$search%");
        }

        $posts = $query->latest()->get()->each(function ($q) {
            $q->append('media_urls')->makeHidden('media','id','user_id','meta_content');
            $q->user->makeHidden('id','media');
        });

        $page = $_GET['page'];
        $perPage = 2;
        $paginatedData = new LengthAwarePaginator($posts->forPage($page, $perPage)->values(), $posts->count(), $perPage);

        return response()->json(['status' => 'success', 'posts' => $paginatedData]);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:1000',
            'content' => 'required',
            'media' => 'nullable|array',
            'media.*' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:5092',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        DB::beginTransaction();
        try {
            $post = Post::create([
                'user_id' => $request->user()->id,
                'post_code' => $this->generatePostCode($request->title),
                'title' => $request->title,
                'content' => $request->content,
                'meta_content' => '',
                'is_ai_generated' => $request->is_ai_generated ?? 0,
                'is_blocked' => $request->is_blocked ?? 0,
            ]);
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $post->addMedia($file)->toMediaCollection('images');
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Post created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e) ;
            return response()->json(['status' => 'error', 'message' => 'Error Occured']);
        }
    
    }
    
    private function generatePostCode($title) {
        $slug = Str::slug($title, '-');
        $uniqueSlug = $slug;
        $count = 1;
    
        while (Post::where('post_code', $uniqueSlug)->exists()) {
            $uniqueSlug = $slug . '-' . $count;
            $count++;
        }
    
        return $uniqueSlug;
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

    public function destroy(Post $post) {
        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
