@component('layouts.admin', ['title' => 'Restaurant Settings'])
@php
$storedLogo = $restaurant->logo_url ?? null;
$storedCover = $restaurant->cover_image_url ?? null;
$removeLogoRequested = (bool) old('remove_logo', false);
$removeCoverRequested = (bool) old('remove_cover_image', false);
$currentLogo = $removeLogoRequested ? null : $storedLogo;
$currentCover = $removeCoverRequested ? null : $storedCover;

    $initialName = old(
        'name',
        $restaurant->name ?? ''
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

    $initialTimezone = old('timezone', $restaurant->timezone ?? config('app.timezone', 'Australia/Sydney'));
    $initialLatitude = old('latitude', $restaurant->latitude ?? '');
    $initialLongitude = old('longitude', $restaurant->longitude ?? '');
    $initialFormattedAddress = old('formatted_address', $restaurant->formatted_address ?? $restaurant->address ?? '');
@endphp

<div
    x-data="{
        restaurantName: @js($initialName),
        shortDescription: @js($initialDescription),
        email: @js($initialEmail),
        phone: @js($initialPhone),
        address: @js($initialAddress),
        deliveryFee: @js((string) $initialDeliveryFee),
        minimumOrder: @js((string) $initialMinimumOrder),
        openingTime: @js($openingTime),
        closingTime: @js($closingTime),
        timezone: @js($initialTimezone),
        latitude: @js((string) $initialLatitude),
        longitude: @js((string) $initialLongitude),
        formattedAddress: @js($initialFormattedAddress),

        logoPreview: @js($currentLogo),
        originalLogo: @js($storedLogo),
        logoFileName: '',
        removeLogoOnSave: @js($removeLogoRequested),

        coverPreview: @js($currentCover),
        originalCover: @js($storedCover),
        coverFileName: '',
        removeCoverOnSave: @js($removeCoverRequested),

        isOpen: {{ $initialIsOpen ? 'true' : 'false' }},
        submitting: false,
        confirmLogoRemoval: false,
        confirmCoverRemoval: false,

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
            this.removeLogoOnSave = false;
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
            this.removeCoverOnSave = false;
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
            this.removeLogoOnSave = false;

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
            this.removeCoverOnSave = false;

            if (this.$refs.coverInput) {
                this.$refs.coverInput.value = '';
            }
        },

        markLogoForRemoval() {
            this.resetLogo();
            this.logoPreview = null;
            this.removeLogoOnSave = true;
        },

        undoLogoRemoval() {
            this.removeLogoOnSave = false;
            this.logoPreview = this.originalLogo;
        },

        markCoverForRemoval() {
            this.resetCover();
            this.coverPreview = null;
            this.removeCoverOnSave = true;
        },

        undoCoverRemoval() {
            this.removeCoverOnSave = false;
            this.coverPreview = this.originalCover;
        }
    }"
    class="space-y-5 pb-28 sm:space-y-6 xl:pb-8"
>
    {{-- Mobile Header --}}
    <header class="xl:hidden">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Restaurant Management
                </p>

                <h1 class="mt-1 text-2xl font-black tracking-tight text-warm-950">
                    Restaurant settings
                </h1>

                <p class="mt-1 text-sm font-semibold leading-5 text-warm-500">
                    Manage ordering, delivery and public information.
                </p>
            </div>

            <a
                href="{{ route('home') }}"
                target="_blank"
                rel="noopener"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-brand-600 shadow-sm transition active:scale-95"
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
            <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-500">
                Restaurant Management
            </p>

            <h1 class="mt-2 text-4xl font-black tracking-tight text-warm-950">
                Restaurant settings
            </h1>

            <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-warm-600">
                Manage your restaurant identity, customer contact information, operating hours, delivery rules, branding and public availability.
            </p>
        </div>

        <a
            href="{{ route('home') }}"
            target="_blank"
            rel="noopener"
            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-white px-5 py-3 text-sm font-black text-brand-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-brand-50"
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
    <section class="relative overflow-hidden rounded-[1.5rem] border border-warm-200 bg-gradient-to-r from-brand-50 via-white to-gold-50 p-4 shadow-sm sm:p-5">
        <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-brand-200/50 blur-3xl"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <span
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl shadow-lg"
                    x-bind:class="isOpen
                        ? 'bg-leaf-700 text-white shadow-leaf-700/20'
                        : 'bg-brand-500 text-white shadow-brand-500/20'"
                >
                    <svg
                        x-show="isOpen"
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
                        x-show="! isOpen"
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
                        <p class="text-sm font-black text-warm-950">
                            Current restaurant configuration
                        </p>

                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em]"
                            x-bind:class="isOpen
                                ? 'bg-leaf-100 text-leaf-700'
                                : 'bg-red-100 text-red-700'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                x-bind:class="isOpen
                                    ? 'bg-leaf-500'
                                    : 'bg-red-500'"
                            ></span>

                            <span x-text="isOpen ? 'Open for orders' : 'Ordering closed'"></span>
                        </span>

                    </div>

                    <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                        Changes affect the public menu, cart, delivery fees and checkout experience.
                    </p>
                </div>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                @foreach (['Identity', 'Operations', 'Branding', 'Availability'] as $index => $step)
                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[10px] font-black text-warm-600 shadow-sm">
                        <span class="grid h-5 w-5 place-items-center rounded-full bg-brand-100 text-[9px] text-brand-600">
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
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500 text-sm font-black text-white shadow-lg shadow-brand-500/20">
                            1
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Restaurant Identity
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Public information and contact
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
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
                                class="block text-sm font-black text-warm-900"
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
                                placeholder="Arcade Kebab House Restaurant"
                                class="mt-2 min-h-12 w-full rounded-xl border bg-warm-50 px-4 py-3 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('name') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >

                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold text-warm-500">
                                    Use the public restaurant name.
                                </p>

                                <span
                                    class="text-[10px] font-bold text-warm-500"
                                    x-text="`${restaurantName.length}/150`"
                                ></span>
                            </div>

                            @error('name')
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
                                class="text-sm font-black text-warm-900"
                            >
                                Short Description
                            </label>

                            <span class="text-[10px] font-bold text-warm-500">
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
                            class="mt-2 w-full resize-y rounded-xl border bg-warm-50 px-4 py-3 text-sm font-semibold leading-6 text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('short_description') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                        >{{ $initialDescription }}</textarea>

                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-warm-500">
                                Summarize cuisine, quality and customer experience.
                            </p>

                            <span
                                class="text-[10px] font-bold text-warm-500"
                                x-text="`${shortDescription.length}/500`"
                            ></span>
                        </div>

                        @error('short_description')
                            <p class="mt-2 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="border-t border-warm-100 pt-5">
                        <div class="mb-4">
                            <p class="text-sm font-black text-warm-950">
                                Customer contact details
                            </p>

                            <p class="mt-1 text-xs font-semibold text-warm-500">
                                Used for support, order questions and public restaurant information.
                            </p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            {{-- Email --}}
                            <div>
                                <label
                                    for="email"
                                    class="block text-sm font-black text-warm-900"
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
                                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
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
                                        class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-11 pr-4 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('email') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
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
                                    class="block text-sm font-black text-warm-900"
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
                                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
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
                                        class="min-h-12 w-full rounded-xl border bg-warm-50 py-3 pl-11 pr-4 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('phone') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
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
                                class="block text-sm font-black text-warm-900"
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
                                class="mt-2 w-full resize-y rounded-xl border bg-warm-50 px-4 py-3 text-sm font-semibold leading-6 text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('address') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >{{ $initialAddress }}</textarea>

	                            @error('address')
	                                <p class="mt-2 text-xs font-semibold text-red-600">
	                                    {{ $message }}
	                                </p>
	                            @enderror
	                        </div>

                            <input
                                id="formatted_address"
                                name="formatted_address"
                                type="hidden"
                                x-model="formattedAddress"
                            >

                            {{-- Restaurant Location --}}
                            <div class="mt-5 rounded-2xl border border-warm-200 bg-warm-50 p-4 sm:p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-sm font-black text-warm-950">
                                            Map location
                                        </p>

                                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                            Pin the restaurant location for accurate public contact and delivery context.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        id="restaurant-use-current-location"
                                        class="inline-flex min-h-10 items-center justify-center rounded-xl border border-brand-200 bg-white px-4 py-2 text-xs font-black text-brand-600 shadow-sm transition hover:bg-brand-50"
                                    >
                                        Use current location
                                    </button>
                                </div>

                                <div
                                    id="restaurant-map"
                                    class="mt-4 h-64 overflow-hidden rounded-2xl border border-warm-200 bg-white"
                                    data-latitude="{{ $initialLatitude }}"
                                    data-longitude="{{ $initialLongitude }}"
                                ></div>

	                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="latitude" class="block text-xs font-black uppercase tracking-[0.12em] text-warm-600">
                                            Latitude
                                        </label>

                                        <input
                                            id="latitude"
                                            name="latitude"
                                            type="number"
                                            step="0.0000001"
                                            min="-90"
                                            max="90"
                                            value="{{ $initialLatitude }}"
                                            x-model="latitude"
                                            class="mt-2 min-h-12 w-full rounded-xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
                                        >

                                        @error('latitude')
                                            <p class="mt-2 text-xs font-semibold text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="longitude" class="block text-xs font-black uppercase tracking-[0.12em] text-warm-600">
                                            Longitude
                                        </label>

                                        <input
                                            id="longitude"
                                            name="longitude"
                                            type="number"
                                            step="0.0000001"
                                            min="-180"
                                            max="180"
                                            value="{{ $initialLongitude }}"
                                            x-model="longitude"
                                            class="mt-2 min-h-12 w-full rounded-xl border border-warm-200 bg-white px-4 py-3 text-sm font-semibold text-warm-900 outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100"
                                        >

                                        @error('longitude')
                                            <p class="mt-2 text-xs font-semibold text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
	                    </div>
	                </div>
	            </section>

            {{-- Step 2: Operations --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            2
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Restaurant Operations
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Hours and ordering rules
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
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
                                    class="mt-2 min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-sm font-black text-warm-900 outline-none transition focus:ring-4 focus:ring-blue-100 @error('opening_time') border-red-300 focus:border-red-400 @else border-blue-200 focus:border-blue-400 @enderror"
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
                                    class="mt-2 min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-sm font-black text-warm-900 outline-none transition focus:ring-4 focus:ring-blue-100 @error('closing_time') border-red-300 focus:border-red-400 @else border-blue-200 focus:border-blue-400 @enderror"
                                >

                                @error('closing_time')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
	                            </div>
	                        </div>

                            <div class="mt-4">
                                <label
                                    for="timezone"
                                    class="block text-xs font-black uppercase tracking-[0.12em] text-blue-800"
                                >
                                    Timezone
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="timezone"
                                    name="timezone"
                                    required
                                    x-model="timezone"
                                    class="mt-2 min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-sm font-black text-warm-900 outline-none transition focus:ring-4 focus:ring-blue-100 @error('timezone') border-red-300 focus:border-red-400 @else border-blue-200 focus:border-blue-400 @enderror"
                                >
                                    @foreach ($timezones as $timezoneValue => $timezoneLabel)
                                        <option value="{{ $timezoneValue }}">
                                            {{ $timezoneLabel }}
                                        </option>
                                    @endforeach
                                </select>

                                <p class="mt-2 text-xs font-semibold leading-5 text-blue-700">
                                    Operating hours are evaluated in this timezone, including overnight closing times.
                                </p>

                                @error('timezone')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
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
                        <div class="rounded-2xl border border-warm-200 bg-brand-50 p-4 sm:p-5">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-brand-500 shadow-sm">
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
                                        class="block text-sm font-black text-brand-900"
                                    >
                                        Delivery Fee
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <p class="mt-1 text-xs font-semibold text-brand-600">
                                        Added to every delivery order.
                                    </p>
                                </div>
                            </div>

                            <div class="relative mt-4">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-brand-600">
	                                    A$
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
                                    class="min-h-12 w-full rounded-xl border bg-white py-3 pl-12 pr-4 text-base font-black text-warm-950 outline-none transition focus:ring-4 focus:ring-brand-100 @error('delivery_fee') border-red-300 focus:border-red-400 @else border-brand-200 focus:border-brand-500 @enderror"
                                >
                            </div>

                            @error('delivery_fee')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="rounded-2xl border border-leaf-100 bg-leaf-50 p-4 sm:p-5">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-leaf-700 shadow-sm">
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
                                        class="block text-sm font-black text-leaf-900"
                                    >
                                        Minimum Order
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <p class="mt-1 text-xs font-semibold text-leaf-700">
                                        Required cart subtotal at checkout.
                                    </p>
                                </div>
                            </div>

                            <div class="relative mt-4">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-leaf-700">
	                                    A$
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
                                    class="min-h-12 w-full rounded-xl border bg-white py-3 pl-12 pr-4 text-base font-black text-warm-950 outline-none transition focus:ring-4 focus:ring-leaf-100 @error('minimum_order_amount') border-red-300 focus:border-red-400 @else border-leaf-100 focus:border-leaf-500 @enderror"
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
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            3
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Restaurant Branding
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Logo and cover image
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Upload recognizable, high-quality assets for the public ordering experience.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 p-4 sm:p-6 md:grid-cols-2">
                    {{-- Logo Upload --}}
                    <div>
                        <input
                            type="hidden"
                            name="remove_logo"
                            x-bind:value="removeLogoOnSave ? '1' : '0'"
                        >

                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-warm-950">
                                    Restaurant Logo
                                </p>

                                <p class="mt-1 text-xs font-semibold text-warm-500">
                                    Square image recommended.
                                </p>
                            </div>

                            <span class="rounded-full bg-warm-100 px-2.5 py-1 text-[9px] font-black text-warm-500">
                                1:1
                            </span>
                        </div>

                        <label
                            for="logo"
                            class="group relative mt-3 flex min-h-[190px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-[1.5rem] border-2 border-dashed border-brand-200 bg-brand-50/60 px-5 py-7 text-center transition hover:border-brand-500 hover:bg-brand-50"
                        >
                            <template x-if="logoPreview">
                                <img
                                    x-bind:src="logoPreview"
                                    alt="Restaurant logo preview"
                                    class="absolute inset-0 h-full w-full object-cover opacity-20"
                                >
                            </template>

                            <div class="absolute inset-0 bg-white/20 backdrop-blur-[1px]"></div>

                            <span class="relative grid h-16 w-16 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-white text-lg font-black text-brand-600 shadow-xl">
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
	                                        ? restaurantName.split(/\s+/).filter(Boolean).slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase()
	                                        : 'AK'"
	                                ></span>
                            </span>

                            <p class="relative mt-3 text-sm font-black text-warm-950">
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

                            <p class="relative mt-1 text-xs font-semibold text-warm-500">
	                                JPG or PNG up to 2MB
                            </p>

                            <input
                                id="logo"
                                name="logo"
                                type="file"
	                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                x-ref="logoInput"
                                x-on:change="handleLogo($event)"
                                class="sr-only"
                            >
                        </label>

                        <div
                            x-show="logoFileName"
                            x-cloak
                            class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-leaf-100 bg-leaf-50 px-3 py-3"
                        >
                            <p
                                class="min-w-0 truncate text-xs font-semibold text-leaf-900"
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

                        @if ($restaurant->logo)
                            <div
                                x-show="! removeLogoOnSave"
                                class="mt-3 rounded-2xl border border-warm-200 bg-white p-3"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black text-warm-950">
                                            Current logo is active
                                        </p>

                                        <p class="mt-1 text-[11px] font-semibold leading-5 text-warm-500">
                                            Remove it with your next save, or delete it immediately.
                                        </p>
                                    </div>

                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500">
                                        <x-ui-icon name="image" class="h-4 w-4" />
                                    </span>
                                </div>

                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        x-on:click="markLogoForRemoval"
                                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-600 transition hover:bg-red-100"
                                    >
                                        <x-ui-icon name="trash" class="h-4 w-4" />
                                        Remove on save
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="confirmLogoRemoval = true"
                                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-warm-200 bg-white px-4 py-2 text-xs font-black text-warm-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                    >
                                        <x-ui-icon name="trash" class="h-4 w-4" />
                                        Delete now
                                    </button>
                                </div>
                            </div>

                            <div
                                x-show="removeLogoOnSave"
                                x-cloak
                                class="mt-3 rounded-2xl border border-red-200 bg-red-50 p-3"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black text-red-700">
                                            Logo will be removed when you save.
                                        </p>

                                        <p class="mt-1 text-[11px] font-semibold leading-5 text-red-700">
                                            The public site will show generated initials instead.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        x-on:click="undoLogoRemoval"
                                        class="shrink-0 rounded-lg bg-white px-2 py-1 text-xs font-black text-red-600 hover:bg-red-100"
                                    >
                                        Undo
                                    </button>
                                </div>
                            </div>
                        @endif
	                    </div>

                    {{-- Cover Upload --}}
                    <div>
                        <input
                            type="hidden"
                            name="remove_cover_image"
                            x-bind:value="removeCoverOnSave ? '1' : '0'"
                        >

                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-black text-warm-950">
                                    Cover Image
                                </p>

                                <p class="mt-1 text-xs font-semibold text-warm-500">
                                    Landscape food or restaurant image.
                                </p>
                            </div>

                            <span class="rounded-full bg-warm-100 px-2.5 py-1 text-[9px] font-black text-warm-500">
                                16:9
                            </span>
                        </div>

                        <label
                            for="cover_image"
                            class="group relative mt-3 flex min-h-[190px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-[1.5rem] border-2 border-dashed border-brand-200 bg-brand-50/60 px-5 py-7 text-center transition hover:border-brand-500 hover:bg-brand-50"
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
                                    ? 'bg-warm-950/45'
                                    : 'bg-transparent'"
                            ></div>

                            <span class="relative grid h-14 w-14 place-items-center rounded-2xl bg-white text-brand-500 shadow-xl">
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
                                    : 'text-warm-950'"
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
                                    : 'text-warm-500'"
                            >
                                Landscape image recommended
                            </p>

                            <input
                                id="cover_image"
                                name="cover_image"
                                type="file"
	                                accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                x-ref="coverInput"
                                x-on:change="handleCover($event)"
                                class="sr-only"
                            >
                        </label>

                        <div
                            x-show="coverFileName"
                            x-cloak
                            class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-leaf-100 bg-leaf-50 px-3 py-3"
                        >
                            <p
                                class="min-w-0 truncate text-xs font-semibold text-leaf-900"
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

                        @if ($restaurant->cover_image)
                            <div
                                x-show="! removeCoverOnSave"
                                class="mt-3 rounded-2xl border border-warm-200 bg-white p-3"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black text-warm-950">
                                            Current cover is active
                                        </p>

                                        <p class="mt-1 text-[11px] font-semibold leading-5 text-warm-500">
                                            Remove it with your next save, or delete it immediately.
                                        </p>
                                    </div>

                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500">
                                        <x-ui-icon name="image" class="h-4 w-4" />
                                    </span>
                                </div>

                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        x-on:click="markCoverForRemoval"
                                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-600 transition hover:bg-red-100"
                                    >
                                        <x-ui-icon name="trash" class="h-4 w-4" />
                                        Remove on save
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="confirmCoverRemoval = true"
                                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-warm-200 bg-white px-4 py-2 text-xs font-black text-warm-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                    >
                                        <x-ui-icon name="trash" class="h-4 w-4" />
                                        Delete now
                                    </button>
                                </div>
                            </div>

                            <div
                                x-show="removeCoverOnSave"
                                x-cloak
                                class="mt-3 rounded-2xl border border-red-200 bg-red-50 p-3"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black text-red-700">
                                            Cover image will be removed when you save.
                                        </p>

                                        <p class="mt-1 text-[11px] font-semibold leading-5 text-red-700">
                                            The public site will use the branded placeholder background.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        x-on:click="undoCoverRemoval"
                                        class="shrink-0 rounded-lg bg-white px-2 py-1 text-xs font-black text-red-600 hover:bg-red-100"
                                    >
                                        Undo
                                    </button>
                                </div>
                            </div>
                        @endif
	                    </div>
                </div>
            </section>

	            {{-- Step 4: Availability --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            4
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Availability Controls
                            </p>

	                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
	                                Ordering availability
	                            </h2>

	                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
	                                Keep the website visible while pausing checkout when the kitchen is offline.
	                            </p>
                        </div>
                    </div>
                </div>

	                <div class="grid gap-4 p-4 sm:p-6">
                    {{-- Open Toggle --}}
                    <label
                        class="cursor-pointer rounded-2xl border p-4 transition sm:p-5"
                        x-bind:class="isOpen
                            ? 'border-leaf-100 bg-leaf-50'
                            : 'border-red-200 bg-red-50'"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <span
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                                    x-bind:class="isOpen
                                        ? 'text-leaf-700'
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
                                            ? 'text-leaf-900'
                                            : 'text-red-950'"
                                        x-text="isOpen
                                            ? 'Open for ordering'
                                            : 'Ordering is closed'"
                                    ></span>

                                    <span
                                        class="mt-1 block text-xs font-semibold leading-5"
                                        x-bind:class="isOpen
                                            ? 'text-leaf-700'
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

                                <span class="block h-7 w-12 rounded-full bg-warm-300 transition peer-checked:bg-leaf-700 peer-focus:ring-4 peer-focus:ring-leaf-100"></span>

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
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-xl shadow-brand-900/5">
                <div class="flex items-center justify-between gap-4 border-b border-warm-200 px-5 py-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                            Live Preview
                        </p>

                        <h2 class="mt-1 text-lg font-black text-warm-950">
                            Customer restaurant card
                        </h2>
                    </div>

	                    <span class="inline-flex items-center gap-1.5 rounded-full bg-leaf-50 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em] text-leaf-700">
	                        <span class="h-1.5 w-1.5 rounded-full bg-leaf-500"></span>
	                        Public
	                    </span>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white shadow-sm">
                        {{-- Cover --}}
                        <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream">
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
                                    class="h-12 w-12 text-brand-200"
                                >
                                    <rect x="3" y="4" width="18" height="16" rx="2" />
                                    <circle cx="8.5" cy="9" r="1.5" />
                                    <path d="m21 15-5-5L5 20" />
                                </svg>
                            </div>

                            <div class="absolute inset-0 bg-gradient-to-t from-warm-950/85 via-warm-950/10 to-transparent"></div>

                            <div class="absolute left-3 top-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/90 px-3 py-1.5 text-[9px] font-black shadow-sm backdrop-blur"
                                    x-bind:class="isOpen
                                        ? 'text-leaf-700'
                                        : 'text-red-700'"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        x-bind:class="isOpen
                                            ? 'bg-leaf-500'
                                            : 'bg-red-500'"
                                    ></span>

                                    <span x-text="isOpen
                                        ? 'Open for orders'
                                        : 'Currently closed'"
                                    ></span>
                                </span>
                            </div>

                            <div class="absolute inset-x-0 bottom-0 flex items-end gap-3 p-4">
                                <span class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl border-4 border-white bg-white text-lg font-black text-brand-600 shadow-xl">
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
	                                            ? restaurantName.split(/\s+/).filter(Boolean).slice(0, 2).map((word) => word.charAt(0)).join('').toUpperCase()
	                                            : 'AK'"
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
                                class="line-clamp-3 min-h-[60px] text-xs font-semibold leading-5 text-warm-600"
                                x-text="shortDescription.trim()
                                    || 'Your restaurant description will appear here for customers.'"
                            ></p>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-brand-50 px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-brand-500">
                                        Delivery
                                    </p>

                                    <p class="mt-1 text-sm font-black text-brand-900">
	                                        A$
                                        <span x-text="Number(deliveryFee || 0).toLocaleString()"></span>
                                    </p>
                                </div>

                                <div class="rounded-xl bg-leaf-50 px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-leaf-700">
                                        Minimum
                                    </p>

                                    <p class="mt-1 text-sm font-black text-leaf-900">
	                                        A$
                                        <span x-text="Number(minimumOrder || 0).toLocaleString()"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="mt-2 flex items-center gap-3 rounded-xl bg-warm-50 px-3 py-3">
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
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                        Operating hours
                                    </p>

                                    <p class="mt-1 text-xs font-black text-warm-950">
                                        <span x-text="formatTime(openingTime)"></span>
                                        <span class="mx-1 text-warm-300">—</span>
                                        <span x-text="formatTime(closingTime)"></span>
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                disabled
                                class="mt-4 min-h-11 w-full rounded-xl px-4 py-3 text-sm font-black"
	                                x-bind:class="isOpen
	                                    ? 'bg-brand-500 text-white'
	                                    : 'cursor-not-allowed bg-warm-200 text-warm-500'"
	                            >
	                                <span x-text="isOpen
	                                    ? 'Browse Menu'
	                                    : 'Ordering Closed'"
	                                ></span>
	                            </button>
                        </div>
                    </div>

                    <p class="mt-3 text-center text-[10px] font-semibold leading-4 text-warm-500">
                        Preview represents the customer-facing restaurant presentation.
                    </p>
                </div>
            </section>

            {{-- Configuration Summary --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Configuration Summary
                </p>

                <h2 class="mt-1 text-lg font-black text-warm-950">
                    Current customer experience
                </h2>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Ordering
                        </span>

                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-black"
                            x-bind:class="isOpen
                                ? 'text-leaf-700'
                                : 'text-red-700'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                x-bind:class="isOpen
                                    ? 'bg-leaf-500'
                                    : 'bg-red-500'"
                            ></span>

                            <span x-text="isOpen ? 'Open' : 'Closed'"></span>
                        </span>
                    </div>

	                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Delivery fee
                        </span>

                        <span class="text-xs font-black text-warm-950">
	                            A$
                            <span x-text="Number(deliveryFee || 0).toLocaleString()"></span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Minimum order
                        </span>

                        <span class="text-xs font-black text-warm-950">
	                            A$
                            <span x-text="Number(minimumOrder || 0).toLocaleString()"></span>
                        </span>
                    </div>
                </div>
            </section>

            {{-- Desktop Actions --}}
            <section class="hidden rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm xl:block">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500">
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
                        <p class="text-sm font-black text-warm-950">
                            Ready to save?
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                            Review the public preview and operational settings before continuing.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    x-bind:disabled="submitting"
                    class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-70"
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
                    class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-warm-200 bg-white px-5 py-3 text-sm font-black text-warm-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600"
                >
                    Back to Dashboard
                </a>
            </section>
        </aside>
	    </form>

        @if ($restaurant->logo)
            <div
                x-show="confirmLogoRemoval"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-[300] grid place-items-center bg-warm-950/55 p-4 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
            >
                <div
                    x-show="confirmLogoRemoval"
                    x-transition
                    x-on:click.outside="confirmLogoRemoval = false"
                    class="w-full max-w-md rounded-[1.5rem] border border-warm-200 bg-white p-5 shadow-2xl shadow-warm-950/20"
                >
                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600">
                            <x-ui-icon name="trash" class="h-5 w-5" />
                        </span>

                        <div>
                            <h2 class="text-lg font-black text-warm-950">
                                Remove restaurant logo?
                            </h2>

                            <p class="mt-2 text-sm font-semibold leading-6 text-warm-600">
                                The uploaded logo file will be deleted and the site will show generated initials instead.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            x-on:click="confirmLogoRemoval = false"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-warm-200 bg-white px-4 py-2 text-sm font-black text-warm-700 transition hover:bg-warm-50"
                        >
                            Cancel
                        </button>

                        <form action="{{ route('admin.settings.restaurant.logo.destroy') }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-black text-white transition hover:bg-red-700 sm:w-auto"
                            >
                                Remove logo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if ($restaurant->cover_image)
            <div
                x-show="confirmCoverRemoval"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-[300] grid place-items-center bg-warm-950/55 p-4 backdrop-blur-sm"
                role="dialog"
                aria-modal="true"
            >
                <div
                    x-show="confirmCoverRemoval"
                    x-transition
                    x-on:click.outside="confirmCoverRemoval = false"
                    class="w-full max-w-md rounded-[1.5rem] border border-warm-200 bg-white p-5 shadow-2xl shadow-warm-950/20"
                >
                    <div class="flex items-start gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600">
                            <x-ui-icon name="trash" class="h-5 w-5" />
                        </span>

                        <div>
                            <h2 class="text-lg font-black text-warm-950">
                                Remove cover image?
                            </h2>

                            <p class="mt-2 text-sm font-semibold leading-6 text-warm-600">
                                The uploaded cover file will be deleted and the site will use the branded placeholder background.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            x-on:click="confirmCoverRemoval = false"
                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-warm-200 bg-white px-4 py-2 text-sm font-black text-warm-700 transition hover:bg-warm-50"
                        >
                            Cancel
                        </button>

                        <form action="{{ route('admin.settings.restaurant.cover.destroy') }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-black text-white transition hover:bg-red-700 sm:w-auto"
                            >
                                Remove cover
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

	    {{-- Persistent Mobile and Tablet Actions --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur xl:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.dashboard') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-95"
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
                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
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

@push('head')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapElement = document.getElementById('restaurant-map');

            if (! mapElement || typeof L === 'undefined') {
                return;
            }

            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const formattedAddressInput = document.getElementById('formatted_address');
            const useCurrentButton = document.getElementById('restaurant-use-current-location');
            const existingLatitude = Number(mapElement.dataset.latitude);
            const existingLongitude = Number(mapElement.dataset.longitude);
            const hasExistingCoordinates = Number.isFinite(existingLatitude) && Number.isFinite(existingLongitude);
            const center = hasExistingCoordinates ? [existingLatitude, existingLongitude] : [-25.2744, 133.7751];
            const map = L.map(mapElement, { scrollWheelZoom: false }).setView(center, hasExistingCoordinates ? 15 : 4);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            let marker = hasExistingCoordinates
                ? L.marker(center, { draggable: true }).addTo(map)
                : null;

            const updateInputs = (latlng) => {
                const latitude = Number(latlng.lat).toFixed(7);
                const longitude = Number(latlng.lng).toFixed(7);

                latitudeInput.value = latitude;
                longitudeInput.value = longitude;
                latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
                longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));

                if (formattedAddressInput && ! formattedAddressInput.value) {
                    formattedAddressInput.value = document.getElementById('address')?.value || '';
                    formattedAddressInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            };

            const setMarker = (latlng, zoom = 15) => {
                if (! marker) {
                    marker = L.marker(latlng, { draggable: true }).addTo(map);
                    marker.on('dragend', () => updateInputs(marker.getLatLng()));
                }

                marker.setLatLng(latlng);
                map.setView(latlng, zoom);
                updateInputs(latlng);
            };

            if (marker) {
                marker.on('dragend', () => updateInputs(marker.getLatLng()));
            }

            map.on('click', (event) => setMarker(event.latlng));

            useCurrentButton?.addEventListener('click', () => {
                if (! navigator.geolocation) {
                    return;
                }

                navigator.geolocation.getCurrentPosition((position) => {
                    setMarker({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    });
                });
            });
        });
    </script>
@endpush

@endcomponent
