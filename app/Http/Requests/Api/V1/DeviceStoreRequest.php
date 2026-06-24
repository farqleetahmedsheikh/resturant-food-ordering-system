<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class DeviceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_uuid' => ['nullable', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'platform' => ['required', 'in:ios,android,web'],
            'push_token' => ['required', 'string', 'max:1000'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ];
    }
}
