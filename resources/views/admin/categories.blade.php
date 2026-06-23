@component('layouts.admin', ['title' => 'Categories'])
@php
$categoryCount = method_exists($categories, 'total')
? $categories->total()
: $categories->count();
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Menu Management
        </p>

        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Categories
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            Organize menu items, control category visibility, upload images, and manage public display order.
        </p>
    </div>

    <a
        href="{{ route('admin.categories.create') }}"
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

        Add Category
    </a>
</div>

{{-- Summary Banner --}}
<section class="relative mb-7 overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-600 via-orange-500 to-red-600 p-6 text-white shadow-2xl shadow-orange-900/20 sm:p-8">
    <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>
    <div class="absolute -bottom-24 left-10 h-60 w-60 rounded-full bg-yellow-200/20 blur-3xl"></div>

    <div class="relative flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-100">
                Menu Organization
            </p>

            <h2 class="mt-3 text-3xl font-black tracking-tight">
                {{ $categoryCount }}
                {{ $categoryCount === 1 ? 'category' : 'categories' }}
            </h2>

            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-orange-50">
                Categories help customers find meals faster and keep your restaurant menu properly organized.
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
                <rect x="3" y="3" width="7" height="7" rx="1" />
                <rect x="14" y="3" width="7" height="7" rx="1" />
                <rect x="3" y="14" width="7" height="7" rx="1" />
                <rect x="14" y="14" width="7" height="7" rx="1" />
            </svg>
        </div>
    </div>
</section>

@if ($categories->isEmpty())
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
                <rect x="3" y="3" width="7" height="7" rx="1" />
                <rect x="14" y="3" width="7" height="7" rx="1" />
                <rect x="3" y="14" width="7" height="7" rx="1" />
                <rect x="14" y="14" width="7" height="7" rx="1" />
            </svg>
        </div>

        <h2 class="mt-6 text-2xl font-black tracking-tight text-slate-950">
            No categories found
        </h2>

        <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-600">
            Create your first category to start organizing menu items for the public restaurant menu.
        </p>

        <a
            href="{{ route('admin.categories.create') }}"
            class="mt-7 inline-flex items-center justify-center rounded-2xl bg-orange-600 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700"
        >
            Create First Category
        </a>
    </section>
@else
    {{-- Desktop Table --}}
    <section class="hidden overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm lg:block">
        <div class="border-b border-orange-100 px-6 py-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-950">
                        Category Directory
                    </h2>

                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        Manage category details and public availability.
                    </p>
                </div>

                <span class="rounded-full bg-orange-50 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-orange-700">
                    {{ $categoryCount }} Total
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/80">
                    <tr class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                        <th class="px-6 py-4">Category</th>
                        <th class="px-5 py-4">Slug</th>
                        <th class="px-5 py-4 text-center">Sort</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-center">Items</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach ($categories as $category)
                        <tr class="group transition hover:bg-orange-50/40">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-orange-100 via-amber-50 to-red-100 shadow-sm">
                                        @if ($category->image_url)
                                            <img
                                                src="{{ $category->image_url }}"
                                                alt="{{ $category->name }}"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            >
                                        @else
                                            <span class="text-xl font-black text-orange-700">
                                                {{ mb_substr($category->name, 0, 1) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-base font-black text-slate-950">
                                            {{ $category->name }}
                                        </p>

                                        <p class="mt-1 max-w-md text-sm leading-6 text-slate-500">
                                            {{ \Illuminate\Support\Str::limit($category->description ?: 'No description added.', 80) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-5">
                                <span class="inline-flex rounded-xl bg-slate-100 px-3 py-2 font-mono text-xs font-bold text-slate-600">
                                    {{ $category->slug }}
                                </span>
                            </td>

                            <td class="px-5 py-5 text-center">
                                <span class="inline-grid h-10 min-w-10 place-items-center rounded-xl bg-orange-50 px-3 font-black text-orange-700">
                                    {{ $category->sort_order }}
                                </span>
                            </td>

                            <td class="px-5 py-5">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-black {{ $category->is_active ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-red-100 bg-red-50 text-red-700' }}">
                                    <span class="h-2 w-2 rounded-full {{ $category->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>

                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-5 py-5 text-center">
                                <span class="inline-flex min-w-12 items-center justify-center rounded-xl bg-slate-100 px-3 py-2 font-black text-slate-700">
                                    {{ $category->menu_items_count }}
                                </span>
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.categories.edit', $category) }}"
                                        class="inline-flex items-center justify-center rounded-xl border border-orange-200 bg-white px-4 py-2.5 text-xs font-black text-orange-700 transition hover:border-orange-600 hover:bg-orange-600 hover:text-white"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.categories.destroy', $category) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this category?');"
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
    <div class="grid gap-5 sm:grid-cols-2 lg:hidden">
        @foreach ($categories as $category)
            <article class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="relative aspect-[16/9] overflow-hidden bg-gradient-to-br from-orange-100 via-amber-50 to-red-100">
                    @if ($category->image_url)
                        <img
                            src="{{ $category->image_url }}"
                            alt="{{ $category->name }}"
                            class="h-full w-full object-cover"
                        >
                    @else
                        <div class="grid h-full place-items-center">
                            <div class="grid h-20 w-20 place-items-center rounded-full bg-white/80 text-4xl font-black text-orange-700 shadow-xl">
                                {{ mb_substr($category->name, 0, 1) }}
                            </div>
                        </div>
                    @endif

                    <div class="absolute left-4 top-4">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/50 bg-white/90 px-3 py-1.5 text-xs font-black shadow-sm backdrop-blur {{ $category->is_active ? 'text-emerald-700' : 'text-red-700' }}">
                            <span class="h-2 w-2 rounded-full {{ $category->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>

                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="absolute right-4 top-4 rounded-full bg-slate-950/75 px-3 py-1.5 text-xs font-black text-white backdrop-blur">
                        {{ $category->menu_items_count }}
                        {{ $category->menu_items_count === 1 ? 'item' : 'items' }}
                    </div>
                </div>

                <div class="p-5">
                    <h2 class="text-xl font-black tracking-tight text-slate-950">
                        {{ $category->name }}
                    </h2>

                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
                        {{ $category->description ?: 'No description has been added for this category.' }}
                    </p>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Slug
                            </p>

                            <p class="mt-1 truncate font-mono text-xs font-bold text-slate-700">
                                {{ $category->slug }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Sort Order
                            </p>

                            <p class="mt-1 text-base font-black text-slate-950">
                                {{ $category->sort_order }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <a
                            href="{{ route('admin.categories.edit', $category) }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-orange-200 bg-white px-4 py-3 text-sm font-black text-orange-700 transition hover:bg-orange-50"
                        >
                            Edit
                        </a>

                        <form
                            action="{{ route('admin.categories.destroy', $category) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this category?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="w-full rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-black text-red-600 transition hover:bg-red-100"
                            >
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if ($categories->hasPages())
        <div class="mt-8 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
            {{ $categories->links() }}
        </div>
    @endif
@endif

@endcomponent
