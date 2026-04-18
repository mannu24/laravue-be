<?php

declare(strict_types=1);

namespace App\Http\Requests\QA;

use Illuminate\Foundation\Http\FormRequest;

class CreateAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question_id' => 'required|integer|exists:questions,id',
            'body' => 'required|string|min:10',
        ];
    }

    /**
     * Merge authenticated user's ID into the request.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()?->id,
        ]);
    }
}

