<?php

namespace App\Http\Requests\Api\V1;

use App\Models\MenuItemAddon;
use App\Support\ImageUpload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('menu_items', 'slug')->ignore($this->route('menuItem'))],
            'description' => ['nullable', 'string', 'max:1500'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'image' => ImageUpload::validationRules(),
            'remove_image' => ['sometimes', 'boolean'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'calories' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'sizes' => ['nullable', 'array'],
            'sizes.*.name' => ['nullable', 'string', 'max:100'],
            'sizes.*.price' => ['nullable', 'numeric', 'min:0'],
            'sizes.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'sizes.*.is_active' => ['nullable', 'boolean'],
            'addons' => ['nullable', 'array'],
            'addons.*.name' => ['nullable', 'string', 'max:100'],
            'addons.*.type' => ['nullable', Rule::in(array_keys(MenuItemAddon::TYPES))],
            'addons.*.price' => ['nullable', 'numeric', 'min:0'],
            'addons.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'addons.*.is_active' => ['nullable', 'boolean'],
        ];
    }
}
