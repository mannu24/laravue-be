<?php

namespace App\Http\Requests\v1\User\Questions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:15|max:255',
            'content' => 'required|string|min:30',
            'tags' => 'required|array|min:1|max:5',
            'tags.*' => 'string|max:50'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The question title is required.',
            'title.min' => 'The title must be at least 15 characters long.',
            'content.required' => 'The question details are required.',
            'content.min' => 'The question details must be at least 30 characters long.',
            'tags.required' => 'At least one tag is required.',
            'tags.min' => 'At least one tag is required.',
            'tags.max' => 'You can only add up to 5 tags.'
        ];
    }
}
