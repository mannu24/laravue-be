<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioSectionController extends Controller
{
    use HttpResponse;

    protected function getPortfolio(Request $request): Portfolio
    {
        return Portfolio::where('user_id', $request->user()->id)->firstOrFail();
    }

    /**
     * Bulk update social links.
     */
    public function updateSocialLinks(Request $request): JsonResponse
    {
        $portfolio = $this->getPortfolio($request);

        $validated = $request->validate([
            'links' => 'present|array|max:8',
            'links.*.platform' => 'required|string|max:50',
            'links.*.url' => 'required|url|max:500',
        ]);

        $portfolio->socialLinks()->delete();

        foreach ($validated['links'] as $i => $link) {
            $portfolio->socialLinks()->create([
                'platform' => $link['platform'],
                'url' => $link['url'],
                'sort_order' => $i,
            ]);
        }

        return $this->success($portfolio->socialLinks()->get(), 'Social links updated.');
    }

    /**
     * Bulk update skills.
     */
    public function updateSkills(Request $request): JsonResponse
    {
        $portfolio = $this->getPortfolio($request);

        $validated = $request->validate([
            'skills' => 'present|array|max:30',
            'skills.*.name' => 'required|string|max:100',
            'skills.*.proficiency' => 'nullable|in:beginner,intermediate,advanced,expert',
        ]);

        $portfolio->skills()->delete();

        foreach ($validated['skills'] as $i => $skill) {
            $portfolio->skills()->create([
                'name' => $skill['name'],
                'proficiency' => $skill['proficiency'] ?? null,
                'sort_order' => $i,
            ]);
        }

        return $this->success($portfolio->skills()->get(), 'Skills updated.');
    }

    /**
     * Bulk update experience.
     */
    public function updateExperience(Request $request): JsonResponse
    {
        $portfolio = $this->getPortfolio($request);

        $validated = $request->validate([
            'experience' => 'present|array|max:10',
            'experience.*.company' => 'required|string|max:255',
            'experience.*.role' => 'required|string|max:255',
            'experience.*.description' => 'nullable|string|max:500',
            'experience.*.start_date' => 'required|date',
            'experience.*.end_date' => 'nullable|date|after_or_equal:experience.*.start_date',
            'experience.*.is_current' => 'sometimes|boolean',
        ]);

        $portfolio->experiences()->delete();

        foreach ($validated['experience'] as $i => $exp) {
            $portfolio->experiences()->create([
                'company' => $exp['company'],
                'role' => $exp['role'],
                'description' => $exp['description'] ?? null,
                'start_date' => $exp['start_date'],
                'end_date' => $exp['end_date'] ?? null,
                'is_current' => $exp['is_current'] ?? false,
                'sort_order' => $i,
            ]);
        }

        return $this->success($portfolio->experiences()->get(), 'Experience updated.');
    }

    /**
     * Bulk update education.
     */
    public function updateEducation(Request $request): JsonResponse
    {
        $portfolio = $this->getPortfolio($request);

        $validated = $request->validate([
            'education' => 'present|array|max:5',
            'education.*.institution' => 'required|string|max:255',
            'education.*.degree' => 'nullable|string|max:255',
            'education.*.field' => 'nullable|string|max:255',
            'education.*.start_year' => 'nullable|integer|min:1950|max:2100',
            'education.*.end_year' => 'nullable|integer|min:1950|max:2100',
        ]);

        $portfolio->educations()->delete();

        foreach ($validated['education'] as $i => $edu) {
            $portfolio->educations()->create([
                'institution' => $edu['institution'],
                'degree' => $edu['degree'] ?? null,
                'field' => $edu['field'] ?? null,
                'start_year' => $edu['start_year'] ?? null,
                'end_year' => $edu['end_year'] ?? null,
                'sort_order' => $i,
            ]);
        }

        return $this->success($portfolio->educations()->get(), 'Education updated.');
    }

    /**
     * Bulk update projects.
     */
    public function updateProjects(Request $request): JsonResponse
    {
        $portfolio = $this->getPortfolio($request);

        // Check project limit based on subscription plan
        $subscription = $request->user()->activePortfolioSubscription;
        $maxProjects = $subscription?->plan?->max_projects;

        $validated = $request->validate([
            'projects' => 'present|array' . ($maxProjects ? "|max:{$maxProjects}" : ''),
            'projects.*.project_id' => 'nullable|integer|exists:projects,id',
            'projects.*.title' => 'required|string|max:255',
            'projects.*.description' => 'nullable|string|max:1000',
            'projects.*.image_path' => 'nullable|string|max:500',
            'projects.*.tech_stack' => 'nullable|array|max:10',
            'projects.*.tech_stack.*' => 'string|max:50',
            'projects.*.live_url' => 'nullable|url|max:500',
            'projects.*.source_url' => 'nullable|url|max:500',
        ]);

        $portfolio->projects()->delete();

        foreach ($validated['projects'] as $i => $proj) {
            $portfolio->projects()->create([
                'project_id' => $proj['project_id'] ?? null,
                'title' => $proj['title'],
                'description' => $proj['description'] ?? null,
                'image_path' => $proj['image_path'] ?? null,
                'tech_stack' => $proj['tech_stack'] ?? null,
                'live_url' => $proj['live_url'] ?? null,
                'source_url' => $proj['source_url'] ?? null,
                'sort_order' => $i,
            ]);
        }

        return $this->success($portfolio->projects()->get(), 'Projects updated.');
    }

    /**
     * Bulk update testimonials.
     */
    public function updateTestimonials(Request $request): JsonResponse
    {
        $portfolio = $this->getPortfolio($request);

        $validated = $request->validate([
            'testimonials' => 'present|array|max:5',
            'testimonials.*.author_name' => 'required|string|max:255',
            'testimonials.*.author_role' => 'nullable|string|max:255',
            'testimonials.*.author_company' => 'nullable|string|max:255',
            'testimonials.*.content' => 'required|string|max:500',
            'testimonials.*.author_photo_url' => 'nullable|url|max:500',
        ]);

        $portfolio->testimonials()->delete();

        foreach ($validated['testimonials'] as $i => $t) {
            $portfolio->testimonials()->create([
                'author_name' => $t['author_name'],
                'author_role' => $t['author_role'] ?? null,
                'author_company' => $t['author_company'] ?? null,
                'content' => $t['content'],
                'author_photo_url' => $t['author_photo_url'] ?? null,
                'sort_order' => $i,
            ]);
        }

        return $this->success($portfolio->testimonials()->get(), 'Testimonials updated.');
    }

    /**
     * Bulk update custom sections.
     */
    public function updateCustomSections(Request $request): JsonResponse
    {
        $portfolio = $this->getPortfolio($request);

        // Custom sections require Pro or Annual plan
        $subscription = $request->user()->activePortfolioSubscription;
        if (!$subscription || $subscription->plan->slug === 'starter') {
            return $this->error(null, 'Custom sections require a Pro or Annual plan.', 403);
        }

        $validated = $request->validate([
            'sections' => 'present|array|max:3',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.content' => 'required|string|max:2000',
        ]);

        $portfolio->customSections()->delete();

        foreach ($validated['sections'] as $i => $section) {
            $portfolio->customSections()->create([
                'title' => $section['title'],
                'content' => $section['content'],
                'sort_order' => $i,
            ]);
        }

        return $this->success($portfolio->customSections()->get(), 'Custom sections updated.');
    }
}
