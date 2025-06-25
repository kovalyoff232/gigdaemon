<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::in(['active', 'completed', 'archived'])],
            'deadline' => 'nullable|date',
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where('user_id', auth()->id()),
            ],
        ];
    }
}