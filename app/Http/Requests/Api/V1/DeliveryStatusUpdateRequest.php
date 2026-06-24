<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['picked_up', 'out_for_delivery', 'delivered', 'failed'])],
            'notes' => ['nullable', 'string', 'max:1000', Rule::requiredIf($this->input('status') === 'failed')],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required' => 'Failed delivery reason is required.',
        ];
    }
}
