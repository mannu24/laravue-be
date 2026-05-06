<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PortfolioCustomDomain;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyPortfolioDomains extends Command
{
    protected $signature = 'portfolio:verify-domains';
    protected $description = 'Auto-verify pending custom domains and check verified domains for DNS changes';

    public function handle(): int
    {
        $serverIp = config('portfolio.server_ip');
        $baseDomain = config('portfolio.domain', 'laravue.in');

        if (!$serverIp) {
            $this->warn('PORTFOLIO_SERVER_IP not configured. Skipping domain verification.');
            return Command::SUCCESS;
        }

        // Check pending domains (added more than 1 hour ago)
        $pending = PortfolioCustomDomain::where('status', 'pending')
            ->where('created_at', '<', now()->subHour())
            ->get();

        $verified = 0;
        foreach ($pending as $domain) {
            $result = $this->checkDns($domain, $serverIp, $baseDomain);
            $domain->update([
                'last_checked_at' => now(),
                'dns_error' => $result['error'],
                'status' => $result['valid'] ? 'verified' : 'pending',
                'verified_at' => $result['valid'] ? now() : null,
            ]);

            if ($result['valid']) {
                $verified++;
                $this->line("✓ Verified: {$domain->domain}");
            }
        }

        // Re-check verified domains for DNS changes
        $verifiedDomains = PortfolioCustomDomain::where('status', 'verified')
            ->where(function ($q) {
                $q->whereNull('last_checked_at')
                  ->orWhere('last_checked_at', '<', now()->subHours(24));
            })
            ->get();

        $broken = 0;
        foreach ($verifiedDomains as $domain) {
            $result = $this->checkDns($domain, $serverIp, $baseDomain);
            $domain->update([
                'last_checked_at' => now(),
                'dns_error' => $result['error'],
            ]);

            if (!$result['valid']) {
                $domain->update(['status' => 'failed']);
                $broken++;
                $this->line("✗ DNS broken: {$domain->domain} — {$result['error']}");
            }
        }

        $this->info("Verified {$verified} pending domains. Found {$broken} broken domains.");

        return Command::SUCCESS;
    }

    protected function checkDns(PortfolioCustomDomain $domain, string $serverIp, string $baseDomain): array
    {
        try {
            if ($domain->type === 'root') {
                // Check A record
                $records = dns_get_record($domain->domain, DNS_A);
                if (empty($records)) {
                    return ['valid' => false, 'error' => 'No A record found.'];
                }
                foreach ($records as $record) {
                    if ($record['ip'] === $serverIp) {
                        return ['valid' => true, 'error' => null];
                    }
                }
                $foundIp = $records[0]['ip'] ?? 'unknown';
                return ['valid' => false, 'error' => "A record points to {$foundIp} instead of {$serverIp}."];
            } else {
                // Check CNAME record
                $records = dns_get_record($domain->domain, DNS_CNAME);
                if (empty($records)) {
                    // CNAME might resolve to A record — check that too
                    $aRecords = dns_get_record($domain->domain, DNS_A);
                    if (!empty($aRecords)) {
                        foreach ($aRecords as $record) {
                            if ($record['ip'] === $serverIp) {
                                return ['valid' => true, 'error' => null];
                            }
                        }
                    }
                    return ['valid' => false, 'error' => 'No CNAME record found.'];
                }
                foreach ($records as $record) {
                    $target = rtrim($record['target'] ?? '', '.');
                    if ($target === $baseDomain || str_ends_with($target, ".{$baseDomain}")) {
                        return ['valid' => true, 'error' => null];
                    }
                }
                $foundTarget = $records[0]['target'] ?? 'unknown';
                return ['valid' => false, 'error' => "CNAME points to {$foundTarget} instead of {$baseDomain}."];
            }
        } catch (\Exception $e) {
            Log::warning("DNS check failed for {$domain->domain}", ['error' => $e->getMessage()]);
            return ['valid' => false, 'error' => 'DNS lookup failed: ' . $e->getMessage()];
        }
    }
}
