@component('layouts.admin', ['title' => 'Restaurant Settings'])
@php
$currentLogo = $restaurant->logo_url ?? null;
$currentCover = $restaurant->cover_image_url ?? null;

    $initialName = old(
        'name',
        $restaurant->name ?? ''
    );

    $initialSlug = old(
        'slug',
        $restaurant->slug ?? ''
    );

    $initialDescription = old(
        'short_description',
        $restaurant->short_description ?? ''
    );

    $initialEmail = old(
        'email',
        $restaurant->email ?? ''
    );

    $initialPhone = old(
        'phone',
        $restaurant->phone ?? ''
    );

    $initialAddress = old(
        'address',
        $restaurant->address ?? ''
    );

    $openingTime = old(
        'opening_time',
        $restaurant->opening_time
            ? \Illuminate\Support\Carbon::parse(
                $restaurant->opening_time
            )->format('H:i')
            : ''
    );

    $closingTime = old(
        'closing_time',
        $restaurant->closing_time
            ? \Illuminate\Support\Carbon::parse(
                $restaurant->closing_time
            )->format('H:i')
            : ''
    );

    $initialDeliveryFee = old(
        'delivery_fee',
        $restaurant->delivery_fee ?? 0
    );

    $initialMinimumOrder = old(
        'minimum_order_amount',
        $restaurant->minimum_order_amount ?? 0
    );

    $initialIsOpen = (bool) old(
        'is_open',
        $restaurant->is_open ?? true
    );

    $initialIsActive = (bool) old(
        'is_active',
        $restaurant->is_active ?? true
    );
@endphp

<div
    x-data="{
        restaurantName: @js($initialName),
        slug: @js($initialSlug),
        shortDescription: @js($initialDescription),
        email: @js($initialEmail),
        phone: @js($initialPhone),
        address: @js($initialAddress),
        deliveryFee: @js((string) $initialDeliveryFee),
        minimumOrder: @js((string) $initialMinimumOrder),
        openingTime: @js($openingTime),
        closingTime: @js($closingTime),

        logoPreview: @js($currentLogo),
        originalLogo: @js($currentLogo),
        logoFileName: '',

        coverPreview: @js($currentCover),
        originalCover: @js($currentCover),
        coverFileName: '',

        isOpen: {{ $initialIsOpen ? 'true' : 'false' }},
        isActive: {{ $initialIsActive ? 'true' : 'false' }},
        submitting: false,

        slugify(value) {
            return (value || '')
                .toString()
                .normalize('NFKD')
                .replace(/[^\w\s-]/g, '')
                .trim()
                .toLowerCase()
                .replace(/[-\s]+/g, '-');
        },

        displaySlug() {
            return this.slug.trim() !== ''
                ? this.slug.trim()
                : this.slugify(this.restaurantName);
        },

        formatTime(value) {
            if (! value) {
                return '--:--';
            }

            const [hour, minute] = value.split(':');
            const parsedHour = Number(hour);
            const suffix = parsedHour >= 12 ? 'PM' : 'AM';
            const displayHour = parsedHour % 12 || 12;

            return `${displayHour}:${minute} ${suffix}`;
        },

        handleLogo(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            if (
                this.logoPreview
                && typeof this.logoPreview === 'string'
                && this.logoPreview.startsWith('blob:')
            ) {
                URL.revokeObjectURL(this.logoPreview);
            }

            this.logoPreview = URL.createObjectURL(file);
            this.logoFileName = file.name;
        },

        handleCover(event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

            if (
                this.coverPreview
                && typeof this.coverPreview === 'string'
                && this.coverPreview.startsWith('blob:')
            ) {
                URL.revokeObjectURL(this.coverPreview);
            }

            this.coverPreview = URL.createObjectURL(file);
            this.coverFileName = file.name;
        },

        resetLogo() {
            if (
                this.logoPreview
                && typeof this.logoPreview === 'string'
                && this.logoPreview.startsWith('blob:')
            ) {
                URL.revokeObjectURL(this.logoPreview);
            }

            this.logoPreview = this.originalLogo;
            this.logoFileName = '';

            if (this.$refs.logoInput) {
                this.$refs.logoInput.value = '';
            }
        },

        resetCover() {
            if (
                this.coverPreview
                && typeof this.coverPreview === 'string'
                && this.coverPreview.startsWith('blob:')
            ) {
                URL.revokeObjectURL(this.coverPreview);
            }

            this.coverPreview = this.originalCover;
            this.coverFileName = '';

            if (this.$refs.coverInput) {
                this.$refs.coverInput.value = '';
            }
        }
    }"
    class="space-y-5 pb-28 sm:space-y-6 xl:pb-8"
>
    {{-- Mobile Header --}}
    <header class="xl:hidden">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                    Restaurant Management
                </p>

                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">
                    Restaurant settings
                </h1>

                <p class="mt-1 text-sm font-semibold leading-5 text-slate-500">
                    Manage ordering, delivery and public information.
                </p>
            </div>

            <a
                href="{{ route('home') }}"
                target="_blank"
                rel="noopener"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-orange-100 bg-white text-orange-700 shadow-sm transition active:scale-95"
                aria-label="View public website"
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
            </a>
        </div>
    </header>

    {{-- Desktop Header --}}
    <header class="hidden items-end justify-between gap-8 xl:flex">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                Restaurant Management
            </p>

            <h1 class="mt-2 text-4xl font-black tracking-tight text-slate-950">
                Restaurant settings
            </h1>

            <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-slate-600">
                Manage your restaurant identity, customer contact information, operating hours, delivery rules, branding and public availability.
            </p>
        </div>

        <a
            href="{{ route('home') }}"
            target="_blank"
            rel="noopener"
            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50"
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
    </header>

    {{-- Operational Status --}}
    <section class="relative overflow-hidden rounded-[1.5rem] border border-orange-100 bg-gradient-to-r from-orange-50 via-white to-amber-50 p-4 shadow-sm sm:p-5">
        <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-orange-200/50 blur-3xl"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl shadow-lg"
                    x-bind:class="isOpen && isActive
                        ? 'bg-emerald-600 text-white shadow-emerald-600/20'
                        : 'bg-orange-600 text-white shadow-orange-600/20'"
                >
                    <svg
                        x-show="isOpen && isActive"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M4 10h16M5 10v10h14V10M3 10l2-6h14l2 6" />
                        <path d="M9 20v-6h6v6" />
                    </svg>

                    <svg
                        x-show="! (isOpen && isActive)"
                        x-cloak
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 7v5M12 16h.01" />
                    </svg>
                </span>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-black text-slate-950">
                            Current restaurant configuration
                        </p>

                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em]"
                            x-bind:class="isOpen
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-red-100 text-red-700'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                x-bind:class="isOpen
                                    ? 'bg-emerald-500'
                                    : 'bg-red-500'"
                            ></span>

                            <span x-text="isOpen ? 'Open for orders' : 'Ordering closed'"></span>
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em]"
                            x-bind:class="isActive
                                ? 'bg-blue-100 text-blue-700'
                                : 'bg-slate-200 text-slate-600'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                x-bind:class="isActive
                                    ? 'bg-blue-500'
                                    : 'bg-slate-400'"
                            ></span>

                            <span x-text="isActive ? 'Publicly visible' : 'Website hidden'"></span>
                        </span>
                    </div>

                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500 sm:text-sm">
                        Changes affect the public menu, cart, delivery fees and checkout experience.
                    </p>
                </div>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                @foreach (['Identity', 'Operations', 'Branding', 'Status'] as $index => $step)
                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[10px] font-black text-slate-600 shadow-sm">
                        <span class="grid h-5 w-5 place-items-center rounded-full bg-orange-100 text-[9px] text-orange-700">
                            {{ $index + 1 }}
                        </span>

                        {{ $step }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Validation Summary --}}
    @if ($errors->any())
        <section
            role="alert"
            aria-live="polite"
            class="rounded-[1.5rem] border border-red-200 bg-red-50 p-4 shadow-sm sm:p-5"
        >
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
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
                </span>

                <div class="min-w-0">
                    <p class="font-black text-red-900">
                        Some settings need your attention
                    </p>

                    <p class="mt-1 text-sm font-semibold text-red-700">
                        Review the highlighted fields and submit the form again.
                    </p>

                    <div class="mt-3 grid gap-1 sm:grid-cols-2">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-start gap-2 text-xs font-semibold leading-5 text-red-700">
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    <form
        id="restaurant-settings-form"
        action="{{ route('admin.settings.restaurant.update') }}"
        method="POST"
        enctype="multipart/form-data"
        class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_390px] xl:items-start xl:gap-6"
        x-on:submit="submitting = true"
    >
        @csrf
        @method('PUT')

        {{-- Form Content --}}
        <main class="min-w-0 space-y-5">
            {{-- Step 1: Identity and Contact --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-600 text-sm font-black text-white shadow-lg shadow-orange-600/20">
                            1
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                                Restaurant Identity
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                                Public information and contact
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500 sm:text-sm">
                                Information customers see when browsing, ordering or requesting support.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-4 sm:p-6">
                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Restaurant Name --}}
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-black text-slate-800"
                            >
                                Restaurant Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                name="name"
                                value="{{ $initialName }}"
                                x-model="restaurantName"
                                required
                                maxlength="150"
                                autocomplete="organization"
                                placeholder="FreshBite Restaurant"
                                class="mt-2 min-h-12 w-full rounded-xl border bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('name') border-red-300 focus:border-red-400 @else border-slate-200 focus:border-orange-400 @enderror"
                            >

                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold text-slate-500">
                                    Use the public restaurant name.
                                </p>

                                <span
                                    class="text-[10px] font-bold text-slate-400"
                                    x-text="`${restaurantName.length}/150`"
                                ></span>
                            </div>

                            @error('name')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <label
                                    for="slug"
                                    class="text-sm font-black text-slate-800"
                                >
                                    URL Slug
                                </label>

                                <span class="text-[10px] font-bold text-slate-400">
                                    Auto-generated
                                </span>
                            </div>

                            <input
                                id="slug"
                                name="slug"
                                value="{{ $initialSlug }}"
                                x-model="slug"
                                maxlength="180"
                                autocomplete="off"
                                placeholder="freshbite-restaurant"
                                class="mt-2 min-h-12 w-full rounded-xl border bg-slate-50 px-4 py-3 font-mono text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('slug') border-red-300 focus:border-red-400 @else border-slate-200 focus:border-orange-400 @enderror"
                            >

                            <div class="mt-2 flex min-w-0 items-center gap-2 rounded-lg bg-slate-50 px-3 py-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-3.5 w-3.5 shrink-0 text-slate-400"
                                >
                                    <path d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1" />
                                    <path d="M14 11a5 5 0 0 0-7.1-.1l-2 2A5 5 0 0 0 12 20l1.1-1.1" />
                                </svg>

                                <span class="text-[10px] font-bold text-slate-400">
                                    /restaurant/
                                </span>

                                <span
                                    class="min-w-0 truncate font-mono text-[10px] font-bold text-slate-600"
                                    x-text="displaySlug() || 'restaurant-name'"
                                ></span>
                            </div>

                            @error('slug')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <label
                                for="short_description"
                                class="text-sm font-black text-slate-800"
                            >
                                Short Description
                            </label>

                            <span class="text-[10px] font-bold text-slate-400">
                                Optional
                            </span>
                        </div>

                        <textarea
                            id="short_description"
                            name="short_description"
                            rows="4"
                            maxlength="500"
                            x-model="shortDescription"
                            placeholder="Describe the restaurant, food style and customer experience."
                            class="mt-2 w-full resize-y rounded-xl border bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('short_description') border-red-300 focus:border-red-400 @else border-slate-200 focus:border-orange-400 @enderror"
                        >{{ $initialDescription }}</textarea>

                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-slate-500">
                                Summarize cuisine, quality and customer experience.
                            </p>

                            <span
                                class="text-[10px] font-bold text-slate-400"
                                x-text="`${shortDescription.length}/500`"
                            ></span>
                        </div>

                        @error('short_description')
                            <p class="mt-2 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="border-t border-slate-100 pt-5">
                        <div class="mb-4">
                            <p class="text-sm font-black text-slate-950">
                                Customer contact details
                            </p>

                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Used for support, order questions and public restaurant information.
                            </p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- Email --}}
                            <div>
                                <label
                                    for="email"
                                    class="block text-sm font-black text-slate-800"
                                >
                                    Email Address
                                </label>

                                <div class="relative mt-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    >
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <path d="m3 7 9 6 9-6" />
                                    </svg>

                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value="{{ $initialEmail }}"
                                        x-model="email"
                                        autocomplete="email"
                                        placeholder="orders@example.com"
                                        class="min-h-12 w-full rounded-xl border bg-slate-50 py-3 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('email') border-red-300 focus:border-red-400 @else border-slate-200 focus:border-orange-400 @enderror"
                                    >
                                </div>

                                @error('email')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label
                                    for="phone"
                                    class="block text-sm font-black text-slate-800"
                                >
                                    Phone Number
                                </label>

                                <div class="relative mt-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    >
                                        <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3" />
                                    </svg>

                                    <input
                                        id="phone"
                                        name="phone"
                                        type="tel"
                                        value="{{ $initialPhone }}"
                                        x-model="phone"
                                        autocomplete="tel"
                                        placeholder="+92 300 0000000"
                                        class="min-h-12 w-full rounded-xl border bg-slate-50 py-3 pl-11 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('phone') border-red-300 focus:border-red-400 @else border-slate-200 focus:border-orange-400 @enderror"
                                    >
                                </div>

                                @error('phone')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="mt-5">
                            <label
                                for="address"
                                class="block text-sm font-black text-slate-800"
                            >
                                Restaurant Address
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                x-model="address"
                                autocomplete="street-address"
                                placeholder="Street, area, city and country"
                                class="mt-2 w-full resize-y rounded-xl border bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4 focus:ring-orange-100 @error('address') border-red-300 focus:border-red-400 @else border-slate-200 focus:border-orange-400 @enderror"
                            >{{ $initialAddress }}</textarea>

                            @error('address')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Step 2: Operations --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-100 text-sm font-black text-orange-700">
                            2
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                                Restaurant Operations
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                                Hours and ordering rules
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500 sm:text-sm">
                                Configure operating hours, delivery fees and the minimum checkout amount.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-4 sm:p-6">
                    {{-- Opening Hours --}}
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-blue-600 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>

                            <div>
                                <p class="text-sm font-black text-blue-950">
                                    Standard operating hours
                                </p>

                                <p class="mt-1 text-xs font-semibold leading-5 text-blue-700">
                                    These times inform customers when the restaurant normally accepts orders.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="opening_time"
                                    class="block text-xs font-black uppercase tracking-[0.12em] text-blue-800"
                                >
                                    Opening Time
                                </label>

                                <input
                                    id="opening_time"
                                    name="opening_time"
                                    type="time"
                                    value="{{ $openingTime }}"
                                    x-model="openingTime"
                                    class="mt-2 min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-sm font-black text-slate-900 outline-none transition focus:ring-4 focus:ring-blue-100 @error('opening_time') border-red-300 focus:border-red-400 @else border-blue-200 focus:border-blue-400 @enderror"
                                >

                                @error('opening_time')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="closing_time"
                                    class="block text-xs font-black uppercase tracking-[0.12em] text-blue-800"
                                >
                                    Closing Time
                                </label>

                                <input
                                    id="closing_time"
                                    name="closing_time"
                                    type="time"
                                    value="{{ $closingTime }}"
                                    x-model="closingTime"
                                    class="mt-2 min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-sm font-black text-slate-900 outline-none transition focus:ring-4 focus:ring-blue-100 @error('closing_time') border-red-300 focus:border-red-400 @else border-blue-200 focus:border-blue-400 @enderror"
                                >

                                @error('closing_time')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl bg-white/70 px-4 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-blue-600">
                                Customer display
                            </p>

                            <p class="mt-1 text-sm font-black text-blue-950">
                                <span x-text="formatTime(openingTime)"></span>
                                <span class="mx-1 text-blue-300">—</span>
                                <span x-text="formatTime(closingTime)"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Ordering Rules --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4 sm:p-5">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <path d="M3 7h11v10H3z" />
                                        <path d="M14 10h4l3 3v4h-7z" />
                                    </svg>
                                </span>

                                <div>
                                    <label
                                        for="delivery_fee"
                                        class="block text-sm font-black text-orange-950"
                                    >
                                        Delivery Fee
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <p class="mt-1 text-xs font-semibold text-orange-700">
                                        Added to every delivery order.
                                    </p>
                                </div>
                            </div>

                            <div class="relative mt-4">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-orange-700">
                                    Rs.
                                </span>

                                <input
                                    id="delivery_fee"
                                    name="delivery_fee"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value="{{ $initialDeliveryFee }}"
                                    x-model="deliveryFee"
                                    required
                                    inputmode="decimal"
                                    class="min-h-12 w-full rounded-xl border bg-white py-3 pl-12 pr-4 text-base font-black text-slate-950 outline-none transition focus:ring-4 focus:ring-orange-100 @error('delivery_fee') border-red-300 focus:border-red-400 @else border-orange-200 focus:border-orange-400 @enderror"
                                >
                            </div>

                            @error('delivery_fee')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 sm:p-5">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-emerald-600 shadow-sm">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <rect x="3" y="6" width="18" height="12" rx="2" />
                                        <circle cx="12" cy="12" r="2" />
                                    </svg>
                                </span>

                                <div>
                                    <label
                                        for="minimum_order_amount"
                                        class="block text-sm font-black text-emerald-950"
                                    >
                                        Minimum Order
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <p class="mt-1 text-xs font-semibold text-emerald-700">
                                        Required cart subtotal at checkout.
                                    </p>
                                </div>
                            </div>

                            <div class="relative mt-4">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-emerald-700">
                                    Rs.
                                </span>

                                <input
                                    id="minimum_order_amount"
                                    name="minimum_order_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value="{{ $initialMinimumOrder }}"
                                    x-model="minimumOrder"
                                    required
                                    inputmode="decimal"
                                    class="min-h-12 w-full rounded-xl border bg-white py-3 pl-12 pr-4 text-base font-black text-slate-950 outline-none transition focus:ring-4 focus:ring-emerald-100 @error('minimum_order_amount') border-red-300 focus:border-red-400 @else border-emerald-200 focus:border-emerald-400 @enderror"
                                >
                            </div>

                            @error('minimum_order_amount')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Step 3: Branding --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-100 text-sm font-black text-orange-700">
                            3
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                                Restaurant Branding
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                                Logo and cover image
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500 sm:text-sm">
                                Upload recognizable, high-quality assets for the public ordering experience.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 p-4 sm:p-6 md:grid-cols-2">
                    {{-- Logo Upload --}}
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-950">
                                    Restaurant Logo
                                </p>

                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Square image recommended.
                                </p>
                            </div>

                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-black text-slate-500">
                                1:1
                            </span>
                        </div>

                        <label
                            for="logo"
                            class="group relative mt-3 flex min-h-[190px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-[1.5rem] border-2 border-dashed border-orange-200 bg-orange-50/60 px-5 py-7 text-center transition hover:border-orange-400 hover:bg-orange-50"
                        >
                            <template x-if="logoPreview">
                                <img
                                    x-bind:src="logoPreview"
                                    alt="Restaurant logo preview"
                                    class="absolute inset-0 h-full w-full object-cover opacity-20"
                                >
                            </template>

                            <div class="absolute inset-0 bg-white/20 backdrop-blur-[1px]"></div>

                            <span class="relative grid h-16 w-16 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-white text-lg font-black text-orange-700 shadow-xl">
                                <template x-if="logoPreview">
                                    <img
                                        x-bind:src="logoPreview"
                                        alt="Selected logo"
                                        class="h-full w-full object-cover"
                                    >
                                </template>

                                <span
                                    x-show="! logoPreview"
                                    x-text="restaurantName
                                        ? restaurantName.substring(0, 2).toUpperCase()
                                        : 'FB'"
                                ></span>
                            </span>

                            <p class="relative mt-3 text-sm font-black text-slate-950">
                                <span x-show="! logoFileName">
                                    Choose logo
                                </span>

                                <span
                                    x-show="logoFileName"
                                    x-cloak
                                >
                                    Replace logo
                                </span>
                            </p>

                            <p class="relative mt-1 text-xs font-semibold text-slate-500">
                                JPG, PNG or WEBP
                            </p>

                            <input
                                id="logo"
                                name="logo"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                x-ref="logoInput"
                                x-on:change="handleLogo($event)"
                                class="sr-only"
                            >
                        </label>

                        <div
                            x-show="logoFileName"
                            x-cloak
                            class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-3"
                        >
                            <p
                                class="min-w-0 truncate text-xs font-semibold text-emerald-800"
                                x-text="logoFileName"
                            ></p>

                            <button
                                type="button"
                                x-on:click="resetLogo"
                                class="shrink-0 rounded-lg px-2 py-1 text-xs font-black text-red-600 hover:bg-red-50"
                            >
                                Undo
                            </button>
                        </div>

                        @error('logo')
                            <p class="mt-2 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Cover Upload --}}
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-slate-950">
                                    Cover Image
                                </p>

                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Landscape food or restaurant image.
                                </p>
                            </div>

                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-black text-slate-500">
                                16:9
                            </span>
                        </div>

                        <label
                            for="cover_image"
                            class="group relative mt-3 flex min-h-[190px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-[1.5rem] border-2 border-dashed border-orange-200 bg-orange-50/60 px-5 py-7 text-center transition hover:border-orange-400 hover:bg-orange-50"
                        >
                            <template x-if="coverPreview">
                                <img
                                    x-bind:src="coverPreview"
                                    alt="Restaurant cover preview"
                                    class="absolute inset-0 h-full w-full object-cover"
                                >
                            </template>

                            <div
                                class="absolute inset-0"
                                x-bind:class="coverPreview
                                    ? 'bg-slate-950/45'
                                    : 'bg-transparent'"
                            ></div>

                            <span class="relative grid h-14 w-14 place-items-center rounded-2xl bg-white text-orange-600 shadow-xl">
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
                            </span>

                            <p
                                class="relative mt-3 text-sm font-black"
                                x-bind:class="coverPreview
                                    ? 'text-white'
                                    : 'text-slate-950'"
                            >
                                <span x-show="! coverFileName">
                                    Choose cover image
                                </span>

                                <span
                                    x-show="coverFileName"
                                    x-cloak
                                >
                                    Replace cover image
                                </span>
                            </p>

                            <p
                                class="relative mt-1 text-xs font-semibold"
                                x-bind:class="coverPreview
                                    ? 'text-white/75'
                                    : 'text-slate-500'"
                            >
                                Landscape image recommended
                            </p>

                            <input
                                id="cover_image"
                                name="cover_image"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                x-ref="coverInput"
                                x-on:change="handleCover($event)"
                                class="sr-only"
                            >
                        </label>

                        <div
                            x-show="coverFileName"
                            x-cloak
                            class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-3"
                        >
                            <p
                                class="min-w-0 truncate text-xs font-semibold text-emerald-800"
                                x-text="coverFileName"
                            ></p>

                            <button
                                type="button"
                                x-on:click="resetCover"
                                class="shrink-0 rounded-lg px-2 py-1 text-xs font-black text-red-600 hover:bg-red-50"
                            >
                                Undo
                            </button>
                        </div>

                        @error('cover_image')
                            <p class="mt-2 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Step 4: Visibility --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-100 text-sm font-black text-orange-700">
                            4
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                                Availability Controls
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                                Ordering and public visibility
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-slate-500 sm:text-sm">
                                Control ordering separately from public website visibility.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 p-4 sm:p-6 md:grid-cols-2">
                    {{-- Open Toggle --}}
                    <label
                        class="cursor-pointer rounded-2xl border p-4 transition sm:p-5"
                        x-bind:class="isOpen
                            ? 'border-emerald-200 bg-emerald-50'
                            : 'border-red-200 bg-red-50'"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <span
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                                    x-bind:class="isOpen
                                        ? 'text-emerald-600'
                                        : 'text-red-600'"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg>
                                </span>

                                <span>
                                    <span
                                        class="block text-sm font-black"
                                        x-bind:class="isOpen
                                            ? 'text-emerald-950'
                                            : 'text-red-950'"
                                        x-text="isOpen
                                            ? 'Open for ordering'
                                            : 'Ordering is closed'"
                                    ></span>

                                    <span
                                        class="mt-1 block text-xs font-semibold leading-5"
                                        x-bind:class="isOpen
                                            ? 'text-emerald-700'
                                            : 'text-red-700'"
                                        x-text="isOpen
                                            ? 'Customers can place new orders.'
                                            : 'Checkout should not accept new orders.'"
                                    ></span>
                                </span>
                            </div>

                            <span class="relative mt-1 shrink-0">
                                <input
                                    type="hidden"
                                    name="is_open"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="is_open"
                                    value="1"
                                    x-model="isOpen"
                                    @checked($initialIsOpen)
                                    class="peer sr-only"
                                >

                                <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-emerald-600 peer-focus:ring-4 peer-focus:ring-emerald-100"></span>

                                <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                        </div>
                    </label>

                    {{-- Active Toggle --}}
                    <label
                        class="cursor-pointer rounded-2xl border p-4 transition sm:p-5"
                        x-bind:class="isActive
                            ? 'border-blue-200 bg-blue-50'
                            : 'border-slate-200 bg-slate-50'"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <span
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                                    x-bind:class="isActive
                                        ? 'text-blue-600'
                                        : 'text-slate-500'"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" />
                                        <circle cx="12" cy="12" r="2.5" />
                                    </svg>
                                </span>

                                <span>
                                    <span
                                        class="block text-sm font-black"
                                        x-bind:class="isActive
                                            ? 'text-blue-950'
                                            : 'text-slate-950'"
                                        x-text="isActive
                                            ? 'Visible on website'
                                            : 'Hidden from website'"
                                    ></span>

                                    <span
                                        class="mt-1 block text-xs font-semibold leading-5"
                                        x-bind:class="isActive
                                            ? 'text-blue-700'
                                            : 'text-slate-500'"
                                        x-text="isActive
                                            ? 'Restaurant details are publicly visible.'
                                            : 'The restaurant remains saved but hidden.'"
                                    ></span>
                                </span>
                            </div>

                            <span class="relative mt-1 shrink-0">
                                <input
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    x-model="isActive"
                                    @checked($initialIsActive)
                                    class="peer sr-only"
                                >

                                <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-blue-600 peer-focus:ring-4 peer-focus:ring-blue-100"></span>

                                <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                        </div>
                    </label>
                </div>
            </section>
        </main>

        {{-- Preview Sidebar --}}
        <aside class="space-y-5 xl:sticky xl:top-24">
            {{-- Live Public Preview --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-xl shadow-orange-900/5">
                <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-5 py-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                            Live Preview
                        </p>

                        <h2 class="mt-1 text-lg font-black text-slate-950">
                            Customer restaurant card
                        </h2>
                    </div>

                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em]"
                        x-bind:class="isActive
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-red-50 text-red-700'"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            x-bind:class="isActive
                                ? 'bg-emerald-500'
                                : 'bg-red-500'"
                        ></span>

                        <span x-text="isActive ? 'Public' : 'Hidden'"></span>
                    </span>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white shadow-sm">
                        {{-- Cover --}}
                        <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-orange-100 via-amber-50 to-red-100">
                            <template x-if="coverPreview">
                                <img
                                    x-bind:src="coverPreview"
                                    alt="Restaurant cover preview"
                                    class="absolute inset-0 h-full w-full object-cover"
                                >
                            </template>

                            <div
                                x-show="! coverPreview"
                                class="absolute inset-0 grid place-items-center"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    class="h-12 w-12 text-orange-300"
                                >
                                    <rect x="3" y="4" width="18" height="16" rx="2" />
                                    <circle cx="8.5" cy="9" r="1.5" />
                                    <path d="m21 15-5-5L5 20" />
                                </svg>
                            </div>

                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/10 to-transparent"></div>

                            <div class="absolute left-3 top-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/90 px-3 py-1.5 text-[9px] font-black shadow-sm backdrop-blur"
                                    x-bind:class="isOpen
                                        ? 'text-emerald-700'
                                        : 'text-red-700'"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        x-bind:class="isOpen
                                            ? 'bg-emerald-500'
                                            : 'bg-red-500'"
                                    ></span>

                                    <span x-text="isOpen
                                        ? 'Open for orders'
                                        : 'Currently closed'"
                                    ></span>
                                </span>
                            </div>

                            <div class="absolute inset-x-0 bottom-0 flex items-end gap-3 p-4">
                                <span class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-white text-lg font-black text-orange-700 shadow-xl">
                                    <template x-if="logoPreview">
                                        <img
                                            x-bind:src="logoPreview"
                                            alt="Restaurant logo preview"
                                            class="h-full w-full object-cover"
                                        >
                                    </template>

                                    <span
                                        x-show="! logoPreview"
                                        x-text="restaurantName
                                            ? restaurantName.substring(0, 2).toUpperCase()
                                            : 'FB'"
                                    ></span>
                                </span>

                                <div class="min-w-0 pb-1">
                                    <h3
                                        class="truncate text-xl font-black text-white"
                                        x-text="restaurantName.trim() || 'Restaurant Name'"
                                    ></h3>

                                    <p
                                        class="mt-1 truncate text-[10px] font-semibold text-white/70"
                                        x-text="address.trim() || 'Restaurant address'"
                                    ></p>
                                </div>
                            </div>
                        </div>

                        {{-- Preview Content --}}
                        <div class="p-4">
                            <p
                                class="line-clamp-3 min-h-[60px] text-xs font-semibold leading-5 text-slate-600"
                                x-text="shortDescription.trim()
                                    || 'Your restaurant description will appear here for customers.'"
                            ></p>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-orange-50 px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-orange-600">
                                        Delivery
                                    </p>

                                    <p class="mt-1 text-sm font-black text-orange-950">
                                        Rs.
                                        <span x-text="Number(deliveryFee || 0).toLocaleString()"></span>
                                    </p>
                                </div>

                                <div class="rounded-xl bg-emerald-50 px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-emerald-600">
                                        Minimum
                                    </p>

                                    <p class="mt-1 text-sm font-black text-emerald-950">
                                        Rs.
                                        <span x-text="Number(minimumOrder || 0).toLocaleString()"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="mt-2 flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-blue-600 shadow-sm">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg>
                                </span>

                                <div>
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                        Operating hours
                                    </p>

                                    <p class="mt-1 text-xs font-black text-slate-950">
                                        <span x-text="formatTime(openingTime)"></span>
                                        <span class="mx-1 text-slate-300">—</span>
                                        <span x-text="formatTime(closingTime)"></span>
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                disabled
                                class="mt-4 min-h-11 w-full rounded-xl px-4 py-3 text-sm font-black"
                                x-bind:class="isOpen && isActive
                                    ? 'bg-orange-600 text-white'
                                    : 'cursor-not-allowed bg-slate-200 text-slate-500'"
                            >
                                <span x-text="isOpen && isActive
                                    ? 'Browse Menu'
                                    : (
                                        ! isActive
                                            ? 'Restaurant Hidden'
                                            : 'Ordering Closed'
                                    )"
                                ></span>
                            </button>
                        </div>
                    </div>

                    <p class="mt-3 text-center text-[10px] font-semibold leading-4 text-slate-400">
                        Preview represents the customer-facing restaurant presentation.
                    </p>
                </div>
            </section>

            {{-- Configuration Summary --}}
            <section class="rounded-[1.75rem] border border-orange-100 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                    Configuration Summary
                </p>

                <h2 class="mt-1 text-lg font-black text-slate-950">
                    Current customer experience
                </h2>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-3">
                        <span class="text-xs font-semibold text-slate-500">
                            Ordering
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-black"
                            x-bind:class="isOpen
                                ? 'text-emerald-700'
                                : 'text-red-700'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                x-bind:class="isOpen
                                    ? 'bg-emerald-500'
                                    : 'bg-red-500'"
                            ></span>

                            <span x-text="isOpen ? 'Open' : 'Closed'"></span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-3">
                        <span class="text-xs font-semibold text-slate-500">
                            Public visibility
                        </span>

                        <span
                            class="text-xs font-black"
                            x-bind:class="isActive
                                ? 'text-blue-700'
                                : 'text-slate-500'"
                            x-text="isActive ? 'Visible' : 'Hidden'"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-3">
                        <span class="text-xs font-semibold text-slate-500">
                            Delivery fee
                        </span>

                        <span class="text-xs font-black text-slate-950">
                            Rs.
                            <span x-text="Number(deliveryFee || 0).toLocaleString()"></span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-3 py-3">
                        <span class="text-xs font-semibold text-slate-500">
                            Minimum order
                        </span>

                        <span class="text-xs font-black text-slate-950">
                            Rs.
                            <span x-text="Number(minimumOrder || 0).toLocaleString()"></span>
                        </span>
                    </div>
                </div>
            </section>

            {{-- Desktop Actions --}}
            <section class="hidden rounded-[1.75rem] border border-orange-100 bg-white p-5 shadow-sm xl:block">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path d="M5 5h12l2 2v12H5z" />
                            <path d="M8 5v5h8V5M8 19v-6h8v6" />
                        </svg>
                    </span>

                    <div>
                        <p class="text-sm font-black text-slate-950">
                            Ready to save?
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                            Review the public preview and operational settings before continuing.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    x-bind:disabled="submitting"
                    class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <svg
                        x-show="submitting"
                        x-cloak
                        class="h-5 w-5 animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                        ></path>
                    </svg>

                    <svg
                        x-show="! submitting"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M5 5h12l2 2v12H5z" />
                        <path d="M8 5v5h8V5M8 19v-6h8v6" />
                    </svg>

                    <span
                        x-text="submitting
                            ? 'Saving settings...'
                            : 'Save Restaurant Settings'"
                    ></span>
                </button>

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
                >
                    Back to Dashboard
                </a>
            </section>
        </aside>
    </form>

    {{-- Persistent Mobile and Tablet Actions --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_rgba(15,23,42,0.14)] backdrop-blur xl:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.dashboard') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-orange-200 bg-orange-50 text-orange-700 transition active:scale-95"
                aria-label="Back to dashboard"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6"
                    />
                </svg>
            </a>

            <button
                type="submit"
                form="restaurant-settings-form"
                x-bind:disabled="submitting"
                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg
                    x-show="submitting"
                    x-cloak
                    class="h-5 w-5 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"
                    ></path>
                </svg>

                <svg
                    x-show="! submitting"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M5 5h12l2 2v12H5z" />
                    <path d="M8 5v5h8V5M8 19v-6h8v6" />
                </svg>

                <span
                    x-text="submitting
                        ? 'Saving settings...'
                        : 'Save Restaurant Settings'"
                ></span>
            </button>
        </div>
    </div>
</div>

@endcomponent
