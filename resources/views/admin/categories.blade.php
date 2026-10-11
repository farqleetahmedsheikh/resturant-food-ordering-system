@component('layouts.admin', ['title' => 'Categories'])
@php
$visibleCategories = collect(
method_exists($categories, 'items')
? $categories->items()
: $categories
);

    $categoryCount = method_exists($categories, 'total')
        ? (int) $categories->total()
        : $visibleCategories->count();

    $activeCategoriesOnPage = $visibleCategories
        ->filter(fn ($category) => (bool) $category->is_active)
        ->count();

    $inactiveCategoriesOnPage = $visibleCategories
        ->reject(fn ($category) => (bool) $category->is_active)
        ->count();

    $menuItemsOnPage = (int) $visibleCategories
        ->sum('menu_items_count');

    $hasPages = method_exists($categories, 'hasPages')
        && $categories->hasPages();
@endphp

<div
    x-data="{
        search: '',
        status: 'all',

        matches(name, slug, categoryStatus) {
            const query = this.search.trim().toLowerCase();

            const matchesSearch =
                query === ''
                || name.toLowerCase().includes(query)
                || slug.toLowerCase().includes(query);

            const matchesStatus =
                this.status === 'all'
                || this.status === categoryStatus;

            return matchesSearch && matchesStatus;
        },

        resetFilters() {
            this.search = '';
            this.status = 'all';
        }
    }"
    class="space-y-5 pb-24 sm:space-y-6 lg:pb-8"
>
    {{-- Page Header and Overview --}}
    <header class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-warm-950 via-warm-900 to-brand-900 p-5 text-white shadow-xl shadow-warm-950/20 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>
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
                        class="h-3.5 w-3.5 text-brand-200"
                    >
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>

                    Menu Management
                    </span>

                    <span class="rounded-full bg-brand-500 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em]">
                        {{ $categoryCount }}
                        {{ $categoryCount === 1 ? 'category' : 'categories' }}
                    </span>
                </div>

                <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                    Categories
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-warm-300 sm:text-base sm:leading-7">
                    Organize menu items, control public visibility, manage category images, and define menu order.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ route('admin.categories.create') }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-950/30 transition active:scale-[0.98] hover:-translate-y-0.5 hover:bg-brand-600 sm:rounded-2xl"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-5 w-5">
                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Add Category
                    </a>

                    <a
                        href="{{ route('home') }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition active:scale-[0.98] hover:bg-white/20 sm:rounded-2xl"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                            <path d="m3 11 9-8 9 8" />
                            <path d="M5 10v10h14V10" />
                        </svg>
                        View Customer Menu
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-2">
                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Active
                            </p>

                            <p class="mt-1 text-2xl font-black text-leaf-500">
                                {{ $activeCategoriesOnPage }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-leaf-500"></span>
                    </div>
                    <p class="mt-1 text-[9px] font-semibold text-white/45">On this page</p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Inactive
                            </p>

                            <p class="mt-1 text-2xl font-black text-red-300">
                                {{ $inactiveCategoriesOnPage }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-red-400"></span>
                    </div>
                    <p class="mt-1 text-[9px] font-semibold text-white/45">On this page</p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Menu Items
                            </p>

                            <p class="mt-1 text-2xl font-black text-brand-200">
                                {{ $menuItemsOnPage }}
                            </p>
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 text-brand-200">
                            <path d="M4 4h16v16H4z" />
                            <path d="M8 8h8M8 12h8M8 16h5" />
                        </svg>
                    </div>
                    <p class="mt-1 text-[9px] font-semibold text-white/45">Across these categories</p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">Menu Order</p>
                    <p class="mt-1 text-2xl font-black">{{ $categoryCount }}</p>
                    <p class="mt-1 text-[9px] font-semibold text-white/45">Categories sorted by position</p>
                </div>
            </div>
        </div>
    </header>

    @if ($visibleCategories->isEmpty())
        {{-- Empty State --}}
        <section class="rounded-[1.75rem] border border-dashed border-brand-200 bg-white p-7 text-center shadow-sm sm:p-12">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-brand-50 text-brand-500 sm:h-20 sm:w-20">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-8 w-8 sm:h-9 sm:w-9"
                >
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>
            </div>

            <h2 class="mt-5 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                Create your first category
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-warm-600">
                Categories group related menu items and make it easier for customers to browse your restaurant menu.
            </p>

            <a
                href="{{ route('admin.categories.create') }}"
                class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.98] hover:bg-brand-600 sm:w-auto sm:rounded-2xl"
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

                Create Category
            </a>
        </section>
    @else
        {{-- Directory Toolbar --}}
        <section class="mt-5 rounded-[1.5rem] border border-warm-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px_auto]">
                    {{-- Search --}}
                    <div class="relative">
                        <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-warm-500">Search Categories</label>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m20 20-3.5-3.5" />
                        </svg>

                        <input
                            type="search"
                            x-model.debounce.200ms="search"
                            placeholder="Search name or slug..."
                            class="min-h-12 w-full rounded-xl border border-warm-200 bg-warm-50 py-3 pl-11 pr-11 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                        >

                        <button
                            type="button"
                            x-show="search.length > 0"
                            x-cloak
                            x-on:click="search = ''"
                            class="absolute right-3 top-1/2 grid h-7 w-7 -translate-y-1/2 place-items-center rounded-lg text-warm-500 transition hover:bg-warm-200 hover:text-warm-600"
                            aria-label="Clear search"
                        >
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
                                    d="m7 7 10 10M17 7 7 17"
                                />
                            </svg>
                        </button>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.14em] text-warm-500">Status</label>
                        <select
                            x-model="status"
                            class="min-h-12 w-full rounded-xl border border-warm-200 bg-warm-50 px-4 py-3 text-sm font-black text-warm-600 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                        >
                            <option value="all">All statuses</option>
                            <option value="active">Active only</option>
                            <option value="inactive">Inactive only</option>
                        </select>
                    </div>

                    {{-- Reset --}}
                    <button
                        type="button"
                        x-on:click="resetFilters"
                        class="mt-6 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-black text-brand-600 transition active:scale-[0.98] hover:bg-brand-100"
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
                            <path stroke-linecap="round" d="M3 4v6h6" />
                        </svg>

                        Reset
                    </button>
            </div>
        </section>

        {{-- Category Directory --}}
        <section class="mt-5 overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-warm-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">Category Directory</p>
                    <h2 class="mt-1 text-lg font-black text-warm-950 sm:text-xl">Restaurant categories</h2>
                    <p class="mt-1 text-xs font-semibold text-warm-500">Showing {{ $visibleCategories->count() }} {{ $visibleCategories->count() === 1 ? 'category' : 'categories' }} on this page</p>
                </div>
                <a href="{{ route('admin.categories.create') }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-4 py-2 text-xs font-black text-brand-600 transition hover:bg-brand-100 sm:inline-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="h-4 w-4"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                    Add Category
                </a>
            </div>
            @foreach ($categories as $category)
                <article
                    x-show="matches(
                        @js($category->name),
                        @js($category->slug),
                        @js($category->is_active ? 'active' : 'inactive')
                    )"
                    x-transition.opacity.duration.200ms
                    class="group relative grid min-w-0 gap-4 border-b border-warm-100 bg-white p-4 transition last:border-b-0 hover:bg-brand-50/30 sm:p-5 lg:grid-cols-[150px_minmax(0,1fr)] lg:items-stretch"
                >
                    {{-- Image --}}
                    <div class="relative h-40 overflow-hidden rounded-[1.25rem] bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream sm:h-32 lg:h-full lg:min-h-[156px]">
                        @if ($category->image_url)
                            <img
                                src="{{ $category->image_url }}"
                                alt="{{ $category->name }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            >
                        @else
                            <div class="grid h-full place-items-center">
                                <span class="grid h-20 w-20 place-items-center rounded-full border border-white/70 bg-white/80 text-3xl font-black text-brand-600 shadow-xl backdrop-blur">
                                    {{ mb_strtoupper(
                                        mb_substr($category->name, 0, 1)
                                    ) }}
                                </span>
                            </div>
                        @endif

                        <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-3 p-3 sm:p-4">
                            <span
                                @class([
                                    'inline-flex items-center gap-2 rounded-full border bg-white/95 px-3 py-1.5 text-[10px] font-black shadow-sm backdrop-blur',
                                    'border-leaf-100 text-leaf-700' => $category->is_active,
                                    'border-red-100 text-red-700' => ! $category->is_active,
                                ])
                            >
                                <span
                                    @class([
                                        'h-2 w-2 rounded-full',
                                        'bg-leaf-500' => $category->is_active,
                                        'bg-red-500' => ! $category->is_active,
                                    ])
                                ></span>

                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>

                            <span class="rounded-full bg-warm-950/80 px-3 py-1.5 text-[10px] font-black text-white shadow-sm backdrop-blur">
                                {{ $category->menu_items_count }}
                                {{ $category->menu_items_count === 1 ? 'item' : 'items' }}
                            </span>
                        </div>

                        {{-- Sort Indicator --}}
                        <div class="absolute bottom-3 right-3 rounded-xl border border-white/30 bg-white/90 px-3 py-2 shadow-sm backdrop-blur sm:bottom-4 sm:right-4">
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                Position
                            </p>

                            <p class="mt-0.5 text-sm font-black text-warm-950">
                                #{{ $category->sort_order }}
                            </p>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex min-w-0 flex-col">
                        <div class="min-w-0 lg:flex lg:items-start lg:justify-between lg:gap-5">
                            <div class="min-w-0">
                                <h3 class="break-words text-xl font-black tracking-tight text-warm-950">
                                    {{ $category->name }}
                                </h3>

                                <p class="mt-1 line-clamp-2 max-w-3xl text-sm font-semibold leading-6 text-warm-500">
                                    {{ $category->description ?: 'No description has been added for this category.' }}
                                </p>
                            </div>

                            <div class="mt-3 flex shrink-0 flex-wrap gap-2 lg:mt-0">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-[9px] font-black text-brand-600">
                                    {{ $category->menu_items_count }} {{ $category->menu_items_count === 1 ? 'item' : 'items' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-warm-100 px-2.5 py-1 text-[9px] font-black text-warm-600">
                                    Position #{{ $category->sort_order }}
                                </span>
                            </div>
                        </div>

                        {{-- Metadata --}}
                        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-[minmax(0,1fr)_auto] lg:max-w-xl">
                            <div class="min-w-0 rounded-xl bg-warm-50 px-3 py-3">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                    URL Slug
                                </p>

                                <p
                                    class="mt-1 truncate font-mono text-xs font-bold text-warm-600"
                                    title="{{ $category->slug }}"
                                >
                                    {{ $category->slug }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-brand-50 px-4 py-3 text-center">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-brand-500">
                                    Items
                                </p>

                                <p class="mt-1 text-base font-black text-brand-900">
                                    {{ $category->menu_items_count }}
                                </p>
                            </div>
                        </div>

                        {{-- Public Visibility Note --}}
                        <div
                            @class([
                                'mt-3 flex items-start gap-2 rounded-xl px-3 py-3',
                                'bg-leaf-50' => $category->is_active,
                                'bg-red-50' => ! $category->is_active,
                            ])
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                @class([
                                    'mt-0.5 h-4 w-4 shrink-0',
                                    'text-leaf-700' => $category->is_active,
                                    'text-red-600' => ! $category->is_active,
                                ])
                            >
                                @if ($category->is_active)
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"
                                    />
                                    <circle cx="12" cy="12" r="2.5" />
                                @else
                                    <path
                                        stroke-linecap="round"
                                        d="m3 3 18 18"
                                    />
                                    <path d="M10.6 6.2A10 10 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-2.2 2.8" />
                                    <path d="M6.6 6.6C3.5 8.4 2 12 2 12s3.5 6 10 6a10 10 0 0 0 3.4-.6" />
                                @endif
                            </svg>

                            <p
                                @class([
                                    'text-xs font-semibold leading-5',
                                    'text-leaf-900' => $category->is_active,
                                    'text-red-800' => ! $category->is_active,
                                ])
                            >
                                @if ($category->is_active)
                                    Visible to customers on the public menu.
                                @else
                                    Hidden from customers until reactivated.
                                @endif
                            </p>
                        </div>

                        {{-- Actions --}}
                        <div class="mt-4 grid grid-cols-[auto_1fr_auto] gap-2 pt-0 sm:max-w-md lg:max-w-xl">
                            <a
                                href="{{ route('menu', ['category' => $category->slug]) }}"
                                target="_blank"
                                rel="noopener"
                                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-warm-200 bg-white text-warm-600 transition active:scale-95 hover:border-warm-500 hover:bg-warm-50"
                                aria-label="Preview {{ $category->name }}"
                                title="Preview category"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" />
                                    <circle cx="12" cy="12" r="2.5" />
                                </svg>
                            </a>
                            <a
                                href="{{ route('admin.categories.edit', $category) }}"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.98] hover:bg-brand-600"
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

                                Edit Category
                            </a>

                            <x-confirm-submit
                                :action="route('admin.categories.destroy', $category)"
                                title="Delete category?"
                                description="Categories with menu items are protected. Empty categories can be deleted safely."
                                confirm-label="Delete Category"
                                button-class="grid h-12 w-12 place-items-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition active:scale-95 hover:border-red-600 hover:bg-red-600 hover:text-white"
                                aria-label="Delete {{ $category->name }}"
                            >
                                <x-ui-icon name="trash" class="h-4 w-4" />
                            </x-confirm-submit>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Pagination --}}
        @if ($hasPages)
            <div class="rounded-[1.5rem] border border-warm-200 bg-white p-4 shadow-sm">
                {{ $categories->withQueryString()->links() }}
            </div>
        @endif
    @endif

    {{-- Mobile Primary Action --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur lg:hidden">
        <div class="mx-auto pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.categories.create') }}"
                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98]"
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

                Add New Category
            </a>
        </div>
    </div>
</div>

@endcomponent
