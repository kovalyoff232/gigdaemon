<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|nullable|email|max:255',
        'phone' => 'sometimes|nullable|string|max:20',
        'default_rate' => 'sometimes|nullable|numeric|min:0',
    ];
}
}