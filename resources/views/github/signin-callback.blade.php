<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Sign In</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #111; color: #fff; }
        .container { text-align: center; padding: 2rem; }
        .spinner { width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.2); border-top-color: #41B883; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
        .error { color: #ef4444; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container">
        @if($code)
            <div class="spinner"></div>
            <p>Signing you in...</p>
            <script>
                if (window.opener) {
                    window.opener.postMessage({ type: 'github-signin', code: '{{ $code }}' }, '*');
                    setTimeout(() => window.close(), 1500);
                } else {
                    document.querySelector('.container').innerHTML = '<p>Please close this window and try again.</p>';
                }
            </script>
        @else
            <div class="error">
                <h2>Sign In Failed</h2>
                <p>{{ $error ?? 'Please try again.' }}</p>
            </div>
            <script>
                if (window.opener) {
                    window.opener.postMessage({ type: 'github-signin-error', message: '{{ $error ?? "Sign in failed" }}' }, '*');
                }
                setTimeout(() => window.close(), 3000);
            </script>
        @endif
    </div>
</body>
</html>
