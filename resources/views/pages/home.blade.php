@component('layouts.public', ['title' => $restaurant?->name ?? 'Arcade Kebab House Restaurant'])
@php
$restaurantName = $restaurant?->name ?? 'Arcade Kebab House Restaurant';
$isOpen = (bool) ($availabilityStatus['is_open'] ?? $restaurant?->is_open ?? true);
$cartCount = \App\Support\Cart::count();

    $deliveryFee = \App\Support\Money::format($restaurant?->delivery_fee ?? 0);
    $minimumOrder = \App\Support\Money::format($restaurant?->minimum_order_amount ?? 0);

    $timingText = $restaurant?->opening_time && $restaurant?->closing_time
        ? $restaurant->opening_time . ' - ' . $restaurant->closing_time
        : 'Fresh meals delivered fast';

    $topSellerEyebrow = match ($topSellerWindow ?? 'weekly') {
        'weekly' => 'Popular This Week',
        'all_time' => 'Customer Favourites',
        default => 'Popular Picks',
    };

    $topSellerTitle = match ($topSellerWindow ?? 'weekly') {
        'weekly' => "This week's most ordered meals",
        'all_time' => 'Most loved by our customers',
        default => 'Start with a popular choice',
    };

    $topSellerCopy = match ($topSellerWindow ?? 'weekly') {
        'weekly' => 'Quick choices based on what customers are ordering most this week.',
        'all_time' => 'Reliable favourites customers continue ordering again and again.',
        default => 'Restaurant-selected favourites for simple and quick ordering.',
    };
@endphp

<main class="bg-[var(--color-surface-warm)] pb-28 lg:pb-0">
    {{-- Mobile-First Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -left-24 -top-20 h-72 w-72 rounded-full bg-brand-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-brand-200/40 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-4 pb-8 pt-5 sm:px-6 sm:pb-12 sm:pt-8 lg:grid lg:grid-cols-[1.02fr_0.98fr] lg:items-center lg:gap-12 lg:px-8 lg:py-20">
            <div class="min-w-0">
                {{-- Restaurant Identity and Availability --}}
                <div class="flex items-center justify-between gap-3">
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

                                <span class="truncate text-[10px] font-semibold text-warm-500 sm:text-xs">
                                    {{ $timingText }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($cartCount > 0)
                        <a
                            href="{{ route('cart.index') }}"
                            class="relative grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-brand-500 shadow-sm transition active:scale-95 lg:hidden"
                            aria-label="Open cart with {{ $cartCount }} items"
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

                            <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        </a>
                    @endif
                </div>

                {{-- Hero Copy --}}
                <div class="mt-6 lg:mt-8">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-500 sm:text-xs">
                        Fresh food delivered
                    </p>

                    <h1 class="mt-2 max-w-3xl text-3xl font-black leading-tight tracking-tight text-warm-950 sm:text-5xl lg:text-6xl">
                        Your next meal,
                        <span class="text-brand-500">ready when you are.</span>
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm font-semibold leading-6 text-warm-600 sm:mt-5 sm:text-base sm:leading-8">
                        {{ $restaurant?->short_description ?: 'Browse fresh meals, customize your favourites, pay on delivery, and track your order from one simple experience.' }}
                    </p>
                </div>

                @if (! $isOpen)
                    <div class="mt-4 rounded-2xl border border-gold-100 bg-gold-50 p-4">
                        <div class="flex items-start gap-3">
                            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-gold-500 shadow-sm">
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
                            </div>

                            <div>
                                <p class="text-sm font-black text-gold-700">
                                    Currently closed
                                </p>

                                <p class="mt-1 text-xs font-semibold leading-5 text-gold-700">
                                    You can still explore the menu. Ordering availability may be limited until the restaurant opens.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Primary Mobile Actions --}}
                <div class="mt-5 grid grid-cols-[1fr_auto] gap-3 sm:flex sm:flex-wrap lg:mt-8">
                    <a
                        href="{{ route('menu') }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98] hover:bg-brand-600 sm:rounded-2xl sm:px-6"
                    >
                        Browse Menu

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

                    @if ($cartCount > 0)
                        <a
                            href="{{ route('cart.index') }}"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-brand-200 bg-white px-4 py-3 text-sm font-black text-brand-600 shadow-sm transition active:scale-[0.98] hover:bg-brand-50 sm:rounded-2xl sm:px-6"
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

                            <span class="hidden sm:inline">Cart</span>
                            <span>{{ $cartCount }}</span>
                        </a>
                    @else
                        <a
                            href="#popular"
                            class="inline-flex h-12 w-12 items-center justify-center rounded-xl border border-brand-200 bg-white text-brand-600 shadow-sm transition active:scale-[0.98] hover:bg-brand-50 sm:w-auto sm:rounded-2xl sm:px-6"
                            aria-label="View popular dishes"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>

                            <span class="ml-2 hidden text-sm font-black sm:inline">
                                Popular dishes
                            </span>
                        </a>
                    @endif
                </div>

                {{-- Mobile Hero Image --}}
                <div class="relative mt-6 lg:hidden">
                    <div class="overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white p-2 shadow-xl shadow-brand-900/10">
                        <div class="relative aspect-[16/10] overflow-hidden rounded-[1.15rem] bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream">
                            @if ($restaurant?->cover_image_url)
                                <img
                                    src="{{ $restaurant->cover_image_url }}"
                                    alt="{{ $restaurantName }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div class="absolute inset-0">
                                    <div class="absolute left-6 top-6 h-20 w-20 rounded-full bg-brand-200/30 blur-2xl"></div>
                                    <div class="absolute bottom-6 right-6 h-24 w-24 rounded-full bg-brand-300/30 blur-2xl"></div>
                                </div>

                                <div class="relative grid h-full place-items-center p-5 text-center">
                                    <div>
                                        <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-white/90 text-brand-500 shadow-lg">
                                            <x-ui-icon name="burger" class="h-8 w-8" />
                                        </div>

                                        <p class="mt-4 text-xl font-black text-brand-600">
                                            {{ $restaurantName }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="absolute inset-x-3 bottom-3 flex items-center justify-between gap-3 rounded-2xl bg-warm-950/75 px-4 py-3 text-white backdrop-blur">
                                <div>
                                    <p class="text-xs font-black">
                                        Freshly prepared
                                    </p>

                                    <p class="mt-0.5 text-[10px] font-semibold text-warm-300">
                                        Made after you order
                                    </p>
                                </div>

                                <span class="rounded-full bg-leaf-500 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em]">
                                    COD available
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Essential Ordering Information --}}
                <div class="mt-4 grid grid-cols-3 gap-2 sm:mt-7 sm:max-w-2xl sm:gap-3">
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

                                <p class="mt-0.5 truncate text-xs font-black text-leaf-700 sm:text-sm">
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
            </div>

            {{-- Desktop Hero Image --}}
            <div class="relative hidden lg:block">
                <div class="absolute -left-5 top-10 z-10 rounded-2xl border border-warm-200 bg-white px-5 py-4 shadow-xl">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 text-brand-500">
                            <x-ui-icon name="utensils" class="h-5 w-5" />
                        </span>

                        <div>
                            <p class="text-sm font-black text-warm-950">
                                Fresh food
                            </p>

                            <p class="text-xs font-semibold text-warm-500">
                                Prepared after ordering
                            </p>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-5 right-8 z-10 rounded-2xl border border-leaf-100 bg-white px-5 py-4 shadow-xl">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-leaf-50 text-leaf-700">
                            <x-ui-icon name="scooter" class="h-5 w-5" />
                        </span>

                        <div>
                            <p class="text-sm font-black text-warm-950">
                                Track delivery
                            </p>

                            <p class="text-xs font-semibold text-warm-500">
                                Follow order progress
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[2rem] border border-warm-200 bg-white p-3 shadow-2xl shadow-brand-900/10">
                    <div class="relative grid aspect-[4/3] place-items-center overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream">
                        @if ($restaurant?->cover_image_url)
                            <img
                                src="{{ $restaurant->cover_image_url }}"
                                alt="{{ $restaurantName }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="absolute inset-0">
                                <div class="absolute left-8 top-8 h-24 w-24 rounded-full bg-brand-200/30 blur-2xl"></div>
                                <div class="absolute bottom-8 right-8 h-32 w-32 rounded-full bg-brand-300/30 blur-2xl"></div>
                            </div>

                            <div class="relative p-8 text-center">
                                <div class="mx-auto grid h-24 w-24 place-items-center rounded-full bg-white/80 text-brand-500 shadow-lg">
                                    <x-ui-icon name="burger" class="h-12 w-12" />
                                </div>

                                <p class="mt-6 text-4xl font-black tracking-tight text-brand-600">
                                    {{ $restaurantName }}
                                </p>

                                <p class="mt-3 text-sm font-bold text-brand-900/70">
                                    Fresh food, simple ordering, quick delivery
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Top Sellers --}}
    @if (($topSellingItems ?? collect())->isNotEmpty())
        <section id="popular" class="scroll-mt-24 py-8 sm:py-14 lg:py-20">
            <div class="mx-auto max-w-7xl">
                <div class="flex items-end justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-500 sm:text-xs">
                            {{ $topSellerEyebrow }}
                        </p>

                        <h2 class="mt-2 text-2xl font-black tracking-tight text-warm-950 sm:text-4xl">
                            {{ $topSellerTitle }}
                        </h2>

                        <p class="mt-2 hidden max-w-2xl text-sm leading-6 text-warm-600 sm:block">
                            {{ $topSellerCopy }}
                        </p>
                    </div>

                    <a
                        href="{{ route('menu') }}"
                        class="shrink-0 text-xs font-black text-brand-600 hover:text-brand-800 sm:text-sm"
                    >
                        See all
                    </a>
                </div>

                {{-- Mobile Horizontal Product Rail --}}
                <div class="mt-5 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-3 sm:px-6 lg:grid lg:grid-cols-4 lg:overflow-visible lg:px-8">
                    @foreach ($topSellingItems as $index => $item)
                        @php
                            $isCustomizable = ($item->active_sizes_count ?? 0) > 0
                                || ($item->active_addons_count ?? 0) > 0;
                        @endphp

                        <article class="group flex min-w-[78vw] max-w-[310px] snap-start flex-col overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white shadow-sm sm:min-w-[300px] lg:min-w-0 lg:max-w-none lg:rounded-[1.75rem] lg:transition lg:hover:-translate-y-1 lg:hover:shadow-2xl lg:hover:shadow-brand-900/10">
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
                                        <div class="absolute inset-0">
                                            <div class="absolute left-6 top-6 h-20 w-20 rounded-full bg-brand-200/30 blur-2xl"></div>
                                            <div class="absolute bottom-6 right-6 h-24 w-24 rounded-full bg-brand-300/30 blur-2xl"></div>
                                        </div>

                                        <span class="relative grid h-full place-items-center">
                                            <span class="grid h-16 w-16 place-items-center rounded-full bg-white/90 text-3xl font-black text-brand-500 shadow-lg">
                                                {{ mb_substr($item->name, 0, 1) }}
                                            </span>
                                        </span>
                                    @endif

                                    <div class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] text-brand-500 shadow-sm">
                                        #{{ $index + 1 }} popular
                                    </div>

                                    @if ($item->getAttribute('sold_quantity'))
                                        <div class="absolute bottom-3 right-3 rounded-full bg-warm-950/80 px-3 py-1.5 text-[9px] font-black text-white backdrop-blur">
                                            {{ $item->getAttribute('sold_quantity') }} ordered
                                        </div>
                                    @endif
                                </div>
                            </a>

                            <div class="flex flex-1 flex-col p-4 sm:p-5">
                                <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500 sm:text-xs">
                                    {{ $item->category?->name ?? 'Arcade Kebab House' }}
                                </p>

                                <a
                                    href="{{ route('menu.show', $item) }}"
                                    class="mt-1 line-clamp-1 text-lg font-black tracking-tight text-warm-950 hover:text-brand-600 sm:text-xl"
                                >
                                    {{ $item->name }}
                                </a>

                                <p class="mt-2 line-clamp-2 text-xs font-semibold leading-5 text-warm-500 sm:text-sm sm:leading-6">
                                    {{ $item->description ?: 'Freshly prepared with quality ingredients.' }}
                                </p>

                                <div class="mt-auto flex items-end justify-between gap-3 pt-4">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                            From
                                        </p>

                                        <p class="mt-0.5 text-xl font-black text-warm-950">
                                            @money($item->price)
                                        </p>
                                    </div>

                                    @if ($isCustomizable)
                                        <a
                                            href="{{ route('menu.show', $item) }}"
                                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.97] hover:bg-brand-600 sm:rounded-2xl sm:text-sm"
                                        >
                                            Customize
                                        </a>
                                    @else
                                        <form
                                            action="{{ route('cart.add', $item) }}"
                                            method="POST"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.97] hover:bg-brand-600 sm:rounded-2xl sm:text-sm"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    class="h-4 w-4"
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

                <p class="mt-1 px-4 text-[10px] font-semibold text-warm-500 sm:hidden">
                    Swipe to explore popular dishes
                </p>
            </div>
        </section>
    @endif

    {{-- Featured Menu --}}
    <section class="bg-white py-9 sm:py-14 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-500 sm:text-xs">
                        Featured Menu
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-warm-950 sm:text-4xl">
                        Restaurant favourites
                    </h2>

                    <p class="mt-2 hidden max-w-2xl text-sm leading-6 text-warm-600 sm:block">
                        Owner-selected meals for customers who want a quick recommendation.
                    </p>
                </div>

                <a
                    href="{{ route('menu') }}"
                    class="shrink-0 text-xs font-black text-brand-600 hover:text-brand-800 sm:text-sm"
                >
                    Full menu
                </a>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 sm:mt-8 sm:gap-5 lg:grid-cols-3">
                @forelse ($featuredItems as $item)
                    @php
                        $isCustomizable = ($item->active_sizes_count ?? 0) > 0
                            || ($item->active_addons_count ?? 0) > 0;

                        $isAvailable = $item->is_available ?? true;
                    @endphp

                    <article class="group flex min-w-0 flex-col overflow-hidden rounded-[1.25rem] border border-warm-200 bg-white shadow-sm sm:rounded-[1.75rem] lg:transition lg:hover:-translate-y-1 lg:hover:shadow-2xl lg:hover:shadow-brand-900/10">
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
                                        <div class="grid h-14 w-14 place-items-center rounded-full bg-white/90 text-2xl font-black text-brand-500 shadow-lg sm:h-20 sm:w-20 sm:text-4xl">
                                            {{ mb_substr($item->name, 0, 1) }}
                                        </div>
                                    </div>
                                @endif

                                <div class="absolute left-2 top-2 max-w-[80%] truncate rounded-full bg-white/95 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.1em] text-brand-500 shadow-sm sm:left-4 sm:top-4 sm:px-3 sm:text-xs">
                                    {{ $item->category?->name ?? 'Arcade Kebab House' }}
                                </div>

                                @if (isset($item->preparation_time) && $item->preparation_time)
                                    <div class="absolute bottom-2 right-2 rounded-full bg-warm-950/80 px-2 py-1 text-[8px] font-black text-white backdrop-blur sm:bottom-4 sm:right-4 sm:px-3 sm:text-xs">
                                        {{ $item->preparation_time }} min
                                    </div>
                                @endif
                            </div>
                        </a>

                        <div class="flex flex-1 flex-col p-3 sm:p-5 lg:p-6">
                            <a
                                href="{{ route('menu.show', $item) }}"
                                class="line-clamp-2 text-sm font-black leading-5 tracking-tight text-warm-950 hover:text-brand-600 sm:text-xl sm:leading-7"
                            >
                                {{ $item->name }}
                            </a>

                            <p class="mt-2 hidden line-clamp-2 text-sm leading-6 text-warm-600 sm:block">
                                {{ $item->description ?: 'Freshly prepared with quality ingredients.' }}
                            </p>

                            <div class="mt-auto pt-3 sm:pt-5">
                                <p class="text-[8px] font-black uppercase tracking-[0.12em] text-warm-500 sm:text-xs">
                                    Price
                                </p>

                                <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-base font-black text-warm-950 sm:text-xl">
                                        @money($item->price)
                                    </p>

                                    @if ($isAvailable)
                                        @if ($isCustomizable)
                                            <a
                                                href="{{ route('menu.show', $item) }}"
                                                class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-brand-500 px-2 py-2 text-[10px] font-black text-white shadow-md shadow-brand-500/15 transition active:scale-[0.97] hover:bg-brand-600 sm:min-h-11 sm:w-auto sm:rounded-xl sm:px-4 sm:text-sm"
                                            >
                                                Customize
                                            </a>
                                        @else
                                            <form
                                                action="{{ route('cart.add', $item) }}"
                                                method="POST"
                                                class="w-full sm:w-auto"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="inline-flex min-h-10 w-full items-center justify-center gap-1 rounded-lg bg-brand-500 px-2 py-2 text-[10px] font-black text-white shadow-md shadow-brand-500/15 transition active:scale-[0.97] hover:bg-brand-600 sm:min-h-11 sm:w-auto sm:rounded-xl sm:px-4 sm:text-sm"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        class="h-3.5 w-3.5 sm:h-4 sm:w-4"
                                                    >
                                                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                                    </svg>

                                                    Add
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-warm-100 px-2 py-2 text-[9px] font-black text-warm-500 sm:min-h-11 sm:w-auto sm:rounded-xl sm:px-4 sm:text-sm">
                                            Unavailable
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-2 rounded-[1.5rem] border border-dashed border-brand-200 bg-brand-50 p-8 text-center lg:col-span-3">
                        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-brand-500 shadow-sm">
                            <x-ui-icon name="utensils" class="h-7 w-7" />
                        </div>

                        <h3 class="mt-4 text-lg font-black text-warm-950">
                            No featured items yet
                        </h3>

                        <p class="mt-2 text-sm font-semibold text-warm-500">
                            Featured dishes will appear here when they become available.
                        </p>
                    </div>
                @endforelse
            </div>

            <a
                href="{{ route('menu') }}"
                class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-5 py-3 text-sm font-black text-brand-600 transition active:scale-[0.98] hover:bg-brand-100 sm:mt-8 sm:w-auto sm:rounded-2xl"
            >
                Explore Complete Menu

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
    </section>

    {{-- Simple Ordering Process --}}
    @guest
        <section class="bg-white pb-9 sm:pb-14 lg:pb-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-[1.75rem] border border-warm-200 bg-[var(--color-surface-warm)] p-4 sm:p-7 lg:p-9">
                    <div class="text-center">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-500 sm:text-xs">
                            Simple Ordering
                        </p>

                        <h2 class="mt-2 text-2xl font-black tracking-tight text-warm-950 sm:text-3xl">
                            From menu to your door
                        </h2>
                    </div>

                    <div class="relative mt-6 grid gap-3 sm:grid-cols-3 sm:gap-5">
                        <article class="flex items-center gap-4 rounded-2xl border border-warm-200 bg-white p-4 shadow-sm sm:block sm:p-6 sm:text-center">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-500 sm:mx-auto">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M4 3h16v18H4z" />
                                    <path d="M8 7h8M8 11h8M8 15h5" />
                                </svg>
                            </span>

                            <div class="min-w-0 sm:mt-4">
                                <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500">
                                    Step 1
                                </p>

                                <h3 class="mt-1 font-black text-warm-950">
                                    Choose your food
                                </h3>

                                <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                    Browse available dishes and categories.
                                </p>
                            </div>
                        </article>

                        <article class="flex items-center gap-4 rounded-2xl border border-warm-200 bg-white p-4 shadow-sm sm:block sm:p-6 sm:text-center">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-500 sm:mx-auto">
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
                            </span>

                            <div class="min-w-0 sm:mt-4">
                                <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500">
                                    Step 2
                                </p>

                                <h3 class="mt-1 font-black text-warm-950">
                                    Confirm your order
                                </h3>

                                <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                    Review your cart and enter delivery details.
                                </p>
                            </div>
                        </article>

                        <article class="flex items-center gap-4 rounded-2xl border border-warm-200 bg-white p-4 shadow-sm sm:block sm:p-6 sm:text-center">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-500 sm:mx-auto">
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
                                    <circle cx="7" cy="18" r="2" />
                                    <circle cx="18" cy="18" r="2" />
                                </svg>
                            </span>

                            <div class="min-w-0 sm:mt-4">
                                <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500">
                                    Step 3
                                </p>

                                <h3 class="mt-1 font-black text-warm-950">
                                    Track delivery
                                </h3>

                                <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                    Follow preparation and rider updates.
                                </p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>
    @endguest

    {{-- Compact Final CTA --}}
    <section class="bg-white pb-10 sm:pb-16 lg:pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-r from-brand-500 to-brand-800 px-5 py-8 text-white shadow-2xl shadow-brand-900/20 sm:px-10 sm:py-12 lg:rounded-[2rem]">
                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-100 sm:text-sm">
                            Ready to order?
                        </p>

                        <h2 class="mt-2 max-w-2xl text-2xl font-black tracking-tight sm:text-4xl">
                            Find your next favourite meal.
                        </h2>

                        <p class="mt-2 max-w-xl text-xs font-semibold leading-5 text-brand-50 sm:text-sm sm:leading-7">
                            Browse the full menu, customize your food, and place your order in a few simple steps.
                        </p>
                    </div>

                    <a
                        href="{{ route('menu') }}"
                        class="inline-flex min-h-12 w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-black text-brand-600 shadow-lg transition active:scale-[0.98] hover:bg-brand-50 sm:w-auto sm:rounded-2xl"
                    >
                        Browse Menu

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
        </div>
    </section>

    {{-- Persistent Mobile Order Bar --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur lg:hidden">
        <div class="mx-auto flex max-w-7xl items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('menu') }}"
                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M4 3h16v18H4z" />
                    <path d="M8 7h8M8 11h8M8 15h5" />
                </svg>

                Browse Menu
            </a>

            <a
                href="{{ route('cart.index') }}"
                class="relative inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-[0.98]"
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
                    <span class="absolute -right-1.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white">
                        {{ $cartCount > 99 ? '99+' : $cartCount }}
                    </span>
                @endif
            </a>
        </div>
    </div>
</main>

@endcomponent
