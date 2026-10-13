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
            'is_default' => (bool) ($addon->pivot?->is_default ?? false),
        ])
        ->values();

    $customizationOptions = $customizationGroups->map(fn ($group) => [
        'id' => $group->id,
        'name' => $group->name,
        'description' => $group->description,
        'selection_type' => $group->selection_type,
        'is_required' => (bool) $group->is_required,
        'min_selections' => (int) $group->min_selections,
        'max_selections' => $group->max_selections !== null ? (int) $group->max_selections : null,
        'quantity_limit' => $group->quantity_limit !== null ? (int) $group->quantity_limit : 1,
        'addons' => $group->activeAddons->map(fn ($addon) => [
            'id' => $addon->id,
            'name' => $addon->name,
            'type' => $addon->type,
            'price' => (float) $addon->price,
            'is_default' => (bool) ($addon->pivot?->is_default ?? false),
        ])->values()->all(),
    ])->values();

    $defaultAddonQuantities = $customizationOptions
        ->mapWithKeys(fn ($group) => [
            $group['id'] => collect($group['addons'])
                ->filter(fn ($addon) => (bool) ($addon['is_default'] ?? false))
                ->mapWithKeys(fn ($addon) => [$addon['id'] => 1])
                ->all(),
        ])
        ->all();

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
        groups: @js($customizationOptions->all()),
        selectedSizeId: @js($selectedSizeId),
        selectedAddonIds: @js($selectedAddonIds->all()),
        selectedAddonQuantities: @js($defaultAddonQuantities),
        quantity: 1,
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

        get groupedAddonsTotal() {
            return this.groups.reduce((total, group) => total + group.addons.reduce((groupTotal, addon) => {
                return groupTotal + (addon.is_default ? 0 : Number(addon.price || 0)) * Number(this.selectedAddonQuantities[group.id]?.[addon.id] || 0);
            }, 0), 0);
        },

        get selectedAddonCount() {
            if (this.groups.length) {
                return Object.values(this.selectedAddonQuantities).reduce((total, group) => total + Object.values(group).reduce((sum, quantity) => sum + Number(quantity || 0), 0), 0);
            }
            return this.selectedAddonIds.length;
        },

        setAddonQuantity(groupId, addonId, quantity) {
            if (! this.selectedAddonQuantities[groupId]) this.selectedAddonQuantities[groupId] = {};
            const group = this.groups.find(entry => Number(entry.id) === Number(groupId));
            const limit = Number(group?.quantity_limit || 1);
            if (quantity > 0 && group?.selection_type === 'single') this.selectedAddonQuantities[groupId] = {};
            this.selectedAddonQuantities[groupId][addonId] = Math.max(0, Math.min(limit, Number(quantity || 0)));
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

            return productPrice + (this.groups.length ? this.groupedAddonsTotal : this.addonsTotal);
        },

        get lineTotal() {
            return this.unitPrice * this.quantity;
        },

        changeQuantity(amount) {
            this.quantity = Math.max(1, Math.min(99, Number(this.quantity || 1) + amount));
        },

        formatPrice(value) {
            return '$' + Number(value || 0).toFixed(2);
        }
    }"
    class="min-h-screen bg-[#fffaf7] pb-28 lg:pb-0"
>
    <section class="pb-8 pt-4 sm:pt-6 lg:pb-14">
        <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
            {{-- Desktop Breadcrumb --}}
            <nav class="mb-4 hidden items-center gap-2 pb-2 text-xs font-semibold text-warm-500 sm:flex lg:mb-6 lg:text-sm">
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

                @if ($menuItem->category)
                    <span class="text-warm-300">/</span>

                    <a
                        href="{{ route('menu', ['category' => $menuItem->category->slug]) }}"
                        class="inline-flex max-w-sm items-center gap-1.5 truncate transition hover:text-brand-500"
                        title="Browse {{ $menuItem->category->name }}"
                    >
                        <span>{{ $menuItem->category->name }}</span>
                        <span class="font-mono text-xs font-semibold text-warm-400">({{ $menuItem->category->slug }})</span>
                    </a>
                @endif

                <span class="text-warm-300">/</span>

                <span class="max-w-sm truncate text-warm-900">
                    {{ $menuItem->name }}
                </span>
            </nav>

            <div class="grid items-start gap-5 lg:grid-cols-[1.08fr_0.92fr] lg:gap-7 xl:gap-9">
                {{-- Product Image --}}
                <div class="relative lg:sticky lg:top-24">
                    <div class="relative aspect-[4/3] overflow-hidden rounded-2xl border border-warm-200 bg-warm-100 shadow-sm sm:aspect-[16/10] lg:aspect-[1/0.92] lg:rounded-3xl">
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

                        @if ($menuItem->image_url)
                            <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-black/55 to-transparent"></div>
                        @endif

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
                                @if ($menuItem->category)
                                    <a
                                        href="{{ route('menu', ['category' => $menuItem->category->slug]) }}"
                                        class="inline-flex max-w-full items-center gap-1.5 truncate rounded-full border border-white/25 bg-warm-950/40 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.13em] backdrop-blur-md transition hover:bg-warm-950/60 sm:text-xs"
                                        title="Browse {{ $menuItem->category->name }}"
                                    >
                                        <span class="truncate">{{ $menuItem->category->name }}</span>
                                        <span class="font-mono text-[8px] font-semibold normal-case tracking-normal text-white/70 sm:text-[10px]">/{{ $menuItem->category->slug }}</span>
                                    </a>
                                @else
                                    <span class="inline-flex max-w-full truncate rounded-full border border-white/25 bg-warm-950/40 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.13em] backdrop-blur-md sm:text-xs">
                                        Arcade Kebab House Menu
                                    </span>
                                @endif

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
                                    @money($restaurant->delivery_fee)
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Product Bottom Sheet / Desktop Card --}}
                <div class="relative z-20 -mt-7 rounded-2xl border border-warm-200 bg-white px-4 pb-6 pt-5 shadow-sm sm:-mt-9 sm:px-6 sm:pt-6 lg:mt-0 lg:rounded-3xl lg:p-7 xl:p-8">
                    {{-- Product Header --}}
                    <header>
                        <div class="flex items-center justify-between gap-3">
                            @if ($menuItem->category)
                                <a
                                    href="{{ route('menu', ['category' => $menuItem->category->slug]) }}"
                                    class="inline-flex max-w-full items-center gap-2 text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 transition hover:text-brand-700 sm:text-xs"
                                    title="Browse {{ $menuItem->category->name }}"
                                >
                                    <span class="truncate">{{ $menuItem->category->name }}</span>
                                    <span class="font-mono text-[9px] font-semibold normal-case tracking-normal text-warm-400">/{{ $menuItem->category->slug }}</span>
                                </a>
                            @else
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                                    Arcade Kebab House Menu
                                </p>
                            @endif

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
                                <span x-text="formatPrice(unitPrice)"></span>
                            </p>

                            @if ($hasDiscount)
                                <p class="pb-1 text-sm font-bold text-warm-500 line-through sm:text-lg">
                                    @money($menuItem->compare_at_price)
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
                            Includes <span x-text="formatPrice(addonsTotal)"></span>
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
                                    @money($restaurant->delivery_fee)
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
                        <input type="hidden" name="quantity" x-bind:value="quantity">

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
                                <div class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-3">
                                    @foreach ($sizeOptions as $size)
                                        <label
                                            class="relative min-h-24 cursor-pointer rounded-2xl border p-3.5 transition active:scale-[0.98] sm:p-4"
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
                                                @money($size['price'])
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

                        {{-- Structured customizations --}}
                        @if ($customizationOptions->isNotEmpty())
                            <section class="space-y-6">
                                @foreach ($customizationOptions as $group)
                                    <div class="border-t border-warm-100 pt-5 first:border-0 first:pt-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex min-w-0 items-start gap-3">
                                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-500 text-sm font-black text-white">{{ $loop->iteration }}</span>
                                                <div class="min-w-0">
                                                    <h2 class="text-base font-black text-warm-950">{{ $group['name'] }}</h2>
                                                    <p class="mt-1 text-xs font-medium leading-5 text-warm-500">{{ $group['description'] ?: ($group['selection_type'] === 'single' ? 'Choose one option.' : 'Choose as many as you like.') }}</p>
                                                </div>
                                            </div>
                                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[9px] font-bold {{ $group['is_required'] ? 'bg-brand-50 text-brand-600' : 'bg-warm-100 text-warm-500' }}">{{ $group['is_required'] ? 'Required' : 'Optional' }}</span>
                                        </div>
                                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                            @foreach ($group['addons'] as $addon)
                                                <div class="rounded-xl border border-warm-200 bg-white p-3 transition" x-bind:class="Number(selectedAddonQuantities[{{ $group['id'] }}]?.[{{ $addon['id'] }}] || 0) > 0 ? 'border-brand-500 bg-brand-50/60 ring-1 ring-brand-100' : 'hover:border-warm-300'">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-3">
                                                            <input type="{{ $group['selection_type'] === 'single' ? 'radio' : 'checkbox' }}" @if ($group['selection_type'] === 'single') name="addon_group_{{ $group['id'] }}" @endif value="{{ $addon['id'] }}" x-bind:checked="Number(selectedAddonQuantities[{{ $group['id'] }}]?.[{{ $addon['id'] }}] || 0) > 0" class="h-5 w-5 shrink-0 border-warm-300 text-brand-500 focus:ring-brand-500" @change="setAddonQuantity({{ $group['id'] }}, {{ $addon['id'] }}, $event.target.checked ? 1 : 0)">
                                                            <span class="min-w-0"><span class="block break-words text-sm font-bold text-warm-950">{{ $addon['name'] }}</span></span>
                                                        </label>
                                                        <span class="shrink-0 text-xs font-black {{ ($addon['is_default'] ?? false) ? 'text-leaf-700' : 'text-brand-600' }}">{{ ($addon['is_default'] ?? false) ? 'Included' : '+ '.\App\Support\Money::format($addon['price']) }}</span>
                                                    </div>
                                                    @if ($group['quantity_limit'] > 1 && $group['selection_type'] !== 'single')
                                                        <div class="mt-3 flex items-center justify-end gap-2" x-show="Number(selectedAddonQuantities[{{ $group['id'] }}]?.[{{ $addon['id'] }}] || 0) > 0" x-cloak>
                                                            <button type="button" class="grid h-8 w-8 place-items-center rounded-lg border border-warm-200 text-lg font-black text-warm-700" @click="setAddonQuantity({{ $group['id'] }}, {{ $addon['id'] }}, Number(selectedAddonQuantities[{{ $group['id'] }}]?.[{{ $addon['id'] }}] || 1) - 1)">−</button>
                                                            <span class="min-w-6 text-center text-sm font-black" x-text="selectedAddonQuantities[{{ $group['id'] }}]?.[{{ $addon['id'] }}] || 0"></span>
                                                            <button type="button" class="grid h-8 w-8 place-items-center rounded-lg border border-warm-200 text-lg font-black text-warm-700" @click="setAddonQuantity({{ $group['id'] }}, {{ $addon['id'] }}, Number(selectedAddonQuantities[{{ $group['id'] }}]?.[{{ $addon['id'] }}] || 0) + 1)">+</button>
                                                        </div>
                                                    @endif
                                                    <input type="hidden" name="addon_selections[{{ $group['id'] }}][{{ $addon['id'] }}]" x-bind:value="selectedAddonQuantities[{{ $group['id'] }}]?.[{{ $addon['id'] }}] || 0">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                                @error('addon_selections')<p class="text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                            </section>
                        @elseif ($addonOptions->isNotEmpty())
                            {{-- Legacy add-ons remain available for older menu items. --}}
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
                                        x-show="selectedAddonCount > 0"
                                        x-cloak
                                        class="rounded-full bg-brand-50 px-2.5 py-1 text-[9px] font-black text-brand-600"
                                    >
                                        <span x-text="selectedAddonCount"></span>
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
                                                + @money($addon['price'])
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

                        <section class="flex items-center justify-between gap-4 border-t border-warm-100 pt-5">
                            <div class="flex items-center gap-3">
                                <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-500 text-sm font-black text-white">{{ $sizeOptions->isNotEmpty() || $customizationOptions->isNotEmpty() ? ($customizationOptions->count() + ($sizeOptions->isNotEmpty() ? 2 : 1)) : 1 }}</span>
                                <div>
                                    <h2 class="text-sm font-black text-warm-950">Quantity</h2>
                                    <p class="text-xs text-warm-500">How many would you like?</p>
                                </div>
                            </div>
                            <div class="flex h-10 items-center overflow-hidden rounded-xl border border-warm-200 bg-white">
                                <button type="button" aria-label="Decrease quantity" class="grid h-10 w-10 place-items-center text-lg font-bold text-warm-600 hover:bg-warm-50" @click="changeQuantity(-1)" x-bind:disabled="quantity <= 1">−</button>
                                <span class="grid h-10 min-w-10 place-items-center border-x border-warm-100 px-2 text-sm font-black" x-text="quantity"></span>
                                <button type="button" aria-label="Increase quantity" class="grid h-10 w-10 place-items-center text-lg font-bold text-warm-600 hover:bg-warm-50" @click="changeQuantity(1)" x-bind:disabled="quantity >= 99">+</button>
                            </div>
                        </section>

                        {{-- Selected Configuration Summary --}}
                        @if ($sizeOptions->isNotEmpty() || $addonOptions->isNotEmpty() || $customizationOptions->isNotEmpty())
                            <div class="rounded-2xl bg-[#fff1f1] p-4 sm:p-5">
                                <div class="flex items-start justify-between gap-4 border-b border-brand-100 pb-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-warm-950">Order summary</p>
                                        <p class="mt-1 truncate text-xs text-warm-600"><span x-text="selectedSize?.name || @js($menuItem->name)"></span></p>
                                        <p class="mt-1 text-xs text-warm-600" x-show="selectedAddonCount > 0"><span x-text="selectedAddonCount"></span> customization<span x-show="selectedAddonCount !== 1">s</span> selected</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-xs text-warm-500">Each</p>
                                        <p class="text-sm font-bold text-warm-800" x-text="formatPrice(unitPrice)"></p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-3">
                                    <span class="text-sm font-black text-warm-950">Total</span>
                                    <span class="text-xl font-black text-brand-600" x-text="formatPrice(lineTotal)"></span>
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
                                        · <span x-text="formatPrice(lineTotal)"></span>
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
                                href="{{ $suggestion->public_url }}"
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
                                    href="{{ $suggestion->public_url }}"
                                    class="line-clamp-1 text-sm font-black text-warm-950 hover:text-brand-600 lg:text-base"
                                >
                                    {{ $suggestion->name }}
                                </a>

                                <p class="mt-1 text-sm font-black text-brand-500">
                                    @money($suggestion->price)
                                </p>

                                <div class="mt-2">
                                    @if ($suggestionCustomizable)
                                        <a
                                            href="{{ $suggestion->public_url }}"
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
                            href="{{ $item->public_url }}"
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
                    <span x-text="formatPrice(lineTotal)"></span>
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
