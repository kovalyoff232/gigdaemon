<?php
namespace App\Http\Requests\Api\V1;
use Illuminate\Foundation\Http\FormRequest;
class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [ 'description' => 'nullable|string|max:255' ];
    }
}