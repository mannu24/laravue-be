<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Mail\ContactQueryMail;
use App\Models\ContactQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    use HttpResponse;

    /**
     * Store a new contact query and send notification email.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contactQuery = ContactQuery::create($validated);

        // Send notification email to admin
        try {
            Mail::to('info@laravue.in')->send(new ContactQueryMail($contactQuery));
        } catch (\Exception $e) {
            Log::error('Failed to send contact query email', [
                'contact_query_id' => $contactQuery->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the request if email fails — the query is saved
        }

        return $this->success(null, 'Your message has been sent successfully. We\'ll get back to you soon.');
    }
}
