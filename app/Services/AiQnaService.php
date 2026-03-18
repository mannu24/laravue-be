<?php

namespace App\Services;

use App\Ai\Agents\QnaAssistant;
use App\Models\AiAnswer;
use App\Models\Question;
use Illuminate\Support\Facades\Http;

class AiQnaService
{
    public function isFrameworkRelevant(string $content): bool
    {
        $text = strtolower($content);
        $keywords = config('ai.allowed_topics', ['laravel', 'vue.js', 'php', 'javascript']);

        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Given a question body, return AI-suggested title and tags.
     * Falls back gracefully if no API key is configured.
     */
    public function suggestMeta(string $content): array
    {
        $geminiKey = config('ai.gemini_key');
        $openaiKey = config('ai.openai_key');

        $prompt = "You are a technical assistant specialising in Laravel and Vue.js.\n\n"
            . "Given the following question body, produce:\n"
            . "1. A short, clear question title (max 25 words)\n"
            . "2. Up to 5 relevant tags from: laravel, vue.js, php, javascript, inertia.js, tailwind, vite, eloquent, blade, livewire, nuxt, mysql, api, routing, middleware\n\n"
            . "Respond ONLY with valid JSON in this exact format:\n"
            . "{\"title\": \"...\", \"tags\": [\"...\"]}\n\n"
            . "Question body:\n{$content}";

        // Try Gemini first
        if ($geminiKey) {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$geminiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]]
                ]);

            dd($response->json());

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text', '');
                $parsed = $this->parseJsonFromText($text);
                if ($parsed) return $parsed;
            }
        }

        // Fallback to OpenAI
        if ($openaiKey) {
            $response = Http::withToken($openaiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->successful()) {
                $text = $response->json('choices.0.message.content', '');
                $parsed = $this->parseJsonFromText($text);
                if ($parsed) return $parsed;
            }
        }

        // No API key – return placeholder so the flow still works
        return [
            'title' => '',
            'tags' => [],
            'ai_unavailable' => true,
        ];
    }

    public function analyzeQuestion(string $content): array
    {
        $isRelevant = $this->isFrameworkRelevant($content);

        if (!$isRelevant) {
            return [
                'out_of_scope' => true,
                'suggestion' => "It looks like your question isn't directly related to Laravel or Vue.js. To get the best help from our community, try to focus on these frameworks."
            ];
        }

        return ['out_of_scope' => false, 'suggestion' => null];
    }

    public function streamAnswer(Question $question)
    {
        $assistant = new QnaAssistant();

        return $assistant->stream("Question Title: {$question->title}\n\nDetails: {$question->body}")
            ->then(function ($response) use ($question) {
                AiAnswer::create([
                    'question_id' => $question->id,
                    'body'        => $response->text,
                    'model'       => config('ai.default_model', 'gemini'),
                    'metadata'    => ['usage' => $response->usage ?? null],
                ]);
            });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function parseJsonFromText(string $text): ?array
    {
        // Strip Markdown code fences if present
        $text = preg_replace('/```(?:json)?(.*?)```/s', '$1', $text);
        $decoded = json_decode(trim($text), true);

        if (is_array($decoded) && isset($decoded['title'], $decoded['tags'])) {
            return [
                'title' => (string) $decoded['title'],
                'tags'  => array_slice((array) $decoded['tags'], 0, 5),
            ];
        }

        return null;
    }
}
