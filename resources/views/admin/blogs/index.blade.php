@extends('admin.layouts.app')

@section('title', 'Blogs')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Blogs</h3>
        <a href="{{ route('blogs.create') }}" class="btn btn-primary float-right">Add New Blog</a>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($blogs as $blog)
                <tr>
                    <td>{{ $blog->id }}</td>
                    <td>{{ $blog->title }}</td>
                    <td>{{ $blog->views }}</td>
                    <td>{{ ucfirst($blog->status) }}</td>
                    <td>
                        <a href="{{ route('blogs.edit', $blog) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('blogs.destroy', $blog) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $blogs->links() }}
    </div>
</div>
@endsection