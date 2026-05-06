<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPortfolioController extends Controller
{
    use HttpResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Portfolio::with(['user:id,name,email,username', 'customDomain'])
            ->withCount('projects');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subdomain', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->query('published') === 'true') {
            $query->where('is_published', true);
        } elseif ($request->query('published') === 'false') {
            $query->where('is_published', false);
        }

        $portfolios = $query->latest()->paginate(20);

        return $this->success($portfolios);
    }

    public function show(int $id): JsonResponse
    {
        $portfolio = Portfolio::with([
            'user:id,name,email,username',
            'socialLinks', 'skills', 'experiences', 'educations',
            'projects', 'testimonials', 'customSections', 'customDomain',
        ])->findOrFail($id);

        return $this->success($portfolio);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $portfolio = Portfolio::findOrFail($id);

        $validated = $request->validate([
            'is_published' => 'sometimes|boolean',
            'template_slug' => 'sometimes|string',
            'title' => 'sometimes|string|max:255',
        ]);

        $portfolio->update($validated);

        return $this->success($portfolio->fresh(), 'Portfolio updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $portfolio = Portfolio::findOrFail($id);

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

    /**
     * Force unpublish a portfolio (content violation).
     */
    public function forceUnpublish(Request $request, int $id): JsonResponse
    {
        $portfolio = Portfolio::findOrFail($id);
        $portfolio->update(['is_published' => false]);

        return $this->success($portfolio->fresh(), 'Portfolio force-unpublished.');
    }
}
