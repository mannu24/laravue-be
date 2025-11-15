<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Connection</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        .success {
            color: #10b981;
        }
        .error {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($status === 'success')
            <div class="success">
                <h2>✓ GitHub Connected Successfully!</h2>
                <p>You can close this window now.</p>
            </div>
            <script>
                // Notify parent window
                if (window.opener) {
                    window.opener.postMessage({
                        type: 'github-connected',
                        username: '{{ $github_username ?? "" }}'
                    }, '*');
                }
                // Auto close after 2 seconds
                setTimeout(() => {
                    window.close();
                }, 2000);
            </script>
        @else
            <div class="error">
                <h2>✗ Connection Failed</h2>
                <p>{{ $message ?? 'Please try again.' }}</p>
            </div>
            <script>
                // Notify parent window of error
                if (window.opener) {
                    window.opener.postMessage({
                        type: 'github-error',
                        message: '{{ $message ?? "Connection failed" }}'
                    }, '*');
                }
                setTimeout(() => {
                    window.close();
                }, 3000);
            </script>
        @endif
    </div>
</body>
</html>

