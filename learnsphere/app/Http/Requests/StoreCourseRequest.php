<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming middleware handles role checking
    }

    public function rules(): array
    {
        return [
            'course.title' => ['required', 'string', 'max:255'],
            'course.description' => ['required', 'string'],
            'course.thumbnail' => ['nullable', 'url'], // Or image validation if file upload
            'course.published' => ['boolean'],

            'modules' => ['required', 'array', 'min:1'],
            'modules.*.title' => ['required', 'string', 'max:255'],
            'modules.*.description' => ['nullable', 'string'],
            'modules.*.order' => ['required', 'integer'],

            'modules.*.lessons' => ['nullable', 'array'],
            'modules.*.lessons.*.title' => ['required', 'string', 'max:255'],
            'modules.*.lessons.*.content' => ['nullable', 'string'],
            'modules.*.lessons.*.video_url' => ['nullable', 'url'],
            'modules.*.lessons.*.order' => ['required', 'integer'],

            'modules.*.assignments' => ['nullable', 'array'],
            'modules.*.assignments.*.title' => ['required', 'string', 'max:255'],
            'modules.*.assignments.*.description' => ['nullable', 'string'],
            'modules.*.assignments.*.due_date' => ['nullable', 'date'],
            'modules.*.assignments.*.max_score' => ['integer', 'min:0'],
        ];
    }
}
