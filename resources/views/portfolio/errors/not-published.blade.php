<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Not Available</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { text-align: center; padding: 2rem; }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
        h1 { font-size: 2rem; font-weight: 700; }
        p { font-size: 1.1rem; color: #94a3b8; margin-top: 0.75rem; }
        a { display: inline-block; margin-top: 2rem; padding: 0.75rem 2rem; background: #41B883; color: white; text-decoration: none; border-radius: 0.5rem; font-weight: 600; transition: opacity 0.2s; }
        a:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚧</div>
        <h1>Portfolio Under Construction</h1>
        <p>This portfolio hasn't been published yet. Check back soon!</p>
        <a href="https://{{ config('portfolio.domain') }}">Go to LaraVue</a>
    </div>
</body>
</html>
