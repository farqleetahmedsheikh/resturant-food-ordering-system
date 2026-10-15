@component('layouts.public', ['title' => 'Menu'])
@php
$restaurantName = $restaurant?->name ?? 'Arcade Kebab House Restaurant';
$isOpen = (bool) ($availabilityStatus['is_open'] ?? $restaurant?->is_open ?? true);
$cartCount = \App\Support\Cart::count();

    $deliveryFee = \App\Support\Money::format($restaurant?->delivery_fee ?? 0);
    $minimumOrder = \App\Support\Money::format($restaurant?->minimum_order_amount ?? 0);

    $selectedCategoryName = $selectedCategory?->name ?? 'All Items';
    $visibleItemCount = $menuItems->count();
    $menuSections = collect();
    foreach ($categories as $menuCategory) {
        $sectionItems = $menuItems->filter(fn ($menuItem) => $menuItem->category_id === $menuCategory->id)->values();
        if ($sectionItems->isNotEmpty()) {
            $menuSections->push(['name' => $menuCategory->name, 'slug' => $menuCategory->slug, 'items' => $sectionItems]);
        }
    }
    $uncategorizedItems = $menuItems->filter(fn ($menuItem) => ! $menuItem->category_id)->values();
    if ($uncategorizedItems->isNotEmpty()) {
        $menuSections->push(['name' => 'More to enjoy', 'slug' => null, 'items' => $uncategorizedItems]);
    }
    $cartSummary = \App\Support\Cart::summary($restaurant);
    $searchQuery = $search ?? '';
    $currentSort = $sort ?? 'recommended';
    $hasActiveFilters = $selectedCategory || $searchQuery !== '' || $currentSort !== 'recommended';
    $sortLabels = [
        'recommended' => 'Recommended',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
        'name' => 'Name A-Z',
    ];
    $heroSlides = collect($restaurant?->menu_hero_slides ?: \App\Models\Restaurant::defaultMenuHeroSlides())
        ->filter(fn ($slide) => (bool) ($slide['is_active'] ?? true))
        ->sortBy(fn ($slide) => $slide['sort_order'] ?? 0)
        ->map(fn ($slide) => [
            'eyebrow' => $slide['eyebrow'] ?? 'Fresh food · Local favourite',
            'title' => $slide['title'] ?? 'Made fresh',
            'highlight' => $slide['highlight'] ?? 'for you.',
            'description' => $slide['description'] ?? 'Order your favourites from Arcade Kebab House.',
            'image' => str_starts_with((string) ($slide['image'] ?? ''), '/')
                ? asset(ltrim($slide['image'], '/'))
                : (\App\Support\ImageUpload::url($slide['image'] ?? null) ?? ''),
        ])->values();
    if ($heroSlides->isEmpty()) {
        $heroSlides = collect(\App\Models\Restaurant::defaultMenuHeroSlides())->map(fn ($slide) => [
            ...$slide,
            'image' => asset(ltrim($slide['image'], '/')),
        ]);
    }
@endphp

<main
    x-data="menuCartDrawer(@js($cartSummary), @js(route('cart.update')), @js(route('cart.remove', '__CART_KEY__')), @js(route('checkout.index')), @js((float) ($restaurant?->minimum_order_amount ?? 0)))"
    x-on:open-cart-drawer.window="openDrawer()"
    x-on:keydown.escape.window="closeDrawer()"
    x-effect="document.body.classList.toggle('overflow-hidden', drawerOpen)"
    class="min-h-screen bg-[var(--color-surface-warm)] pb-24 lg:pb-0"
>
    {{-- Rotating food photography hero --}}
    <section class="px-3 pt-4 sm:px-5 sm:pt-6 lg:px-8" x-data="menuHeroCarousel(@js($heroSlides))" x-init="start()" x-on:mouseenter="pause()" x-on:mouseleave="resume()" x-on:focusin="pause()" x-on:focusout="resume()">
        <div class="relative mx-auto h-[300px] max-w-[1600px] overflow-hidden rounded-3xl bg-warm-950 shadow-xl sm:h-[360px] lg:h-[430px]">
            <template x-for="(slide, index) in slides" x-bind:key="index">
                <div x-cloak x-show="active === index" x-transition:enter="transition-opacity duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0 bg-cover bg-center" x-bind:style="`background-image: url('${slide.image}')`" role="img" x-bind:aria-label="slide.title + ' ' + slide.highlight">
                    <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/60 to-black/10"></div>
                    <div class="relative flex h-full max-w-3xl flex-col justify-center px-6 py-8 text-white sm:px-12 lg:px-16">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-red-300 sm:text-xs" x-text="slide.eyebrow"></p>
                        <h1 class="mt-3 text-3xl font-black leading-tight sm:text-5xl lg:text-6xl"><span x-text="slide.title"></span> <span class="text-red-400" x-text="slide.highlight"></span></h1>
                        <p class="mt-3 max-w-xl text-sm font-medium leading-6 text-white/85 sm:mt-5 sm:text-base" x-text="slide.description"></p>
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <a href="#menu-content" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-500 px-5 text-sm font-black text-white transition hover:bg-brand-600">Explore the menu</a>
                            <span @class(['inline-flex items-center gap-2 rounded-full px-3 py-2 text-xs font-bold', 'bg-leaf-500/20 text-white' => $isOpen, 'bg-gold-500/25 text-white' => ! $isOpen])><span class="h-2 w-2 rounded-full {{ $isOpen ? 'bg-leaf-400' : 'bg-gold-400' }}"></span>{{ $isOpen ? 'Open now' : 'Currently closed' }}</span>
                        </div>
                    </div>
                </div>
            </template>

            <div class="absolute bottom-4 right-4 z-10 flex items-center gap-2 sm:bottom-6 sm:right-6">
                <button type="button" x-on:click="previous()" aria-label="Previous hero image" class="grid h-10 w-10 place-items-center rounded-full border border-white/40 bg-black/25 text-white backdrop-blur hover:bg-black/50">‹</button>
                <template x-for="(slide, index) in slides" x-bind:key="`dot-${index}`"><button type="button" x-on:click="go(index)" x-bind:aria-label="`Show hero image ${index + 1}`" x-bind:aria-current="active === index ? 'true' : null" x-bind:class="active === index ? 'w-7 bg-white' : 'w-2 bg-white/60'" class="h-2 rounded-full transition-all"></button></template>
                <button type="button" x-on:click="next()" aria-label="Next hero image" class="grid h-10 w-10 place-items-center rounded-full border border-white/40 bg-black/25 text-white backdrop-blur hover:bg-black/50">›</button>
            </div>
        </div>

        @if (! $isOpen)
            <div class="mx-auto mt-3 max-w-[1600px] rounded-xl border border-gold-200 bg-gold-50 px-4 py-3 text-sm font-semibold text-gold-800">You can browse and add items now; checkout when the restaurant is open.</div>
        @endif
    </section>

    {{-- Category Navigation --}}
    <section class="sticky top-[4.5rem] z-30 border-y border-warm-200 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto max-w-[1600px] px-3 py-3 sm:px-5 lg:px-8">
            <form
                action="{{ route('menu') }}"
                method="GET"
                class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px]"
            >
                @if ($selectedCategory)
                    <input type="hidden" name="category" value="{{ $selectedCategory->slug }}">
                @endif

                <label class="relative block">
                    <span class="sr-only">Search menu</span>

                    <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-warm-500">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m20 20-3.5-3.5" />
                        </svg>
                    </span>

                    <input
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="Search kebabs, drinks, desserts..."
                        class="h-12 w-full rounded-2xl border border-warm-200 bg-warm-50 pl-11 pr-24 text-sm font-bold text-warm-950 outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                    >

                    <button
                        type="submit"
                        class="absolute inset-y-1 right-1 inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 text-xs font-black text-white transition hover:bg-brand-600"
                    >
                        Search
                    </button>
                </label>

                <label class="relative block">
                    <span class="sr-only">Sort menu items</span>

                    <select
                        name="sort"
                        onchange="this.form.submit()"
                        class="h-12 w-full appearance-none rounded-2xl border border-warm-200 bg-warm-50 px-4 pr-10 text-sm font-black text-warm-950 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                    >
                        @foreach ($sortLabels as $value => $label)
                            <option value="{{ $value }}" @selected($currentSort === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <span class="pointer-events-none absolute inset-y-0 right-0 grid w-10 place-items-center text-warm-500">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </label>
            </form>

            <div class="mt-3 flex items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-warm-500">
                    Browse Categories
                </p>

                @if ($hasActiveFilters)
                    <a
                        href="{{ route('menu') }}"
                        class="text-[10px] font-black text-brand-600 hover:text-brand-800"
                    >
                        Clear all
                    </a>
                @endif
            </div>

            <nav
                class="mt-2 flex snap-x snap-mandatory gap-2 overflow-x-auto pb-1 lg:flex-wrap lg:overflow-visible"
                aria-label="Menu categories"
            >
                <a
                    href="{{ route('menu', array_filter(['q' => $searchQuery ?: null, 'sort' => $currentSort !== 'recommended' ? $currentSort : null])) }}"
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
                        href="{{ route('menu', array_filter(['category' => $category->slug, 'q' => $searchQuery ?: null, 'sort' => $currentSort !== 'recommended' ? $currentSort : null])) }}"
                        @if ($isSelectedCategory) aria-current="page" @endif
                        @class([
                            'inline-flex min-h-11 shrink-0 snap-start items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition active:scale-[0.97] sm:rounded-2xl sm:px-5 sm:text-sm',
                            'bg-brand-500 text-white shadow-lg shadow-brand-500/20' => $isSelectedCategory,
                            'bg-warm-50 text-warm-600 hover:bg-brand-50 hover:text-brand-600' => ! $isSelectedCategory,
                        ])
                    >
                        @if ($category->image_url)
                            <img src="{{ $category->image_url }}" alt="" class="h-7 w-7 rounded-full object-cover">
                        @else
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-brand-100 text-[10px] font-black text-brand-600">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                        @endif
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
        <section class="bg-white py-6 sm:py-8 lg:py-9">
            <div class="mx-auto max-w-[1600px] px-3 sm:px-5 lg:px-8">
                <div class="flex items-end justify-between gap-4">
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
                <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4 lg:gap-5">
                    @foreach ($featuredItems as $item)
                        @php
                            $featuredCustomizable =
                                ($item->active_sizes_count ?? 0) > 0
                                || ($item->active_addons_count ?? 0) > 0;
                        @endphp

                        <article class="group flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-warm-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                            <a
                                href="{{ $item->public_url }}"
                                class="block"
                            >
                                <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream">
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

                            <div class="flex min-h-[142px] flex-1 flex-col p-3 sm:min-h-[154px] sm:p-4">
                                <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500">
                                    {{ $item->category?->name ?? 'Arcade Kebab House' }}
                                </p>

                                <a
                                    href="{{ $item->public_url }}"
                                    class="mt-1 line-clamp-2 text-base font-black leading-5 text-warm-950 hover:text-brand-600"
                                >
                                    {{ $item->name }}
                                </a>

                                <div class="mt-auto flex items-end justify-between gap-3 pt-4">
                                    <p class="text-lg font-black text-warm-950">
                                        @money($item->price)
                                    </p>

                                    @if ($featuredCustomizable)
                                        <a
                                            href="{{ $item->public_url }}"
                                            class="inline-flex min-h-10 items-center justify-center rounded-xl bg-brand-500 px-3.5 py-2 text-[10px] font-black text-white shadow-sm transition active:scale-[0.97] hover:bg-brand-600"
                                        >
                                            Customize
                                        </a>
                                    @elseif (($item->is_available ?? true))
                                        <form
                                            action="{{ route('cart.add', $item) }}"
                                            method="POST"
                                            x-on:submit.prevent="addForm($event)"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                x-bind:disabled="pending"
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

                                                <span x-text="pending ? 'Adding...' : 'Add'"></span>
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

    {{-- Main Menu --}}
    <section id="menu-content" class="scroll-mt-44 py-6 sm:py-8 lg:py-10">
        <div class="mx-auto max-w-[1600px] px-3 sm:px-5 lg:px-8">
            {{-- Results Heading --}}
            <div class="mb-5 rounded-2xl border border-warm-200 bg-white p-4 shadow-sm sm:mb-6 sm:p-5">
                <div class="flex items-end justify-between gap-4">
                <div class="min-w-0">
                    <p class="truncate text-[10px] font-black uppercase tracking-[0.2em] text-brand-500 sm:text-xs">
                        {{ $selectedCategoryName }}
                    </p>

                    <h2 class="mt-1 text-2xl font-black tracking-tight text-warm-950 sm:mt-2 sm:text-3xl">
                        Browse menu
                    </h2>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <span class="hidden rounded-full bg-white px-3 py-1.5 text-[10px] font-black text-warm-600 shadow-sm sm:inline-flex sm:px-4 sm:py-2 sm:text-xs">
                        {{ $visibleItemCount }} {{ $visibleItemCount === 1 ? 'item' : 'items' }}
                    </span>
                    <button type="button" x-on:click="openDrawer()" class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-brand-500 px-3.5 text-xs font-black text-white shadow-sm hover:bg-brand-600 sm:min-h-11 sm:px-4 sm:text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M3 4h2l2 11h10l2-8H7"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg>
                        Cart <span class="grid h-5 min-w-5 place-items-center rounded-full bg-white/20 px-1" x-text="cart.count"></span>
                    </button>
                </div>
                </div>

                @if ($hasActiveFilters)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($selectedCategory)
                            <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-[10px] font-black text-brand-600">
                                Category: {{ $selectedCategory->name }}
                            </span>
                        @endif

                        @if ($searchQuery !== '')
                            <span class="inline-flex items-center gap-2 rounded-full bg-warm-100 px-3 py-1.5 text-[10px] font-black text-warm-700">
                                Search: "{{ $searchQuery }}"
                            </span>
                        @endif

                        @if ($currentSort !== 'recommended')
                            <span class="inline-flex items-center gap-2 rounded-full bg-gold-50 px-3 py-1.5 text-[10px] font-black text-gold-700">
                                {{ $sortLabels[$currentSort] ?? 'Sorted' }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="min-w-0 space-y-9">
            @forelse ($menuSections as $menuSection)
                <section id="category-{{ $menuSection['slug'] ?? 'more' }}" class="scroll-mt-44">
                    <div class="mb-4 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">Freshly prepared favourites</p>
                            <h3 class="mt-1 text-xl font-black text-warm-950 sm:text-2xl">{{ $menuSection['name'] }}</h3>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1.5 text-xs font-bold text-warm-600 shadow-sm">{{ $menuSection['items']->count() }} {{ $menuSection['items']->count() === 1 ? 'item' : 'items' }}</span>
                    </div>
                    <div class="grid grid-cols-2 items-stretch gap-3 sm:gap-4 lg:grid-cols-4 lg:gap-5">
                @foreach ($menuSection['items'] as $item)
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

                    <article class="group flex h-full min-h-[340px] min-w-0 flex-col overflow-hidden rounded-2xl border border-warm-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl sm:min-h-[410px]">
                        {{-- Product Image --}}
                        <a
                            href="{{ $item->public_url }}"
                            class="block"
                        >
                            <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream">
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
                        <div class="flex min-h-[205px] flex-1 flex-col p-3 sm:min-h-[210px] sm:p-5">
                            <a
                                href="{{ $item->public_url }}"
                                class="line-clamp-2 text-lg font-black leading-6 tracking-tight text-warm-950 transition hover:text-brand-600 sm:text-xl sm:leading-7"
                            >
                                {{ $item->name }}
                            </a>

                            <p class="mt-2 line-clamp-2 text-sm font-semibold leading-6 text-warm-600">
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
                                        @money($item->price)
                                    </p>

                                    @if ($itemHasDiscount)
                                        <p class="hidden pb-0.5 text-xs font-bold text-warm-500 line-through sm:block">
                                            @money($item->compare_at_price)
                                        </p>
                                    @endif
                                </div>

                                {{-- Product Action --}}
                                <div class="mt-3">
                                    @if ($itemIsAvailable)
                                        @if ($itemCustomizable)
                                            <p class="mb-2 text-[10px] font-bold text-warm-500">
                                                Choose size, sauces, and extras
                                            </p>

                                            <a
                                                href="{{ $item->public_url }}"
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
                                                x-on:submit.prevent="addForm($event)"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    x-bind:disabled="pending"
                                                    class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-2 py-2 text-[10px] font-black text-white shadow-md shadow-brand-500/15 transition active:scale-[0.97] hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-70 sm:min-h-12 sm:rounded-2xl sm:px-4 sm:text-sm"
                                                >
                                                    <svg
                                                        x-show="! pending"
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
                                                        x-show="pending"
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

                                                    <span x-text="pending ? 'Adding...' : 'Add to Cart'"></span>
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
                @endforeach
                    </div>
                </section>
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

    <div x-cloak x-show="cart.count > 0" x-transition class="fixed inset-x-0 bottom-0 z-40 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-lg backdrop-blur lg:hidden">
        <div class="mx-auto flex max-w-[1600px] items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <p class="min-w-0 shrink-0 text-sm font-black text-warm-950"><span x-text="cart.count"></span> <span x-text="cart.count === 1 ? 'item' : 'items'"></span> · <span x-text="money(cart.total)"></span></p>
            <button type="button" x-on:click="openDrawer()" class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-between gap-3 rounded-xl bg-brand-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 active:scale-[0.98]"><span>View cart</span><span aria-hidden="true">→</span></button>
        </div>
    </div>

    <div x-cloak x-show="drawerOpen" x-transition.opacity class="fixed inset-0 z-[250] bg-warm-950/50 backdrop-blur-[2px]" x-on:click.self="closeDrawer()">
        <section x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute inset-y-0 right-0 flex w-full max-w-[460px] flex-col bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title" x-on:click.stop>
            <header class="flex items-center justify-between border-b border-warm-200 px-5 py-4 sm:px-6">
                <div><p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">Your order</p><h2 id="cart-drawer-title" class="mt-1 text-xl font-black text-warm-950">Cart <span class="text-warm-500" x-text="`(${cart.count})`"></span></h2></div>
                <button x-ref="drawerClose" type="button" x-on:click="closeDrawer()" aria-label="Close cart" class="grid h-10 w-10 place-items-center rounded-full border border-warm-200 text-warm-600 hover:bg-warm-50"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg></button>
            </header>
            <div x-show="error" x-text="error" class="mx-4 mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700" role="alert"></div>

            <div x-show="cart.count === 0" class="flex flex-1 flex-col items-center justify-center px-8 text-center">
                <span class="grid h-16 w-16 place-items-center rounded-full bg-brand-50 text-brand-500"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7"><path d="M3 4h2l2 11h10l2-8H7"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg></span>
                <h3 class="mt-4 text-lg font-black text-warm-950">Your cart is empty</h3><p class="mt-1 text-sm text-warm-500">Add a favourite and it will show up here.</p>
                <button type="button" x-on:click="closeDrawer()" class="mt-5 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white hover:bg-brand-600">Continue browsing</button>
            </div>

            <div x-show="cart.count > 0" class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6">
                <div class="space-y-3">
                    <template x-for="item in cart.items" x-bind:key="item.cart_key">
                        <article class="rounded-2xl border border-warm-200 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0"><h3 class="font-black text-warm-950" x-text="item.name"></h3><p class="mt-1 text-xs text-warm-500" x-show="item.size_name" x-text="item.size_name"></p><p class="mt-1 text-xs font-semibold text-warm-600" x-text="money(item.price) + ' each'"></p></div>
                                <button type="button" x-on:click="removeItem(item)" x-bind:disabled="busyKey === item.cart_key" aria-label="Remove item" class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 disabled:opacity-50"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13"/></svg></button>
                            </div>
                            <div x-show="item.addons?.length" class="mt-2 space-y-1 text-xs text-warm-600"><template x-for="addon in item.addons" x-bind:key="addon.id"><p x-text="`+ ${addon.name}${addon.quantity > 1 ? ` ×${addon.quantity}` : ''}`"></p></template></div>
                            <div x-show="item.customizations?.length" class="mt-2 space-y-2"><template x-for="group in item.customizations" x-bind:key="group.group_id || group.group_name"><div class="rounded-lg bg-warm-50 px-3 py-2 text-xs"><p class="font-bold text-warm-800" x-text="group.group_name || 'Customization'"></p><p class="mt-1 text-warm-600" x-text="(group.selected || []).map(option => `${option.name}${option.quantity > 1 ? ` ×${option.quantity}` : ''}`).join(', ') || 'No selections'"></p><p x-show="group.removed?.length" class="mt-1 font-semibold text-red-700" x-text="`No ${group.removed.map(option => option.name).join(', ')}`"></p></div></template></div>
                            <div class="mt-3 flex items-center justify-between border-t border-warm-100 pt-3">
                                <div class="inline-flex items-center gap-1 rounded-xl border border-warm-200 bg-warm-50 p-1"><button type="button" x-on:click="changeQuantity(item, -1)" x-bind:disabled="busyKey === item.cart_key" aria-label="Decrease quantity" class="grid h-8 w-8 place-items-center rounded-lg bg-white font-black text-warm-700 shadow-sm disabled:opacity-50">−</button><span class="grid min-w-9 place-items-center text-sm font-black text-warm-950" x-text="item.quantity"></span><button type="button" x-on:click="changeQuantity(item, 1)" x-bind:disabled="busyKey === item.cart_key || item.quantity >= 99" aria-label="Increase quantity" class="grid h-8 w-8 place-items-center rounded-lg bg-white font-black text-warm-700 shadow-sm disabled:opacity-50">+</button></div>
                                <p class="font-black text-warm-950" x-text="money(item.total)"></p>
                            </div>
                        </article>
                    </template>
                </div>
            </div>

            <footer x-show="cart.count > 0" class="border-t border-warm-200 bg-white px-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] pt-4 sm:px-6">
                <p x-show="minimumOrder > 0 && cart.subtotal < minimumOrder" class="mb-3 rounded-xl bg-gold-50 px-3 py-2 text-xs font-semibold text-gold-800">Add <span x-text="money(minimumOrder - cart.subtotal)"></span> more to reach the <span x-text="money(minimumOrder)"></span> minimum order.</p>
                <div class="space-y-2 text-sm"><div class="flex justify-between text-warm-600"><span>Subtotal</span><span x-text="money(cart.subtotal)"></span></div><div class="flex justify-between text-warm-600"><span>Delivery</span><span x-text="money(cart.delivery_fee)"></span></div><div class="flex justify-between border-t border-warm-200 pt-3 text-base font-black text-warm-950"><span>Total</span><span x-text="money(cart.total)"></span></div></div>
                <a x-bind:href="checkoutUrl" x-bind:aria-disabled="cart.count === 0 || (minimumOrder > 0 && cart.subtotal < minimumOrder)" x-bind:class="cart.count === 0 || (minimumOrder > 0 && cart.subtotal < minimumOrder) ? 'pointer-events-none opacity-50' : ''" class="mt-4 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-black text-white shadow-lg shadow-brand-500/20 hover:bg-brand-600">Continue to checkout</a>
                <a href="{{ route('cart.index') }}" class="mt-2 inline-flex min-h-10 w-full items-center justify-center text-xs font-bold text-warm-600 hover:text-brand-600">Open full cart</a>
            </footer>
        </section>
    </div>
</main>

@push('scripts')
<script>
    window.menuCartDrawer = (initialCart, updateUrl, removeUrl, checkoutUrl, minimumOrder) => ({
        cart: initialCart,
        updateUrl,
        removeUrl,
        checkoutUrl,
        minimumOrder,
        drawerOpen: false,
        pending: false,
        busyKey: null,
        error: '',
        openDrawer() {
            this.lastFocus = document.activeElement;
            this.drawerOpen = true;
            this.$nextTick(() => this.$refs.drawerClose?.focus());
        },
        closeDrawer() {
            this.drawerOpen = false;
            this.$nextTick(() => this.lastFocus?.focus?.());
        },
        money(value) {
            return new Intl.NumberFormat('en-AU', { style: 'currency', currency: 'AUD' }).format(Number(value || 0));
        },
        async addForm(event) {
            if (this.pending) return;
            this.pending = true;
            this.error = '';
            try {
                await this.request(event.currentTarget.action, new FormData(event.currentTarget));
                this.openDrawer();
            } catch (error) {
                this.error = error.message || 'Could not add this item. Please try again.';
            } finally {
                this.pending = false;
            }
        },
        async changeQuantity(item, amount) {
            this.busyKey = item.cart_key;
            this.error = '';
            const form = new FormData();
            form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            form.append('item', item.cart_key);
            form.append('quantity', Math.max(0, Math.min(99, Number(item.quantity) + amount)));
            try { await this.request(this.updateUrl, form); }
            catch (error) { this.error = error.message || 'Could not update this item.'; }
            finally { this.busyKey = null; }
        },
        async removeItem(item) {
            this.busyKey = item.cart_key;
            this.error = '';
            const form = new FormData();
            form.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            try { await this.request(this.removeUrl.replace('__CART_KEY__', encodeURIComponent(item.cart_key)), form); }
            catch (error) { this.error = error.message || 'Could not remove this item.'; }
            finally { this.busyKey = null; }
        },
        async request(url, formData) {
            const response = await fetch(url, { method: 'POST', body: formData, headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                window.location.assign(response.url);
                throw new Error('Please sign in to update your cart.');
            }
            const payload = await response.json();
            if (!response.ok) {
                const validationMessage = Object.values(payload.errors || {}).flat()[0];
                throw new Error(validationMessage || payload.message || 'Cart update failed.');
            }
            this.cart = payload.cart;
            window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: this.cart.count }));
            return payload;
        },
    });

    window.menuHeroCarousel = (slides) => ({
        slides,
        active: 0,
        timer: null,
        start() {
            if (this.slides.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            this.timer = window.setInterval(() => this.next(), 3000);
        },
        pause() {
            if (this.timer) window.clearInterval(this.timer);
            this.timer = null;
        },
        resume() {
            if (!this.timer) this.start();
        },
        next() { this.active = (this.active + 1) % this.slides.length; },
        previous() { this.active = (this.active - 1 + this.slides.length) % this.slides.length; },
        go(index) { this.active = index; },
    });
</script>
@endpush

@endcomponent
