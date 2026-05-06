<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\Portfolio;
use App\Models\PortfolioCustomDomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioCustomDomainController extends Controller
{
    use HttpResponse;

    /**
     * Get custom domain status and DNS instructions.
     */
    public function status(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();
        $domain = $portfolio->customDomain;

        // Check if user's plan allows custom domains
        $subscription = $request->user()->activePortfolioSubscription;
        $allowsCustomDomain = $subscription?->plan?->allows_custom_domain ?? false;

        return $this->success([
            'domain' => $domain,
            'allows_custom_domain' => $allowsCustomDomain,
            'server_ip' => config('portfolio.server_ip'),
            'base_domain' => config('portfolio.domain'),
            'affiliate_url' => config('portfolio.affiliate_url'),
            'affiliate_coupon' => config('portfolio.affiliate_coupon'),
        ]);
    }

    /**
     * Add a custom domain.
     */
    public function store(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();

        // Check plan
        $subscription = $request->user()->activePortfolioSubscription;
        if (!$subscription?->plan?->allows_custom_domain) {
            return $this->error(null, 'Custom domains require a Pro or Annual plan.', 403);
        }

        // Check if already has a domain
        if ($portfolio->customDomain) {
            return $this->error(null, 'You already have a custom domain configured. Remove it first to add a new one.', 422);
        }

        $validated = $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*\.[a-z]{2,}$/i',
                'unique:portfolio_custom_domains,domain',
            ],
        ], [
            'domain.regex' => 'Please enter a valid domain name (e.g., example.com or portfolio.example.com).',
            'domain.unique' => 'This domain is already in use by another portfolio.',
        ]);

        $domain = strtolower($validated['domain']);
        $baseDomain = config('portfolio.domain', 'laravue.in');

        // Block adding the base domain itself
        if ($domain === $baseDomain || str_ends_with($domain, ".{$baseDomain}")) {
            return $this->error(null, 'You cannot use a laravue.in domain as a custom domain.', 422);
        }

        // Determine type: root domain or subdomain
        $parts = explode('.', $domain);
        $type = count($parts) > 2 ? 'subdomain' : 'root';

        $customDomain = PortfolioCustomDomain::create([
            'portfolio_id' => $portfolio->id,
            'domain' => $domain,
            'type' => $type,
            'status' => 'pending',
        ]);

        $serverIp = config('portfolio.server_ip');

        return $this->success([
            'domain' => $customDomain,
            'dns_instructions' => $type === 'root'
                ? [
                    'type' => 'A',
                    'host' => '@',
                    'value' => $serverIp,
                    'ttl' => 3600,
                ]
                : [
                    'type' => 'CNAME',
                    'host' => explode('.', $domain)[0],
                    'value' => $baseDomain,
                    'ttl' => 3600,
                ],
        ], 'Custom domain added. Configure your DNS records and then verify.');
    }

    /**
     * Trigger manual domain verification.
     */
    public function verify(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();
        $domain = $portfolio->customDomain;

        if (!$domain) {
            return $this->error(null, 'No custom domain configured.', 404);
        }

        $serverIp = config('portfolio.server_ip');
        $baseDomain = config('portfolio.domain', 'laravue.in');

        if (!$serverIp) {
            return $this->error(null, 'Server IP not configured. Contact support.', 500);
        }

        $result = $this->checkDns($domain, $serverIp, $baseDomain);

        $domain->update([
            'last_checked_at' => now(),
            'dns_error' => $result['error'],
            'status' => $result['valid'] ? 'verified' : 'pending',
            'verified_at' => $result['valid'] ? now() : $domain->verified_at,
        ]);

        if ($result['valid']) {
            return $this->success($domain->fresh(), 'Domain verified! Your portfolio is now accessible at ' . $domain->domain);
        }

        return $this->error(null, $result['error'] ?? 'DNS records not found. Please check your DNS configuration and try again.', 422);
    }

    /**
     * Remove custom domain.
     */
    public function destroy(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();
        $domain = $portfolio->customDomain;

        if (!$domain) {
            return $this->error(null, 'No custom domain configured.', 404);
        }

        $domain->delete();

        return $this->success(null, 'Custom domain removed.');
    }

    protected function checkDns(PortfolioCustomDomain $domain, string $serverIp, string $baseDomain): array
    {
        try {
            if ($domain->type === 'root') {
                $records = @dns_get_record($domain->domain, DNS_A);
                if (empty($records)) {
                    return ['valid' => false, 'error' => 'No A record found for ' . $domain->domain . '. Add an A record pointing to ' . $serverIp];
                }
                foreach ($records as $record) {
                    if (($record['ip'] ?? '') === $serverIp) {
                        return ['valid' => true, 'error' => null];
                    }
                }
                return ['valid' => false, 'error' => 'A record points to ' . ($records[0]['ip'] ?? 'unknown') . ' instead of ' . $serverIp];
            } else {
                $records = @dns_get_record($domain->domain, DNS_CNAME);
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $target = rtrim($record['target'] ?? '', '.');
                        if ($target === $baseDomain) {
                            return ['valid' => true, 'error' => null];
                        }
                    }
                    return ['valid' => false, 'error' => 'CNAME points to ' . rtrim($records[0]['target'] ?? 'unknown', '.') . ' instead of ' . $baseDomain];
                }
                // Fallback: check A record
                $aRecords = @dns_get_record($domain->domain, DNS_A);
                if (!empty($aRecords)) {
                    foreach ($aRecords as $record) {
                        if (($record['ip'] ?? '') === $serverIp) {
                            return ['valid' => true, 'error' => null];
                        }
                    }
                }
                return ['valid' => false, 'error' => 'No CNAME record found. Add a CNAME record pointing to ' . $baseDomain];
            }
        } catch (\Exception $e) {
            return ['valid' => false, 'error' => 'DNS lookup failed. Please try again in a few minutes.'];
        }
    }
}
