<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\AiAnswer;
use App\Services\AiQnaService;
use Illuminate\Http\Request;

class AiQnaController extends Controller
{
    protected AiQnaService $aiService;

    public function __construct(AiQnaService $aiService)
    {
        $this->aiService = $aiService;

        // Feature flag: disable all AI endpoints unless explicitly enabled
        if (! config('ai.qna_enabled')) {
            abort(503, 'The AI Q&A feature is currently disabled.');
        }
    }

    /**
     * Suggest a title and tags from a question body.
     */
    public function suggestMeta(Request $request)
    {
        $request->validate(['content' => 'required|string|min:30']);

        return response()->json(
            $this->aiService->suggestMeta($request->content)
        );
    }

    /**
     * Scope check: is this question about Laravel / Vue.js?
     */
    public function analyze(Request $request)
    {
        $request->validate(['content' => 'required|string']);

        return response()->json(
            $this->aiService->analyzeQuestion($request->content)
        );
    }

    /**
     * Stream an AI answer for a published question (SSE).
     */
    public function stream(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        return $this->aiService->streamAnswer($question)
            ->usingVercelDataProtocol();
    }

    /**
     * Record community feedback on an AI answer.
     */
    public function validateAnswer(Request $request, $id)
    {
        $request->validate(['is_helpful' => 'required|boolean']);

        $aiAnswer = AiAnswer::findOrFail($id);
        $aiAnswer->update([
            'is_helpful'   => $request->is_helpful,
            'is_incorrect' => !$request->is_helpful,
        ]);

        return response()->json(['status' => 'success']);
    }
}
