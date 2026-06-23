@component('layouts.admin', ['title' => $mode === 'create' ? 'Add Category' : 'Edit Category'])
@php
$isCreateMode = $mode === 'create';
$pageTitle = $isCreateMode ? 'Add category' : 'Edit category';
$submitLabel = $isCreateMode ? 'Create Category' : 'Save Changes';
$currentImage = $category->image_url ?? null;
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Menu Management
        </p>

        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            {{ $pageTitle }}
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            {{ $isCreateMode
                ? 'Create a category to organize menu items and improve customer navigation.'
                : 'Update category information, visibility, image, and public display order.' }}
        </p>
    </div>

    <a
        href="{{ route('admin.categories.index') }}"
        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50 hover:text-orange-700"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            class="h-4 w-4"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
        </svg>

        Back to Categories
    </a>
</div>

{{-- Validation Summary --}}
@if ($errors->any())
    <div class="mb-7 rounded-[1.5rem] border border-red-200 bg-red-50 p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4M12 17h.01M10.3 4.4 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0z" />
                </svg>
            </div>

            <div>
                <p class="font-black text-red-800">
                    Please check the form
                </p>

                <ul class="mt-2 list-inside list-disc space-y-1 text-sm font-semibold text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<form
    action="{{ $isCreateMode
        ? route('admin.categories.store')
        : route('admin.categories.update', $category) }}"
    method="POST"
    enctype="multipart/form-data"
    class="grid gap-7 xl:grid-cols-[minmax(0,1fr)_360px]"
    x-data="{ preview: @js($currentImage) }"
>
    @csrf

    @unless ($isCreateMode)
        @method('PUT')
    @endunless

    {{-- Main Form --}}
    <div class="space-y-7">
        {{-- Basic Information --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Basic Information
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Category details
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Add a clear category name and description that customers will understand.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                {{-- Restaurant --}}
                <div class="sm:col-span-2">
                    <label for="restaurant_id" class="block text-sm font-black text-slate-800">
                        Restaurant
                    </label>

                    <select
                        id="restaurant_id"
                        name="restaurant_id"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                        <option value="">No restaurant</option>

                        @foreach ($restaurants as $restaurant)
                            <option
                                value="{{ $restaurant->id }}"
                                @selected(old('restaurant_id', $category->restaurant_id ?? '') == $restaurant->id)
                            >
                                {{ $restaurant->name }}
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                        Select the restaurant this category belongs to.
                    </p>

                    @error('restaurant_id')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-black text-slate-800">
                        Category Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="name"
                        name="name"
                        value="{{ old('name', $category->name ?? '') }}"
                        required
                        placeholder="For example: Burgers"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    @error('name')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Slug --}}
                <div>
                    <label for="slug" class="block text-sm font-black text-slate-800">
                        URL Slug
                    </label>

                    <input
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $category->slug ?? '') }}"
                        placeholder="burgers"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm font-semibold text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                        Leave empty to generate it automatically from the name.
                    </p>

                    @error('slug')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-black text-slate-800">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Describe the food items available in this category."
                        class="mt-2 w-full resize-y rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold leading-7 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >{{ old('description', $category->description ?? '') }}</textarea>

                    @error('description')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Display Settings --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <circle cx="12" cy="12" r="3" />
                        <path d="M12 2v3M12 19v3M4.9 4.9 7 7M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1 7 17M17 7l2.1-2.1" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Display Settings
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Visibility and ordering
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Control where the category appears and whether customers can see it.
                    </p>
                </div>
            </div>

            <div class="mt-7 grid gap-5 md:grid-cols-2">
                {{-- Sort Order --}}
                <div>
                    <label for="sort_order" class="block text-sm font-black text-slate-800">
                        Sort Order
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        min="0"
                        value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                        required
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >

                    <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                        Categories with lower numbers appear first.
                    </p>

                    @error('sort_order')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Active Toggle --}}
                <div>
                    <p class="block text-sm font-black text-slate-800">
                        Public Status
                    </p>

                    <div class="mt-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <label class="flex cursor-pointer items-center justify-between gap-4">
                            <span>
                                <span class="block text-sm font-black text-slate-950">
                                    Active Category
                                </span>

                                <span class="mt-1 block text-xs font-semibold leading-5 text-slate-500">
                                    Show this category on the public menu.
                                </span>
                            </span>

                            <span class="relative shrink-0">
                                <input type="hidden" name="is_active" value="0">

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    @checked(old('is_active', $category->is_active ?? true))
                                    class="peer sr-only"
                                >

                                <span class="block h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-orange-600 peer-focus:ring-4 peer-focus:ring-orange-100"></span>

                                <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </section>

        {{-- Category Image --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <rect x="3" y="4" width="18" height="16" rx="2" />
                        <circle cx="8.5" cy="9" r="1.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5L5 20" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Category Image
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Upload a cover image
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Use a clear food image that represents the category on the public menu.
                    </p>
                </div>
            </div>

            <div class="mt-7">
                <label
                    for="image"
                    class="group flex cursor-pointer flex-col items-center justify-center rounded-[1.5rem] border-2 border-dashed border-orange-200 bg-orange-50/60 px-5 py-10 text-center transition hover:border-orange-400 hover:bg-orange-50"
                >
                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-orange-600 shadow-sm transition group-hover:scale-105">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-7 w-7"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                        </svg>
                    </div>

                    <p class="mt-4 text-sm font-black text-slate-950">
                        Click to select an image
                    </p>

                    <p class="mt-2 text-xs font-semibold text-slate-500">
                        JPG, PNG or WEBP — maximum 2MB
                    </p>

                    <input
                        id="image"
                        name="image"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="sr-only"
                        x-on:change="
                            const file = $event.target.files[0];

                            if (file) {
                                preview = URL.createObjectURL(file);
                            }
                        "
                    >
                </label>

                @error('image')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </section>
    </div>

    {{-- Preview and Actions --}}
    <aside class="h-fit space-y-5 xl:sticky xl:top-28">
        <section class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-xl shadow-orange-900/5">
            <div class="border-b border-orange-100 px-6 py-5">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                    Live Preview
                </p>

                <h2 class="mt-2 text-xl font-black text-slate-950">
                    Category card
                </h2>
            </div>

            <div class="p-5">
                <div class="relative aspect-[4/3] overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-orange-100 via-amber-50 to-red-100">
                    <template x-if="preview">
                        <img
                            x-bind:src="preview"
                            alt="Category preview"
                            class="h-full w-full object-cover"
                        >
                    </template>

                    <div
                        x-show="! preview"
                        class="absolute inset-0 grid place-items-center"
                    >
                        <div class="grid h-24 w-24 place-items-center rounded-full bg-white/80 text-5xl font-black text-orange-700 shadow-xl backdrop-blur">
                            {{ mb_substr(old('name', $category->name ?? 'C') ?: 'C', 0, 1) }}
                        </div>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 to-transparent px-5 pb-5 pt-14">
                        <p class="break-words text-xl font-black text-white">
                            {{ old('name', $category->name ?? '') ?: 'Category Name' }}
                        </p>

                        <p class="mt-1 text-xs font-bold text-white/75">
                            FreshBite Menu Category
                        </p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                            Mode
                        </p>

                        <p class="mt-1 text-sm font-black text-slate-950">
                            {{ $isCreateMode ? 'Creating' : 'Editing' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                            Sort
                        </p>

                        <p class="mt-1 text-sm font-black text-slate-950">
                            {{ old('sort_order', $category->sort_order ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Save Actions --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 hover:shadow-xl"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5h12l2 2v12H5zM8 5v5h8V5M8 19v-6h8v6" />
                </svg>

                {{ $submitLabel }}
            </button>

            <a
                href="{{ route('admin.categories.index') }}"
                class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
            >
                Cancel
            </a>

            <p class="mt-4 text-center text-xs font-semibold leading-5 text-slate-500">
                Review the information and preview before saving.
            </p>
        </section>
    </aside>
</form>

@endcomponent
