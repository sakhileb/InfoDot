<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSolutionRequest extends FormRequest
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
            'solution_title' => 'required|string|min:3|max:255',
            'solution_description' => 'required|string|min:3',
            'tags' => 'required|string|min:3',
            'duration' => 'required|integer|min:1',
            'duration_type' => 'required|in:hours,days,weeks,months,years,infinite',
            'steps' => 'required|integer|min:1',
            'solution_heading' => 'required|array|min:1',
            'solution_heading.*' => 'required|string|min:3|max:255',
            'solution_body' => 'required|array|min:1',
            'solution_body.*' => 'required|string|min:3',
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
            'solution_title.required' => 'The solution title field is mandatory.',
            'solution_title.min' => 'The solution title must be at least 3 characters.',
            'solution_title.max' => 'The solution title must not exceed 255 characters.',
            'solution_description.required' => 'The solution description field is mandatory.',
            'solution_description.min' => 'The solution description must be at least 3 characters.',
            'tags.required' => 'The tags field is mandatory.',
            'tags.min' => 'The tags must be at least 3 characters.',
            'duration.required' => 'The duration field is mandatory.',
            'duration.integer' => 'The duration must be a number.',
            'duration.min' => 'The duration must be at least 1.',
            'duration_type.required' => 'The duration type field is mandatory.',
            'duration_type.in' => 'The duration type must be one of: hours, days, weeks, months, years, infinite.',
            'steps.required' => 'The steps field is mandatory.',
            'steps.integer' => 'The steps must be a number.',
            'steps.min' => 'There must be at least 1 step.',
            'solution_heading.required' => 'Solution headings are required.',
            'solution_heading.*.required' => 'Each step must have a heading.',
            'solution_heading.*.min' => 'Each step heading must be at least 3 characters.',
            'solution_body.required' => 'Solution bodies are required.',
            'solution_body.*.required' => 'Each step must have a body.',
            'solution_body.*.min' => 'Each step body must be at least 3 characters.',
        ];
    }
}
