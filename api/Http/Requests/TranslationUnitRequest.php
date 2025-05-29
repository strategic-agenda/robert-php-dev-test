<?php

declare(strict_types=1);

namespace Api\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranslationUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_text' => 'required|string',
            'source_language' => 'required|string|regex:/^[a-z]{2}(-[A-Z]{2})?$/',
            'target_language' => 'required|string|regex:/^[a-z]{2}(-[A-Z]{2})?$/',
            'project_id' => 'required|string',
            'target_text' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'source_language.regex' => 'The source language must be in the format xx or xx-XX (e.g., en, en-US)',
            'target_language.regex' => 'The target language must be in the format xx or xx-XX (e.g., en, en-US)'
        ];
    }
} 