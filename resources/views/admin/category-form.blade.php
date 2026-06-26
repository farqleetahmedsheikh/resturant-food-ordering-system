@component('layouts.admin', ['title' => $mode === 'create' ? 'Add Category' : 'Edit Category'])
@php
$isCreateMode = $mode === 'create';

    $pageTitle = $isCreateMode
        ? 'Add category'
        : 'Edit category';

    $submitLabel = $isCreateMode
        ? 'Create Category'
        : 'Save Changes';

    $currentImage = $category->image_url ?? null;

    $initialName = old(
        'name',
        $category->name ?? ''
    );

    $initialSlug = old(
        'slug',
        $category->slug ?? ''
    );

    $initialDescription = old(
        'description',
        $category->description ?? ''
    );

    $initialSortOrder = old(
        'sort_order',
        $category->sort_order ?? 0
    );

    $initialActive = (bool) old(
        'is_active',
        $category->is_active ?? true
    );

    $initialRestaurant = old(
        'restaurant_id',
        $category->restaurant_id ?? ''
    );
@endphp

<div
    x-data="{
        preview: @js($currentImage),
        currentImage: @js($currentImage),
        name: @js($initialName),
        slug: @js($initialSlug),
        description: @js($initialDescription),
        sortOrder: @js((string) $initialSortOrder),
        isActive: {{ $initialActive ? 'true' : 'false' }},
        fileName: '',
        submitting: false,

        slugify(value) {
            return (value || '')
                .toString()
                .normalize('NFKD')
                .replace(/[^\w\s-]/g, '')
                .trim()
                .toLowerCase()
                .replace(/[-\s]+/g, '-');
        },

        displaySlug() {
            return this.slug.trim() !== ''
                ? this.slug.trim()
                : this.slugify(this.name);
        },

        handleImage(event) {
            const file = event.target.files?.[0];

            if (!file) {
                return;
            }

            if (
                this.preview
                && typeof this.preview === 'string'
                && this.preview.startsWith('blob:')
            ) {
                URL.revokeObjectURL(this.preview);
            }

            this.preview = URL.createObjectURL(file);
            this.fileName = file.name;
        },

        resetSelectedImage() {
            if (
                this.preview
                && typeof this.preview === 'string'
                && this.preview.startsWith('blob:')
            ) {
                URL.revokeObjectURL(this.preview);
            }

            this.preview = this.currentImage;
            this.fileName = '';

            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = '';
            }
        }
    }"
    class="space-y-5 pb-28 sm:space-y-6 lg:pb-8"
>
    {{-- Mobile Header --}}
    <header class="lg:hidden">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Menu Management
                </p>

                <h1 class="mt-1 text-2xl font-black tracking-tight text-warm-950">
                    {{ $pageTitle }}
                </h1>

                <p class="mt-1 text-sm font-semibold leading-5 text-warm-500">
                    {{ $isCreateMode
                        ? 'Create a new menu category.'
                        : 'Update category information.' }}
                </p>
            </div>

            <a
                href="{{ route('admin.categories.index') }}"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-warm-600 shadow-sm transition active:scale-95"
                aria-label="Back to categories"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.25"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6"
                    />
                </svg>
            </a>
        </div>
    </header>

    {{-- Desktop Header --}}
    <header class="hidden items-end justify-between gap-8 lg:flex">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-500">
                Menu Management
            </p>

            <h1 class="mt-2 text-4xl font-black tracking-tight text-warm-950">
                {{ $pageTitle }}
            </h1>

            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-warm-600">
                {{ $isCreateMode
                    ? 'Create a clearly named category, configure its public visibility, and add an image customers can recognize quickly.'
                    : 'Update the category information, public visibility, display order, and customer-facing image.' }}
            </p>
        </div>

        <a
            href="{{ route('admin.categories.index') }}"
            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-white px-5 py-3 text-sm font-black text-brand-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-brand-50"
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
                    stroke-linejoin="round"
                    d="m15 18-6-6 6-6"
                />
            </svg>

            Back to Categories
        </a>
    </header>

    {{-- Context Banner --}}
    <section class="relative overflow-hidden rounded-[1.5rem] border border-warm-200 bg-gradient-to-r from-brand-50 via-white to-gold-50 p-4 shadow-sm sm:p-5">
        <div class="pointer-events-none absolute -right-12 -top-14 h-40 w-40 rounded-full bg-brand-200/50 blur-3xl"></div>

        <div class="relative flex items-center gap-4">
            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-brand-500 text-white shadow-lg shadow-brand-500/20">
                @if ($isCreateMode)
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
                @else
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="m14 4 6 6L8 22H2v-6L14 4z" />
                        <path d="m12 6 6 6" />
                    </svg>
                @endif
            </span>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-black text-warm-950">
                        {{ $isCreateMode
                            ? 'Creating a new category'
                            : 'Editing an existing category' }}
                    </p>

                    <span
                        x-bind:class="isActive
                            ? 'bg-leaf-50 text-leaf-700'
                            : 'bg-red-50 text-red-700'"
                        class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em]"
                        x-text="isActive ? 'Public' : 'Hidden'"
                    ></span>
                </div>

                <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                    Complete the category details, display settings, and image before saving.
                </p>
            </div>

            <div class="hidden items-center gap-2 md:flex">
                @foreach (['Details', 'Display', 'Image'] as $index => $step)
                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[10px] font-black text-warm-600 shadow-sm">
                        <span class="grid h-5 w-5 place-items-center rounded-full bg-brand-100 text-[9px] text-brand-600">
                            {{ $index + 1 }}
                        </span>

                        {{ $step }}
                    </span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Validation Summary --}}
    @if ($errors->any())
        <section
            role="alert"
            aria-live="polite"
            class="rounded-[1.5rem] border border-red-200 bg-red-50 p-4 shadow-sm sm:p-5"
        >
            <div class="flex items-start gap-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v4M12 17h.01M10.3 4.4 2.6 18a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 4.4a2 2 0 0 0-3.4 0z"
                        />
                    </svg>
                </span>

                <div class="min-w-0">
                    <p class="font-black text-red-900">
                        Some information needs your attention
                    </p>

                    <p class="mt-1 text-sm font-semibold text-red-700">
                        Review the highlighted fields and submit the form again.
                    </p>

                    <div class="mt-3 grid gap-1 sm:grid-cols-2">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-start gap-2 text-xs font-semibold leading-5 text-red-700">
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-red-500"></span>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    <form
        id="category-form"
        action="{{ $isCreateMode
            ? route('admin.categories.store')
            : route('admin.categories.update', $category) }}"
        method="POST"
        enctype="multipart/form-data"
        class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_370px] xl:items-start xl:gap-6"
        x-on:submit="submitting = true"
    >
        @csrf

        @unless ($isCreateMode)
            @method('PUT')
        @endunless

        {{-- Main Form --}}
        <div class="min-w-0 space-y-5">
            {{-- Step 1: Basic Information --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500 text-sm font-black text-white shadow-lg shadow-brand-500/20">
                            1
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Basic Information
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Category details
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Give customers a clear name and helpful description.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-4 sm:p-6">
                    {{-- Restaurant --}}
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <label
                                for="restaurant_id"
                                class="text-sm font-black text-warm-900"
                            >
                                Restaurant
                            </label>

                            <span class="text-[10px] font-bold text-warm-500">
                                Optional
                            </span>
                        </div>

                        <div class="relative mt-2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-warm-500"
                            >
                                <path d="M3 10h18" />
                                <path d="m5 10 1-6h12l1 6" />
                                <path d="M5 10v10h14V10" />
                                <path d="M9 20v-6h6v6" />
                            </svg>

                            <select
                                id="restaurant_id"
                                name="restaurant_id"
                                class="min-h-12 w-full appearance-none rounded-xl border bg-warm-50 py-3 pl-12 pr-11 text-sm font-semibold text-warm-900 outline-none transition focus:bg-white focus:ring-4 focus:ring-brand-100 @error('restaurant_id') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >
                                <option value="">No restaurant</option>

                                @foreach ($restaurants as $restaurant)
                                    <option
                                        value="{{ $restaurant->id }}"
                                        @selected($initialRestaurant == $restaurant->id)
                                    >
                                        {{ $restaurant->name }}
                                    </option>
                                @endforeach
                            </select>

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m6 9 6 6 6-6"
                                />
                            </svg>
                        </div>

                        <p class="mt-2 text-xs font-semibold leading-5 text-warm-500">
                            Associate the category with a restaurant, or leave it unassigned.
                        </p>

                        @error('restaurant_id')
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Name --}}
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-black text-warm-900"
                            >
                                Category Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                name="name"
                                value="{{ $initialName }}"
                                x-model="name"
                                required
                                maxlength="100"
                                autocomplete="off"
                                placeholder="For example: Burgers"
                                class="mt-2 min-h-12 w-full rounded-xl border bg-warm-50 px-4 py-3 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('name') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >

                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold text-warm-500">
                                    Keep the name short and familiar.
                                </p>

                                <span
                                    class="shrink-0 text-[10px] font-bold text-warm-500"
                                    x-text="`${name.length}/100`"
                                ></span>
                            </div>

                            @error('name')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <label
                                    for="slug"
                                    class="text-sm font-black text-warm-900"
                                >
                                    URL Slug
                                </label>

                                <span class="text-[10px] font-bold text-warm-500">
                                    Auto-generated
                                </span>
                            </div>

                            <input
                                id="slug"
                                name="slug"
                                value="{{ $initialSlug }}"
                                x-model="slug"
                                maxlength="120"
                                autocomplete="off"
                                placeholder="burgers"
                                class="mt-2 min-h-12 w-full rounded-xl border bg-warm-50 px-4 py-3 font-mono text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('slug') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >

                            <div class="mt-2 flex min-w-0 items-center gap-2 rounded-lg bg-warm-50 px-3 py-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-3.5 w-3.5 shrink-0 text-warm-500"
                                >
                                    <path d="M10 13a5 5 0 0 0 7.1.1l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1" />
                                    <path d="M14 11a5 5 0 0 0-7.1-.1l-2 2A5 5 0 0 0 12 20l1.1-1.1" />
                                </svg>

                                <span class="text-[10px] font-bold text-warm-500">
                                    /menu/
                                </span>

                                <span
                                    class="min-w-0 truncate font-mono text-[10px] font-bold text-warm-600"
                                    x-text="displaySlug() || 'category-name'"
                                ></span>
                            </div>

                            @error('slug')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <div class="flex items-center justify-between gap-3">
                            <label
                                for="description"
                                class="text-sm font-black text-warm-900"
                            >
                                Description
                            </label>

                            <span class="text-[10px] font-bold text-warm-500">
                                Optional
                            </span>
                        </div>

                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            maxlength="500"
                            x-model="description"
                            placeholder="Describe the food items customers will find in this category."
                            class="mt-2 w-full resize-y rounded-xl border bg-warm-50 px-4 py-3 text-sm font-semibold leading-6 text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('description') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                        >{{ $initialDescription }}</textarea>

                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-warm-500">
                                Explain the type of meals available in this category.
                            </p>

                            <span
                                class="shrink-0 text-[10px] font-bold text-warm-500"
                                x-text="`${description.length}/500`"
                            ></span>
                        </div>

                        @error('description')
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Step 2: Display Settings --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            2
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Display Settings
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Visibility and ordering
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Decide where the category appears and whether customers can see it.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 p-4 sm:p-6 md:grid-cols-2">
                    {{-- Sort Order --}}
                    <div class="rounded-2xl border border-warm-100 bg-warm-50 p-4">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-brand-500 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M4 6h16M4 12h12M4 18h8" />
                                </svg>
                            </span>

                            <div class="min-w-0 flex-1">
                                <label
                                    for="sort_order"
                                    class="block text-sm font-black text-warm-900"
                                >
                                    Display Position
                                    <span class="text-red-500">*</span>
                                </label>

                                <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                    Lower numbers appear first.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2">
                            <button
                                type="button"
                                x-on:click="sortOrder = Math.max(0, Number(sortOrder || 0) - 1)"
                                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-warm-200 bg-white text-warm-600 transition active:scale-95 hover:border-brand-200 hover:text-brand-600"
                                aria-label="Decrease sort order"
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
                                        d="M5 12h14"
                                    />
                                </svg>
                            </button>

                            <input
                                id="sort_order"
                                name="sort_order"
                                type="number"
                                min="0"
                                value="{{ $initialSortOrder }}"
                                x-model="sortOrder"
                                required
                                class="min-h-12 min-w-0 flex-1 rounded-xl border bg-white px-4 py-3 text-center text-base font-black text-warm-900 outline-none transition focus:ring-4 focus:ring-brand-100 @error('sort_order') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >

                            <button
                                type="button"
                                x-on:click="sortOrder = Number(sortOrder || 0) + 1"
                                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-warm-200 bg-white text-warm-600 transition active:scale-95 hover:border-brand-200 hover:text-brand-600"
                                aria-label="Increase sort order"
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
                            </button>
                        </div>

                        @error('sort_order')
                            <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Active Toggle --}}
                    <label
                        class="group cursor-pointer rounded-2xl border p-4 transition"
                        x-bind:class="isActive
                            ? 'border-leaf-100 bg-leaf-50'
                            : 'border-warm-200 bg-warm-50'"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <span
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                                    x-bind:class="isActive
                                        ? 'text-leaf-700'
                                        : 'text-warm-500'"
                                >
                                    <svg
                                        x-show="isActive"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" />
                                        <circle cx="12" cy="12" r="2.5" />
                                    </svg>

                                    <svg
                                        x-show="! isActive"
                                        x-cloak
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            d="m3 3 18 18"
                                        />
                                        <path d="M10.6 6.2A10 10 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-2.2 2.8" />
                                        <path d="M6.6 6.6C3.5 8.4 2 12 2 12s3.5 6 10 6a10 10 0 0 0 3.4-.6" />
                                    </svg>
                                </span>

                                <span>
                                    <span
                                        class="block text-sm font-black"
                                        x-bind:class="isActive
                                            ? 'text-leaf-900'
                                            : 'text-warm-900'"
                                        x-text="isActive
                                            ? 'Visible on public menu'
                                            : 'Hidden from customers'"
                                    ></span>

                                    <span
                                        class="mt-1 block text-xs font-semibold leading-5"
                                        x-bind:class="isActive
                                            ? 'text-leaf-700'
                                            : 'text-warm-500'"
                                        x-text="isActive
                                            ? 'Customers can browse this category.'
                                            : 'The category remains saved but is not public.'"
                                    ></span>
                                </span>
                            </div>

                            <span class="relative mt-1 shrink-0">
                                <input
                                    type="hidden"
                                    name="is_active"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    x-model="isActive"
                                    @checked($initialActive)
                                    class="peer sr-only"
                                >

                                <span class="block h-7 w-12 rounded-full bg-warm-300 transition peer-checked:bg-leaf-700 peer-focus:ring-4 peer-focus:ring-leaf-100"></span>

                                <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                        </div>
                    </label>
                </div>
            </section>

            {{-- Step 3: Category Image --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            3
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Category Image
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Customer-facing cover
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Choose a clear image that represents this category.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <label
                        for="image"
                        class="group relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-[1.5rem] border-2 border-dashed border-brand-200 bg-brand-50/60 px-5 py-9 text-center transition hover:border-brand-500 hover:bg-brand-50 sm:py-10"
                    >
                        <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-brand-200/40 blur-3xl"></div>

                        <span class="relative grid h-14 w-14 place-items-center rounded-2xl bg-white text-brand-500 shadow-sm transition group-hover:-translate-y-0.5 group-hover:shadow-md">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-7 w-7"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 16V4M7 9l5-5 5 5M5 20h14"
                                />
                            </svg>
                        </span>

                        <p class="relative mt-4 text-sm font-black text-warm-950">
                            <span x-show="! fileName">
                                Choose category image
                            </span>

                            <span
                                x-show="fileName"
                                x-cloak
                            >
                                Replace selected image
                            </span>
                        </p>

                        <p class="relative mt-1 text-xs font-semibold text-warm-500">
                            JPG, PNG or WEBP · Maximum 2 MB
                        </p>

                        <input
                            id="image"
                            name="image"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            x-ref="imageInput"
                            x-on:change="handleImage($event)"
                            class="sr-only"
                        >
                    </label>

                    <div
                        x-show="fileName"
                        x-cloak
                        class="mt-3 flex items-center justify-between gap-3 rounded-xl border border-leaf-100 bg-leaf-50 px-4 py-3"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-leaf-700 shadow-sm">
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
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-xs font-black text-leaf-900">
                                    New image selected
                                </p>

                                <p
                                    class="mt-0.5 truncate text-xs font-semibold text-leaf-700"
                                    x-text="fileName"
                                ></p>
                            </div>
                        </div>

                        <button
                            type="button"
                            x-on:click="resetSelectedImage"
                            class="shrink-0 rounded-lg px-2.5 py-2 text-xs font-black text-red-600 transition hover:bg-red-50"
                        >
                            Undo
                        </button>
                    </div>

                    @error('image')
                        <p class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </section>
        </div>

        {{-- Preview and Desktop Actions --}}
        <aside class="order-first space-y-5 xl:order-none xl:sticky xl:top-24">
            {{-- Live Preview --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-xl shadow-brand-900/5">
                <div class="flex items-center justify-between gap-4 border-b border-warm-200 px-5 py-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                            Live Preview
                        </p>

                        <h2 class="mt-1 text-lg font-black text-warm-950">
                            Public category card
                        </h2>
                    </div>

                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em]"
                        x-bind:class="isActive
                            ? 'bg-leaf-50 text-leaf-700'
                            : 'bg-red-50 text-red-700'"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            x-bind:class="isActive
                                ? 'bg-leaf-500'
                                : 'bg-red-500'"
                        ></span>

                        <span x-text="isActive ? 'Active' : 'Inactive'"></span>
                    </span>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white shadow-sm">
                        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream">
                            <template x-if="preview">
                                <img
                                    x-bind:src="preview"
                                    alt="Category preview"
                                    class="absolute inset-0 h-full w-full object-cover"
                                >
                            </template>

                            <div
                                x-show="! preview"
                                class="absolute inset-0 grid place-items-center"
                            >
                                <span
                                    class="grid h-20 w-20 place-items-center rounded-full border border-white/70 bg-white/80 text-3xl font-black text-brand-600 shadow-xl backdrop-blur"
                                    x-text="(name || 'C').charAt(0).toUpperCase()"
                                ></span>
                            </div>

                            <div class="absolute inset-0 bg-gradient-to-t from-warm-950/75 via-transparent to-transparent"></div>

                            <div class="absolute left-3 top-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full border border-white/40 bg-white/90 px-3 py-1.5 text-[9px] font-black shadow-sm backdrop-blur"
                                    x-bind:class="isActive
                                        ? 'text-leaf-700'
                                        : 'text-red-700'"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        x-bind:class="isActive
                                            ? 'bg-leaf-500'
                                            : 'bg-red-500'"
                                    ></span>

                                    <span x-text="isActive ? 'Available' : 'Hidden'"></span>
                                </span>
                            </div>

                            <div class="absolute bottom-0 inset-x-0 p-4">
                                <p
                                    class="break-words text-xl font-black text-white"
                                    x-text="name.trim() || 'Category Name'"
                                ></p>

                                <p class="mt-1 text-[10px] font-bold text-white/70">
                                    Arcade Kebab House Menu Category
                                </p>
                            </div>
                        </div>

                        <div class="p-4">
                            <p
                                class="line-clamp-2 min-h-10 text-xs font-semibold leading-5 text-warm-600"
                                x-text="description.trim() || 'Your category description will appear here for customers.'"
                            ></p>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="min-w-0 rounded-xl bg-warm-50 px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                        Slug
                                    </p>

                                    <p
                                        class="mt-1 truncate font-mono text-[10px] font-bold text-warm-600"
                                        x-text="displaySlug() || 'category-name'"
                                    ></p>
                                </div>

                                <div class="rounded-xl bg-brand-50 px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-brand-500">
                                        Position
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-black text-brand-900"
                                        x-text="`#${sortOrder || 0}`"
                                    ></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-3 text-center text-[10px] font-semibold leading-4 text-warm-500">
                        Preview represents how the category may appear on the customer menu.
                    </p>
                </div>
            </section>

            {{-- Desktop Publish Actions --}}
            <section class="hidden rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm xl:block">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 5h12l2 2v12H5zM8 5v5h8V5M8 19v-6h8v6"
                            />
                        </svg>
                    </span>

                    <div>
                        <p class="text-sm font-black text-warm-950">
                            Ready to save?
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                            Review the public preview and category settings before continuing.
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    x-bind:disabled="submitting"
                    class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <svg
                        x-show="submitting"
                        x-cloak
                        class="h-5 w-5 animate-spin"
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

                    <svg
                        x-show="! submitting"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 5h12l2 2v12H5zM8 5v5h8V5M8 19v-6h8v6"
                        />
                    </svg>

                    <span
                        x-text="submitting
                            ? 'Saving category...'
                            : @js($submitLabel)"
                    ></span>
                </button>

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-warm-200 bg-white px-5 py-3 text-sm font-black text-warm-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600"
                >
                    Cancel
                </a>
            </section>
        </aside>
    </form>

    {{-- Persistent Mobile Actions --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur xl:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.categories.index') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-95"
                aria-label="Cancel and return to categories"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m15 18-6-6 6-6"
                    />
                </svg>
            </a>

            <button
                type="submit"
                form="category-form"
                x-bind:disabled="submitting"
                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg
                    x-show="submitting"
                    x-cloak
                    class="h-5 w-5 animate-spin"
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

                <svg
                    x-show="! submitting"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 5h12l2 2v12H5zM8 5v5h8V5M8 19v-6h8v6"
                    />
                </svg>

                <span
                    x-text="submitting
                        ? 'Saving...'
                        : @js($submitLabel)"
                ></span>
            </button>
        </div>
    </div>
</div>

@endcomponent
