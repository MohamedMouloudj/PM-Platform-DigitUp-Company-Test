<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
            'confidentiality_level' => ['sometimes', Rule::enum(ConfidentialityLevel::class)],
        ];
    }
}
