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
    {{-- Mobile Header --}}
    <header class="lg:hidden">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                    Menu Management
                </p>

                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">
                    Categories
                </h1>

                <p class="mt-1 text-sm font-semibold text-slate-500">
                    Organize your restaurant menu.
                </p>
            </div>

            <a
                href="{{ route('admin.categories.create') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-600 text-white shadow-lg shadow-orange-600/25 transition active:scale-95"
                aria-label="Add category"
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
            </a>
        </div>
    </header>

    {{-- Desktop Header --}}
    <header class="hidden items-end justify-between gap-8 lg:flex">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                Menu Management
            </p>

            <h1 class="mt-2 text-4xl font-black tracking-tight text-slate-950">
                Categories
            </h1>

            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-600">
                Organize menu items, control public visibility, manage category images, and define display order.
            </p>
        </div>

        <a
            href="{{ route('admin.categories.create') }}"
            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl"
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

            Add Category
        </a>
    </header>

    {{-- Overview --}}
    <section class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-orange-600 via-orange-500 to-red-600 p-5 text-white shadow-xl shadow-orange-900/15 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-8 h-64 w-64 rounded-full bg-yellow-200/20 blur-3xl"></div>

        <div class="relative grid gap-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-3.5 w-3.5"
                    >
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>

                    Category directory
                </span>

                <h2 class="mt-4 text-2xl font-black tracking-tight sm:text-4xl">
                    {{ $categoryCount }}
                    {{ $categoryCount === 1 ? 'menu category' : 'menu categories' }}
                </h2>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-orange-50">
                    Well-organized categories help customers discover meals quickly and make menu management easier.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-2 sm:gap-3 xl:min-w-[470px]">
                <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70 sm:text-[10px]">
                        Active
                    </p>

                    <p class="mt-1 text-xl font-black sm:text-2xl">
                        {{ $activeCategoriesOnPage }}
                    </p>

                    <p class="mt-0.5 hidden text-[9px] font-semibold text-white/60 sm:block">
                        This page
                    </p>
                </div>

                <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70 sm:text-[10px]">
                        Inactive
                    </p>

                    <p class="mt-1 text-xl font-black sm:text-2xl">
                        {{ $inactiveCategoriesOnPage }}
                    </p>

                    <p class="mt-0.5 hidden text-[9px] font-semibold text-white/60 sm:block">
                        This page
                    </p>
                </div>

                <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70 sm:text-[10px]">
                        Menu Items
                    </p>

                    <p class="mt-1 text-xl font-black sm:text-2xl">
                        {{ $menuItemsOnPage }}
                    </p>

                    <p class="mt-0.5 hidden text-[9px] font-semibold text-white/60 sm:block">
                        This page
                    </p>
                </div>
            </div>
        </div>
    </section>

    @if ($visibleCategories->isEmpty())
        {{-- Empty State --}}
        <section class="rounded-[1.75rem] border border-dashed border-orange-200 bg-white p-7 text-center shadow-sm sm:p-12">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-orange-50 text-orange-600 sm:h-20 sm:w-20">
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

            <h2 class="mt-5 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                Create your first category
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                Categories group related menu items and make it easier for customers to browse your restaurant menu.
            </p>

            <a
                href="{{ route('admin.categories.create') }}"
                class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition active:scale-[0.98] hover:bg-orange-700 sm:w-auto sm:rounded-2xl"
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
        <section class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                        Category Directory
                    </p>

                    <h2 class="mt-1 text-lg font-black text-slate-950 sm:text-xl">
                        Manage menu organization
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Search and status filters apply to categories loaded on this page.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px_auto] xl:min-w-[650px]">
                    {{-- Search --}}
                    <div class="relative">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m20 20-3.5-3.5" />
                        </svg>

                        <input
                            type="search"
                            x-model.debounce.200ms="search"
                            placeholder="Search name or slug..."
                            class="min-h-12 w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                        >

                        <button
                            type="button"
                            x-show="search.length > 0"
                            x-cloak
                            x-on:click="search = ''"
                            class="absolute right-3 top-1/2 grid h-7 w-7 -translate-y-1/2 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
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
                    <select
                        x-model="status"
                        class="min-h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                    >
                        <option value="all">All statuses</option>
                        <option value="active">Active only</option>
                        <option value="inactive">Inactive only</option>
                    </select>

                    {{-- Reset --}}
                    <button
                        type="button"
                        x-on:click="resetFilters"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-black text-orange-700 transition active:scale-[0.98] hover:bg-orange-100"
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
            </div>
        </section>

        {{-- Responsive Category Grid --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($categories as $category)
                <article
                    x-show="matches(
                        @js($category->name),
                        @js($category->slug),
                        @js($category->is_active ? 'active' : 'inactive')
                    )"
                    x-transition.opacity.duration.200ms
                    class="group flex min-w-0 flex-col overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-orange-200 hover:shadow-xl hover:shadow-orange-900/5 sm:rounded-[1.75rem]"
                >
                    {{-- Image --}}
                    <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-orange-100 via-amber-50 to-red-100">
                        @if ($category->image_url)
                            <img
                                src="{{ $category->image_url }}"
                                alt="{{ $category->name }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            >
                        @else
                            <div class="grid h-full place-items-center">
                                <span class="grid h-20 w-20 place-items-center rounded-full border border-white/70 bg-white/80 text-3xl font-black text-orange-700 shadow-xl backdrop-blur">
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
                                    'border-emerald-100 text-emerald-700' => $category->is_active,
                                    'border-red-100 text-red-700' => ! $category->is_active,
                                ])
                            >
                                <span
                                    @class([
                                        'h-2 w-2 rounded-full',
                                        'bg-emerald-500' => $category->is_active,
                                        'bg-red-500' => ! $category->is_active,
                                    ])
                                ></span>

                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>

                            <span class="rounded-full bg-slate-950/80 px-3 py-1.5 text-[10px] font-black text-white shadow-sm backdrop-blur">
                                {{ $category->menu_items_count }}
                                {{ $category->menu_items_count === 1 ? 'item' : 'items' }}
                            </span>
                        </div>

                        {{-- Sort Indicator --}}
                        <div class="absolute bottom-3 right-3 rounded-xl border border-white/30 bg-white/90 px-3 py-2 shadow-sm backdrop-blur sm:bottom-4 sm:right-4">
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                Position
                            </p>

                            <p class="mt-0.5 text-sm font-black text-slate-950">
                                #{{ $category->sort_order }}
                            </p>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex flex-1 flex-col p-4 sm:p-5">
                        <div class="min-w-0">
                            <h3 class="truncate text-xl font-black tracking-tight text-slate-950">
                                {{ $category->name }}
                            </h3>

                            <p class="mt-2 line-clamp-2 min-h-12 text-sm font-semibold leading-6 text-slate-600">
                                {{ $category->description ?: 'No description has been added for this category.' }}
                            </p>
                        </div>

                        {{-- Metadata --}}
                        <div class="mt-4 grid grid-cols-[minmax(0,1fr)_auto] gap-2">
                            <div class="min-w-0 rounded-xl bg-slate-50 px-3 py-3">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    URL Slug
                                </p>

                                <p
                                    class="mt-1 truncate font-mono text-xs font-bold text-slate-700"
                                    title="{{ $category->slug }}"
                                >
                                    {{ $category->slug }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-orange-50 px-4 py-3 text-center">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-orange-600">
                                    Items
                                </p>

                                <p class="mt-1 text-base font-black text-orange-950">
                                    {{ $category->menu_items_count }}
                                </p>
                            </div>
                        </div>

                        {{-- Public Visibility Note --}}
                        <div
                            @class([
                                'mt-3 flex items-start gap-2 rounded-xl px-3 py-3',
                                'bg-emerald-50' => $category->is_active,
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
                                    'text-emerald-600' => $category->is_active,
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
                                    'text-emerald-800' => $category->is_active,
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
                        <div class="mt-auto grid grid-cols-[1fr_auto] gap-2 pt-5">
                            <a
                                href="{{ route('admin.categories.edit', $category) }}"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition active:scale-[0.98] hover:bg-orange-700"
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

                            <form
                                action="{{ route('admin.categories.destroy', $category) }}"
                                method="POST"
                                onsubmit="return confirm('Delete this category? This action may affect related menu items.');"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="grid h-12 w-12 place-items-center rounded-xl border border-red-100 bg-red-50 text-red-600 transition active:scale-95 hover:border-red-600 hover:bg-red-600 hover:text-white"
                                    aria-label="Delete {{ $category->name }}"
                                    title="Delete category"
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
        </section>

        {{-- Pagination --}}
        @if ($hasPages)
            <div class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
                {{ $categories->withQueryString()->links() }}
            </div>
        @endif
    @endif

    {{-- Mobile Primary Action --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_rgba(15,23,42,0.14)] backdrop-blur lg:hidden">
        <div class="mx-auto pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.categories.create') }}"
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

                Add New Category
            </a>
        </div>
    </div>
</div>

@endcomponent
