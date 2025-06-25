<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'status' => ['sometimes', 'required', Rule::in(['active', 'completed', 'archived'])],
            'deadline' => 'sometimes|nullable|date',
            'client_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('clients', 'id')->where('user_id', auth()->id()),
            ],
        ];
    }
}