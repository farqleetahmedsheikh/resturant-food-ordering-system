@component('layouts.public', ['title' => $restaurant?->name ?? 'Arcade Kebab House'])
@php
    $restaurantName = $restaurant?->name ?? 'Arcade Kebab House';
    $isOpen = (bool) ($availabilityStatus['is_open'] ?? $restaurant?->is_open ?? true);
    $cartCount = \App\Support\Cart::count();
    $deliveryFee = \App\Support\Money::format($restaurant?->delivery_fee ?? 0);
    $minimumOrder = \App\Support\Money::format($restaurant?->minimum_order_amount ?? 0);
    $openUntil = $restaurant?->closing_time
        ? 'Until ' . \Illuminate\Support\Carbon::parse($restaurant->closing_time)->format('g:i A')
        : 'Freshly prepared';
    $topSellerEyebrow = match ($topSellerWindow ?? 'weekly') {
        'weekly' => 'Popular this week',
        'all_time' => 'Customer favourites',
        default => 'Popular picks',
    };
    $topSellerCopy = match ($topSellerWindow ?? 'weekly') {
        'weekly' => 'Quick choices based on what customers are ordering most this week.',
        'all_time' => 'Reliable favourites customers continue ordering again and again.',
        default => 'Restaurant-selected favourites for simple and quick ordering.',
    };
@endphp

<main class="pb-24 lg:pb-0">
    {{-- Homepage hero --}}
    <section class="bg-white">
        <div class="mx-auto max-w-[1600px] px-3 pt-4 sm:px-5 sm:pt-6 lg:px-8">
            <div class="relative isolate min-h-[460px] overflow-hidden rounded-3xl bg-[#f7eee4] shadow-xl sm:min-h-[500px] lg:min-h-[510px]">
                <img
                    src="{{ asset('images/home/kebab-hero.webp') }}"
                    alt="Freshly grilled kebab wrap served with fries and garlic sauce"
                    fetchpriority="high"
                    class="absolute inset-0 -z-20 h-full w-full object-cover object-[62%_center]"
                >
                <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#fffaf4] via-[#fffaf4]/95 to-[#fffaf4]/60 sm:via-[#fffaf4]/90 sm:to-transparent"></div>
                <div class="absolute inset-y-0 right-0 -z-10 hidden w-[58%] bg-gradient-to-r from-transparent via-transparent to-black/5 lg:block"></div>

                <div class="flex min-h-[460px] flex-col justify-center px-5 py-10 sm:min-h-[500px] sm:px-10 lg:min-h-[510px] lg:w-[59%] lg:px-12">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-warm-700 sm:text-xs">
                        Fresh food <span class="px-1 text-brand-500">•</span> Great taste <span class="px-1 text-brand-500">•</span> Delivered to you
                    </p>
                    <h1 class="mt-4 max-w-[620px] text-4xl font-black leading-[1.04] text-warm-950 sm:text-5xl lg:text-[3.65rem]">
                        Authentic kebabs and meals,
                        <span class="block text-brand-600">made fresh for you.</span>
                    </h1>
                    <p class="mt-4 max-w-lg text-sm font-medium leading-6 text-warm-700 sm:text-base sm:leading-7">
                        {{ $restaurant?->short_description ?: 'Delicious kebabs, pizzas, burgers, pides, drinks and desserts delivered fresh to your door. Great food, simple ordering.' }}
                    </p>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('menu') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-600 px-6 text-sm font-black text-white shadow-md transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                            Order now
                            <x-ui-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                        <a href="#popular" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-brand-600 bg-white/85 px-6 text-sm font-black text-brand-700 transition hover:bg-brand-50">
                            See popular items
                        </a>
                    </div>

                    <div class="mt-7 grid max-w-[610px] grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
                        <div class="flex min-w-0 items-center gap-2 rounded-xl border border-white bg-white/90 px-3 py-3 shadow-sm backdrop-blur">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-leaf-50 text-leaf-700"><x-ui-icon name="check-circle" class="h-5 w-5" /></span>
                            <span class="min-w-0"><span class="block truncate text-[10px] font-black text-warm-950">{{ $isOpen ? 'Currently open' : 'Currently closed' }}</span><span class="block truncate text-[9px] text-warm-500">{{ $openUntil }}</span></span>
                        </div>
                        <div class="flex min-w-0 items-center gap-2 rounded-xl border border-white bg-white/90 px-3 py-3 shadow-sm backdrop-blur">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600"><x-ui-icon name="scooter" class="h-5 w-5" /></span>
                            <span class="min-w-0"><span class="block truncate text-[10px] font-black text-warm-950">Delivery</span><span class="block truncate text-[9px] text-warm-500">{{ $deliveryFee }}</span></span>
                        </div>
                        <div class="flex min-w-0 items-center gap-2 rounded-xl border border-white bg-white/90 px-3 py-3 shadow-sm backdrop-blur">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-leaf-50 text-leaf-700"><x-ui-icon name="credit-card" class="h-5 w-5" /></span>
                            <span class="min-w-0"><span class="block truncate text-[10px] font-black text-warm-950">Secure payment</span><span class="block truncate text-[9px] text-warm-500">Stripe</span></span>
                        </div>
                        <div class="flex min-w-0 items-center gap-2 rounded-xl border border-white bg-white/90 px-3 py-3 shadow-sm backdrop-blur">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-gold-50 text-gold-700"><x-ui-icon name="receipt" class="h-5 w-5" /></span>
                            <span class="min-w-0"><span class="block truncate text-[10px] font-black text-warm-950">Minimum order</span><span class="block truncate text-[9px] text-warm-500">{{ $minimumOrder }}</span></span>
                        </div>
                    </div>
                </div>
                <span class="absolute bottom-4 right-5 hidden rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-bold text-warm-700 shadow-sm lg:inline-flex">Freshly prepared, just for you</span>
            </div>
        </div>
    </section>

    {{-- Category discovery --}}
    <section class="bg-[#fffaf6] py-9 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-600">Our menu</p>
                    <h2 class="mt-1 text-2xl font-black text-warm-950 sm:text-3xl">Explore our delicious menu</h2>
                    <p class="mt-1 text-sm text-warm-600">A wide range of fresh meals, prepared with quality ingredients.</p>
                </div>
                <a href="{{ route('menu') }}" class="shrink-0 text-xs font-black text-brand-600 hover:text-brand-800 sm:text-sm">View full menu <span aria-hidden="true">→</span></a>
            </div>

            @if ($categories->isNotEmpty())
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($categories as $category)
                        <a href="{{ route('menu', ['category' => $category->slug]) }}" class="group overflow-hidden rounded-xl border border-warm-200 bg-white transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md">
                            <div class="aspect-[1.6/1] overflow-hidden bg-[#f5eee7]">
                                @if ($category->image_url)
                                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <div class="grid h-full place-items-center bg-gradient-to-br from-[#fff0ea] to-[#f8e9c9] text-brand-600"><x-ui-icon name="utensils" class="h-8 w-8" /></div>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-2 px-3 py-3">
                                <span class="truncate text-sm font-black text-warm-950">{{ $category->name }}</span>
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full border border-warm-200 text-brand-600 transition group-hover:border-brand-200 group-hover:bg-brand-50"><x-ui-icon name="arrow-right" class="h-3.5 w-3.5" /></span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Live customer favourites --}}
    @if (($topSellingItems ?? collect())->isNotEmpty())
        <section id="popular" class="scroll-mt-24 bg-white py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-600">{{ $topSellerEyebrow }}</p>
                        <h2 class="mt-1 text-2xl font-black text-warm-950 sm:text-3xl">Customer favourites</h2>
                        <p class="mt-1 text-sm text-warm-600">{{ $topSellerCopy }}</p>
                    </div>
                    <a href="{{ route('menu') }}" class="shrink-0 text-xs font-black text-brand-600 hover:text-brand-800 sm:text-sm">See all dishes <span aria-hidden="true">→</span></a>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
                    @foreach ($topSellingItems->take(4) as $item)
                        @php
                            $isCustomizable = ($item->active_sizes_count ?? 0) > 0 || ($item->active_addons_count ?? 0) > 0 || ($item->active_addon_groups_count ?? 0) > 0;
                        @endphp
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-xl border border-warm-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                            <a href="{{ $item->public_url }}" class="relative block aspect-[1.65/1] overflow-hidden bg-[#f5eee7]">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <div class="grid h-full place-items-center bg-gradient-to-br from-[#fff0ea] to-[#f8e9c9]"><span class="grid h-14 w-14 place-items-center rounded-full bg-white text-2xl font-black text-brand-600 shadow">{{ mb_substr($item->name, 0, 1) }}</span></div>
                                @endif
                                <span class="absolute bottom-2 left-2 rounded-full bg-white px-2.5 py-1 text-[9px] font-black uppercase tracking-wide text-brand-700">{{ $item->category?->name ?? 'Favourite' }}</span>
                            </a>
                            <div class="flex flex-1 flex-col p-3 sm:p-4">
                                <a href="{{ $item->public_url }}" class="line-clamp-1 text-sm font-black text-warm-950 hover:text-brand-600 sm:text-base">{{ $item->name }}</a>
                                <p class="mt-1 line-clamp-2 min-h-9 text-[11px] leading-4 text-warm-600 sm:text-xs">{{ $item->description ?: 'Freshly prepared with quality ingredients.' }}</p>
                                <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                                    <span class="text-sm font-black text-warm-950 sm:text-base">@money($item->price)</span>
                                    @if ($isCustomizable)
                                        <a href="{{ $item->public_url }}" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-brand-600 px-3 text-[10px] font-black text-white transition hover:bg-brand-700 sm:text-xs">Customize</a>
                                    @else
                                        <form action="{{ route('cart.add', $item) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex min-h-9 items-center justify-center gap-1 rounded-lg bg-brand-600 px-3 text-[10px] font-black text-white transition hover:bg-brand-700 sm:text-xs"><x-ui-icon name="plus" class="h-3.5 w-3.5" /> Add</button>
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

    {{-- Ordering steps --}}
    @guest
        <section class="bg-[#fff8f2] py-9 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-600">How it works</p>
                    <h2 class="mt-1 text-2xl font-black text-warm-950 sm:text-3xl">Your favourite food in 3 simple steps</h2>
                    <p class="mt-1 text-sm text-warm-600">Quick, easy and convenient, from our kitchen to your door.</p>
                </div>
                <div class="mt-7 grid gap-3 sm:grid-cols-3 sm:gap-5">
                    @foreach ([['search', 'Choose your meal', 'Browse the menu and pick your favourite dishes.'], ['shopping-cart', 'Place your order', 'Add to cart, customize, and complete secure checkout.'], ['scooter', 'Track your delivery', 'Follow your order as we prepare and deliver it.']] as [$icon, $title, $copy])
                        <div class="flex items-center gap-4 rounded-xl border border-warm-200 bg-white p-4 sm:p-5">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-600"><x-ui-icon :name="$icon" class="h-6 w-6" /></span>
                            <div><p class="text-[9px] font-black uppercase tracking-widest text-brand-600">Step {{ $loop->iteration }}</p><h3 class="mt-0.5 text-sm font-black text-warm-950">{{ $title }}</h3><p class="mt-1 text-xs leading-5 text-warm-600">{{ $copy }}</p></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endguest

    {{-- Real menu combos only; this section stays hidden until combo products exist. --}}
    @if (($comboItems ?? collect())->isNotEmpty())
        <section class="bg-white py-10 sm:py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4"><div><p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-600">Good value</p><h2 class="mt-1 text-2xl font-black text-warm-950 sm:text-3xl">Meal combos</h2><p class="mt-1 text-sm text-warm-600">A satisfying meal, ready to order.</p></div><a href="{{ route('menu') }}" class="shrink-0 text-xs font-black text-brand-600">View all combos <span aria-hidden="true">→</span></a></div>
                <div class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
                    @foreach ($comboItems as $item)
                        <a href="{{ $item->public_url }}" class="group flex min-w-0 flex-col overflow-hidden rounded-xl border border-warm-200 bg-white transition hover:border-brand-300 hover:shadow-md">
                            <div class="aspect-[1.6/1] w-full bg-[#f5eee7]">@if ($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">@else<div class="grid h-full place-items-center text-brand-600"><x-ui-icon name="utensils" class="h-8 w-8" /></div>@endif</div>
                            <div class="flex min-w-0 flex-1 flex-col p-3 sm:p-4"><span class="line-clamp-1 text-sm font-black text-warm-950">{{ $item->name }}</span><span class="mt-1 line-clamp-2 text-xs text-warm-600">{{ $item->description ?: 'A delicious combination of customer favourites.' }}</span><span class="mt-auto pt-2 text-sm font-black text-brand-600">@money($item->price)</span></div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Family sharing callout --}}
    <section class="bg-white px-4 pb-10 sm:px-6 sm:pb-14 lg:px-8">
        <div class="relative mx-auto max-w-7xl overflow-hidden rounded-2xl bg-warm-950">
            <img src="{{ asset('images/home/family-feast.webp') }}" alt="A shareable spread of kebabs, pizza and sides" loading="lazy" class="absolute inset-0 h-full w-full object-cover object-[65%_center]">
            <div class="absolute inset-0 bg-gradient-to-r from-[#650b10]/95 via-[#b90d18]/85 to-transparent"></div>
            <div class="relative max-w-2xl px-5 py-9 text-white sm:px-9 sm:py-12">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-red-100">Good food brings people together</p>
                <h2 class="mt-2 text-2xl font-black leading-tight sm:text-4xl">Make room for one more.</h2>
                <p class="mt-2 max-w-lg text-sm leading-6 text-white/90">Bring everyone to the table with freshly made meals, generous portions and flavours worth sharing.</p>
                <a href="{{ route('menu') }}" class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-white px-5 text-sm font-black text-brand-700 transition hover:bg-red-50">Explore the menu <x-ui-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
        </div>
    </section>

    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-warm-200 bg-white/95 px-4 pt-2.5 shadow-lg backdrop-blur lg:hidden">
        <div class="mx-auto flex max-w-7xl gap-2 pb-[calc(.5rem+env(safe-area-inset-bottom))]">
            <a href="{{ route('menu') }}" class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 text-sm font-black text-white"><x-ui-icon name="utensils" class="h-4 w-4" /> Order now</a>
            <a href="{{ route('cart.index') }}" class="relative grid h-11 w-12 place-items-center rounded-lg border border-warm-200 bg-white text-brand-600" aria-label="Open cart">
                <x-ui-icon name="cart" class="h-5 w-5" />
                @if ($cartCount > 0)<span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-brand-600 px-1 text-[10px] font-black text-white">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>@endif
            </a>
        </div>
    </div>
</main>
@endcomponent
