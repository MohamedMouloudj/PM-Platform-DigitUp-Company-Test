<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'status' => ['nullable', Rule::enum(ProjectStatus::class)],
            'confidentiality_level' => ['required', Rule::enum(ConfidentialityLevel::class)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'description.required' => 'Project description is required.',
            'confidentiality_level.required' => 'Confidentiality level is required.',
        ];
    }
}
