@component('layouts.admin', ['title' => $mode === 'create' ? 'Add Menu Item' : 'Edit Menu Item'])
@php
$isCreateMode = $mode === 'create';
$pageTitle = $isCreateMode ? 'Add menu item' : 'Edit menu item';
$submitLabel = $isCreateMode ? 'Create Menu Item' : 'Save Changes';
$currentImage = $menuItem->image_url ?? null;

    $selectedCategoryId = old('category_id', $menuItem->category_id ?? '');
    $selectedCategory = $categories->firstWhere('id', $selectedCategoryId);

    $sizeRows = collect(old('sizes', $menuItem->sizes?->map(fn ($size) => [
        'name' => $size->name,
        'price' => (string) $size->price,
        'sort_order' => $size->sort_order,
        'is_active' => (bool) $size->is_active,
    ])->values()->all() ?? []))->values()->all();

    $addonRows = collect(old('addons', $menuItem->addons?->map(fn ($addon) => [
        'name' => $addon->name,
        'type' => $addon->type,
        'price' => (string) $addon->price,
        'sort_order' => $addon->sort_order,
        'is_active' => (bool) $addon->is_active,
    ])->values()->all() ?? []))->values()->all();

    $addonTypes = \App\Models\MenuItemAddon::TYPES;
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Menu Management
        </p>

        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            {{ $pageTitle }}
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            {{ $isCreateMode
                ? 'Create a new food item with pricing, category, image, preparation details, and public availability.'
                : 'Update item information, pricing, image, menu visibility, and featured status.' }}
        </p>
    </div>

    <a
        href="{{ route('admin.menu-items.index') }}"
        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50 hover:text-orange-700"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            class="h-4 w-4"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
        </svg>

        Back to Menu
    </a>
</div>

{{-- Validation Summary --}}
@if ($errors->any())
    <div class="mb-7 rounded-[1.5rem] border border-red-200 bg-red-50 p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4M12 17h.01M10.3 4.4 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0z" />
                </svg>
            </div>

            <div>
                <p class="font-black text-red-800">
                    Please check the form
                </p>

                <ul class="mt-2 list-inside list-disc space-y-1 text-sm font-semibold text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<form
    action="{{ $isCreateMode
        ? route('admin.menu-items.store')
        : route('admin.menu-items.update', $menuItem) }}"
    method="POST"
    enctype="multipart/form-data"
    class="grid gap-7 xl:grid-cols-[minmax(0,1fr)_380px]"
    x-data="{
        preview: @js($currentImage),
        itemName: @js(old('name', $menuItem->name ?? '')),
        itemDescription: @js(old('description', $menuItem->description ?? '')),
        price: @js((string) old('price', $menuItem->price ?? '')),
        compareAtPrice: @js((string) old('compare_at_price', $menuItem->compare_at_price ?? '')),
        preparationTime: @js((string) old('preparation_time', $menuItem->preparation_time ?? '')),
        calories: @js((string) old('calories', $menuItem->calories ?? '')),
        categoryName: @js($selectedCategory?->name ?? 'Uncategorized'),
        available: @js((bool) old('is_available', $menuItem->is_available ?? true)),
        featured: @js((bool) old('is_featured', $menuItem->is_featured ?? false)),
        sizes: @js($sizeRows),
        addons: @js($addonRows),
        addSize() {
            this.sizes.push({ name: '', price: '', sort_order: this.sizes.length, is_active: true });
        },
        removeSize(index) {
            this.sizes.splice(index, 1);
        },
        addAddon() {
            this.addons.push({ name: '', type: 'topping', price: '', sort_order: this.addons.length, is_active: true });
        },
        removeAddon(index) {
            this.addons.splice(index, 1);
        }
    }"
>
    @csrf

    @unless ($isCreateMode)
        @method('PUT')
    @endunless

    {{-- Main Form --}}
    <div class="space-y-7">
        {{-- Basic Details --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <path d="M4 4h16v16H4z" />
                        <path d="M8 8h8M8 12h8M8 16h5" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Basic Information
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Item details
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Add the item name, restaurant, category, slug, and customer-facing description.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                {{-- Restaurant --}}
                <div>
                    <label for="restaurant_id" class="block text-sm font-black text-slate-800">
                        Restaurant
                    </label>

                    <select
                        id="restaurant_id"
                        name="restaurant_id"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                        <option value="">No restaurant</option>

                        @foreach ($restaurants as $restaurant)
                            <option
                                value="{{ $restaurant->id }}"
                                @selected(old('restaurant_id', $menuItem->restaurant_id ?? '') == $restaurant->id)
                            >
                                {{ $restaurant->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('restaurant_id')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="category_id" class="block text-sm font-black text-slate-800">
                        Category
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        x-on:change="categoryName = $event.target.options[$event.target.selectedIndex].text"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                        <option value="">Uncategorized</option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id', $menuItem->category_id ?? '') == $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-black text-slate-800">
                        Item Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="name"
                        name="name"
                        value="{{ old('name', $menuItem->name ?? '') }}"
                        x-model="itemName"
                        required
                        placeholder="For example: Classic Beef Burger"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    @error('name')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Slug --}}
                <div>
                    <label for="slug" class="block text-sm font-black text-slate-800">
                        URL Slug
                    </label>

                    <input
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $menuItem->slug ?? '') }}"
                        placeholder="classic-beef-burger"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                        Leave empty to generate it automatically from the item name.
                    </p>

                    @error('slug')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-black text-slate-800">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        x-model="itemDescription"
                        placeholder="Describe the ingredients, taste, portion, and anything customers should know."
                        class="mt-2 w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold leading-7 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >{{ old('description', $menuItem->description ?? '') }}</textarea>

                    @error('description')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Pricing --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <rect x="3" y="6" width="18" height="12" rx="2" />
                        <circle cx="12" cy="12" r="2" />
                        <path d="M7 9h.01M17 15h.01" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Pricing
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Item price and discount
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Set the selling price and an optional original price to show a discount.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="price" class="block text-sm font-black text-slate-800">
                        Selling Price
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-black text-slate-500">
                            Rs.
                        </span>

                        <input
                            id="price"
                            name="price"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('price', $menuItem->price ?? '') }}"
                            x-model="price"
                            required
                            placeholder="0.00"
                            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                        >
                    </div>

                    @error('price')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="compare_at_price" class="block text-sm font-black text-slate-800">
                        Compare-at Price
                        <span class="font-semibold text-slate-400">(optional)</span>
                    </label>

                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-black text-slate-500">
                            Rs.
                        </span>

                        <input
                            id="compare_at_price"
                            name="compare_at_price"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('compare_at_price', $menuItem->compare_at_price ?? '') }}"
                            x-model="compareAtPrice"
                            placeholder="0.00"
                            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                        >
                    </div>

                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                        This should be higher than the selling price.
                    </p>

                    @error('compare_at_price')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Item Metadata --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5l3 2" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Item Metadata
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Preparation and nutrition
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Add useful information customers can review before ordering.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-3">
                {{-- Preparation Time --}}
                <div>
                    <label for="preparation_time" class="block text-sm font-black text-slate-800">
                        Preparation Time
                    </label>

                    <div class="relative mt-2">
                        <input
                            id="preparation_time"
                            name="preparation_time"
                            type="number"
                            min="1"
                            value="{{ old('preparation_time', $menuItem->preparation_time ?? '') }}"
                            x-model="preparationTime"
                            placeholder="15"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                        >

                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-xs font-bold text-slate-400">
                            min
                        </span>
                    </div>

                    @error('preparation_time')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Calories --}}
                <div>
                    <label for="calories" class="block text-sm font-black text-slate-800">
                        Calories
                    </label>

                    <div class="relative mt-2">
                        <input
                            id="calories"
                            name="calories"
                            type="number"
                            min="0"
                            value="{{ old('calories', $menuItem->calories ?? '') }}"
                            x-model="calories"
                            placeholder="450"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pr-14 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                        >

                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-xs font-bold text-slate-400">
                            kcal
                        </span>
                    </div>

                    @error('calories')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Sort Order --}}
                <div>
                    <label for="sort_order" class="block text-sm font-black text-slate-800">
                        Sort Order
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        min="0"
                        value="{{ old('sort_order', $menuItem->sort_order ?? 0) }}"
                        required
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    @error('sort_order')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Visibility --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Public Visibility
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Availability and promotion
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Control whether customers can order this item and whether it appears in featured sections.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-4 md:grid-cols-2">
                {{-- Available Toggle --}}
                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200">
                    <span>
                        <span class="block text-sm font-black text-slate-950">
                            Available for Ordering
                        </span>

                        <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
                            Customers can see and add this item to their cart.
                        </span>
                    </span>

                    <span class="relative shrink-0">
                        <input type="hidden" name="is_available" value="0">

                        <input
                            type="checkbox"
                            name="is_available"
                            value="1"
                            x-model="available"
                            @checked(old('is_available', $menuItem->is_available ?? true))
                            class="peer sr-only"
                        >

                        <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500 peer-focus:ring-4 peer-focus:ring-emerald-100"></span>
                        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                </label>

                {{-- Featured Toggle --}}
                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200">
                    <span>
                        <span class="block text-sm font-black text-slate-950">
                            Featured Item
                        </span>

                        <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
                            Promote this item in featured menu sections.
                        </span>
                    </span>

                    <span class="relative shrink-0">
                        <input type="hidden" name="is_featured" value="0">

                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            x-model="featured"
                            @checked(old('is_featured', $menuItem->is_featured ?? false))
                            class="peer sr-only"
                        >

                        <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-orange-600 peer-focus:ring-4 peer-focus:ring-orange-100"></span>
                        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                </label>
            </div>
        </section>

        {{-- Sizes and Add-ons --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
                <div class="flex items-start gap-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-6 w-6"
                        >
                            <path d="M4 4h16v16H4z" />
                            <path d="M8 8h8M8 12h8M8 16h5" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                            Ordering Options
                        </p>

                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                            Sizes, toppings, and paid extras
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Add pizza sizes like Small, Medium, Large, and Party. Add paid extras like extra cheese, toppings, and dips.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-7 grid gap-7 xl:grid-cols-2">
                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-black text-slate-950">
                                Size Prices
                            </h3>

                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                These are final selling prices. Leave empty if this item has no sizes.
                            </p>
                        </div>

                        <button
                            type="button"
                            x-on:click="addSize()"
                            class="shrink-0 rounded-2xl bg-orange-600 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-orange-700"
                        >
                            Add Size
                        </button>
                    </div>

                    <div class="mt-5 space-y-3">
                        <template x-if="sizes.length === 0">
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-center text-sm font-semibold text-slate-500">
                                No size prices yet. Customers will use the base selling price.
                            </div>
                        </template>

                        <template x-for="(size, index) in sizes" x-bind:key="'size-' + index">
                            <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                <div class="grid gap-3 sm:grid-cols-[1fr_130px_90px_auto] sm:items-end">
                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Size Name
                                        </label>

                                        <input
                                            type="text"
                                            x-model="size.name"
                                            x-bind:name="'sizes[' + index + '][name]'"
                                            placeholder="Large"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Price
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            x-model="size.price"
                                            x-bind:name="'sizes[' + index + '][price]'"
                                            placeholder="1499"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Sort
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            x-model="size.sort_order"
                                            x-bind:name="'sizes[' + index + '][sort_order]'"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                        >
                                    </div>

                                    <div class="flex items-center justify-between gap-3 sm:justify-end">
                                        <label class="flex items-center gap-2 rounded-2xl bg-slate-50 px-3 py-2 text-xs font-black text-slate-700">
                                            <input type="hidden" x-bind:name="'sizes[' + index + '][is_active]'" value="0">
                                            <input type="checkbox" value="1" x-model="size.is_active" x-bind:name="'sizes[' + index + '][is_active]'" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                                            Active
                                        </label>

                                        <button
                                            type="button"
                                            x-on:click="removeSize(index)"
                                            class="rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-black text-red-600 hover:bg-red-100"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-black text-slate-950">
                                Add-ons and Toppings
                            </h3>

                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                These appear as paid checkboxes during ordering.
                            </p>
                        </div>

                        <button
                            type="button"
                            x-on:click="addAddon()"
                            class="shrink-0 rounded-2xl bg-orange-600 px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:bg-orange-700"
                        >
                            Add Add-on
                        </button>
                    </div>

                    <div class="mt-5 space-y-3">
                        <template x-if="addons.length === 0">
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-center text-sm font-semibold text-slate-500">
                                No paid extras yet. Add toppings, extra cheese, sauces, or dips.
                            </div>
                        </template>

                        <template x-for="(addon, index) in addons" x-bind:key="'addon-' + index">
                            <div class="rounded-2xl border border-white bg-white p-4 shadow-sm">
                                <div class="grid gap-3 sm:grid-cols-[1fr_130px_110px_80px_auto] sm:items-end">
                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Add-on Name
                                        </label>

                                        <input
                                            type="text"
                                            x-model="addon.name"
                                            x-bind:name="'addons[' + index + '][name]'"
                                            placeholder="Extra Cheese"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Type
                                        </label>

                                        <select
                                            x-model="addon.type"
                                            x-bind:name="'addons[' + index + '][type]'"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                        >
                                            @foreach ($addonTypes as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Price
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            x-model="addon.price"
                                            x-bind:name="'addons[' + index + '][price]'"
                                            placeholder="150"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                                            Sort
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            x-model="addon.sort_order"
                                            x-bind:name="'addons[' + index + '][sort_order]'"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                                        >
                                    </div>

                                    <div class="flex items-center justify-between gap-3 sm:justify-end">
                                        <label class="flex items-center gap-2 rounded-2xl bg-slate-50 px-3 py-2 text-xs font-black text-slate-700">
                                            <input type="hidden" x-bind:name="'addons[' + index + '][is_active]'" value="0">
                                            <input type="checkbox" value="1" x-model="addon.is_active" x-bind:name="'addons[' + index + '][is_active]'" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                                            Active
                                        </label>

                                        <button
                                            type="button"
                                            x-on:click="removeAddon(index)"
                                            class="rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-black text-red-600 hover:bg-red-100"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            @error('sizes')
                <p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p>
            @enderror

            @error('addons')
                <p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p>
            @enderror
        </section>

        {{-- Image Upload --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <rect x="3" y="4" width="18" height="16" rx="2" />
                        <circle cx="8.5" cy="9" r="1.5" />
                        <path d="m21 15-5-5L5 20" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Item Image
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Upload a food image
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Use a clear landscape image that accurately represents the menu item.
                    </p>
                </div>
            </div>

            <div class="mt-7">
                <label
                    for="image"
                    class="group flex cursor-pointer flex-col items-center justify-center rounded-[1.5rem] border-2 border-dashed border-orange-200 bg-orange-50/60 px-5 py-10 text-center transition hover:border-orange-400 hover:bg-orange-50"
                >
                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-orange-600 shadow-sm transition group-hover:scale-105">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-7 w-7"
                        >
                            <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                        </svg>
                    </div>

                    <p class="mt-4 text-sm font-black text-slate-950">
                        Click to select an image
                    </p>

                    <p class="mt-2 text-xs font-semibold text-slate-500">
                        JPG, PNG or WEBP — maximum 2MB
                    </p>

                    <input
                        id="image"
                        name="image"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="sr-only"
                        x-on:change="
                            const file = $event.target.files[0];

                            if (file) {
                                if (preview && preview.startsWith('blob:')) {
                                    URL.revokeObjectURL(preview);
                                }

                                preview = URL.createObjectURL(file);
                            }
                        "
                    >
                </label>

                @error('image')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </section>
    </div>

    {{-- Preview Sidebar --}}
    <aside class="h-fit space-y-5 xl:sticky xl:top-28">
        <section class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-xl shadow-orange-900/5">
            <div class="border-b border-orange-100 px-6 py-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                    Live Preview
                </p>

                <h2 class="mt-2 text-xl font-black text-slate-950">
                    Customer menu card
                </h2>
            </div>

            <div class="p-5">
                <div class="overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white shadow-sm">
                    {{-- Preview Image --}}
                    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-orange-100 via-amber-50 to-red-100">
                        <template x-if="preview">
                            <img
                                x-bind:src="preview"
                                alt="Menu item preview"
                                class="h-full w-full object-cover"
                            >
                        </template>

                        <div
                            x-show="! preview"
                            class="absolute inset-0 grid place-items-center"
                        >
                            <div class="grid h-24 w-24 place-items-center rounded-full bg-white/80 text-5xl font-black text-orange-700 shadow-xl backdrop-blur">
                                <span x-text="itemName ? itemName.charAt(0).toUpperCase() : 'M'"></span>
                            </div>
                        </div>

                        <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                            <span
                                x-show="featured"
                                x-cloak
                                class="rounded-full bg-orange-600 px-3 py-1.5 text-xs font-black text-white shadow-lg"
                            >
                                Featured
                            </span>

                            <span
                                x-show="!available"
                                x-cloak
                                class="rounded-full bg-red-600 px-3 py-1.5 text-xs font-black text-white shadow-lg"
                            >
                                Unavailable
                            </span>
                        </div>
                    </div>

                    {{-- Preview Content --}}
                    <div class="p-5">
                        <p
                            class="text-xs font-black uppercase tracking-[0.16em] text-orange-600"
                            x-text="categoryName"
                        ></p>

                        <h3
                            class="mt-2 break-words text-xl font-black tracking-tight text-slate-950"
                            x-text="itemName || 'Menu Item Name'"
                        ></h3>

                        <p
                            class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600"
                            x-text="itemDescription || 'The menu item description will appear here.'"
                        ></p>

                        <div class="mt-5 flex flex-wrap items-end gap-3">
                            <p class="text-2xl font-black text-orange-600">
                                Rs.
                                <span x-text="Number(price || 0).toLocaleString()"></span>
                            </p>

                            <p
                                x-show="Number(compareAtPrice) > Number(price) && Number(compareAtPrice) > 0"
                                x-cloak
                                class="pb-0.5 text-sm font-bold text-slate-400 line-through"
                            >
                                Rs.
                                <span x-text="Number(compareAtPrice || 0).toLocaleString()"></span>
                            </p>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span
                                x-show="preparationTime"
                                class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600"
                            >
                                <span x-text="preparationTime"></span> min
                            </span>

                            <span
                                x-show="calories"
                                class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600"
                            >
                                <span x-text="calories"></span> kcal
                            </span>
                        </div>

                        <button
                            type="button"
                            disabled
                            x-bind:class="available
                                ? 'bg-orange-600 text-white'
                                : 'cursor-not-allowed bg-slate-200 text-slate-500'"
                            class="mt-5 w-full rounded-2xl px-5 py-3 text-sm font-black shadow-sm"
                        >
                            <span x-text="available ? 'Add to Cart' : 'Unavailable'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Save Actions --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M5 5h12l2 2v12H5zM8 5v5h8V5M8 19v-6h8v6" />
                </svg>

                {{ $submitLabel }}
            </button>

            <a
                href="{{ route('admin.menu-items.index') }}"
                class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
            >
                Cancel
            </a>

            <p class="mt-4 text-center text-xs font-semibold leading-5 text-slate-500">
                Review the item preview, pricing, and availability before saving.
            </p>
        </section>
    </aside>
</form>

@endcomponent
