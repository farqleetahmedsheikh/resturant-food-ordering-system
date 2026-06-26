@component('layouts.admin', ['title' => $mode === 'create' ? 'Add Menu Item' : 'Edit Menu Item'])
@php
$isCreateMode = $mode === 'create';

    $pageTitle = $isCreateMode
        ? 'Add menu item'
        : 'Edit menu item';

    $submitLabel = $isCreateMode
        ? 'Create Menu Item'
        : 'Save Changes';

    $currentImage = $menuItem->image_url ?? null;

    $selectedCategoryId = old(
        'category_id',
        $menuItem->category_id ?? ''
    );

    $selectedCategory = $categories->firstWhere(
        'id',
        $selectedCategoryId
    );

    $rawSizeRows = old('sizes');

    if ($rawSizeRows === null) {
        $rawSizeRows = $menuItem->sizes
            ?->map(fn ($size) => [
                'name' => $size->name,
                'price' => (string) $size->price,
                'sort_order' => $size->sort_order,
                'is_active' => (bool) $size->is_active,
            ])
            ->values()
            ->all() ?? [];
    }

    $sizeRows = collect($rawSizeRows)
        ->map(fn ($size) => [
            'name' => $size['name'] ?? '',
            'price' => (string) ($size['price'] ?? ''),
            'sort_order' => (int) ($size['sort_order'] ?? 0),
            'is_active' => filter_var(
                $size['is_active'] ?? true,
                FILTER_VALIDATE_BOOL
            ),
        ])
        ->values()
        ->all();

    $rawAddonRows = old('addons');

    if ($rawAddonRows === null) {
        $rawAddonRows = $menuItem->addons
            ?->map(fn ($addon) => [
                'name' => $addon->name,
                'type' => $addon->type,
                'price' => (string) $addon->price,
                'sort_order' => $addon->sort_order,
                'is_active' => (bool) $addon->is_active,
            ])
            ->values()
            ->all() ?? [];
    }

    $addonRows = collect($rawAddonRows)
        ->map(fn ($addon) => [
            'name' => $addon['name'] ?? '',
            'type' => $addon['type'] ?? 'topping',
            'price' => (string) ($addon['price'] ?? ''),
            'sort_order' => (int) ($addon['sort_order'] ?? 0),
            'is_active' => filter_var(
                $addon['is_active'] ?? true,
                FILTER_VALIDATE_BOOL
            ),
        ])
        ->values()
        ->all();

    $addonTypes = \App\Models\MenuItemAddon::TYPES;

    $initialName = old(
        'name',
        $menuItem->name ?? ''
    );

    $initialSlug = old(
        'slug',
        $menuItem->slug ?? ''
    );

    $initialDescription = old(
        'description',
        $menuItem->description ?? ''
    );

    $initialPrice = old(
        'price',
        $menuItem->price ?? ''
    );

    $initialCompareAtPrice = old(
        'compare_at_price',
        $menuItem->compare_at_price ?? ''
    );

    $initialPreparationTime = old(
        'preparation_time',
        $menuItem->preparation_time ?? ''
    );

    $initialCalories = old(
        'calories',
        $menuItem->calories ?? ''
    );

    $initialSortOrder = old(
        'sort_order',
        $menuItem->sort_order ?? 0
    );

    $initialAvailable = (bool) old(
        'is_available',
        $menuItem->is_available ?? true
    );

    $initialFeatured = (bool) old(
        'is_featured',
        $menuItem->is_featured ?? false
    );
@endphp

<div
    x-data="{
        preview: @js($currentImage),
        originalImage: @js($currentImage),
        fileName: '',
        itemName: @js($initialName),
        slug: @js($initialSlug),
        itemDescription: @js($initialDescription),
        price: @js((string) $initialPrice),
        compareAtPrice: @js((string) $initialCompareAtPrice),
        preparationTime: @js((string) $initialPreparationTime),
        calories: @js((string) $initialCalories),
        sortOrder: @js((string) $initialSortOrder),
        categoryName: @js($selectedCategory?->name ?? 'Uncategorized'),
        available: {{ $initialAvailable ? 'true' : 'false' }},
        featured: {{ $initialFeatured ? 'true' : 'false' }},
        sizes: @js($sizeRows),
        addons: @js($addonRows),
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
                : this.slugify(this.itemName);
        },

        discountPercent() {
            const sellingPrice = Number(this.price || 0);
            const originalPrice = Number(this.compareAtPrice || 0);

            if (
                sellingPrice <= 0
                || originalPrice <= sellingPrice
            ) {
                return 0;
            }

            return Math.round(
                ((originalPrice - sellingPrice) / originalPrice) * 100
            );
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

            this.preview = this.originalImage;
            this.fileName = '';

            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = '';
            }
        },

        addSize() {
            this.sizes.push({
                name: '',
                price: '',
                sort_order: this.sizes.length,
                is_active: true
            });
        },

        removeSize(index) {
            this.sizes.splice(index, 1);
        },

        addAddon() {
            this.addons.push({
                name: '',
                type: 'topping',
                price: '',
                sort_order: this.addons.length,
                is_active: true
            });
        },

        removeAddon(index) {
            this.addons.splice(index, 1);
        }
    }"
    class="space-y-5 pb-28 sm:space-y-6 xl:pb-8"
>
    {{-- Mobile Header --}}
    <header class="xl:hidden">
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
                        ? 'Create a customer-ready menu item.'
                        : 'Update menu item information.' }}
                </p>
            </div>

            <a
                href="{{ route('admin.menu-items.index') }}"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-warm-600 shadow-sm transition active:scale-95"
                aria-label="Back to menu"
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
    <header class="hidden items-end justify-between gap-8 xl:flex">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-500">
                Menu Management
            </p>

            <h1 class="mt-2 text-4xl font-black tracking-tight text-warm-950">
                {{ $pageTitle }}
            </h1>

            <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-warm-600">
                {{ $isCreateMode
                    ? 'Create a complete food listing with pricing, availability, variants, paid extras, and a customer-facing preview.'
                    : 'Update the menu item, pricing, ordering options, public visibility, and customer-facing image.' }}
            </p>
        </div>

        <a
            href="{{ route('admin.menu-items.index') }}"
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

            Back to Menu
        </a>
    </header>

    {{-- Workflow Overview --}}
    <section class="relative overflow-hidden rounded-[1.5rem] border border-warm-200 bg-gradient-to-r from-brand-50 via-white to-gold-50 p-4 shadow-sm sm:p-5">
        <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-brand-200/50 blur-3xl"></div>

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
                            ? 'Creating a new menu item'
                            : 'Editing menu item' }}
                    </p>

                    <span
                        class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em]"
                        x-bind:class="available
                            ? 'bg-leaf-50 text-leaf-700'
                            : 'bg-red-50 text-red-700'"
                        x-text="available ? 'Available' : 'Unavailable'"
                    ></span>

                    <span
                        x-show="featured"
                        x-cloak
                        class="rounded-full bg-brand-100 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] text-brand-600"
                    >
                        Featured
                    </span>
                </div>

                <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                    Complete the item information, selling setup, ordering options, and image before saving.
                </p>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                @foreach (['Details', 'Selling', 'Options', 'Image'] as $index => $step)
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
        id="menu-item-form"
        action="{{ $isCreateMode
            ? route('admin.menu-items.store')
            : route('admin.menu-items.update', $menuItem) }}"
        method="POST"
        enctype="multipart/form-data"
        class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_390px] xl:items-start xl:gap-6"
        x-on:submit="submitting = true"
    >
        @csrf

        @unless ($isCreateMode)
            @method('PUT')
        @endunless

        {{-- Main Form --}}
        <div class="min-w-0 space-y-5">
            {{-- Step 1: Essentials --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500 text-sm font-black text-white shadow-lg shadow-brand-500/20">
                            1
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Item Information
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Customer-facing details
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Add a recognizable name, category, and useful description.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-4 sm:p-6">
                    <div class="grid gap-5 md:grid-cols-2">
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
                                            @selected(old('restaurant_id', $menuItem->restaurant_id ?? '') == $restaurant->id)
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

                            @error('restaurant_id')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <label
                                    for="category_id"
                                    class="text-sm font-black text-warm-900"
                                >
                                    Category
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
                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                </svg>

                                <select
                                    id="category_id"
                                    name="category_id"
                                    x-on:change="categoryName = $event.target.options[$event.target.selectedIndex].text"
                                    class="min-h-12 w-full appearance-none rounded-xl border bg-warm-50 py-3 pl-12 pr-11 text-sm font-semibold text-warm-900 outline-none transition focus:bg-white focus:ring-4 focus:ring-brand-100 @error('category_id') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                                >
                                    <option value="">Uncategorized</option>

                                    @foreach ($categories as $category)
                                        <option
                                            value="{{ $category->id }}"
                                            @selected($selectedCategoryId == $category->id)
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
                                    class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-warm-500"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m6 9 6 6 6-6"
                                    />
                                </svg>
                            </div>

                            @error('category_id')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Name --}}
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-black text-warm-900"
                            >
                                Item Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                name="name"
                                value="{{ $initialName }}"
                                x-model="itemName"
                                required
                                maxlength="150"
                                autocomplete="off"
                                placeholder="For example: Classic Beef Burger"
                                class="mt-2 min-h-12 w-full rounded-xl border bg-warm-50 px-4 py-3 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('name') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >

                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold text-warm-500">
                                    Use a short and recognizable product name.
                                </p>

                                <span
                                    class="shrink-0 text-[10px] font-bold text-warm-500"
                                    x-text="`${itemName.length}/150`"
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
                                maxlength="180"
                                autocomplete="off"
                                placeholder="classic-beef-burger"
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
                                    x-text="displaySlug() || 'item-name'"
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
                            maxlength="1000"
                            x-model="itemDescription"
                            placeholder="Describe the ingredients, taste, portion size, and anything customers should know."
                            class="mt-2 w-full resize-y rounded-xl border bg-warm-50 px-4 py-3 text-sm font-semibold leading-6 text-warm-900 outline-none transition placeholder:text-warm-500 focus:bg-white focus:ring-4 focus:ring-brand-100 @error('description') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                        >{{ $initialDescription }}</textarea>

                        <div class="mt-2 flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-warm-500">
                                Mention ingredients, flavour, serving size, or dietary information.
                            </p>

                            <span
                                class="shrink-0 text-[10px] font-bold text-warm-500"
                                x-text="`${itemDescription.length}/1000`"
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

            {{-- Step 2: Selling Setup --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            2
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Selling Setup
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Pricing, metadata and visibility
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Configure the base price, discount, preparation details, and public status.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 p-4 sm:p-6">
                    {{-- Price Cards --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        {{-- Selling Price --}}
                        <div class="rounded-2xl border border-warm-200 bg-brand-50 p-4">
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
                                        <rect x="3" y="6" width="18" height="12" rx="2" />
                                        <circle cx="12" cy="12" r="2" />
                                    </svg>
                                </span>

                                <div>
                                    <label
                                        for="price"
                                        class="block text-sm font-black text-brand-900"
                                    >
                                        Selling Price
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <p class="mt-1 text-xs font-semibold text-brand-600">
                                        Main price shown to customers.
                                    </p>
                                </div>
                            </div>

                            <div class="relative mt-4">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-brand-600">
	                                    A$
                                </span>

                                <input
                                    id="price"
                                    name="price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value="{{ $initialPrice }}"
                                    x-model="price"
                                    required
                                    inputmode="decimal"
                                    placeholder="0.00"
                                    class="min-h-12 w-full rounded-xl border bg-white py-3 pl-12 pr-4 text-base font-black text-warm-950 outline-none transition placeholder:text-warm-300 focus:ring-4 focus:ring-brand-100 @error('price') border-red-300 focus:border-red-400 @else border-brand-200 focus:border-brand-500 @enderror"
                                >
                            </div>

                            @error('price')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Compare Price --}}
                        <div class="rounded-2xl border border-warm-200 bg-warm-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-warm-600 shadow-sm">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <path d="M3 6h18M7 3v6M17 3v6M5 12h14M5 16h9" />
                                        </svg>
                                    </span>

                                    <div>
                                        <label
                                            for="compare_at_price"
                                            class="block text-sm font-black text-warm-950"
                                        >
                                            Original Price
                                        </label>

                                        <p class="mt-1 text-xs font-semibold text-warm-500">
                                            Optional crossed-out price.
                                        </p>
                                    </div>
                                </div>

                                <span
                                    x-show="discountPercent() > 0"
                                    x-cloak
                                    class="rounded-full bg-leaf-100 px-2.5 py-1 text-[9px] font-black text-leaf-700"
                                    x-text="`${discountPercent()}% off`"
                                ></span>
                            </div>

                            <div class="relative mt-4">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-warm-500">
	                                    A$
                                </span>

                                <input
                                    id="compare_at_price"
                                    name="compare_at_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value="{{ $initialCompareAtPrice }}"
                                    x-model="compareAtPrice"
                                    inputmode="decimal"
                                    placeholder="0.00"
                                    class="min-h-12 w-full rounded-xl border bg-white py-3 pl-12 pr-4 text-base font-black text-warm-950 outline-none transition placeholder:text-warm-300 focus:ring-4 focus:ring-brand-100 @error('compare_at_price') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                                >
                            </div>

                            @error('compare_at_price')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-red-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Metadata --}}
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label
                                for="preparation_time"
                                class="block text-sm font-black text-warm-900"
                            >
                                Preparation Time
                            </label>

                            <div class="relative mt-2">
                                <input
                                    id="preparation_time"
                                    name="preparation_time"
                                    type="number"
                                    min="1"
                                    value="{{ $initialPreparationTime }}"
                                    x-model="preparationTime"
                                    inputmode="numeric"
                                    placeholder="15"
                                    class="min-h-12 w-full rounded-xl border border-warm-200 bg-warm-50 px-4 py-3 pr-14 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >

                                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-warm-500">
                                    min
                                </span>
                            </div>

                            @error('preparation_time')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="calories"
                                class="block text-sm font-black text-warm-900"
                            >
                                Calories
                            </label>

                            <div class="relative mt-2">
                                <input
                                    id="calories"
                                    name="calories"
                                    type="number"
                                    min="0"
                                    value="{{ $initialCalories }}"
                                    x-model="calories"
                                    inputmode="numeric"
                                    placeholder="450"
                                    class="min-h-12 w-full rounded-xl border border-warm-200 bg-warm-50 px-4 py-3 pr-14 text-sm font-semibold text-warm-900 outline-none transition placeholder:text-warm-500 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                >

                                <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-warm-500">
                                    kcal
                                </span>
                            </div>

                            @error('calories')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="sort_order"
                                class="block text-sm font-black text-warm-900"
                            >
                                Display Position
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="sort_order"
                                name="sort_order"
                                type="number"
                                min="0"
                                value="{{ $initialSortOrder }}"
                                x-model="sortOrder"
                                required
                                inputmode="numeric"
                                class="mt-2 min-h-12 w-full rounded-xl border bg-warm-50 px-4 py-3 text-center text-base font-black text-warm-900 outline-none transition focus:bg-white focus:ring-4 focus:ring-brand-100 @error('sort_order') border-red-300 focus:border-red-400 @else border-warm-200 focus:border-brand-500 @enderror"
                            >

                            @error('sort_order')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Status Toggles --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        {{-- Available --}}
                        <label
                            class="cursor-pointer rounded-2xl border p-4 transition"
                            x-bind:class="available
                                ? 'border-leaf-100 bg-leaf-50'
                                : 'border-warm-200 bg-warm-50'"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <span
                                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                                        x-bind:class="available
                                            ? 'text-leaf-700'
                                            : 'text-warm-500'"
                                    >
                                        <svg
                                            x-show="available"
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
                                            x-show="! available"
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
                                        </svg>
                                    </span>

                                    <span>
                                        <span
                                            class="block text-sm font-black"
                                            x-bind:class="available
                                                ? 'text-leaf-900'
                                                : 'text-warm-950'"
                                            x-text="available
                                                ? 'Available for ordering'
                                                : 'Unavailable to customers'"
                                        ></span>

                                        <span
                                            class="mt-1 block text-xs font-semibold leading-5"
                                            x-bind:class="available
                                                ? 'text-leaf-700'
                                                : 'text-warm-500'"
                                            x-text="available
                                                ? 'Customers can view and order this item.'
                                                : 'The item remains saved but cannot be ordered.'"
                                        ></span>
                                    </span>
                                </div>

                                <span class="relative mt-1 shrink-0">
                                    <input
                                        type="hidden"
                                        name="is_available"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="is_available"
                                        value="1"
                                        x-model="available"
                                        @checked($initialAvailable)
                                        class="peer sr-only"
                                    >

                                    <span class="block h-7 w-12 rounded-full bg-warm-300 transition peer-checked:bg-leaf-700 peer-focus:ring-4 peer-focus:ring-leaf-100"></span>

                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                </span>
                            </div>
                        </label>

                        {{-- Featured --}}
                        <label
                            class="cursor-pointer rounded-2xl border p-4 transition"
                            x-bind:class="featured
                                ? 'border-brand-200 bg-brand-50'
                                : 'border-warm-200 bg-warm-50'"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <span
                                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white shadow-sm"
                                        x-bind:class="featured
                                            ? 'text-brand-500'
                                            : 'text-warm-500'"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3z" />
                                        </svg>
                                    </span>

                                    <span>
                                        <span
                                            class="block text-sm font-black"
                                            x-bind:class="featured
                                                ? 'text-brand-900'
                                                : 'text-warm-950'"
                                            x-text="featured
                                                ? 'Featured menu item'
                                                : 'Standard menu item'"
                                        ></span>

                                        <span
                                            class="mt-1 block text-xs font-semibold leading-5"
                                            x-bind:class="featured
                                                ? 'text-brand-600'
                                                : 'text-warm-500'"
                                            x-text="featured
                                                ? 'Promoted in featured menu areas.'
                                                : 'Shown normally inside its category.'"
                                        ></span>
                                    </span>
                                </div>

                                <span class="relative mt-1 shrink-0">
                                    <input
                                        type="hidden"
                                        name="is_featured"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="is_featured"
                                        value="1"
                                        x-model="featured"
                                        @checked($initialFeatured)
                                        class="peer sr-only"
                                    >

                                    <span class="block h-7 w-12 rounded-full bg-warm-300 transition peer-checked:bg-brand-500 peer-focus:ring-4 peer-focus:ring-brand-100"></span>

                                    <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                </span>
                            </div>
                        </label>
                    </div>
                </div>
            </section>

            {{-- Step 3: Ordering Options --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-warm-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            3
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Ordering Options
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Sizes and paid extras
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Optional choices customers can select before adding the item to their cart.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <span class="rounded-full bg-warm-100 px-3 py-1.5 text-[10px] font-black text-warm-600">
                            <span x-text="sizes.length"></span>
                            sizes
                        </span>

                        <span class="rounded-full bg-brand-50 px-3 py-1.5 text-[10px] font-black text-brand-600">
                            <span x-text="addons.length"></span>
                            extras
                        </span>
                    </div>
                </div>

                <div class="grid gap-5 p-4 sm:p-6 2xl:grid-cols-2">
                    {{-- Sizes --}}
                    <div class="rounded-[1.5rem] border border-warm-200 bg-warm-50 p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-black text-warm-950">
                                    Size Pricing
                                </h3>

                                <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                    Use final selling prices for each available size.
                                </p>
                            </div>

                            <button
                                type="button"
                                x-on:click="addSize()"
                                class="inline-flex min-h-10 shrink-0 items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-3.5 py-2 text-xs font-black text-white shadow-sm transition active:scale-95 hover:bg-brand-600"
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

                                Add Size
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-if="sizes.length === 0">
                                <div class="rounded-2xl border border-dashed border-warm-300 bg-white px-5 py-7 text-center">
                                    <span class="mx-auto grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-500">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <circle cx="12" cy="12" r="8" />
                                            <path d="M8 12h8" />
                                        </svg>
                                    </span>

                                    <p class="mt-3 text-sm font-black text-warm-900">
                                        No size options
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                        Customers will use the base selling price.
                                    </p>
                                </div>
                            </template>

                            <template
                                x-for="(size, index) in sizes"
                                x-bind:key="'size-' + index"
                            >
                                <div class="rounded-2xl border border-warm-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-xs font-black text-brand-600">
                                                <span x-text="index + 1"></span>
                                            </span>

                                            <div class="min-w-0">
                                                <p class="text-sm font-black text-warm-950">
                                                    Size option
                                                </p>

                                                <p
                                                    class="mt-0.5 truncate text-xs font-semibold text-warm-500"
                                                    x-text="size.name || 'Unnamed size'"
                                                ></p>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            x-on:click="removeSize(index)"
                                            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-red-50 text-red-600 transition active:scale-95 hover:bg-red-600 hover:text-white"
                                            aria-label="Remove size"
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
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-[minmax(0,1fr)_130px_85px]">
                                        <div>
                                            <label class="block text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Size Name
                                            </label>

                                            <input
                                                type="text"
                                                x-model="size.name"
                                                x-bind:name="'sizes[' + index + '][name]'"
                                                placeholder="Large"
                                                class="mt-1.5 min-h-11 w-full rounded-xl border border-warm-200 bg-warm-50 px-3 py-2.5 text-sm font-semibold text-warm-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                            >
                                        </div>

                                        <div>
                                            <label class="block text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Final Price
                                            </label>

                                            <div class="relative mt-1.5">
                                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-warm-500">
	                                                    A$
                                                </span>

                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    x-model="size.price"
                                                    x-bind:name="'sizes[' + index + '][price]'"
                                                    placeholder="1499"
                                                    class="min-h-11 w-full rounded-xl border border-warm-200 bg-warm-50 py-2.5 pl-9 pr-3 text-sm font-semibold text-warm-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                                >
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Sort
                                            </label>

                                            <input
                                                type="number"
                                                min="0"
                                                x-model="size.sort_order"
                                                x-bind:name="'sizes[' + index + '][sort_order]'"
                                                class="mt-1.5 min-h-11 w-full rounded-xl border border-warm-200 bg-warm-50 px-3 py-2.5 text-center text-sm font-black text-warm-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                            >
                                        </div>
                                    </div>

                                    <label class="mt-3 flex cursor-pointer items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                                        <span class="text-xs font-black text-warm-600">
                                            Available to customers
                                        </span>

                                        <span class="relative shrink-0">
                                            <input
                                                type="hidden"
                                                x-bind:name="'sizes[' + index + '][is_active]'"
                                                value="0"
                                            >

                                            <input
                                                type="checkbox"
                                                value="1"
                                                x-model="size.is_active"
                                                x-bind:name="'sizes[' + index + '][is_active]'"
                                                class="peer sr-only"
                                            >

                                            <span class="block h-6 w-11 rounded-full bg-warm-300 transition peer-checked:bg-leaf-500"></span>

                                            <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                        </span>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Add-ons --}}
                    <div class="rounded-[1.5rem] border border-warm-200 bg-warm-50 p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-black text-warm-950">
                                    Add-ons and Extras
                                </h3>

                                <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                    Additional paid selections shown during ordering.
                                </p>
                            </div>

                            <button
                                type="button"
                                x-on:click="addAddon()"
                                class="inline-flex min-h-10 shrink-0 items-center justify-center gap-1.5 rounded-xl bg-brand-500 px-3.5 py-2 text-xs font-black text-white shadow-sm transition active:scale-95 hover:bg-brand-600"
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

                                Add Extra
                            </button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <template x-if="addons.length === 0">
                                <div class="rounded-2xl border border-dashed border-warm-300 bg-white px-5 py-7 text-center">
                                    <span class="mx-auto grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-500">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <path d="M12 5v14M5 12h14" />
                                        </svg>
                                    </span>

                                    <p class="mt-3 text-sm font-black text-warm-900">
                                        No paid extras
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                        Add cheese, toppings, sauces, or dips.
                                    </p>
                                </div>
                            </template>

                            <template
                                x-for="(addon, index) in addons"
                                x-bind:key="'addon-' + index"
                            >
                                <div class="rounded-2xl border border-warm-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-xs font-black text-brand-600">
                                                <span x-text="index + 1"></span>
                                            </span>

                                            <div class="min-w-0">
                                                <p class="text-sm font-black text-warm-950">
                                                    Paid extra
                                                </p>

                                                <p
                                                    class="mt-0.5 truncate text-xs font-semibold text-warm-500"
                                                    x-text="addon.name || 'Unnamed extra'"
                                                ></p>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            x-on:click="removeAddon(index)"
                                            class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-red-50 text-red-600 transition active:scale-95 hover:bg-red-600 hover:text-white"
                                            aria-label="Remove add-on"
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
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Name
                                            </label>

                                            <input
                                                type="text"
                                                x-model="addon.name"
                                                x-bind:name="'addons[' + index + '][name]'"
                                                placeholder="Extra Cheese"
                                                class="mt-1.5 min-h-11 w-full rounded-xl border border-warm-200 bg-warm-50 px-3 py-2.5 text-sm font-semibold text-warm-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                            >
                                        </div>

                                        <div>
                                            <label class="block text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Type
                                            </label>

                                            <select
                                                x-model="addon.type"
                                                x-bind:name="'addons[' + index + '][type]'"
                                                class="mt-1.5 min-h-11 w-full rounded-xl border border-warm-200 bg-warm-50 px-3 py-2.5 text-sm font-semibold text-warm-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                            >
                                                @foreach ($addonTypes as $value => $label)
                                                    <option value="{{ $value }}">
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Additional Price
                                            </label>

                                            <div class="relative mt-1.5">
                                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-warm-500">
	                                                    A$
                                                </span>

                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    x-model="addon.price"
                                                    x-bind:name="'addons[' + index + '][price]'"
                                                    placeholder="150"
                                                    class="min-h-11 w-full rounded-xl border border-warm-200 bg-warm-50 py-2.5 pl-9 pr-3 text-sm font-semibold text-warm-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                                >
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Display Position
                                            </label>

                                            <input
                                                type="number"
                                                min="0"
                                                x-model="addon.sort_order"
                                                x-bind:name="'addons[' + index + '][sort_order]'"
                                                class="mt-1.5 min-h-11 w-full rounded-xl border border-warm-200 bg-warm-50 px-3 py-2.5 text-center text-sm font-black text-warm-900 outline-none transition focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"
                                            >
                                        </div>
                                    </div>

                                    <label class="mt-3 flex cursor-pointer items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                                        <span class="text-xs font-black text-warm-600">
                                            Available to customers
                                        </span>

                                        <span class="relative shrink-0">
                                            <input
                                                type="hidden"
                                                x-bind:name="'addons[' + index + '][is_active]'"
                                                value="0"
                                            >

                                            <input
                                                type="checkbox"
                                                value="1"
                                                x-model="addon.is_active"
                                                x-bind:name="'addons[' + index + '][is_active]'"
                                                class="peer sr-only"
                                            >

                                            <span class="block h-6 w-11 rounded-full bg-warm-300 transition peer-checked:bg-leaf-500"></span>

                                            <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                        </span>
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                @error('sizes')
                    <p class="mx-4 mb-4 text-sm font-semibold text-red-600 sm:mx-6">
                        {{ $message }}
                    </p>
                @enderror

                @error('addons')
                    <p class="mx-4 mb-4 text-sm font-semibold text-red-600 sm:mx-6">
                        {{ $message }}
                    </p>
                @enderror
            </section>

            {{-- Step 4: Image --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-sm font-black text-brand-600">
                            4
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Item Image
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Customer-facing food image
                            </h2>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500 sm:text-sm">
                                Choose a clear landscape photo that accurately represents the item.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <label
                        for="image"
                        class="group relative flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-[1.5rem] border-2 border-dashed border-brand-200 bg-brand-50/60 px-5 py-9 text-center transition hover:border-brand-500 hover:bg-brand-50 sm:py-10"
                    >
                        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand-200/40 blur-3xl"></div>

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
                                Choose menu item image
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

        {{-- Preview and Actions --}}
        <aside class="order-first space-y-5 xl:order-none xl:sticky xl:top-24">
            {{-- Live Preview --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-xl shadow-brand-900/5">
                <div class="flex items-center justify-between gap-4 border-b border-warm-200 px-5 py-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                            Live Preview
                        </p>

                        <h2 class="mt-1 text-lg font-black text-warm-950">
                            Customer menu card
                        </h2>
                    </div>

                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em]"
                        x-bind:class="available
                            ? 'bg-leaf-50 text-leaf-700'
                            : 'bg-red-50 text-red-700'"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            x-bind:class="available
                                ? 'bg-leaf-500'
                                : 'bg-red-500'"
                        ></span>

                        <span x-text="available ? 'Available' : 'Unavailable'"></span>
                    </span>
                </div>

                <div class="p-4 sm:p-5">
                    <div class="overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white shadow-sm">
                        {{-- Preview Image --}}
                        <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-brand-100 via-gold-50 to-food-cream">
                            <template x-if="preview">
                                <img
                                    x-bind:src="preview"
                                    alt="Menu item preview"
                                    class="absolute inset-0 h-full w-full object-cover"
                                >
                            </template>

                            <div
                                x-show="! preview"
                                class="absolute inset-0 grid place-items-center"
                            >
                                <span
                                    class="grid h-20 w-20 place-items-center rounded-full border border-white/70 bg-white/80 text-3xl font-black text-brand-600 shadow-xl backdrop-blur"
                                    x-text="(itemName || 'M').charAt(0).toUpperCase()"
                                ></span>
                            </div>

                            <div class="absolute inset-0 bg-gradient-to-t from-warm-950/70 via-transparent to-transparent"></div>

                            <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                                <span
                                    x-show="featured"
                                    x-cloak
                                    class="rounded-full bg-brand-500 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em] text-white shadow-lg"
                                >
                                    Featured
                                </span>

                                <span
                                    x-show="discountPercent() > 0"
                                    x-cloak
                                    class="rounded-full bg-leaf-700 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em] text-white shadow-lg"
                                    x-text="`${discountPercent()}% Off`"
                                ></span>
                            </div>

                            <div
                                x-show="! available"
                                x-cloak
                                class="absolute inset-0 grid place-items-center bg-warm-950/55 backdrop-blur-[1px]"
                            >
                                <span class="rounded-full bg-white px-4 py-2 text-xs font-black uppercase tracking-[0.12em] text-red-600 shadow-xl">
                                    Currently Unavailable
                                </span>
                            </div>
                        </div>

                        {{-- Preview Content --}}
                        <div class="p-4">
                            <p
                                class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500"
                                x-text="categoryName || 'Uncategorized'"
                            ></p>

                            <h3
                                class="mt-1.5 break-words text-xl font-black tracking-tight text-warm-950"
                                x-text="itemName.trim() || 'Menu Item Name'"
                            ></h3>

                            <p
                                class="mt-2 line-clamp-3 min-h-[60px] text-xs font-semibold leading-5 text-warm-600"
                                x-text="itemDescription.trim() || 'Your item description will appear here for customers.'"
                            ></p>

                            <div class="mt-4 flex flex-wrap items-end gap-2">
                                <p class="text-2xl font-black text-brand-500">
	                                    A$
                                    <span x-text="Number(price || 0).toLocaleString()"></span>
                                </p>

                                <p
                                    x-show="Number(compareAtPrice) > Number(price) && Number(compareAtPrice) > 0"
                                    x-cloak
                                    class="pb-0.5 text-sm font-bold text-warm-500 line-through"
                                >
	                                    A$
                                    <span x-text="Number(compareAtPrice || 0).toLocaleString()"></span>
                                </p>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span
                                    x-show="preparationTime"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-warm-100 px-3 py-1.5 text-[10px] font-bold text-warm-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-3.5 w-3.5"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 2" />
                                    </svg>

                                    <span x-text="`${preparationTime} min`"></span>
                                </span>

                                <span
                                    x-show="calories"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-warm-100 px-3 py-1.5 text-[10px] font-bold text-warm-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-3.5 w-3.5"
                                    >
                                        <path d="M12 22c4-3 6-6.2 6-10a6 6 0 0 0-12 0c0 3.8 2 7 6 10z" />
                                    </svg>

                                    <span x-text="`${calories} kcal`"></span>
                                </span>

                                <span
                                    x-show="sizes.length > 0"
                                    class="rounded-full bg-indigo-50 px-3 py-1.5 text-[10px] font-bold text-indigo-700"
                                >
                                    <span x-text="sizes.length"></span>
                                    sizes
                                </span>

                                <span
                                    x-show="addons.length > 0"
                                    class="rounded-full bg-violet-50 px-3 py-1.5 text-[10px] font-bold text-violet-700"
                                >
                                    <span x-text="addons.length"></span>
                                    extras
                                </span>
                            </div>

                            <button
                                type="button"
                                disabled
                                class="mt-5 min-h-12 w-full rounded-xl px-5 py-3 text-sm font-black shadow-sm"
                                x-bind:class="available
                                    ? 'bg-brand-500 text-white'
                                    : 'cursor-not-allowed bg-warm-200 text-warm-500'"
                            >
                                <span x-text="available ? 'Add to Cart' : 'Unavailable'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Preview Metadata --}}
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div class="min-w-0 rounded-xl bg-warm-50 px-3 py-3">
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                URL Slug
                            </p>

                            <p
                                class="mt-1 truncate font-mono text-[10px] font-bold text-warm-600"
                                x-text="displaySlug() || 'item-name'"
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

                    <p class="mt-3 text-center text-[10px] font-semibold leading-4 text-warm-500">
                        Preview represents how the item may appear on the customer menu.
                    </p>
                </div>
            </section>

            {{-- Completion Summary --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Item Summary
                </p>

                <h2 class="mt-1 text-lg font-black text-warm-950">
                    Before publishing
                </h2>

                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Base price
                        </span>

                        <span class="text-sm font-black text-warm-950">
	                            A$
                            <span x-text="Number(price || 0).toLocaleString()"></span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Size options
                        </span>

                        <span
                            class="text-sm font-black text-warm-950"
                            x-text="sizes.length"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Paid extras
                        </span>

                        <span
                            class="text-sm font-black text-warm-950"
                            x-text="addons.length"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-xl bg-warm-50 px-3 py-3">
                        <span class="text-xs font-semibold text-warm-500">
                            Public status
                        </span>

                        <span
                            class="rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em]"
                            x-bind:class="available
                                ? 'bg-leaf-100 text-leaf-700'
                                : 'bg-red-100 text-red-700'"
                            x-text="available ? 'Available' : 'Unavailable'"
                        ></span>
                    </div>
                </div>
            </section>

            {{-- Desktop Save Actions --}}
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
                            <path d="M5 5h12l2 2v12H5z" />
                            <path d="M8 5v5h8V5M8 19v-6h8v6" />
                        </svg>
                    </span>

                    <div>
                        <p class="text-sm font-black text-warm-950">
                            Ready to save?
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                            Review the preview, price, availability, and ordering options.
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
                        <path d="M5 5h12l2 2v12H5z" />
                        <path d="M8 5v5h8V5M8 19v-6h8v6" />
                    </svg>

                    <span
                        x-text="submitting
                            ? 'Saving menu item...'
                            : @js($submitLabel)"
                    ></span>
                </button>

                <a
                    href="{{ route('admin.menu-items.index') }}"
                    class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-warm-200 bg-white px-5 py-3 text-sm font-black text-warm-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600"
                >
                    Cancel
                </a>
            </section>
        </aside>
    </form>

    {{-- Persistent Mobile / Tablet Actions --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur xl:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.menu-items.index') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-95"
                aria-label="Cancel and return to menu"
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
                form="menu-item-form"
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
                    <path d="M5 5h12l2 2v12H5z" />
                    <path d="M8 5v5h8V5M8 19v-6h8v6" />
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
