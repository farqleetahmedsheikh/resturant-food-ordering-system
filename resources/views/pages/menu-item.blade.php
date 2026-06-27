@component('layouts.public', ['title' => $menuItem->name])
@php
$isOpen = (bool) ($availabilityStatus['is_open'] ?? $restaurant?->is_open ?? true);
$isAvailable = (bool) ($menuItem->is_available ?? true);
$canAddToCart = $isAvailable;
$canOrder = $canAddToCart;
$cartCount = \App\Support\Cart::count();

    $sizeOptions = $menuItem->activeSizes
        ->map(fn ($size) => [
            'id' => $size->id,
            'name' => $size->name,
            'price' => (float) $size->price,
        ])
        ->values();

    $addonOptions = $menuItem->activeAddons
        ->map(fn ($addon) => [
            'id' => $addon->id,
            'name' => $addon->name,
            'type' => $addon->type,
            'price' => (float) $addon->price,
        ])
        ->values();

    $selectedSizeId = (string) old(
        'size_id',
        $sizeOptions->first()['id'] ?? ''
    );

    $selectedAddonIds = collect(old('addon_ids', []))
        ->map(fn ($id) => (string) $id)
        ->values();

    $hasDiscount = $menuItem->compare_at_price
        && (float) $menuItem->compare_at_price > (float) $menuItem->price;

    $discountPercentage = $hasDiscount
        ? round(
            (
                ($menuItem->compare_at_price - $menuItem->price)
                / $menuItem->compare_at_price
            ) * 100
        )
        : null;

    $savingAmount = $hasDiscount
        ? (float) $menuItem->compare_at_price - (float) $menuItem->price
        : 0;
@endphp

<main
    x-data="{
        basePrice: @js((float) $menuItem->price),
        sizes: @js($sizeOptions->all()),
        addons: @js($addonOptions->all()),
        selectedSizeId: @js($selectedSizeId),
        selectedAddonIds: @js($selectedAddonIds->all()),
        submitting: false,

        get selectedSize() {
            return this.sizes.find(
                size => String(size.id) === String(this.selectedSizeId)
            ) || null;
        },

        get selectedAddons() {
            return this.addons.filter(
                addon => this.selectedAddonIds.includes(String(addon.id))
            );
        },

        get addonsTotal() {
            return this.selectedAddons.reduce(
                (total, addon) => total + Number(addon.price || 0),
                0
            );
        },

        get unitPrice() {
            const productPrice = this.selectedSize
                ? Number(this.selectedSize.price)
                : Number(this.basePrice);

            return productPrice + this.addonsTotal;
        },

        formatPrice(value) {
            return Number(value || 0).toLocaleString();
        }
    }"
    class="min-h-screen bg-[var(--color-surface-warm)] pb-28 lg:pb-0"
>
    <section class="pb-8 lg:py-12">
        <div class="mx-auto max-w-7xl lg:px-8">
            {{-- Desktop Breadcrumb --}}
            <nav class="hidden items-center gap-2 pb-6 text-sm font-bold text-warm-500 lg:flex">
                <a
                    href="{{ route('home') }}"
                    class="transition hover:text-brand-500"
                >
                    Home
                </a>

                <span class="text-warm-300">/</span>

                <a
                    href="{{ route('menu') }}"
                    class="transition hover:text-brand-500"
                >
                    Menu
                </a>

                <span class="text-warm-300">/</span>

                <span class="max-w-sm truncate text-warm-900">
                    {{ $menuItem->name }}
                </span>
            </nav>

            <div class="lg:grid lg:grid-cols-[0.92fr_1.08fr] lg:items-start lg:gap-8">
                {{-- Product Image --}}
                <div class="relative lg:sticky lg:top-24">
                    <div class="relative aspect-[5/4] overflow-hidden bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream sm:aspect-[16/10] lg:aspect-[4/5] lg:rounded-[2rem] lg:border lg:border-warm-200 lg:shadow-2xl lg:shadow-brand-900/10">
                        @if ($menuItem->image_url)
                            <img
                                src="{{ $menuItem->image_url }}"
                                alt="{{ $menuItem->name }}"
                                class="absolute inset-0 h-full w-full object-cover"
                                loading="eager"
                                fetchpriority="high"
                            >
                        @else
                            <div class="absolute inset-0">
                                <div class="absolute left-8 top-8 h-24 w-24 rounded-full bg-brand-200/30 blur-2xl"></div>
                                <div class="absolute bottom-8 right-8 h-32 w-32 rounded-full bg-brand-300/30 blur-2xl"></div>
                            </div>

                            <div class="relative grid h-full place-items-center">
                                <div class="grid h-24 w-24 place-items-center rounded-full bg-white/85 text-5xl font-black text-brand-500 shadow-2xl backdrop-blur sm:h-32 sm:w-32 sm:text-7xl">
                                    {{ mb_substr($menuItem->name, 0, 1) }}
                                </div>
                            </div>
                        @endif

                        <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-warm-950/55 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-warm-950/65 to-transparent"></div>

                        {{-- Mobile Navigation Over Image --}}
                        <div class="absolute inset-x-0 top-0 z-10 flex items-center justify-between p-4 lg:hidden">
                            <a
                                href="{{ route('menu') }}"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-warm-950/35 text-white shadow-lg backdrop-blur-md transition active:scale-95"
                                aria-label="Back to menu"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    class="h-5 w-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m15 18-6-6 6-6"
                                    />
                                </svg>
                            </a>

                            <a
                                href="{{ route('cart.index') }}"
                                class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-warm-950/35 text-white shadow-lg backdrop-blur-md transition active:scale-95"
                                aria-label="Open cart"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M3 4h2l2 11h10l2-8H7" />
                                    <circle cx="9" cy="20" r="1" />
                                    <circle cx="17" cy="20" r="1" />
                                </svg>

                                @if ($cartCount > 0)
                                    <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white">
                                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                                    </span>
                                @endif
                            </a>
                        </div>

                        {{-- Product Badges --}}
                        <div class="absolute bottom-5 left-4 right-4 flex items-end justify-between gap-3 text-white sm:left-6 sm:right-6 lg:bottom-7">
                            <div class="min-w-0">
                                <span class="inline-flex max-w-full truncate rounded-full border border-white/25 bg-warm-950/40 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.13em] backdrop-blur-md sm:text-xs">
                                    {{ $menuItem->category?->name ?? 'Arcade Kebab House Menu' }}
                                </span>

                                @if ($menuItem->preparation_time)
                                    <p class="mt-2 text-xs font-bold text-white/90">
                                        Prepared in approximately {{ $menuItem->preparation_time }} minutes
                                    </p>
                                @endif
                            </div>

                            @if ($discountPercentage)
                                <span class="shrink-0 rounded-full bg-red-600 px-3 py-2 text-[10px] font-black uppercase tracking-[0.12em] shadow-lg">
                                    {{ $discountPercentage }}% off
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Desktop Information --}}
                    <div class="mt-4 hidden grid-cols-3 gap-3 lg:grid">
                        @if ($menuItem->preparation_time)
                            <div class="rounded-2xl border border-warm-200 bg-white p-4 text-center shadow-sm">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                    Ready In
                                </p>

                                <p class="mt-1 text-sm font-black text-warm-950">
                                    {{ $menuItem->preparation_time }} min
                                </p>
                            </div>
                        @endif

                        @if ($menuItem->calories)
                            <div class="rounded-2xl border border-warm-200 bg-white p-4 text-center shadow-sm">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                    Calories
                                </p>

                                <p class="mt-1 text-sm font-black text-warm-950">
                                    {{ $menuItem->calories }} kcal
                                </p>
                            </div>
                        @endif

                        @if ($restaurant)
                            <div class="rounded-2xl border border-warm-200 bg-white p-4 text-center shadow-sm">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                    Delivery
                                </p>

                                <p class="mt-1 text-sm font-black text-warm-950">
                                    ($restaurant->delivery_fee)
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Product Bottom Sheet / Desktop Card --}}
                <div class="relative z-20 -mt-7 rounded-t-[2rem] bg-white px-4 pb-7 pt-5 shadow-[var(--shadow-card)] sm:-mt-10 sm:px-6 sm:pt-7 lg:mt-0 lg:rounded-[2rem] lg:border lg:border-warm-200 lg:p-9 lg:shadow-sm">
                    {{-- Product Header --}}
                    <header>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                                {{ $menuItem->category?->name ?? 'Arcade Kebab House Menu' }}
                            </p>

                            <span
                                @class([
                                    'inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1.5 text-[9px] font-black uppercase tracking-[0.1em]',
                                    'bg-leaf-50 text-leaf-700' => $canOrder,
                                    'bg-gold-50 text-gold-700' => ! $canOrder,
                                ])
                            >
                                <span
                                    @class([
                                        'h-1.5 w-1.5 rounded-full',
                                        'animate-pulse bg-leaf-500' => $canOrder,
                                        'bg-gold-500' => ! $canOrder,
                                    ])
                                ></span>

                                {{ $canOrder ? 'Available' : 'Unavailable' }}
                            </span>
                        </div>

                        <h1 class="mt-2 break-words text-2xl font-black leading-tight tracking-tight text-warm-950 sm:text-4xl lg:text-5xl">
                            {{ $menuItem->name }}
                        </h1>

                        <div class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-2">
                            <p
                                aria-live="polite"
                                class="text-3xl font-black tracking-tight text-brand-500 sm:text-4xl"
                            >
	                                A$
                                <span x-text="formatPrice(unitPrice)"></span>
                            </p>

                            @if ($hasDiscount)
                                <p class="pb-1 text-sm font-bold text-warm-500 line-through sm:text-lg">
                                    ($menuItem->compare_at_price)
                                </p>

                                <span class="mb-1 rounded-full bg-red-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] text-red-600 sm:text-xs">
                                    Save @money($savingAmount)
                                </span>
                            @endif
                        </div>

                        <p
                            x-show="addonsTotal > 0"
                            x-cloak
                            class="mt-1 text-xs font-bold text-leaf-700"
                        >
	                            Includes A$
                            <span x-text="formatPrice(addonsTotal)"></span>
                            in extras
                        </p>
                    </header>

                    {{-- Mobile Essential Information --}}
                    <div class="mt-5 flex divide-x divide-warm-200 rounded-2xl bg-warm-50 px-2 py-3 lg:hidden">
                        @if ($menuItem->preparation_time)
                            <div class="min-w-0 flex-1 px-2 text-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="mx-auto h-4 w-4 text-brand-500"
                                >
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" d="M12 7v5l3 2" />
                                </svg>

                                <p class="mt-1 text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                    Ready In
                                </p>

                                <p class="mt-0.5 truncate text-xs font-black text-warm-950">
                                    {{ $menuItem->preparation_time }} min
                                </p>
                            </div>
                        @endif

                        @if ($menuItem->calories)
                            <div class="min-w-0 flex-1 px-2 text-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="mx-auto h-4 w-4 text-brand-500"
                                >
                                    <path d="M12 22c4 0 7-3 7-7 0-5-4-8-7-13-3 5-7 8-7 13 0 4 3 7 7 7z" />
                                </svg>

                                <p class="mt-1 text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                    Calories
                                </p>

                                <p class="mt-0.5 truncate text-xs font-black text-warm-950">
                                    {{ $menuItem->calories }} kcal
                                </p>
                            </div>
                        @endif

                        @if ($restaurant)
                            <div class="min-w-0 flex-1 px-2 text-center">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="mx-auto h-4 w-4 text-brand-500"
                                >
                                    <path d="M3 7h11v10H3z" />
                                    <path d="M14 10h4l3 3v4h-7z" />
                                </svg>

                                <p class="mt-1 text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                    Delivery
                                </p>

                                <p class="mt-0.5 truncate text-xs font-black text-warm-950">
                                    ($restaurant->delivery_fee)
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Description --}}
                    <p class="mt-5 hidden text-sm font-semibold leading-7 text-warm-600 sm:block">
                        {{ $menuItem->description ?: 'Freshly prepared with carefully selected ingredients for a delicious and satisfying meal.' }}
                    </p>

                    <details class="group mt-4 border-b border-warm-200 pb-4 sm:hidden">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4">
                            <span class="text-sm font-black text-warm-900">
                                About this item
                            </span>

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5 shrink-0 text-warm-500 transition group-open:rotate-180"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m6 9 6 6 6-6"
                                />
                            </svg>
                        </summary>

                        <p class="mt-3 text-sm font-semibold leading-6 text-warm-600">
                            {{ $menuItem->description ?: 'Freshly prepared with carefully selected ingredients for a delicious and satisfying meal.' }}
                        </p>
                    </details>

                    @if (! $isOpen || ! $isAvailable)
                        <div class="mt-5 rounded-2xl border border-gold-100 bg-gold-50 p-4">
                            <div class="flex items-start gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-gold-500 shadow-sm">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" d="M12 7v5M12 16h.01" />
                                    </svg>
                                </span>

                                <div>
                                    <p class="text-sm font-black text-gold-700">
                                        @if (! $isAvailable)
                                            Item currently unavailable
                                        @else
                                            Restaurant currently closed
                                        @endif
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-gold-700">
                                        @if (! $isAvailable)
                                            You can continue browsing, but this item cannot be ordered right now.
                                        @else
                                            You can add this item to cart now and checkout later when the restaurant opens.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4">
                            <p class="text-sm font-black text-red-900">
                                Review your selections
                            </p>

                            <p class="mt-1 text-xs font-semibold leading-5 text-red-700">
                                A required option may be missing or invalid.
                            </p>
                        </div>
                    @endif

                    {{-- Configuration Form --}}
                    <form
                        id="product-add-form"
                        action="{{ route('cart.add', $menuItem) }}"
                        method="POST"
                        class="mt-6 space-y-7"
                        x-on:submit="submitting = true"
                    >
                        @csrf

                        {{-- Size Selection --}}
                        @if ($sizeOptions->isNotEmpty())
                            <section>
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="text-base font-black text-warm-950">
                                            Choose a size
                                        </h2>

                                        <p class="mt-1 text-xs font-semibold text-warm-500">
                                            Select one option
                                        </p>
                                    </div>

                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] text-brand-600">
                                        Required
                                    </span>
                                </div>

                                {{-- No Horizontal Scrolling --}}
                                <div class="mt-4 grid grid-cols-2 gap-2.5 sm:gap-3">
                                    @foreach ($sizeOptions as $size)
                                        <label
                                            class="relative cursor-pointer rounded-2xl border p-3.5 transition active:scale-[0.98] sm:p-4"
                                            x-bind:class="String(selectedSizeId) === '{{ $size['id'] }}'
                                                ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-100'
                                                : 'border-warm-200 bg-white'"
                                        >
                                            <input
                                                type="radio"
                                                name="size_id"
                                                value="{{ $size['id'] }}"
                                                x-model="selectedSizeId"
                                                required
                                                class="absolute right-3 top-3 h-5 w-5 border-warm-300 text-brand-500 focus:ring-brand-500"
                                            >

                                            <span class="block pr-7 text-sm font-black text-warm-950">
                                                {{ $size['name'] }}
                                            </span>

                                            <span class="mt-2 block text-base font-black text-brand-500 sm:text-lg">
                                                ($size['price'])
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('size_id')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </section>
                        @endif

                        {{-- Add-ons --}}
                        @if ($addonOptions->isNotEmpty())
                            <section>
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 class="text-base font-black text-warm-950">
                                            Add extras
                                        </h2>

                                        <p class="mt-1 text-xs font-semibold text-warm-500">
                                            Optional toppings and sauces
                                        </p>
                                    </div>

                                    <span
                                        x-show="selectedAddonIds.length > 0"
                                        x-cloak
                                        class="rounded-full bg-brand-50 px-2.5 py-1 text-[9px] font-black text-brand-600"
                                    >
                                        <span x-text="selectedAddonIds.length"></span>
                                        selected
                                    </span>
                                </div>

                                <div class="mt-4 space-y-2.5">
                                    @foreach ($addonOptions as $addon)
                                        <label
                                            class="flex min-h-14 cursor-pointer items-center justify-between gap-3 rounded-2xl border px-4 py-3.5 transition active:scale-[0.99]"
                                            x-bind:class="selectedAddonIds.includes('{{ $addon['id'] }}')
                                                ? 'border-brand-500 bg-brand-50 ring-2 ring-brand-100'
                                                : 'border-warm-200 bg-white'"
                                        >
                                            <span class="flex min-w-0 items-center gap-3">
                                                <input
                                                    type="checkbox"
                                                    name="addon_ids[]"
                                                    value="{{ $addon['id'] }}"
                                                    x-model="selectedAddonIds"
                                                    class="h-5 w-5 shrink-0 rounded-md border-warm-300 text-brand-500 focus:ring-brand-500"
                                                >

                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-black text-warm-950">
                                                        {{ $addon['name'] }}
                                                    </span>

                                                    <span class="mt-0.5 block text-[9px] font-bold uppercase tracking-[0.1em] text-warm-500">
                                                        {{ ucfirst($addon['type']) }}
                                                    </span>
                                                </span>
                                            </span>

                                            <span class="shrink-0 text-sm font-black text-brand-500">
                                                + ($addon['price'])
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('addon_ids')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </section>
                        @endif

                        {{-- Selected Configuration Summary --}}
                        @if ($sizeOptions->isNotEmpty() || $addonOptions->isNotEmpty())
                            <div class="rounded-2xl border border-warm-200 bg-brand-50 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-[0.12em] text-brand-500">
                                            Your Selection
                                        </p>

                                        <p
                                            x-show="selectedSize"
                                            class="mt-1 text-sm font-black text-warm-950"
                                        >
                                            <span x-text="selectedSize ? selectedSize.name : ''"></span>
                                        </p>

                                        <p
                                            x-show="selectedAddonIds.length > 0"
                                            x-cloak
                                            class="mt-1 text-xs font-semibold text-warm-600"
                                        >
                                            <span x-text="selectedAddonIds.length"></span>
                                            extra<span x-show="selectedAddonIds.length !== 1">s</span>
                                            added
                                        </p>

                                        <p
                                            x-show="selectedAddonIds.length === 0"
                                            class="mt-1 text-xs font-semibold text-warm-500"
                                        >
                                            No extras selected
                                        </p>
                                    </div>

                                    <p class="shrink-0 text-lg font-black text-brand-500">
	                                        A$ <span x-text="formatPrice(unitPrice)"></span>
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- Desktop Actions --}}
                        <div class="hidden gap-3 lg:grid lg:grid-cols-[1fr_auto]">
                            @if ($canOrder)
                                <button
                                    type="submit"
                                    x-bind:disabled="submitting"
                                    class="inline-flex min-h-14 w-full items-center justify-center gap-2 rounded-2xl bg-brand-500 px-6 py-4 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-70"
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
                                        <path d="M3 4h2l2 11h10l2-8H7" />
                                        <circle cx="9" cy="20" r="1" />
                                        <circle cx="17" cy="20" r="1" />
                                    </svg>

                                    <span x-text="submitting ? 'Adding...' : 'Add to Cart'"></span>

                                    <span>
	                                        · A$
                                        <span x-text="formatPrice(unitPrice)"></span>
                                    </span>
                                </button>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="inline-flex min-h-14 w-full cursor-not-allowed items-center justify-center rounded-2xl bg-warm-200 px-6 py-4 text-sm font-black text-warm-500"
                                >
                                    Ordering unavailable
                                </button>
                            @endif

                            <a
                                href="{{ route('menu') }}"
                                class="inline-flex min-h-14 items-center justify-center rounded-2xl border border-brand-200 bg-white px-6 py-4 text-sm font-black text-warm-900 shadow-sm transition hover:bg-brand-50 hover:text-brand-600"
                            >
                                Back to Menu
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- Smart Suggestions --}}
    @if (($suggestions ?? collect())->isNotEmpty())
        <section class="border-t border-warm-200 bg-white py-8 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                            Complete Your Meal
                        </p>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-3xl">
                            Popular pairings
                        </h2>
                    </div>

                    <a
                        href="{{ route('menu') }}"
                        class="shrink-0 text-xs font-black text-brand-600 hover:text-brand-800"
                    >
                        View menu
                    </a>
                </div>

                {{-- Compact Mobile List --}}
                <div class="mt-5 grid gap-3 lg:grid-cols-4">
                    @foreach ($suggestions as $suggestion)
                        @php
                            $suggestionCustomizable =
                                ($suggestion->active_sizes_count ?? 0) > 0
                                || ($suggestion->active_addons_count ?? 0) > 0;
                        @endphp

                        <article class="flex items-center gap-3 rounded-2xl border border-warm-200 bg-white p-3 shadow-sm lg:block lg:overflow-hidden lg:p-0">
                            <a
                                href="{{ route('menu.show', $suggestion) }}"
                                class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-brand-50 lg:block lg:h-auto lg:w-auto lg:rounded-none"
                            >
                                <div class="grid h-full w-full place-items-center lg:aspect-[4/3]">
                                    @if ($suggestion->image_url)
                                        <img
                                            src="{{ $suggestion->image_url }}"
                                            alt="{{ $suggestion->name }}"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="text-2xl font-black text-brand-500">
                                            {{ mb_substr($suggestion->name, 0, 1) }}
                                        </span>
                                    @endif
                                </div>
                            </a>

                            <div class="min-w-0 flex-1 lg:p-4">
                                <a
                                    href="{{ route('menu.show', $suggestion) }}"
                                    class="line-clamp-1 text-sm font-black text-warm-950 hover:text-brand-600 lg:text-base"
                                >
                                    {{ $suggestion->name }}
                                </a>

                                <p class="mt-1 text-sm font-black text-brand-500">
                                    ($suggestion->price)
                                </p>

                                <div class="mt-2">
                                    @if ($suggestionCustomizable)
                                        <a
                                            href="{{ route('menu.show', $suggestion) }}"
                                            class="inline-flex min-h-9 items-center justify-center rounded-lg bg-brand-50 px-3 py-2 text-[10px] font-black text-brand-600 transition active:scale-[0.97]"
                                        >
                                            Customize
                                        </a>
                                    @else
                                        <form
                                            action="{{ route('cart.add', $suggestion) }}"
                                            method="POST"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="inline-flex min-h-9 items-center justify-center gap-1 rounded-lg bg-brand-500 px-3 py-2 text-[10px] font-black text-white transition active:scale-[0.97]"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    class="h-3.5 w-3.5"
                                                >
                                                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                                </svg>

                                                Add
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Related Items --}}
    @if ($relatedItems->isNotEmpty())
        <section class="border-t border-warm-200 py-8 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                            More To Explore
                        </p>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-3xl">
                            You may also like
                        </h2>
                    </div>

                    <a
                        href="{{ route('menu') }}"
                        class="shrink-0 text-xs font-black text-brand-600 hover:text-brand-800"
                    >
                        View all
                    </a>
                </div>

                <div class="mt-5 grid gap-3 lg:grid-cols-3">
                    @foreach ($relatedItems as $item)
                        <a
                            href="{{ route('menu.show', $item) }}"
                            class="group flex items-center gap-3 rounded-2xl border border-warm-200 bg-white p-3 shadow-sm transition active:scale-[0.99] lg:block lg:overflow-hidden lg:p-0"
                        >
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-brand-50 lg:h-auto lg:w-auto lg:rounded-none">
                                <div class="grid h-full w-full place-items-center lg:aspect-[16/9]">
                                    @if ($item->image_url)
                                        <img
                                            src="{{ $item->image_url }}"
                                            alt="{{ $item->name }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="text-2xl font-black text-brand-500">
                                            {{ mb_substr($item->name, 0, 1) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="min-w-0 flex-1 lg:p-5">
                                <h3 class="line-clamp-1 text-sm font-black text-warm-950 lg:text-lg">
                                    {{ $item->name }}
                                </h3>

                                <p class="mt-1 line-clamp-1 text-xs font-semibold text-warm-500 lg:line-clamp-2 lg:text-sm">
                                    {{ $item->description ?: 'Freshly prepared with quality ingredients.' }}
                                </p>

                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <p class="text-sm font-black text-brand-500 lg:text-lg">
                                        @money($item->price)
                                    </p>

                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-500">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m9 18 6-6-6-6"
                                            />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Persistent Mobile Action --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur lg:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <div class="min-w-0 shrink-0">
                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                    Total
                </p>

                <p
                    aria-live="polite"
                    class="mt-0.5 whitespace-nowrap text-lg font-black text-warm-950"
                >
	                    A$ <span x-text="formatPrice(unitPrice)"></span>
                </p>
            </div>

            @if ($canOrder)
                <button
                    type="submit"
                    form="product-add-form"
                    x-bind:disabled="submitting"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
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
                        <path d="M3 4h2l2 11h10l2-8H7" />
                        <circle cx="9" cy="20" r="1" />
                        <circle cx="17" cy="20" r="1" />
                    </svg>

                    <span x-text="submitting ? 'Adding...' : 'Add to Cart'"></span>
                </button>
            @else
                <button
                    type="button"
                    disabled
                    class="inline-flex min-h-12 min-w-0 flex-1 cursor-not-allowed items-center justify-center rounded-xl bg-warm-200 px-4 py-3 text-sm font-black text-warm-500"
                >
                    Ordering unavailable
                </button>
            @endif
        </div>
    </div>
</main>

@endcomponent
