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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'course.title' => [$isUpdate ? 'sometimes' : 'required', 'nullable', 'string', 'max:255'],
            'course.description' => [$isUpdate ? 'sometimes' : 'required', 'nullable', 'string'],
            'course.thumbnail' => ['nullable', 'url'],
            'course.published' => ['nullable', 'boolean'],
            'course.enrollment_code' => ['nullable', 'string', 'max:50'],

            'modules' => ['nullable', 'array'],
            'modules.*.id' => ['nullable', 'exists:modules,id'],
            'modules.*.title' => [$isUpdate ? 'sometimes' : 'required', 'nullable', 'string', 'max:255'],
            'modules.*.description' => ['nullable', 'string'],
            'modules.*.order' => ['nullable', 'integer'],

            'modules.*.lessons' => ['nullable', 'array'],
            'modules.*.lessons.*.id' => ['nullable', 'exists:lessons,id'],
            'modules.*.lessons.*.title' => [$isUpdate ? 'sometimes' : 'required', 'nullable', 'string', 'max:255'],
            'modules.*.lessons.*.content_type' => ['nullable', 'string', 'in:text,video,pdf,doc'],
            'modules.*.lessons.*.content' => ['nullable', 'string'],
            'modules.*.lessons.*.video_url' => ['nullable', 'url'],
            'modules.*.lessons.*.attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,gif,zip,rar|max:51200'],
            'modules.*.lessons.*.attachment_name' => ['nullable', 'string', 'max:255'],
            'modules.*.lessons.*.order' => ['nullable', 'integer'],

            'modules.*.assignments' => ['nullable', 'array'],
            'modules.*.assignments.*.id' => ['nullable', 'exists:assignments,id'],
            'modules.*.assignments.*.title' => [$isUpdate ? 'sometimes' : 'required', 'nullable', 'string', 'max:255'],
            'modules.*.assignments.*.description' => ['nullable', 'string'],
            'modules.*.assignments.*.weight' => ['nullable', 'numeric', 'min:0'],
            'modules.*.assignments.*.due_date' => ['nullable', 'date'],
            'modules.*.assignments.*.max_score' => ['nullable', 'integer', 'min:0'],
            'modules.*.assignments.*.attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,gif,zip,rar|max:51200'],
            'modules.*.assignments.*.attachment_name' => ['nullable', 'string', 'max:255'],

            'modules.*.lessons.*.assessment_weight' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
