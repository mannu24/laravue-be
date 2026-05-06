<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portfolio Domain
    |--------------------------------------------------------------------------
    |
    | The base domain for portfolio subdomains. Portfolios are served at
    | {subdomain}.{domain}. The main app is served at {domain}.
    |
    */
    'domain' => env('PORTFOLIO_DOMAIN', 'laravue.in'),

    /*
    |--------------------------------------------------------------------------
    | Server IP
    |--------------------------------------------------------------------------
    |
    | The server IP address shown in DNS instructions for custom domains.
    |
    */
    'server_ip' => env('PORTFOLIO_SERVER_IP', ''),

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Number of days after subscription expiry before the portfolio goes offline.
    |
    */
    'grace_period_days' => (int) env('PORTFOLIO_GRACE_PERIOD_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to keep portfolio data after subscription expires
    | (beyond the grace period). After this, data is soft-deleted.
    |
    */
    'data_retention_days' => (int) env('PORTFOLIO_DATA_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Reserved Subdomains
    |--------------------------------------------------------------------------
    |
    | Subdomains that cannot be claimed by users.
    |
    */
    'reserved_subdomains' => [
        'www', 'api', 'admin', 'mail', 'ftp', 'cpanel', 'webmail',
        'staging', 'dev', 'test', 'app', 'blog', 'shop', 'store',
        'help', 'support', 'status', 'docs', 'cdn', 'static',
        'assets', 'media', 'img', 'images', 'ns1', 'ns2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain Affiliate
    |--------------------------------------------------------------------------
    |
    | Affiliate link and coupon code shown to users who need to buy a domain.
    |
    */
    'affiliate_url' => env('DOMAIN_AFFILIATE_URL', 'https://hostinger.com'),
    'affiliate_coupon' => env('DOMAIN_AFFILIATE_COUPON', 'LARAVUE'),

];
