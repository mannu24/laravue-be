<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioUploadController extends Controller
{
    use HttpResponse;

    /**
     * Upload portfolio profile photo.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();

        // Delete old photo if exists
        if ($portfolio->photo_path && file_exists(public_path($portfolio->photo_path))) {
            @unlink(public_path($portfolio->photo_path));
        }

        $file = $request->file('photo');
        $filename = 'portfolio_' . $portfolio->id . '_photo_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path('uploads/portfolio/photos'), $filename);

        $relativePath = 'uploads/portfolio/photos/' . $filename;
        $portfolio->update(['photo_path' => $relativePath]);

        return $this->success([
            'photo_path' => $relativePath,
            'url' => asset($relativePath),
        ], 'Photo uploaded.');
    }

    /**
     * Upload portfolio resume (PDF).
     */
    public function uploadResume(Request $request): JsonResponse
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf|max:5120',
        ]);

        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();

        // Delete old resume if exists
        if ($portfolio->resume_path && file_exists(public_path($portfolio->resume_path))) {
            @unlink(public_path($portfolio->resume_path));
        }

        $file = $request->file('resume');
        $filename = 'portfolio_' . $portfolio->id . '_resume_' . time() . '.pdf';
        $file->move(public_path('uploads/portfolio/resumes'), $filename);

        $relativePath = 'uploads/portfolio/resumes/' . $filename;
        $portfolio->update(['resume_path' => $relativePath]);

        return $this->success([
            'resume_path' => $relativePath,
            'url' => asset($relativePath),
        ], 'Resume uploaded.');
    }

    /**
     * Upload a project image for portfolio.
     */
    public function uploadProjectImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $portfolio = Portfolio::where('user_id', $request->user()->id)->firstOrFail();

        $file = $request->file('image');
        $filename = 'portfolio_' . $portfolio->id . '_project_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/portfolio/projects'), $filename);

        $relativePath = 'uploads/portfolio/projects/' . $filename;

        return $this->success([
            'image_path' => $relativePath,
            'url' => asset($relativePath),
        ], 'Project image uploaded.');
    }
}
