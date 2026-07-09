<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:80'],
            'recipient_name' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['required', 'string', 'min:8', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery_notes' => ['nullable', 'string', 'max:700'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
