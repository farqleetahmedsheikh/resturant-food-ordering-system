@component('layouts.admin', ['title' => 'Menu Items'])
@php
/*
* Support both paginated results and regular collections.
*/
$visibleMenuItems = collect(
method_exists($menuItems, 'items')
? $menuItems->items()
: $menuItems
);

    $menuItemCount = method_exists($menuItems, 'total')
        ? (int) $menuItems->total()
        : $visibleMenuItems->count();

    $pageItemCount = $visibleMenuItems->count();

    $availableItemsOnPage = $visibleMenuItems
        ->filter(fn ($item) => (bool) $item->is_available)
        ->count();

    $unavailableItemsOnPage = $visibleMenuItems
        ->reject(fn ($item) => (bool) $item->is_available)
        ->count();

    $featuredItemsOnPage = $visibleMenuItems
        ->filter(fn ($item) => (bool) $item->is_featured)
        ->count();

    $itemsWithOptionsOnPage = $visibleMenuItems
        ->filter(function ($item) {
            return (int) ($item->active_sizes_count ?? 0) > 0
                || (int) ($item->active_addons_count ?? 0) > 0;
        })
        ->count();

    $filtersActive = request()->filled('search')
        || request()->filled('category_id')
        || request()->filled('availability');

    $activeFilterCount = collect([
        request()->filled('search'),
        request()->filled('category_id'),
        request()->filled('availability'),
    ])->filter()->count();

    $selectedCategory = $categories->firstWhere(
        'id',
        request('category_id')
    );

    $hasPages = method_exists($menuItems, 'hasPages')
        && $menuItems->hasPages();
@endphp

<div class="space-y-5 pb-24 sm:space-y-6 lg:pb-8">
    {{-- Page Header and Overview --}}
    <header class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-5 text-white shadow-xl shadow-slate-950/20 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-orange-500/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-10 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative grid gap-7 xl:grid-cols-[minmax(0,1fr)_460px] xl:items-center">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-3.5 w-3.5 text-orange-300"
                        >
                            <path d="M4 4h16v16H4z" />
                            <path d="M8 8h8M8 12h8M8 16h5" />
                        </svg>

                        Menu Management
                    </span>

                    <span class="rounded-full bg-orange-500 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em]">
                        {{ $menuItemCount }}
                        {{ $menuItemCount === 1 ? 'item' : 'items' }}
                    </span>
                </div>

                <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                    Menu items
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-300 sm:text-base sm:leading-7">
                    Manage customer-facing meals, pricing, availability, promotions, sizes, add-ons, and menu display order.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ route('admin.menu-items.create') }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-950/30 transition active:scale-[0.98] hover:-translate-y-0.5 hover:bg-orange-500 sm:rounded-2xl"
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
                                d="M12 5v14M5 12h14"
                            />
                        </svg>

                        Add Menu Item
                    </a>

                    <a
                        href="{{ route('home') }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition active:scale-[0.98] hover:bg-white/20 sm:rounded-2xl"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path d="m3 11 9-8 9 8" />
                            <path d="M5 10v10h14V10" />
                        </svg>

                        View Customer Menu
                    </a>
                </div>
            </div>

            {{-- Current Page Statistics --}}
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-2">
                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Available
                            </p>

                            <p class="mt-1 text-2xl font-black text-emerald-300">
                                {{ $availableItemsOnPage }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        On this page
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Unavailable
                            </p>

                            <p class="mt-1 text-2xl font-black text-red-300">
                                {{ $unavailableItemsOnPage }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-red-400"></span>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        On this page
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Featured
                            </p>

                            <p class="mt-1 text-2xl font-black text-orange-300">
                                {{ $featuredItemsOnPage }}
                            </p>
                        </div>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4 text-orange-300"
                        >
                            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3z" />
                        </svg>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        Promoted items
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                With Options
                            </p>

                            <p class="mt-1 text-2xl font-black">
                                {{ $itemsWithOptionsOnPage }}
                            </p>
                        </div>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4 text-violet-300"
                        >
                            <path d="M4 7h16M7 12h10M9 17h6" />
                        </svg>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        Sizes or extras
                    </p>
                </div>
            </div>
        </div>
    </header>

    {{-- Filters --}}
    <section class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm sm:p-5">
        <form
            method="GET"
            action="{{ route('admin.menu-items.index') }}"
            class="space-y-4"
        >
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                {{-- Search --}}
                <div class="min-w-0 flex-1">
                    <label
                        for="search"
                        class="block text-[10px] font-black uppercase tracking-[0.14em] text-slate-500"
                    >
                        Search Menu
                    </label>

                    <div class="relative mt-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path
                                stroke-linecap="round"
                                d="m20 20-3.5-3.5"
                            />
                        </svg>

                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ request('search') }}"
                            placeholder="Search item name..."
                            class="min-h-12 w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                        >
                    </div>
                </div>

                {{-- Category --}}
                <div class="lg:w-[230px]">
                    <label
                        for="category_id"
                        class="block text-[10px] font-black uppercase tracking-[0.14em] text-slate-500"
                    >
                        Category
                    </label>

                    <div class="relative mt-2">
                        <select
                            id="category_id"
                            name="category_id"
                            class="min-h-12 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-semibold text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                        >
                            <option value="">All categories</option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(request('category_id') == $category->id)
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m6 9 6 6 6-6"
                            />
                        </svg>
                    </div>
                </div>

                {{-- Availability --}}
                <div class="lg:w-[210px]">
                    <label
                        for="availability"
                        class="block text-[10px] font-black uppercase tracking-[0.14em] text-slate-500"
                    >
                        Availability
                    </label>

                    <div class="relative mt-2">
                        <select
                            id="availability"
                            name="availability"
                            class="min-h-12 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-semibold text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                        >
                            <option value="">All availability</option>

                            <option
                                value="available"
                                @selected(request('availability') === 'available')
                            >
                                Available
                            </option>

                            <option
                                value="unavailable"
                                @selected(request('availability') === 'unavailable')
                            >
                                Unavailable
                            </option>
                        </select>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m6 9 6 6 6-6"
                            />
                        </svg>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="grid grid-cols-[1fr_auto] gap-2 lg:flex">
                    <button
                        type="submit"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-lg transition active:scale-[0.98] hover:bg-slate-800"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <path d="M4 5h16l-6 7v5l-4 2v-7L4 5z" />
                        </svg>

                        Apply Filters
                    </button>

                    @if ($filtersActive)
                        <a
                            href="{{ route('admin.menu-items.index') }}"
                            class="grid h-12 w-12 place-items-center rounded-xl border border-orange-200 bg-orange-50 text-orange-700 transition active:scale-95 hover:bg-orange-100"
                            aria-label="Clear filters"
                            title="Clear filters"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <path d="M3 12a9 9 0 1 0 3-6.7" />
                                <path
                                    stroke-linecap="round"
                                    d="M3 4v6h6"
                                />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Active Filter Chips --}}
            @if ($filtersActive)
                <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                    <span class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        Active Filters
                    </span>

                    @if (request()->filled('search'))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-black text-slate-700">
                            Search:
                            <span class="max-w-[180px] truncate">
                                {{ request('search') }}
                            </span>
                        </span>
                    @endif

                    @if ($selectedCategory)
                        <span class="rounded-full bg-orange-50 px-3 py-1.5 text-[10px] font-black text-orange-700">
                            {{ $selectedCategory->name }}
                        </span>
                    @endif

                    @if (request()->filled('availability'))
                        <span
                            @class([
                                'rounded-full px-3 py-1.5 text-[10px] font-black',
                                'bg-emerald-50 text-emerald-700' => request('availability') === 'available',
                                'bg-red-50 text-red-700' => request('availability') === 'unavailable',
                            ])
                        >
                            {{ \Illuminate\Support\Str::headline(request('availability')) }}
                        </span>
                    @endif

                    <span class="ml-auto text-[10px] font-bold text-slate-400">
                        {{ $activeFilterCount }}
                        {{ $activeFilterCount === 1 ? 'filter' : 'filters' }}
                    </span>
                </div>
            @endif
        </form>
    </section>

    @if ($visibleMenuItems->isEmpty())
        {{-- Empty State --}}
        <section class="rounded-[1.75rem] border border-dashed border-orange-200 bg-white p-7 text-center shadow-sm sm:p-12">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-orange-50 text-orange-600 sm:h-20 sm:w-20">
                @if ($filtersActive)
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-8 w-8"
                    >
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3.5-3.5" />
                        <path d="m8.5 8.5 5 5M13.5 8.5l-5 5" />
                    </svg>
                @else
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-8 w-8"
                    >
                        <path d="M4 4h16v16H4z" />
                        <path d="M8 8h8M8 12h8M8 16h5" />
                    </svg>
                @endif
            </span>

            <h2 class="mt-5 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                {{ $filtersActive
                    ? 'No menu items match these filters'
                    : 'Create your first menu item' }}
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                @if ($filtersActive)
                    Adjust your search, select another category, or clear the current filters.
                @else
                    Add a food item with a price, image, availability, and ordering options.
                @endif
            </p>

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                @if ($filtersActive)
                    <a
                        href="{{ route('admin.menu-items.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl border border-orange-200 bg-orange-50 px-5 py-3 text-sm font-black text-orange-700 transition hover:bg-orange-100"
                    >
                        Clear Filters
                    </a>
                @endif

                <a
                    href="{{ route('admin.menu-items.create') }}"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
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
                            d="M12 5v14M5 12h14"
                        />
                    </svg>

                    Add Menu Item
                </a>
            </div>
        </section>
    @else
        {{-- Menu Directory --}}
        <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                        Menu Directory
                    </p>

                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                        Restaurant products
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Showing {{ $pageItemCount }}
                        {{ $pageItemCount === 1 ? 'item' : 'items' }}
                        on this page
                    </p>
                </div>

                <a
                    href="{{ route('admin.menu-items.create') }}"
                    class="hidden min-h-10 items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-black text-orange-700 transition hover:bg-orange-100 sm:inline-flex"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        class="h-4 w-4"
                    >
                        <path
                            stroke-linecap="round"
                            d="M12 5v14M5 12h14"
                        />
                    </svg>

                    Add Item
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($menuItems as $item)
                    @php
                        $hasDiscount = $item->compare_at_price
                            && (float) $item->compare_at_price > (float) $item->price;

                        $discountPercentage = $hasDiscount
                            ? max(
                                1,
                                round(
                                    (
                                        (
                                            (float) $item->compare_at_price
                                            - (float) $item->price
                                        )
                                        / (float) $item->compare_at_price
                                    ) * 100
                                )
                            )
                            : null;

                        $activeSizesCount = (int) (
                            $item->active_sizes_count ?? 0
                        );

                        $activeAddonsCount = (int) (
                            $item->active_addons_count ?? 0
                        );
                    @endphp

                    <article class="group relative p-4 transition hover:bg-orange-50/30 sm:p-5">
                        <div
                            @class([
                                'absolute inset-y-0 left-0 w-1',
                                'bg-emerald-500' => $item->is_available,
                                'bg-red-500' => ! $item->is_available,
                            ])
                        ></div>

                        <div class="grid gap-4 pl-2 sm:grid-cols-[118px_minmax(0,1fr)] sm:pl-3 lg:grid-cols-[88px_minmax(0,1fr)_160px_170px_auto] lg:items-center">
                            {{-- Image --}}
                            <div class="relative h-44 overflow-hidden rounded-[1.25rem] bg-gradient-to-br from-orange-100 via-amber-50 to-red-100 sm:h-24 lg:h-20">
                                @if ($item->image_url)
                                    <img
                                        src="{{ $item->image_url }}"
                                        alt="{{ $item->name }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    >
                                @else
                                    <div class="grid h-full place-items-center">
                                        <span class="grid h-14 w-14 place-items-center rounded-full bg-white/85 text-2xl font-black text-orange-700 shadow-lg">
                                            {{ mb_strtoupper(
                                                mb_substr(
                                                    $item->name ?? 'M',
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Mobile Image Badges --}}
                                <div class="absolute left-2 top-2 flex flex-wrap gap-1.5 lg:hidden">
                                    @if ($item->is_featured)
                                        <span class="rounded-full bg-orange-600 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.08em] text-white shadow">
                                            Featured
                                        </span>
                                    @endif

                                    @if ($discountPercentage)
                                        <span class="rounded-full bg-emerald-600 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.08em] text-white shadow">
                                            {{ $discountPercentage }}% off
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Main Information --}}
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-orange-50 px-2.5 py-1 text-[9px] font-black text-orange-700">
                                        {{ $item->category?->name ?? 'Uncategorized' }}
                                    </span>

                                    <span
                                        @class([
                                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-black',
                                            'bg-emerald-50 text-emerald-700' => $item->is_available,
                                            'bg-red-50 text-red-700' => ! $item->is_available,
                                        ])
                                    >
                                        <span
                                            @class([
                                                'h-1.5 w-1.5 rounded-full',
                                                'bg-emerald-500' => $item->is_available,
                                                'bg-red-500' => ! $item->is_available,
                                            ])
                                        ></span>

                                        {{ $item->is_available ? 'Available' : 'Unavailable' }}
                                    </span>

                                    @if ($item->is_featured)
                                        <span class="hidden rounded-full bg-violet-50 px-2.5 py-1 text-[9px] font-black text-violet-700 lg:inline-flex">
                                            Featured
                                        </span>
                                    @endif

                                    @if ($discountPercentage)
                                        <span class="hidden rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black text-emerald-700 lg:inline-flex">
                                            {{ $discountPercentage }}% off
                                        </span>
                                    @endif
                                </div>

                                <h3 class="mt-2 break-words text-lg font-black tracking-tight text-slate-950 sm:text-xl">
                                    {{ $item->name }}
                                </h3>

                                <p class="mt-1 line-clamp-2 max-w-2xl text-xs font-semibold leading-5 text-slate-500 sm:text-sm sm:leading-6">
                                    {{ $item->description ?: 'No description has been added for this menu item.' }}
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($item->preparation_time)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-bold text-slate-600">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="h-3 w-3"
                                            >
                                                <circle cx="12" cy="12" r="9" />
                                                <path d="M12 7v5l3 2" />
                                            </svg>

                                            {{ $item->preparation_time }} min
                                        </span>
                                    @endif

                                    @if ($item->calories)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-bold text-slate-600">
                                            {{ $item->calories }} kcal
                                        </span>
                                    @endif

                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-bold text-slate-600">
                                        Position #{{ $item->sort_order }}
                                    </span>
                                </div>
                            </div>

                            {{-- Price --}}
                            <div class="rounded-xl bg-orange-50 px-4 py-3 lg:bg-transparent lg:px-0 lg:py-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-orange-600 lg:text-slate-400">
                                    Selling Price
                                </p>

                                <div class="mt-1 flex items-end gap-2 lg:block">
                                    <p class="text-xl font-black text-orange-600 lg:text-lg">
                                        Rs. {{ number_format($item->price, 0) }}
                                    </p>

                                    @if ($hasDiscount)
                                        <p class="pb-0.5 text-xs font-bold text-slate-400 line-through lg:mt-1">
                                            Rs. {{ number_format($item->compare_at_price, 0) }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Ordering Options --}}
                            <div class="grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-indigo-50 px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-indigo-600">
                                        Sizes
                                    </p>

                                    <p class="mt-1 text-base font-black text-indigo-950">
                                        {{ $activeSizesCount }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-violet-50 px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-violet-600">
                                        Extras
                                    </p>

                                    <p class="mt-1 text-base font-black text-violet-950">
                                        {{ $activeAddonsCount }}
                                    </p>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="grid grid-cols-[auto_1fr_auto] gap-2 sm:col-span-2 lg:col-span-1 lg:flex lg:justify-end">
                                <a
                                    href="{{ route('menu.show', $item) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition active:scale-95 hover:border-slate-400 hover:bg-slate-50"
                                    aria-label="Preview {{ $item->name }}"
                                    title="Preview item"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" />
                                        <circle cx="12" cy="12" r="2.5" />
                                    </svg>
                                </a>

                                <a
                                    href="{{ route('admin.menu-items.edit', $item) }}"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-xs font-black text-white shadow-lg shadow-orange-600/15 transition active:scale-[0.98] hover:bg-orange-700 lg:min-w-[94px]"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <path d="m14 4 6 6L8 22H2v-6L14 4z" />
                                        <path d="m12 6 6 6" />
                                    </svg>

                                    Edit
                                </a>

                                <form
                                    action="{{ route('admin.menu-items.destroy', $item) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this menu item? This action cannot be undone.');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition active:scale-95 hover:border-red-600 hover:bg-red-600 hover:text-white"
                                        aria-label="Delete {{ $item->name }}"
                                        title="Delete item"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4"
                                        >
                                            <path d="M4 7h16" />
                                            <path d="M10 11v6M14 11v6" />
                                            <path d="m6 7 1 14h10l1-14" />
                                            <path d="M9 7V4h6v3" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Pagination --}}
        @if ($hasPages)
            <div class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
                {{ $menuItems->withQueryString()->links() }}
            </div>
        @endif
    @endif

    {{-- Persistent Mobile Action --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_rgba(15,23,42,0.14)] backdrop-blur lg:hidden">
        <div class="mx-auto pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.menu-items.create') }}"
                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98]"
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
                        d="M12 5v14M5 12h14"
                    />
                </svg>

                Add New Menu Item
            </a>
        </div>
    </div>
</div>

@endcomponent
