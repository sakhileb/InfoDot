<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnswerRequest extends FormRequest
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
            'question_id' => 'required|exists:questions,id',
            'content' => 'required|string|min:10|max:5000',
            'is_accepted' => 'sometimes|boolean',
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
            'question_id.required' => 'The question field is mandatory.',
            'question_id.exists' => 'The selected question does not exist.',
            'content.required' => 'The content field is mandatory.',
            'content.min' => 'The answer must be at least 10 characters long.',
            'content.max' => 'The answer cannot exceed 5000 characters.',
        ];
    }
}
