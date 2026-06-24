<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CartItemStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'size_id' => ['nullable', 'integer', 'exists:menu_item_sizes,id'],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => ['integer', 'exists:menu_item_addons,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}
