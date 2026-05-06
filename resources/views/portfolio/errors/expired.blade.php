<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Subscription Expired</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { text-align: center; padding: 2rem; max-width: 500px; }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
        h1 { font-size: 2rem; font-weight: 700; }
        p { font-size: 1.1rem; color: #94a3b8; margin-top: 0.75rem; line-height: 1.6; }
        .actions { margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        a { padding: 0.75rem 2rem; text-decoration: none; border-radius: 0.5rem; font-weight: 600; transition: opacity 0.2s; }
        a:hover { opacity: 0.9; }
        .btn-primary { background: #41B883; color: white; }
        .btn-secondary { background: transparent; color: #94a3b8; border: 1px solid #334155; }
        .affiliate { margin-top: 2.5rem; padding: 1.5rem; background: #1e293b; border-radius: 0.75rem; border: 1px solid #334155; }
        .affiliate p { font-size: 0.9rem; }
        .affiliate a { display: inline-block; margin-top: 0.75rem; padding: 0.5rem 1.5rem; background: #f59e0b; color: #0f172a; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⏰</div>
        <h1>Subscription Expired</h1>
        <p>This portfolio's subscription has expired. The owner can renew it to bring it back online.</p>
        <div class="actions">
            <a href="https://{{ config('portfolio.domain') }}/portfolio/plans" class="btn-primary">Renew Subscription</a>
            <a href="https://{{ config('portfolio.domain') }}" class="btn-secondary">Go to LaraVue</a>
        </div>
        @if(config('portfolio.affiliate_url'))
        <div class="affiliate">
            <p>🌐 Need your own domain? Get one starting at ₹149/year.</p>
            <a href="{{ config('portfolio.affiliate_url') }}" target="_blank" rel="noopener noreferrer">Buy a Domain →</a>
            @if(config('portfolio.affiliate_coupon'))
            <p style="margin-top: 0.5rem; font-size: 0.8rem;">Use code <strong>{{ config('portfolio.affiliate_coupon') }}</strong> for a discount!</p>
            @endif
        </div>
        @endif
    </div>
</body>
</html>
