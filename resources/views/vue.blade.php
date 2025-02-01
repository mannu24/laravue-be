<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="site_name" content="{{ env('APP_NAME', 'Laravue') }}" />
    <meta name="description" content="{{ env('APP_NAME', 'Laravue') }} is your go-to platform for Laravel and Vue.js developers. Join the community to explore Q&A, feeds, blogs, projects, and more while connecting with experts and peers.">
    <meta name="keywords" content="Laravel, Vue.js, Laravue, Developer Community, Q&A, Blogs, Projects, Profiles, Coding, Web Development, {{ env('APP_NAME', 'Laravue') }}">
    <meta name="author" content="{{ env('APP_NAME', 'Laravue') }}">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="{{ env('APP_NAME', 'Laravue') }} - The Laravel & Vue.js Developer Community">
    <meta property="og:description" content="Connect with Laravel and Vue.js enthusiasts, share knowledge, and explore blogs, Q&A, and projects in a vibrant developer community.">
    <meta property="og:image" content="{{ asset('app-assets/images/laravue-logo.png') }}">
    <meta property="og:url" content="{{ env('APP_URL', 'https://www.laravue.in') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ env('APP_NAME', 'Laravue') }} - The Laravel & Vue.js Developer Community">
    <meta name="twitter:description" content="Explore Q&A, feeds, blogs, projects, and connect with Laravel and Vue.js developers worldwide.">
    <meta name="twitter:image" content="{{ asset('app-assets/images/laravue-logo.png') }}">
    <meta name="theme-color" content="#42b883">
    <link rel="canonical" href="https://www.laravue.in">
    <title>{{ env('APP_NAME', 'Laravue') }} - The Laravel & Vue.js Developer Community</title>

    <link rel="stylesheet" href="{{ asset('assets/front/css/fa6/css/all.css') }}">
    <title>Laravue</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="frontend"></div>
</body>
</html>