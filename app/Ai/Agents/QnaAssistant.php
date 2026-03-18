<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Stringable;

class QnaAssistant implements Agent, Conversational
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return "You are a specialized technical assistant for the Laravue platform. 
        Your expertise is strictly limited to Laravel and Vue.js (including related ecosystem like Inertia, Tailwind, Vite).
        
        Rules:
        1. If a question is about Laravel or Vue.js, provide a detailed, accurate, and concise answer with code examples.
        2. If a question is NOT about Laravel or Vue.js, politely inform the user that you only specialize in these frameworks.
        3. Use high-quality coding standards (PSR-12 for PHP, Composition API for Vue).
        4. Always suggest relevant tags for the question.";
    }

    public function messages(): iterable
    {
        // For simple instant answers, we might not need history.
        // But the SDK supports it if we want to pass previous messages.
        return [];
    }
}
