@component('layouts.public', ['title' => 'Menu'])
@php
$restaurantName = $restaurant?->name ?? 'Arcade Kebab House Restaurant';
$isOpen = (bool) ($availabilityStatus['is_open'] ?? $restaurant?->is_open ?? true);
$cartCount = \App\Support\Cart::count();

    $deliveryFee = \App\Support\Money::format($restaurant?->delivery_fee ?? 0);
    $minimumOrder = \App\Support\Money::format($restaurant?->minimum_order_amount ?? 0);

    $selectedCategoryName = $selectedCategory?->name ?? 'All Items';
    $visibleItemCount = $menuItems->count();
@endphp

<main class="min-h-screen bg-[var(--color-surface-warm)] pb-28 lg:pb-0">
    {{-- Compact Mobile-First Menu Header --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -left-24 -top-20 h-64 w-64 rounded-full bg-brand-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-28 bottom-0 h-72 w-72 rounded-full bg-brand-200/40 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 pb-6 pt-5 sm:px-6 sm:pb-9 sm:pt-8 lg:px-8 lg:pb-12 lg:pt-12">
            {{-- Restaurant Identity --}}
            <div class="flex items-center justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-brand-500 text-sm font-black text-white shadow-lg shadow-brand-500/20 sm:h-14 sm:w-14">
                        @if ($restaurant?->logo_url)
                            <img
                                src="{{ $restaurant->logo_url }}"
                                alt="{{ $restaurantName }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            {{ mb_strtoupper(mb_substr($restaurantName, 0, 2)) }}
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-warm-950 sm:text-base">
                            {{ $restaurantName }}
                        </p>

                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span
                                @class([
                                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em]',
                                    'bg-leaf-50 text-leaf-700' => $isOpen,
                                    'bg-gold-50 text-gold-700' => ! $isOpen,
                                ])
                            >
                                <span
                                    @class([
                                        'h-1.5 w-1.5 rounded-full',
                                        'animate-pulse bg-leaf-500' => $isOpen,
                                        'bg-gold-500' => ! $isOpen,
                                    ])
                                ></span>

                                {{ $isOpen ? 'Open now' : 'Closed' }}
                            </span>

                            <span class="text-[10px] font-semibold text-warm-500 sm:text-xs">
                                Cash on delivery
                            </span>
                        </div>
                    </div>
                </div>

                <a
                    href="{{ route('cart.index') }}"
                    class="relative grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-brand-500 shadow-sm transition active:scale-95 lg:hidden"
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

            {{-- Menu Heading --}}
            <div class="mt-6 lg:grid lg:grid-cols-[1fr_auto] lg:items-end lg:gap-8">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-500 sm:text-xs">
                        Explore Our Menu
                    </p>

                    <h1 class="mt-2 max-w-3xl text-3xl font-black leading-tight tracking-tight text-warm-950 sm:text-5xl lg:text-6xl">
                        What are you
                        <span class="text-brand-500">craving today?</span>
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold leading-6 text-warm-600 sm:mt-5 sm:text-base sm:leading-8">
                        Browse fresh meals, customize your favourites, and order with cash on delivery.
                    </p>
                </div>

                {{-- Desktop Cart --}}
                <a
                    href="{{ route('cart.index') }}"
                    class="mt-6 hidden min-h-12 items-center justify-center gap-3 rounded-2xl border border-brand-200 bg-white px-5 py-3 text-sm font-black text-brand-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-brand-50 lg:inline-flex"
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

                    Cart

                    @if ($cartCount > 0)
                        <span class="grid h-6 min-w-6 place-items-center rounded-full bg-brand-500 px-1.5 text-[10px] font-black text-white">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            </div>

            {{-- Essential Service Information --}}
            <div class="mt-5 grid grid-cols-3 gap-2 sm:mt-7 sm:max-w-2xl sm:gap-3">
                <div class="rounded-xl border border-warm-200 bg-white px-3 py-3 shadow-sm sm:rounded-2xl sm:px-4 sm:py-4">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-500 sm:h-9 sm:w-9 sm:rounded-xl">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <path d="M3 7h11v10H3z" />
                                <path d="M14 10h4l3 3v4h-7z" />
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500 sm:text-[10px]">
                                Delivery
                            </p>

                            <p class="mt-0.5 truncate text-xs font-black text-warm-950 sm:text-sm">
                                {{ $deliveryFee }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-leaf-100 bg-white px-3 py-3 shadow-sm sm:rounded-2xl sm:px-4 sm:py-4">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-leaf-50 text-leaf-700 sm:h-9 sm:w-9 sm:rounded-xl">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <rect x="3" y="6" width="18" height="12" rx="2" />
                                <circle cx="12" cy="12" r="2" />
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500 sm:text-[10px]">
                                Payment
                            </p>

                            <p class="mt-0.5 text-xs font-black text-leaf-700 sm:text-sm">
                                COD
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gold-100 bg-white px-3 py-3 shadow-sm sm:rounded-2xl sm:px-4 sm:py-4">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-gold-50 text-gold-500 sm:h-9 sm:w-9 sm:rounded-xl">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500 sm:text-[10px]">
                                Minimum
                            </p>

                            <p class="mt-0.5 truncate text-xs font-black text-warm-950 sm:text-sm">
                                {{ $minimumOrder }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if (! $isOpen)
                <div class="mt-4 rounded-2xl border border-gold-100 bg-gold-50 p-4">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-gold-500 shadow-sm">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 7v5M12 16h.01" />
                            </svg>
                        </span>

                        <div>
                            <p class="text-sm font-black text-gold-700">
                                Restaurant currently closed
                            </p>

                            <p class="mt-1 text-xs font-semibold leading-5 text-gold-700 sm:text-sm">
                                You can still add items to cart now and checkout later when the restaurant opens.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Category Navigation --}}
    <section class="border-y border-warm-200 bg-white">
        <div class="mx-auto max-w-7xl py-3">
            <div class="mb-2 flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-warm-500">
                    Browse Categories
                </p>

                @if ($selectedCategory)
                    <a
                        href="{{ route('menu') }}"
                        class="text-[10px] font-black text-brand-600 hover:text-brand-800"
                    >
                        Clear filter
                    </a>
                @endif
            </div>

            <nav
                class="flex snap-x snap-mandatory gap-2 overflow-x-auto px-4 pb-1 sm:px-6 lg:flex-wrap lg:overflow-visible lg:px-8"
                aria-label="Menu categories"
            >
                <a
                    href="{{ route('menu') }}"
                    @if (! request('category')) aria-current="page" @endif
                    @class([
                        'inline-flex min-h-11 shrink-0 snap-start items-center justify-center rounded-xl px-4 py-2.5 text-xs font-black transition active:scale-[0.97] sm:rounded-2xl sm:px-5 sm:text-sm',
                        'bg-brand-500 text-white shadow-lg shadow-brand-500/20' => ! request('category'),
                        'bg-warm-50 text-warm-600 hover:bg-brand-50 hover:text-brand-600' => request('category'),
                    ])
                >
                    All Items
                </a>

                @foreach ($categories as $category)
                    @php
                        $isSelectedCategory = $selectedCategory?->id === $category->id;
                    @endphp

                    <a
                        href="{{ route('menu', ['category' => $category->slug]) }}"
                        @if ($isSelectedCategory) aria-current="page" @endif
                        @class([
                            'inline-flex min-h-11 shrink-0 snap-start items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition active:scale-[0.97] sm:rounded-2xl sm:px-5 sm:text-sm',
                            'bg-brand-500 text-white shadow-lg shadow-brand-500/20' => $isSelectedCategory,
                            'bg-warm-50 text-warm-600 hover:bg-brand-50 hover:text-brand-600' => ! $isSelectedCategory,
                        ])
                    >
                        <span>{{ $category->name }}</span>

                        <span
                            @class([
                                'rounded-full px-2 py-0.5 text-[9px] font-black',
                                'bg-white/20 text-white' => $isSelectedCategory,
                                'bg-white text-warm-500' => ! $isSelectedCategory,
                            ])
                        >
                            {{ $category->available_items_count }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>
    </section>

    {{-- Featured Items --}}
    @if ($featuredItems->isNotEmpty() && ! request('category'))
        <section class="bg-white py-8 sm:py-12 lg:py-16">
            <div class="mx-auto max-w-7xl">
                <div class="flex items-end justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-500 sm:text-xs">
                            Featured Items
                        </p>

                        <h2 class="mt-2 text-2xl font-black tracking-tight text-warm-950 sm:text-3xl">
                            Popular picks today
                        </h2>

                        <p class="mt-2 hidden max-w-2xl text-sm leading-6 text-warm-600 sm:block">
                            Customer favourites selected for faster ordering.
                        </p>
                    </div>

                    <span class="shrink-0 rounded-full bg-brand-50 px-3 py-1.5 text-[10px] font-black text-brand-600 sm:px-4 sm:py-2 sm:text-xs">
                        {{ $featuredItems->count() }} featured
                    </span>
                </div>

                {{-- Swipeable Featured Products --}}
                <div class="mt-5 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-3 sm:px-6 lg:grid lg:grid-cols-4 lg:overflow-visible lg:px-8">
                    @foreach ($featuredItems as $item)
                        @php
                            $featuredCustomizable =
                                ($item->active_sizes_count ?? 0) > 0
                                || ($item->active_addons_count ?? 0) > 0;
                        @endphp

                        <article class="group flex min-w-[72vw] max-w-[270px] snap-start flex-col overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white shadow-sm sm:min-w-[270px] lg:min-w-0 lg:max-w-none lg:transition lg:hover:-translate-y-1 lg:hover:shadow-xl">
                            <a
                                href="{{ route('menu.show', $item) }}"
                                class="block"
                            >
                                <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream">
                                    @if ($item->image_url)
                                        <img
                                            src="{{ $item->image_url }}"
                                            alt="{{ $item->name }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="grid h-full place-items-center">
                                            <span class="grid h-16 w-16 place-items-center rounded-full bg-white/90 text-3xl font-black text-brand-500 shadow-lg">
                                                {{ mb_substr($item->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif

                                    <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em] text-brand-500 shadow-sm">
                                        Featured
                                    </span>
                                </div>
                            </a>

                            <div class="flex flex-1 flex-col p-4">
                                <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500">
                                    {{ $item->category?->name ?? 'Arcade Kebab House' }}
                                </p>

                                <a
                                    href="{{ route('menu.show', $item) }}"
                                    class="mt-1 line-clamp-2 text-base font-black leading-5 text-warm-950 hover:text-brand-600"
                                >
                                    {{ $item->name }}
                                </a>

                                <div class="mt-auto flex items-end justify-between gap-3 pt-4">
                                    <p class="text-lg font-black text-warm-950">
                                        ($item->price)
                                    </p>

                                    @if ($featuredCustomizable)
                                        <a
                                            href="{{ route('menu.show', $item) }}"
                                            class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-500 px-3.5 py-2 text-[10px] font-black text-white shadow-sm transition active:scale-[0.97] hover:bg-brand-600"
                                        >
                                            Customize
                                        </a>
                                    @elseif (($item->is_available ?? true))
                                        <form
                                            action="{{ route('cart.add', $item) }}"
                                            method="POST"
                                            x-data="{ loading: false }"
                                            x-on:submit="loading = true"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                x-bind:disabled="loading"
                                                class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-3.5 py-2 text-[10px] font-black text-white shadow-sm transition active:scale-[0.97] hover:bg-brand-600 disabled:opacity-70"
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

                                                <span x-text="loading ? 'Adding...' : 'Add'"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <p class="mt-1 px-4 text-[10px] font-semibold text-warm-500 sm:hidden">
                    Swipe to explore featured dishes
                </p>
            </div>
        </section>
    @endif

    {{-- Main Menu --}}
    <section class="py-8 sm:py-12 lg:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Results Heading --}}
            <div class="mb-5 flex items-end justify-between gap-4 sm:mb-8">
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-black uppercase tracking-[0.2em] text-brand-500 sm:text-xs">
                        {{ $selectedCategoryName }}
                    </p>

                    <h2 class="mt-1 text-2xl font-black tracking-tight text-warm-950 sm:mt-2 sm:text-3xl">
                        Browse menu
                    </h2>
                </div>

                <span class="shrink-0 rounded-full bg-white px-3 py-1.5 text-[10px] font-black text-warm-600 shadow-sm sm:px-4 sm:py-2 sm:text-xs">
                    {{ $visibleItemCount }}
                    {{ $visibleItemCount === 1 ? 'item' : 'items' }}
                </span>
            </div>

            {{-- Mobile-First Product Grid --}}
            <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-3">
                @forelse ($menuItems as $item)
                    @php
                        $itemIsAvailable = (bool) ($item->is_available ?? true);

                        $itemCustomizable =
                            ($item->active_sizes_count ?? 0) > 0
                            || ($item->active_addons_count ?? 0) > 0;

                        $itemHasDiscount = $item->compare_at_price
                            && (float) $item->compare_at_price > (float) $item->price;

                        $itemDiscount = $itemHasDiscount
                            ? round(
                                (
                                    ($item->compare_at_price - $item->price)
                                    / $item->compare_at_price
                                ) * 100
                            )
                            : null;
                    @endphp

                    <article class="group flex min-w-0 flex-col overflow-hidden rounded-[1.25rem] border border-warm-200 bg-white shadow-sm sm:rounded-[1.75rem] lg:transition lg:hover:-translate-y-1 lg:hover:shadow-2xl lg:hover:shadow-brand-900/10">
                        {{-- Product Image --}}
                        <a
                            href="{{ route('menu.show', $item) }}"
                            class="block"
                        >
                            <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream sm:aspect-[4/3]">
                                @if ($item->image_url)
                                    <img
                                        src="{{ $item->image_url }}"
                                        alt="{{ $item->name }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="absolute inset-0">
                                        <div class="absolute left-4 top-4 h-16 w-16 rounded-full bg-brand-200/30 blur-2xl"></div>
                                        <div class="absolute bottom-4 right-4 h-20 w-20 rounded-full bg-brand-300/30 blur-2xl"></div>
                                    </div>

                                    <div class="relative grid h-full place-items-center">
                                        <span class="grid h-14 w-14 place-items-center rounded-full bg-white/90 text-2xl font-black text-brand-500 shadow-lg sm:h-20 sm:w-20 sm:text-4xl">
                                            {{ mb_substr($item->name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Category --}}
                                <span class="absolute left-2 top-2 max-w-[75%] truncate rounded-full bg-white/95 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.08em] text-brand-500 shadow-sm sm:left-4 sm:top-4 sm:px-3 sm:text-xs">
                                    {{ $item->category?->name ?? 'Arcade Kebab House' }}
                                </span>

                                {{-- Discount or Popular --}}
                                @if ($itemDiscount)
                                    <span class="absolute right-2 top-2 rounded-full bg-red-600 px-2 py-1 text-[8px] font-black text-white shadow-sm sm:right-4 sm:top-4 sm:px-3 sm:text-xs">
                                        -{{ $itemDiscount }}%
                                    </span>
                                @elseif ($item->is_featured)
                                    <span class="absolute right-2 top-2 rounded-full bg-brand-500 px-2 py-1 text-[8px] font-black text-white shadow-sm sm:right-4 sm:top-4 sm:px-3 sm:text-xs">
                                        Popular
                                    </span>
                                @endif

                                @if (! $itemIsAvailable)
                                    <div class="absolute inset-0 grid place-items-center bg-warm-950/55 p-3 backdrop-blur-[1px]">
                                        <span class="rounded-full bg-white px-3 py-1.5 text-center text-[9px] font-black text-warm-900 shadow-lg sm:px-4 sm:py-2 sm:text-sm">
                                            Unavailable
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </a>

                        {{-- Product Content --}}
                        <div class="flex flex-1 flex-col p-3 sm:p-5 lg:p-6">
                            <a
                                href="{{ route('menu.show', $item) }}"
                                class="line-clamp-2 text-sm font-black leading-5 tracking-tight text-warm-950 transition hover:text-brand-600 sm:text-xl sm:leading-7"
                            >
                                {{ $item->name }}
                            </a>

                            <p class="mt-2 hidden line-clamp-2 text-sm leading-6 text-warm-600 sm:block">
                                {{ $item->description ?: 'Freshly prepared with quality ingredients.' }}
                            </p>

                            {{-- Compact Metadata --}}
                            @if ($item->preparation_time || $item->calories)
                                <div class="mt-2 flex flex-wrap gap-1.5 sm:mt-4 sm:gap-2">
                                    @if ($item->preparation_time)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-1 text-[8px] font-black text-brand-600 sm:px-3 sm:text-xs">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="h-3 w-3"
                                            >
                                                <circle cx="12" cy="12" r="9" />
                                                <path stroke-linecap="round" d="M12 7v5l3 2" />
                                            </svg>

                                            {{ $item->preparation_time }} min
                                        </span>
                                    @endif

                                    @if ($item->calories)
                                        <span class="hidden rounded-full bg-warm-100 px-3 py-1 text-xs font-black text-warm-600 sm:inline-flex">
                                            {{ $item->calories }} cal
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Price --}}
                            <div class="mt-auto pt-3 sm:pt-5">
                                <div class="flex min-w-0 items-end gap-2">
                                    <p class="truncate text-base font-black text-warm-950 sm:text-xl">
                                        ($item->price)
                                    </p>

                                    @if ($itemHasDiscount)
                                        <p class="hidden pb-0.5 text-xs font-bold text-warm-500 line-through sm:block">
                                            ($item->compare_at_price)
                                        </p>
                                    @endif
                                </div>

                                {{-- Product Action --}}
                                <div class="mt-3">
                                    @if ($itemIsAvailable)
                                        @if ($itemCustomizable)
                                            <a
                                                href="{{ route('menu.show', $item) }}"
                                                class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-2 py-2 text-[10px] font-black text-white shadow-md shadow-brand-500/15 transition active:scale-[0.97] hover:bg-brand-600 sm:min-h-12 sm:rounded-2xl sm:px-4 sm:text-sm"
                                            >
                                                Customize

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    class="h-3.5 w-3.5 sm:h-4 sm:w-4"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                                </svg>
                                            </a>
                                        @else
                                            <form
                                                action="{{ route('cart.add', $item) }}"
                                                method="POST"
                                                x-data="{ loading: false }"
                                                x-on:submit="loading = true"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    x-bind:disabled="loading"
                                                    class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-2 py-2 text-[10px] font-black text-white shadow-md shadow-brand-500/15 transition active:scale-[0.97] hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-70 sm:min-h-12 sm:rounded-2xl sm:px-4 sm:text-sm"
                                                >
                                                    <svg
                                                        x-show="! loading"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        class="h-3.5 w-3.5 sm:h-4 sm:w-4"
                                                    >
                                                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                                    </svg>

                                                    <svg
                                                        x-show="loading"
                                                        x-cloak
                                                        class="h-3.5 w-3.5 animate-spin sm:h-4 sm:w-4"
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

                                                    <span x-text="loading ? 'Adding...' : 'Add to Cart'"></span>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-warm-100 px-2 py-2 text-[9px] font-black text-warm-500 sm:min-h-12 sm:rounded-2xl sm:px-4 sm:text-sm">
                                            Unavailable
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-2 rounded-[1.75rem] border border-dashed border-brand-200 bg-white p-8 text-center shadow-sm lg:col-span-3 lg:p-12">
                        <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-brand-50 text-brand-500">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-7 w-7"
                            >
                                <path d="M4 3h16v18H4z" />
                                <path d="M8 7h8M8 11h8M8 15h5" />
                            </svg>
                        </div>

                        <h2 class="mt-5 text-xl font-black text-warm-950 sm:text-2xl">
                            No menu items found
                        </h2>

                        <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-warm-600">
                            Try selecting another category to see more available dishes.
                        </p>

                        <a
                            href="{{ route('menu') }}"
                            class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600 sm:rounded-2xl"
                        >
                            View All Items
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Safe Pagination Support --}}
            @if (method_exists($menuItems, 'hasPages') && $menuItems->hasPages())
                <div class="mt-8 rounded-2xl border border-warm-200 bg-white p-4 shadow-sm">
                    {{ $menuItems->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </section>

    {{-- Persistent Mobile Cart Bar --}}
    @if ($cartCount > 0)
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur lg:hidden">
            <div class="mx-auto flex max-w-7xl items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                <div class="min-w-0 shrink-0">
                    <p class="text-[9px] font-black uppercase tracking-[0.13em] text-warm-500">
                        Your Cart
                    </p>

                    <p class="mt-0.5 whitespace-nowrap text-sm font-black text-warm-950">
                        {{ $cartCount }}
                        {{ $cartCount === 1 ? 'item' : 'items' }}
                    </p>
                </div>

                <a
                    href="{{ route('cart.index') }}"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-between gap-3 rounded-xl bg-brand-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98]"
                >
                    <span class="flex items-center gap-2">
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

                        View Cart
                    </span>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-4 w-4"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            </div>
        </div>
    @endif
</main>

@endcomponent
