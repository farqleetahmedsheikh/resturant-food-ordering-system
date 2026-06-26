<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\RestaurantAvailabilityService;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRestaurantSettingsController extends Controller
{
    public function edit(RestaurantAvailabilityService $availability): View
    {
        $restaurant = Restaurant::current() ?? new Restaurant([
            'name' => 'Arcade Kebab House',
            'delivery_fee' => 0,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ]);

        return view('admin.restaurant-settings', [
            'restaurant' => $restaurant,
            'availabilityStatus' => $availability->status($restaurant->exists ? $restaurant : null),
            'timezones' => config('restaurant.timezones', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $restaurant = Restaurant::current() ?? new Restaurant;
        $timezoneRules = implode(',', array_keys(config('restaurant.timezones', [])));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'formatted_address' => ['nullable', 'string', 'max:1000'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i'],
            'timezone' => ['required', 'string', 'in:'.$timezoneRules],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'logo' => ImageUpload::validationRules(),
            'cover_image' => ImageUpload::validationRules(),
            'is_open' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'formatted_address' => $validated['formatted_address'] ?? $validated['address'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'opening_time' => $validated['opening_time'] ?? null,
            'closing_time' => $validated['closing_time'] ?? null,
            'timezone' => $validated['timezone'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'delivery_fee' => $validated['delivery_fee'],
            'minimum_order_amount' => $validated['minimum_order_amount'],
            'is_open' => $request->boolean('is_open'),
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

    public function destroyLogo(): RedirectResponse
    {
        $restaurant = Restaurant::current();

        if (! $restaurant) {
            return back()->with('error', 'Restaurant settings have not been configured yet.');
        }

        ImageUpload::delete($restaurant->logo);
        $restaurant->forceFill(['logo' => null])->save();

        return back()->with('status', 'Restaurant logo removed successfully.');
    }

    public function destroyCoverImage(): RedirectResponse
    {
        $restaurant = Restaurant::current();

        if (! $restaurant) {
            return back()->with('error', 'Restaurant settings have not been configured yet.');
        }

        ImageUpload::delete($restaurant->cover_image);
        $restaurant->forceFill(['cover_image' => null])->save();

        return back()->with('status', 'Restaurant cover image removed successfully.');
    }
}
