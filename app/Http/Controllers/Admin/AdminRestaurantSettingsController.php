<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminRestaurantSettingsController extends Controller
{
    public function edit(): View
    {
        $restaurant = Restaurant::where('is_active', true)->first() ?? new Restaurant([
            'name' => 'FreshBite Restaurant',
            'slug' => 'freshbite-restaurant',
            'delivery_fee' => 0,
            'minimum_order_amount' => 0,
            'is_open' => true,
            'is_active' => true,
        ]);

        return view('admin.restaurant-settings', compact('restaurant'));
    }

    public function update(Request $request): RedirectResponse
    {
        $restaurant = Restaurant::where('is_active', true)->first() ?? new Restaurant();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('restaurants', 'slug')->ignore($restaurant->id)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_open' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $validated['slug'] ?: str($validated['name'])->slug()->toString();

        if (Restaurant::where('slug', $slug)->where('id', '!=', $restaurant->id)->exists()) {
            throw ValidationException::withMessages(['slug' => 'The slug has already been taken.']);
        }

        $payload = [
            'name' => $validated['name'],
            'slug' => $slug,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'opening_time' => $validated['opening_time'] ?? null,
            'closing_time' => $validated['closing_time'] ?? null,
            'delivery_fee' => $validated['delivery_fee'],
            'minimum_order_amount' => $validated['minimum_order_amount'],
            'is_open' => $request->boolean('is_open'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('logo')) {
            $payload['logo'] = ImageUpload::store($request->file('logo'), 'restaurant/logos', $restaurant->logo);
        }

        if ($request->hasFile('cover_image')) {
            $payload['cover_image'] = ImageUpload::store($request->file('cover_image'), 'restaurant/covers', $restaurant->cover_image);
        }

        $restaurant->fill($payload)->save();

        return back()->with('status', 'Restaurant settings saved successfully.');
    }
}
