@extends('admin.layouts.app')

@section('title', 'View Blog')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $blog->title }}</h3>
    </div>
    <div class="card-body">
        <p><strong>Slug:</strong> {{ $blog->slug }}</p>
        <p><strong>Description:</strong> {{ $blog->description }}</p>
        <p><strong>Content:</strong> {!! $blog->content !!}</p>
        @if ($blog->image)
            <img src="{{ asset('storage/' . $blog->image) }}" width="200">
        @endif
    </div>
</div>
@endsection