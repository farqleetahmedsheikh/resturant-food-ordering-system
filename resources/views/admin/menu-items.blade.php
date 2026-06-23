@component('layouts.admin', ['title' => 'Menu Items'])
@php
$menuItemCount = method_exists($menuItems, 'total')
? $menuItems->total()
: $menuItems->count();

    $filtersActive = request()->filled('search')
        || request()->filled('category_id')
        || request()->filled('availability');
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Menu Management
        </p>

        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Menu items
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            Manage food images, pricing, categories, availability, featured items, and public display order.
        </p>
    </div>

    <a
        href="{{ route('admin.menu-items.create') }}"
        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            class="h-5 w-5"
        >
            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
        </svg>

        Add Menu Item
    </a>
</div>

{{-- Summary Banner --}}
<section class="relative mb-7 overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-600 via-orange-500 to-red-600 p-6 text-white shadow-2xl shadow-orange-900/20 sm:p-8">
    <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-10 h-60 w-60 rounded-full bg-yellow-200/20 blur-3xl"></div>

    <div class="relative flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-100">
                Restaurant Menu
            </p>

            <h2 class="mt-3 text-3xl font-black tracking-tight">
                {{ $menuItemCount }}
                {{ $menuItemCount === 1 ? 'menu item' : 'menu items' }}
            </h2>

            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-orange-50">
                Keep item details, pricing, images, and availability accurate for the public ordering experience.
            </p>
        </div>

        <div class="grid h-16 w-16 shrink-0 place-items-center rounded-[1.4rem] border border-white/20 bg-white/15 shadow-xl backdrop-blur">
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
        </div>
    </div>
</section>

{{-- Filters --}}
<section class="mb-7 rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-6">
    <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                Search and Filters
            </p>

            <h2 class="mt-1 text-xl font-black text-slate-950">
                Find menu items
            </h2>
        </div>

        @if ($filtersActive)
            <a
                href="{{ route('admin.menu-items.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
            >
                Clear All Filters
            </a>
        @endif
    </div>

    <form
        method="GET"
        action="{{ route('admin.menu-items.index') }}"
        class="grid gap-4 lg:grid-cols-[minmax(260px,1fr)_240px_220px_auto]"
    >
        {{-- Search --}}
        <div>
            <label for="search" class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                Search
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
                    <path stroke-linecap="round" d="m20 20-3.5-3.5" />
                </svg>

                <input
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search item name"
                    class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-12 pr-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                >
            </div>
        </div>

        {{-- Category Filter --}}
        <div>
            <label for="category_id" class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                Category
            </label>

            <select
                id="category_id"
                name="category_id"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
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
        </div>

        {{-- Availability Filter --}}
        <div>
            <label for="availability" class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                Availability
            </label>

            <select
                id="availability"
                name="availability"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
            >
                <option value="">All availability</option>
                <option value="available" @selected(request('availability') === 'available')>
                    Available
                </option>
                <option value="unavailable" @selected(request('availability') === 'unavailable')>
                    Unavailable
                </option>
            </select>
        </div>

        <div class="flex items-end">
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-6 py-3 text-sm font-black text-white shadow-lg transition hover:bg-slate-800 lg:w-auto"
            >
                Apply Filters
            </button>
        </div>
    </form>
</section>

@if ($menuItems->isEmpty())
    {{-- Empty State --}}
    <section class="rounded-[2rem] border border-dashed border-orange-200 bg-white p-8 text-center shadow-sm sm:p-12">
        <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-50 text-orange-600">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-9 w-9"
            >
                <path d="M4 4h16v16H4z" />
                <path d="M8 8h8M8 12h8M8 16h5" />
            </svg>
        </div>

        <h2 class="mt-6 text-2xl font-black tracking-tight text-slate-950">
            {{ $filtersActive ? 'No matching menu items' : 'No menu items found' }}
        </h2>

        <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-600">
            @if ($filtersActive)
                No menu items match your current search or filter selection. Try adjusting or clearing the filters.
            @else
                Create your first food item and make it available on the public restaurant menu.
            @endif
        </p>

        <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
            @if ($filtersActive)
                <a
                    href="{{ route('admin.menu-items.index') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-orange-200 bg-white px-6 py-3.5 text-sm font-black text-orange-700 transition hover:bg-orange-50"
                >
                    Clear Filters
                </a>
            @endif

            <a
                href="{{ route('admin.menu-items.create') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-orange-600 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700"
            >
                Add Menu Item
            </a>
        </div>
    </section>
@else
    {{-- Desktop Table --}}
    <section class="hidden overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm xl:block">
        <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-6 py-5">
            <div>
                <h2 class="text-xl font-black text-slate-950">
                    Menu directory
                </h2>

                <p class="mt-1 text-sm font-semibold text-slate-500">
                    Review and manage all food items.
                </p>
            </div>

            <span class="rounded-full bg-orange-50 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-orange-700">
                {{ $menuItemCount }} Results
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/80">
                    <tr class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <th class="px-6 py-4">Menu Item</th>
                        <th class="px-5 py-4">Category</th>
                        <th class="px-5 py-4">Price</th>
                        <th class="px-5 py-4">Options</th>
                        <th class="px-5 py-4">Availability</th>
                        <th class="px-5 py-4">Featured</th>
                        <th class="px-5 py-4 text-center">Sort</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach ($menuItems as $item)
                        @php
                            $hasDiscount = $item->compare_at_price
                                && (float) $item->compare_at_price > (float) $item->price;
                        @endphp

                        <tr class="group transition hover:bg-orange-50/40">
                            {{-- Item --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-orange-100 via-amber-50 to-red-100 shadow-sm">
                                        @if ($item->image_url)
                                            <img
                                                src="{{ $item->image_url }}"
                                                alt="{{ $item->name }}"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            >
                                        @else
                                            <span class="text-xl font-black text-orange-700">
                                                {{ mb_substr($item->name, 0, 1) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="max-w-xs truncate text-base font-black text-slate-950">
                                            {{ $item->name }}
                                        </p>

                                        <p class="mt-1 max-w-sm text-sm leading-6 text-slate-500">
                                            {{ \Illuminate\Support\Str::limit($item->description ?: 'No description added.', 75) }}
                                        </p>

                                        @if ($item->preparation_time || $item->calories)
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @if ($item->preparation_time)
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-600">
                                                        {{ $item->preparation_time }} min
                                                    </span>
                                                @endif

                                                @if ($item->calories)
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-600">
                                                        {{ $item->calories }} kcal
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Category --}}
                            <td class="px-5 py-5">
                                <span class="inline-flex rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-700">
                                    {{ $item->category?->name ?? 'Uncategorized' }}
                                </span>
                            </td>

                            {{-- Price --}}
                            <td class="px-5 py-5">
                                <p class="font-black text-slate-950">
                                    Rs. {{ number_format($item->price, 0) }}
                                </p>

                                @if ($hasDiscount)
                                    <p class="mt-1 text-xs font-bold text-slate-400 line-through">
                                        Rs. {{ number_format($item->compare_at_price, 0) }}
                                    </p>
                                @endif
                            </td>

                            {{-- Options --}}
                            <td class="px-5 py-5">
                                <div class="flex flex-col gap-2">
                                    <span class="inline-flex w-fit rounded-full bg-orange-50 px-3 py-1.5 text-xs font-black text-orange-700">
                                        {{ $item->active_sizes_count }} sizes
                                    </span>

                                    <span class="inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                                        {{ $item->active_addons_count }} add-ons
                                    </span>
                                </div>
                            </td>

                            {{-- Availability --}}
                            <td class="px-5 py-5">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-black {{ $item->is_available ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-red-100 bg-red-50 text-red-700' }}">
                                    <span class="h-2 w-2 rounded-full {{ $item->is_available ? 'bg-emerald-500' : 'bg-red-500' }}"></span>

                                    {{ $item->is_available ? 'Available' : 'Unavailable' }}
                                </span>
                            </td>

                            {{-- Featured --}}
                            <td class="px-5 py-5">
                                @if ($item->is_featured)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-orange-100 bg-orange-50 px-3 py-1.5 text-xs font-black text-orange-700">
                                        <span>★</span>
                                        Featured
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-500">
                                        Standard
                                    </span>
                                @endif
                            </td>

                            {{-- Sort --}}
                            <td class="px-5 py-5 text-center">
                                <span class="inline-grid h-10 min-w-10 place-items-center rounded-xl bg-orange-50 px-3 font-black text-orange-700">
                                    {{ $item->sort_order }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('menu.show', $item) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-black text-slate-600 transition hover:border-slate-400 hover:bg-slate-50"
                                    >
                                        Preview
                                    </a>

                                    <a
                                        href="{{ route('admin.menu-items.edit', $item) }}"
                                        class="inline-flex items-center justify-center rounded-xl border border-orange-200 bg-white px-4 py-2.5 text-xs font-black text-orange-700 transition hover:border-orange-600 hover:bg-orange-600 hover:text-white"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.menu-items.destroy', $item) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this menu item?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-4 py-2.5 text-xs font-black text-red-600 transition hover:bg-red-600 hover:text-white"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Mobile and Tablet Cards --}}
    <div class="grid gap-5 md:grid-cols-2 xl:hidden">
        @foreach ($menuItems as $item)
            @php
                $hasDiscount = $item->compare_at_price
                    && (float) $item->compare_at_price > (float) $item->price;

                $discountPercentage = $hasDiscount
                    ? round((($item->compare_at_price - $item->price) / $item->compare_at_price) * 100)
                    : null;
            @endphp

            <article class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                {{-- Item Image --}}
                <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-orange-100 via-amber-50 to-red-100">
                    @if ($item->image_url)
                        <img
                            src="{{ $item->image_url }}"
                            alt="{{ $item->name }}"
                            class="h-full w-full object-cover"
                        >
                    @else
                        <div class="grid h-full place-items-center">
                            <div class="grid h-20 w-20 place-items-center rounded-full bg-white/80 text-4xl font-black text-orange-700 shadow-xl">
                                {{ mb_substr($item->name, 0, 1) }}
                            </div>
                        </div>
                    @endif

                    <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-white/90 px-3 py-1.5 text-xs font-black text-orange-700 shadow-sm backdrop-blur">
                            {{ $item->category?->name ?? 'Uncategorized' }}
                        </span>

                        @if ($item->is_featured)
                            <span class="rounded-full bg-orange-600 px-3 py-1.5 text-xs font-black text-white shadow-sm">
                                ★ Featured
                            </span>
                        @endif
                    </div>

                    <div class="absolute right-4 top-4">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-xs font-black shadow-sm backdrop-blur {{ $item->is_available ? 'text-emerald-700' : 'text-red-700' }}">
                            <span class="h-2 w-2 rounded-full {{ $item->is_available ? 'bg-emerald-500' : 'bg-red-500' }}"></span>

                            {{ $item->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </div>

                    @if ($discountPercentage)
                        <div class="absolute bottom-4 left-4 rounded-full bg-red-600 px-3 py-1.5 text-xs font-black text-white shadow-lg">
                            {{ $discountPercentage }}% Off
                        </div>
                    @endif
                </div>

                {{-- Item Content --}}
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="break-words text-xl font-black tracking-tight text-slate-950">
                                {{ $item->name }}
                            </h2>

                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
                                {{ $item->description ?: 'No description has been added for this item.' }}
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-xl font-black text-orange-600">
                                Rs. {{ number_format($item->price, 0) }}
                            </p>

                            @if ($hasDiscount)
                                <p class="mt-1 text-xs font-bold text-slate-400 line-through">
                                    Rs. {{ number_format($item->compare_at_price, 0) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Metadata --}}
                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-50 px-3 py-3 text-center">
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Sort
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                {{ $item->sort_order }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-3 py-3 text-center">
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Prep
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                {{ $item->preparation_time ? $item->preparation_time . 'm' : '—' }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-3 py-3 text-center">
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Calories
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                {{ $item->calories ?? '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Mobile Actions --}}
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <a
                            href="{{ route('admin.menu-items.edit', $item) }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                        >
                            Edit Item
                        </a>

                        <a
                            href="{{ route('menu.show', $item) }}"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center rounded-2xl border border-orange-200 bg-white px-4 py-3 text-sm font-black text-orange-700 transition hover:bg-orange-50"
                        >
                            Preview
                        </a>
                    </div>

                    <form
                        action="{{ route('admin.menu-items.destroy', $item) }}"
                        method="POST"
                        class="mt-3"
                        onsubmit="return confirm('Are you sure you want to delete this menu item?');"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="w-full rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-black text-red-600 transition hover:bg-red-100"
                        >
                            Delete Menu Item
                        </button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if ($menuItems->hasPages())
        <div class="mt-8 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
            {{ $menuItems->withQueryString()->links() }}
        </div>
    @endif
@endif

@endcomponent
