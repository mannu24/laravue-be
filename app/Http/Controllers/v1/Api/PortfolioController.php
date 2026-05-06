<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\Portfolio;
use App\Models\PortfolioPlan;
use App\Models\PortfolioTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    use HttpResponse;

    /**
     * Get the authenticated user's portfolio with all sections.
     */
    public function show(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)
            ->with([
                'socialLinks',
                'skills',
                'experiences',
                'educations',
                'projects',
                'testimonials',
                'customSections',
                'customDomain',
            ])
            ->first();

        if (!$portfolio) {
            return $this->success(null, 'No portfolio found.');
        }

        $subscription = $request->user()->activePortfolioSubscription;

        return $this->success([
            'portfolio' => $portfolio,
            'subscription' => $subscription?->load('plan'),
            'subdomain_url' => $portfolio->subdomain_url,
        ]);
    }

    /**
     * Create a new portfolio.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user already has a portfolio
        if (Portfolio::where('user_id', $user->id)->exists()) {
            return $this->error(null, 'You already have a portfolio.', 422);
        }

        $validated = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
                'unique:portfolios,subdomain',
                Rule::notIn(config('portfolio.reserved_subdomains', [])),
            ],
            'template_slug' => 'sometimes|string|exists:portfolio_templates,slug',
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens. Cannot start or end with a hyphen.',
            'subdomain.not_in' => 'This subdomain is reserved and cannot be used.',
        ]);

        $portfolio = Portfolio::create([
            'user_id' => $user->id,
            'subdomain' => strtolower($validated['subdomain']),
            'template_slug' => $validated['template_slug'] ?? 'minimal',
            'title' => $user->name,
        ]);

        return $this->success([
            'portfolio' => $portfolio,
            'subdomain_url' => $portfolio->subdomain_url,
        ], 'Portfolio created successfully.');
    }

    /**
     * Update portfolio profile data.
     */
    public function update(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'tagline' => 'sometimes|nullable|string|max:255',
            'bio' => 'sometimes|nullable|string|max:2000',
            'location_city' => 'sometimes|nullable|string|max:100',
            'location_country' => 'sometimes|nullable|string|max:100',
            'available_for_hire' => 'sometimes|boolean',
            'meta_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string|max:500',
        ]);

        $portfolio->update($validated);

        return $this->success($portfolio->fresh(), 'Portfolio updated.');
    }

    /**
     * Switch template.
     */
    public function updateTemplate(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'template_slug' => 'required|string|exists:portfolio_templates,slug',
        ]);

        $template = PortfolioTemplate::where('slug', $validated['template_slug'])->firstOrFail();

        // Check if template is premium and user has Starter plan
        if ($template->is_premium) {
            $subscription = $request->user()->activePortfolioSubscription;
            if (!$subscription || $subscription->plan->slug === 'starter') {
                return $this->error(null, 'This template requires a Pro or Annual plan.', 403);
            }
        }

        $portfolio->update(['template_slug' => $validated['template_slug']]);

        return $this->success($portfolio->fresh(), 'Template updated.');
    }

    /**
     * Publish portfolio.
     */
    public function publish(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();

        // Must have active subscription
        if (!$portfolio->hasActiveAccess()) {
            return $this->error(null, 'You need an active subscription to publish your portfolio.', 403);
        }

        $portfolio->update(['is_published' => true]);

        return $this->success([
            'portfolio' => $portfolio->fresh(),
            'subdomain_url' => $portfolio->subdomain_url,
        ], 'Portfolio published!');
    }

    /**
     * Unpublish portfolio.
     */
    public function unpublish(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();
        $portfolio->update(['is_published' => false]);

        return $this->success($portfolio->fresh(), 'Portfolio unpublished.');
    }

    /**
     * Check subdomain availability.
     */
    public function checkSubdomain(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => 'required|string|min:3|max:30',
        ]);

        $subdomain = strtolower($request->subdomain);

        // Check format
        if (!preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $subdomain) && strlen($subdomain) > 2) {
            return $this->success(['available' => false, 'reason' => 'Invalid format. Use lowercase letters, numbers, and hyphens only.']);
        }

        // Check reserved
        if (in_array($subdomain, config('portfolio.reserved_subdomains', []))) {
            return $this->success(['available' => false, 'reason' => 'This subdomain is reserved.']);
        }

        // Check taken
        if (Portfolio::where('subdomain', $subdomain)->exists()) {
            return $this->success(['available' => false, 'reason' => 'This subdomain is already taken.']);
        }

        return $this->success(['available' => true]);
    }

    /**
     * Preview portfolio (returns rendered HTML).
     */
    public function preview(Request $request): \Illuminate\Http\Response
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)
            ->with([
                'socialLinks', 'skills', 'experiences', 'educations',
                'projects', 'testimonials', 'customSections', 'user',
            ])
            ->firstOrFail();

        $templateSlug = $portfolio->template_slug ?? 'minimal';
        $viewName = "portfolio.templates.{$templateSlug}.index";

        if (!view()->exists($viewName)) {
            $viewName = 'portfolio.templates.minimal.index';
        }

        return response()->view($viewName, [
            'portfolio' => $portfolio,
            'isGracePeriod' => false,
        ]);
    }

    /**
     * List available templates.
     */
    public function templates(): JsonResponse
    {
        $templates = PortfolioTemplate::active()->get();
        return $this->success($templates);
    }

    /**
     * List available plans.
     */
    public function plans(): JsonResponse
    {
        $plans = PortfolioPlan::active()->get();
        return $this->success($plans);
    }

    /**
     * Delete portfolio (soft — just unpublish and clear).
     */
    public function destroy(Request $request): JsonResponse
    {
        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();

        // Delete all related data
        $portfolio->socialLinks()->delete();
        $portfolio->skills()->delete();
        $portfolio->experiences()->delete();
        $portfolio->educations()->delete();
        $portfolio->projects()->delete();
        $portfolio->testimonials()->delete();
        $portfolio->customSections()->delete();
        $portfolio->customDomain()->delete();
        $portfolio->delete();

        return $this->success(null, 'Portfolio deleted.');
    }
}
