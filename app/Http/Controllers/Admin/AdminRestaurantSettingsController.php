<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\RestaurantAvailabilityService;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

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
            'remove_logo' => ['nullable', 'boolean'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'is_open' => ['nullable', 'boolean'],
        ]);

        $formattedAddress = trim((string) ($validated['formatted_address'] ?? ''));
        if ($formattedAddress === '' || in_array(mb_strtolower($formattedAddress), ['address to be configured', 'not configured yet'], true)) {
            $formattedAddress = trim((string) ($validated['address'] ?? ''));
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'formatted_address' => $formattedAddress !== '' ? $formattedAddress : null,
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

        if ($request->boolean('remove_logo') && ! $request->hasFile('logo')) {
            ImageUpload::delete($restaurant->logo);
            $payload['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            $payload['logo'] = ImageUpload::store($request->file('logo'), 'restaurant/logos', $restaurant->logo);
        }

        if ($request->boolean('remove_cover_image') && ! $request->hasFile('cover_image')) {
            ImageUpload::delete($restaurant->cover_image);
            $payload['cover_image'] = null;
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

    public function editMenuHero(): View
    {
        $restaurant = Restaurant::current() ?? new Restaurant(['name' => 'Arcade Kebab House']);
        $slides = $restaurant->menu_hero_slides ?: Restaurant::defaultMenuHeroSlides();

        return view('admin.menu-hero-slides', compact('restaurant', 'slides'));
    }

    public function updateMenuHero(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slides' => ['required', 'array', 'min:1', 'max:8'],
            'slides.*.id' => ['nullable', 'string', 'max:80'],
            'slides.*.eyebrow' => ['required', 'string', 'max:100'],
            'slides.*.title' => ['required', 'string', 'max:80'],
            'slides.*.highlight' => ['required', 'string', 'max:80'],
            'slides.*.description' => ['required', 'string', 'max:200'],
            'slides.*.is_active' => ['nullable', 'boolean'],
            'slides.*.image' => ImageUpload::validationRules(),
        ]);

        if (! collect($validated['slides'])->contains(fn (array $slide): bool => (bool) ($slide['is_active'] ?? false))) {
            return back()->withErrors(['slides' => 'Keep at least one hero slide active.'])->withInput();
        }

        $restaurant = Restaurant::current() ?? new Restaurant(['name' => 'Arcade Kebab House']);
        $existingSlides = collect($restaurant->menu_hero_slides ?: Restaurant::defaultMenuHeroSlides())->keyBy('id');
        $savedSlides = [];

        foreach ($validated['slides'] as $index => $slide) {
            $id = (string) ($slide['id'] ?? '');
            $existing = $id !== '' ? $existingSlides->get($id) : null;

            if ($id !== '' && ! $existing) {
                return back()->withErrors(['slides' => 'A slide was not recognized. Refresh the page and try again.'])->withInput();
            }

            $imagePath = $existing['image'] ?? null;
            $upload = $request->file("slides.{$index}.image");

            if ($upload) {
                $imagePath = ImageUpload::store($upload, 'restaurant/menu-hero', $imagePath);
            } elseif (! $imagePath) {
                return back()->withErrors(["slides.{$index}.image" => 'Please choose an image for this slide.'])->withInput();
            }

            $savedSlides[] = [
                'id' => $existing['id'] ?? Str::uuid()->toString(),
                'eyebrow' => $slide['eyebrow'],
                'title' => $slide['title'],
                'highlight' => $slide['highlight'],
                'description' => $slide['description'],
                'image' => $imagePath,
                'is_active' => (bool) ($slide['is_active'] ?? false),
                'sort_order' => $index,
            ];
        }

        $retainedIds = collect($savedSlides)->pluck('id')->all();
        $existingSlides->each(function (array $slide) use ($retainedIds): void {
            if (! in_array($slide['id'], $retainedIds, true) && ! str_starts_with((string) $slide['image'], '/')) {
                ImageUpload::delete($slide['image'] ?? null);
            }
        });

        $restaurant->menu_hero_slides = $savedSlides;
        $restaurant->save();

        return redirect()->route('admin.settings.menu-hero.edit')->with('status', 'Menu hero slides updated.');
    }
}
