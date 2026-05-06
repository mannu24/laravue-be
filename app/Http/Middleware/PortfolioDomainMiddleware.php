<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Portfolio;
use App\Models\PortfolioCustomDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortfolioDomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Detects if the request is for a portfolio subdomain or custom domain.
     * If so, renders the portfolio view and short-circuits normal routing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $baseDomain = strtolower(config('portfolio.domain', 'laravue.in'));

        // Skip if this is the main domain or www
        if ($host === $baseDomain || $host === "www.{$baseDomain}") {
            return $next($request);
        }

        // Check if it's a subdomain of the base domain
        if (str_ends_with($host, ".{$baseDomain}")) {
            $subdomain = str_replace(".{$baseDomain}", '', $host);

            // Skip reserved subdomains
            if (in_array($subdomain, config('portfolio.reserved_subdomains', []))) {
                return $next($request);
            }

            $portfolio = Portfolio::where('subdomain', $subdomain)->first();

            return $this->renderPortfolio($portfolio, $request);
        }

        // Check if it's a custom domain
        $customDomain = PortfolioCustomDomain::where('domain', $host)
            ->where('status', 'verified')
            ->first();

        if ($customDomain) {
            $portfolio = $customDomain->portfolio;
            return $this->renderPortfolio($portfolio, $request);
        }

        // Not a portfolio request — continue to main app
        return $next($request);
    }

    /**
     * Render a portfolio or an appropriate error page.
     */
    protected function renderPortfolio(?Portfolio $portfolio, Request $request): Response
    {
        if (!$portfolio) {
            return response()->view('portfolio.errors.not-found', [], 404);
        }

        if (!$portfolio->is_published) {
            return response()->view('portfolio.errors.not-published', [], 404);
        }

        if (!$portfolio->hasActiveAccess()) {
            return response()->view('portfolio.errors.expired', [
                'portfolio' => $portfolio,
            ], 403);
        }

        // Load all portfolio data
        $portfolio->load([
            'socialLinks',
            'skills',
            'experiences',
            'educations',
            'projects',
            'testimonials',
            'customSections',
            'user',
        ]);

        $templateSlug = $portfolio->template_slug ?? 'minimal';
        $viewName = "portfolio.templates.{$templateSlug}.index";

        // Fallback to minimal if template view doesn't exist
        if (!view()->exists($viewName)) {
            $viewName = 'portfolio.templates.minimal.index';
        }

        $isGracePeriod = $portfolio->isInGracePeriod();

        return response()->view($viewName, [
            'portfolio' => $portfolio,
            'isGracePeriod' => $isGracePeriod,
        ]);
    }
}
