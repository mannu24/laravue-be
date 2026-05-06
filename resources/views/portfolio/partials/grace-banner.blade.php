@if($isGracePeriod ?? false)
<div style="background: #f59e0b; color: #0f172a; text-align: center; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;">
    ⚠️ This portfolio's subscription is expiring soon.
    <a href="https://{{ config('portfolio.domain') }}/portfolio/plans" style="color: #0f172a; text-decoration: underline; margin-left: 0.5rem;">Renew now</a>
</div>
@endif
