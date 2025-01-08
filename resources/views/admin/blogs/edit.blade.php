@extends('admin.layouts.app')

@section('title', 'Edit Blog')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Blog</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ $blog->title }}" required>
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" name="slug" id="slug" class="form-control" value="{{ $blog->slug }}" required>
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea name="content" id="content" rows="5" class="form-control" required>{{ $blog->content }}</textarea>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="published" {{ $blog->status == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ $blog->status == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" name="image" id="image" class="form-control">
                @if ($blog->image)
                    <img src="{{ asset('storage/' . $blog->image) }}" width="100" class="mt-2">
                @endif
            </div>
            <button type="submit" class="btn btn-warning">Update Blog</button>
        </form>
    </div>
</div>
@endsection