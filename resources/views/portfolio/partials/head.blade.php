<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $portfolio->meta_title ?? $portfolio->title ?? $portfolio->user->name }}</title>
<meta name="description" content="{{ $portfolio->meta_description ?? $portfolio->tagline ?? '' }}">
<meta property="og:title" content="{{ $portfolio->meta_title ?? $portfolio->title ?? $portfolio->user->name }}">
<meta property="og:description" content="{{ $portfolio->meta_description ?? $portfolio->tagline ?? '' }}">
@if($portfolio->og_image_path)
<meta property="og:image" content="{{ asset($portfolio->og_image_path) }}">
@elseif($portfolio->photo_path)
<meta property="og:image" content="{{ asset($portfolio->photo_path) }}">
@endif
<meta property="og:type" content="profile">
<meta name="twitter:card" content="summary_large_image">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@if($isGracePeriod ?? false)
<!-- Grace period banner injected -->
@endif
