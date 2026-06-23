@component('layouts.admin', ['title' => 'Restaurant Settings'])
@php
$currentLogo = $restaurant->logo_url ?? null;
$currentCover = $restaurant->cover_image_url ?? null;

    $openingTime = old(
        'opening_time',
        $restaurant->opening_time
            ? \Illuminate\Support\Carbon::parse($restaurant->opening_time)->format('H:i')
            : ''
    );

    $closingTime = old(
        'closing_time',
        $restaurant->closing_time
            ? \Illuminate\Support\Carbon::parse($restaurant->closing_time)->format('H:i')
            : ''
    );
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Restaurant Management
        </p>

        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Restaurant settings
        </h1>

        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
            Manage public restaurant information, contact details, operating hours, delivery rules, branding, and ordering availability.
        </p>
    </div>

    <a
        href="{{ route('home') }}"
        target="_blank"
        rel="noopener"
        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50 hover:text-orange-700"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            class="h-5 w-5"
        >
            <path d="M14 3h7v7M10 14 21 3" />
            <path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5" />
        </svg>

        View Public Site
    </a>
</div>

{{-- Validation Summary --}}
@if ($errors->any())
    <div class="mb-7 rounded-[1.5rem] border border-red-200 bg-red-50 p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M12 9v4M12 17h.01" />
                    <path d="M10.3 4.4 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0z" />
                </svg>
            </div>

            <div>
                <p class="font-black text-red-800">
                    Please correct the following information
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
    action="{{ route('admin.settings.restaurant.update') }}"
    method="POST"
    enctype="multipart/form-data"
    class="grid gap-7 xl:grid-cols-[minmax(0,1fr)_390px]"
    x-data="{
        logoPreview: @js($currentLogo),
        coverPreview: @js($currentCover),
        restaurantName: @js(old('name', $restaurant->name ?? '')),
        shortDescription: @js(old('short_description', $restaurant->short_description ?? '')),
        deliveryFee: @js((string) old('delivery_fee', $restaurant->delivery_fee ?? 0)),
        minimumOrder: @js((string) old('minimum_order_amount', $restaurant->minimum_order_amount ?? 0)),
        openingTime: @js($openingTime),
        closingTime: @js($closingTime),
        isOpen: @js((bool) old('is_open', $restaurant->is_open ?? true)),
        isActive: @js((bool) old('is_active', $restaurant->is_active ?? true))
    }"
>
    @csrf
    @method('PUT')

    <div class="space-y-7">
        {{-- Restaurant Identity --}}
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
                        <path d="M4 10h16M5 10v10h14V10M3 10l2-6h14l2 6" />
                        <path d="M9 20v-6h6v6" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Restaurant Identity
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Public information
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        This information appears across the customer-facing website and ordering experience.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-black text-slate-800">
                        Restaurant Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="name"
                        name="name"
                        value="{{ old('name', $restaurant->name) }}"
                        x-model="restaurantName"
                        required
                        placeholder="FreshBite Restaurant"
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
                        value="{{ old('slug', $restaurant->slug) }}"
                        placeholder="freshbite-restaurant"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                        Used as a readable URL identifier for the restaurant.
                    </p>

                    @error('slug')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2">
                    <label for="short_description" class="block text-sm font-black text-slate-800">
                        Short Description
                    </label>

                    <textarea
                        id="short_description"
                        name="short_description"
                        rows="4"
                        x-model="shortDescription"
                        placeholder="Describe the restaurant, food style, and customer experience."
                        class="mt-2 w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold leading-7 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >{{ old('short_description', $restaurant->short_description) }}</textarea>

                    @error('short_description')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Contact Information --}}
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
                        <path d="m4 6 8 6 8-6" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Contact Details
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Customer contact information
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Keep customer support details and the restaurant address accurate.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-black text-slate-800">
                        Email Address
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $restaurant->email) }}"
                        placeholder="orders@example.com"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    @error('email')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-black text-slate-800">
                        Phone Number
                    </label>

                    <input
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $restaurant->phone) }}"
                        placeholder="+92 300 0000000"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    @error('phone')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Address --}}
                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-black text-slate-800">
                        Restaurant Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        rows="4"
                        placeholder="Street, area, city, country"
                        class="mt-2 w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold leading-7 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >{{ old('address', $restaurant->address) }}</textarea>

                    @error('address')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Operating Hours --}}
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
                        Operating Hours
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Opening and closing times
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        These hours inform customers when the restaurant normally accepts orders.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="opening_time" class="block text-sm font-black text-slate-800">
                        Opening Time
                    </label>

                    <input
                        id="opening_time"
                        name="opening_time"
                        type="time"
                        value="{{ $openingTime }}"
                        x-model="openingTime"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    @error('opening_time')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="closing_time" class="block text-sm font-black text-slate-800">
                        Closing Time
                    </label>

                    <input
                        id="closing_time"
                        name="closing_time"
                        type="time"
                        value="{{ $closingTime }}"
                        x-model="closingTime"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    @error('closing_time')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Ordering Rules --}}
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
                        Ordering Rules
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Delivery fee and minimum order
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        These amounts are used when calculating the customer cart and checkout total.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                {{-- Delivery Fee --}}
                <div>
                    <label for="delivery_fee" class="block text-sm font-black text-slate-800">
                        Delivery Fee
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-black text-slate-500">
                            Rs.
                        </span>

                        <input
                            id="delivery_fee"
                            name="delivery_fee"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('delivery_fee', $restaurant->delivery_fee ?? 0) }}"
                            x-model="deliveryFee"
                            required
                            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                        >
                    </div>

                    @error('delivery_fee')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Minimum Order --}}
                <div>
                    <label for="minimum_order_amount" class="block text-sm font-black text-slate-800">
                        Minimum Order Amount
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-black text-slate-500">
                            Rs.
                        </span>

                        <input
                            id="minimum_order_amount"
                            name="minimum_order_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('minimum_order_amount', $restaurant->minimum_order_amount ?? 0) }}"
                            x-model="minimumOrder"
                            required
                            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                        >
                    </div>

                    @error('minimum_order_amount')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Restaurant Status --}}
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
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 2v3M12 19v3M4.9 4.9 7 7M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1 7 17M17 7l2.1-2.1" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Restaurant Status
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Ordering and public visibility
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Control whether customers can place orders and whether the restaurant is displayed publicly.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-4 md:grid-cols-2">
                {{-- Open Status --}}
                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200">
                    <span>
                        <span class="block text-sm font-black text-slate-950">
                            Restaurant Open
                        </span>

                        <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
                            Customers can place new orders while this is enabled.
                        </span>
                    </span>

                    <span class="relative shrink-0">
                        <input type="hidden" name="is_open" value="0">

                        <input
                            type="checkbox"
                            name="is_open"
                            value="1"
                            x-model="isOpen"
                            @checked(old('is_open', $restaurant->is_open ?? true))
                            class="peer sr-only"
                        >

                        <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-500 peer-focus:ring-4 peer-focus:ring-emerald-100"></span>
                        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                </label>

                {{-- Active Status --}}
                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5 transition hover:border-orange-200">
                    <span>
                        <span class="block text-sm font-black text-slate-950">
                            Active on Website
                        </span>

                        <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
                            Display the restaurant information on the public website.
                        </span>
                    </span>

                    <span class="relative shrink-0">
                        <input type="hidden" name="is_active" value="0">

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            x-model="isActive"
                            @checked(old('is_active', $restaurant->is_active ?? true))
                            class="peer sr-only"
                        >

                        <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-orange-600 peer-focus:ring-4 peer-focus:ring-orange-100"></span>
                        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                </label>
            </div>
        </section>

        {{-- Branding Uploads --}}
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
                        Restaurant Branding
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Logo and cover image
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Upload clear, high-quality images that represent the restaurant brand.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 md:grid-cols-2">
                {{-- Logo Upload --}}
                <div>
                    <p class="text-sm font-black text-slate-800">
                        Restaurant Logo
                    </p>

                    <label
                        for="logo"
                        class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-[1.5rem] border-2 border-dashed border-orange-200 bg-orange-50/60 px-5 py-8 text-center transition hover:border-orange-400 hover:bg-orange-50"
                    >
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-orange-600 shadow-sm">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-6 w-6"
                            >
                                <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                            </svg>
                        </div>

                        <p class="mt-3 text-sm font-black text-slate-950">
                            Upload logo
                        </p>

                        <p class="mt-1 text-xs font-semibold text-slate-500">
                            JPG, PNG or WEBP
                        </p>

                        <input
                            id="logo"
                            name="logo"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="sr-only"
                            x-on:change="
                                const file = $event.target.files[0];

                                if (file) {
                                    if (logoPreview && logoPreview.startsWith('blob:')) {
                                        URL.revokeObjectURL(logoPreview);
                                    }

                                    logoPreview = URL.createObjectURL(file);
                                }
                            "
                        >
                    </label>

                    @error('logo')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Cover Upload --}}
                <div>
                    <p class="text-sm font-black text-slate-800">
                        Cover Image
                    </p>

                    <label
                        for="cover_image"
                        class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-[1.5rem] border-2 border-dashed border-orange-200 bg-orange-50/60 px-5 py-8 text-center transition hover:border-orange-400 hover:bg-orange-50"
                    >
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-orange-600 shadow-sm">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-6 w-6"
                            >
                                <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                            </svg>
                        </div>

                        <p class="mt-3 text-sm font-black text-slate-950">
                            Upload cover image
                        </p>

                        <p class="mt-1 text-xs font-semibold text-slate-500">
                            Landscape image recommended
                        </p>

                        <input
                            id="cover_image"
                            name="cover_image"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="sr-only"
                            x-on:change="
                                const file = $event.target.files[0];

                                if (file) {
                                    if (coverPreview && coverPreview.startsWith('blob:')) {
                                        URL.revokeObjectURL(coverPreview);
                                    }

                                    coverPreview = URL.createObjectURL(file);
                                }
                            "
                        >
                    </label>

                    @error('cover_image')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>
    </div>

    {{-- Preview Sidebar --}}
    <aside class="h-fit space-y-5 xl:sticky xl:top-28">
        {{-- Public Preview --}}
        <section class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-xl shadow-orange-900/5">
            <div class="border-b border-orange-100 px-6 py-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                    Live Preview
                </p>

                <h2 class="mt-2 text-xl font-black text-slate-950">
                    Public restaurant card
                </h2>
            </div>

            <div class="p-5">
                <div class="overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white shadow-sm">
                    {{-- Cover Preview --}}
                    <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-orange-100 via-amber-50 to-red-100">
                        <template x-if="coverPreview">
                            <img
                                x-bind:src="coverPreview"
                                alt="Restaurant cover preview"
                                class="h-full w-full object-cover"
                            >
                        </template>

                        <div
                            x-show="!coverPreview"
                            class="absolute inset-0 grid place-items-center"
                        >
                            <span class="text-sm font-black text-orange-700">
                                Cover image preview
                            </span>
                        </div>

                        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-950/70 to-transparent"></div>

                        <div class="absolute bottom-4 left-4 flex items-end gap-3">
                            <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-white text-lg font-black text-orange-700 shadow-xl">
                                <template x-if="logoPreview">
                                    <img
                                        x-bind:src="logoPreview"
                                        alt="Restaurant logo preview"
                                        class="h-full w-full object-cover"
                                    >
                                </template>

                                <span
                                    x-show="!logoPreview"
                                    x-text="restaurantName ? restaurantName.substring(0, 2).toUpperCase() : 'FB'"
                                ></span>
                            </div>

                            <div class="min-w-0 pb-1">
                                <p
                                    class="truncate text-lg font-black text-white"
                                    x-text="restaurantName || 'Restaurant Name'"
                                ></p>

                                <div class="mt-1 flex items-center gap-2">
                                    <span
                                        class="h-2 w-2 rounded-full"
                                        x-bind:class="isOpen ? 'bg-emerald-400' : 'bg-red-400'"
                                    ></span>

                                    <span
                                        class="text-xs font-black text-white"
                                        x-text="isOpen ? 'Open for orders' : 'Currently closed'"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Restaurant Details --}}
                    <div class="p-5">
                        <p
                            class="line-clamp-3 text-sm leading-6 text-slate-600"
                            x-text="shortDescription || 'The restaurant description will appear here.'"
                        ></p>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">
                                    Delivery
                                </p>

                                <p class="mt-1 font-black text-slate-950">
                                    Rs.
                                    <span x-text="Number(deliveryFee || 0).toLocaleString()"></span>
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">
                                    Minimum
                                </p>

                                <p class="mt-1 font-black text-slate-950">
                                    Rs.
                                    <span x-text="Number(minimumOrder || 0).toLocaleString()"></span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 rounded-2xl bg-orange-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-orange-600">
                                Operating Hours
                            </p>

                            <p class="mt-1 font-black text-slate-950">
                                <span x-text="openingTime || '--:--'"></span>
                                –
                                <span x-text="closingTime || '--:--'"></span>
                            </p>
                        </div>

                        <div
                            class="mt-4 rounded-2xl px-4 py-3 text-center text-sm font-black"
                            x-bind:class="isActive
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-red-50 text-red-700'"
                            x-text="isActive
                                ? 'Visible on public website'
                                : 'Hidden from public website'"
                        ></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Current Status --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                Current Configuration
            </p>

            <div class="mt-5 space-y-3">
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                    <span class="text-sm font-semibold text-slate-500">
                        Ordering
                    </span>

                    <span
                        class="inline-flex items-center gap-2 text-sm font-black"
                        x-bind:class="isOpen ? 'text-emerald-700' : 'text-red-700'"
                    >
                        <span
                            class="h-2 w-2 rounded-full"
                            x-bind:class="isOpen ? 'bg-emerald-500' : 'bg-red-500'"
                        ></span>

                        <span x-text="isOpen ? 'Open' : 'Closed'"></span>
                    </span>
                </div>

                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                    <span class="text-sm font-semibold text-slate-500">
                        Public status
                    </span>

                    <span
                        class="text-sm font-black"
                        x-bind:class="isActive ? 'text-emerald-700' : 'text-red-700'"
                        x-text="isActive ? 'Active' : 'Inactive'"
                    ></span>
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

                Save Restaurant Settings
            </button>

            <a
                href="{{ route('admin.dashboard') }}"
                class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
            >
                Back to Dashboard
            </a>

            <p class="mt-4 text-center text-xs font-semibold leading-5 text-slate-500">
                Updated values will affect the public menu, cart, and checkout experience.
            </p>
        </section>
    </aside>
</form>

@endcomponent
