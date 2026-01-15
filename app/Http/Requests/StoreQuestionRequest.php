<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:3',
            'tags' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'question.required' => 'The question field is mandatory.',
            'question.min' => 'The question must be at least 3 characters.',
            'question.max' => 'The question must not exceed 255 characters.',
            'description.required' => 'The description field is mandatory.',
            'description.min' => 'The description must be at least 3 characters.',
        ];
    }
}
