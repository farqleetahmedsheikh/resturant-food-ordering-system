@component('layouts.public', ['title' => 'Cart'])
@php
$isOpen = (bool) ($restaurant?->is_open ?? true);
$minimumOrderAmount = (float) ($restaurant?->minimum_order_amount ?? 0);
$subtotal = (float) ($cart['subtotal'] ?? 0);
$cartCount = (int) ($cart['count'] ?? 0);

    $needsMoreAmount = $minimumOrderAmount > 0
        && $subtotal > 0
        && $subtotal < $minimumOrderAmount;

    $amountNeeded = max(
        0,
        $minimumOrderAmount - $subtotal
    );

    $minimumProgress = $minimumOrderAmount > 0
        ? min(100, max(0, ($subtotal / $minimumOrderAmount) * 100))
        : 100;

    $canCheckout = $cartCount > 0
        && $isOpen
        && ! $needsMoreAmount;
@endphp

<main
    x-data="{ clearing: false }"
    class="min-h-screen bg-[var(--color-surface-warm)] pb-28 lg:pb-0"
>
    <section class="py-5 sm:py-9 lg:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Mobile Navigation --}}
            <div class="mb-5 flex items-center justify-between lg:hidden">
                <a
                    href="{{ route('menu') }}"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-orange-100 bg-white text-slate-700 shadow-sm transition active:scale-95"
                    aria-label="Back to menu"
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

                <div class="text-center">
                    <p class="text-sm font-black text-slate-950">
                        Your cart
                    </p>

                    @if ($cartCount > 0)
                        <p class="mt-0.5 text-[10px] font-semibold text-slate-500">
                            {{ $cartCount }}
                            {{ $cartCount === 1 ? 'item' : 'items' }}
                        </p>
                    @endif
                </div>

                <a
                    href="{{ route('menu') }}"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-orange-100 bg-white text-orange-600 shadow-sm transition active:scale-95"
                    aria-label="Add more items"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>
                </a>
            </div>

            {{-- Page Header --}}
            <header class="mb-6 flex items-end justify-between gap-5 sm:mb-8">
                <div>
                    <p class="hidden text-xs font-black uppercase tracking-[0.22em] text-orange-600 lg:block">
                        Your Cart
                    </p>

                    <h1 class="text-2xl font-black tracking-tight text-slate-950 sm:text-3xl lg:mt-2 lg:text-4xl">
                        Review your order
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-600">
                        Adjust quantities, review your choices, and continue to cash-on-delivery checkout.
                    </p>
                </div>

                <a
                    href="{{ route('menu') }}"
                    class="hidden min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50 lg:inline-flex"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>

                    Add More Items
                </a>
            </header>

            {{-- Checkout Alerts --}}
            @if (! $isOpen)
                <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm sm:p-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-amber-600 shadow-sm">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 7v5M12 16h.01" />
                            </svg>
                        </span>

                        <div>
                            <p class="text-sm font-black text-amber-900">
                                Restaurant currently closed
                            </p>

                            <p class="mt-1 text-xs font-semibold leading-5 text-amber-800 sm:text-sm">
                                Your cart is saved, but checkout is unavailable until the restaurant opens.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($needsMoreAmount)
                <div class="mb-4 rounded-2xl border border-orange-200 bg-orange-50 p-4 shadow-sm sm:p-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                            </svg>
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black text-orange-900">
                                        Add Rs. {{ number_format($amountNeeded, 0) }} more
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-orange-800 sm:text-sm">
                                        Minimum order is Rs. {{ number_format($minimumOrderAmount, 0) }}.
                                    </p>
                                </div>

                                <a
                                    href="{{ route('menu') }}"
                                    class="shrink-0 text-xs font-black text-orange-700 hover:text-orange-800"
                                >
                                    Add items
                                </a>
                            </div>

                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-orange-100">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-orange-500 to-red-500"
                                    style="width: {{ $minimumProgress }}%"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($cartCount < 1)
                {{-- Empty Cart --}}
                <section class="rounded-[1.75rem] border border-dashed border-orange-200 bg-white p-7 text-center shadow-sm sm:p-12">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-orange-50 text-orange-600 sm:h-20 sm:w-20">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-7 w-7 sm:h-9 sm:w-9"
                        >
                            <path d="M3 4h2l2 11h10l2-8H7" />
                            <circle cx="9" cy="20" r="1" />
                            <circle cx="17" cy="20" r="1" />
                        </svg>
                    </div>

                    <h2 class="mt-5 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                        Your cart is empty
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                        Browse the menu and add something fresh to start your order.
                    </p>

                    <a
                        href="{{ route('menu') }}"
                        class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition active:scale-[0.98] hover:bg-orange-700 sm:w-auto sm:rounded-2xl"
                    >
                        Browse Menu

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                </section>
            @else
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_390px] lg:items-start lg:gap-8">
                    {{-- Cart Items --}}
                    <div class="min-w-0 space-y-5">
                        <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                            <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                                        Cart Items
                                    </p>

                                    <h2 class="mt-1 text-lg font-black text-slate-950 sm:text-xl">
                                        {{ $cartCount }}
                                        {{ $cartCount === 1 ? 'item' : 'items' }}
                                        selected
                                    </h2>
                                </div>

                                <a
                                    href="{{ route('menu') }}"
                                    class="text-xs font-black text-orange-700 hover:text-orange-800 sm:text-sm"
                                >
                                    Add more
                                </a>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach ($cart['items'] as $cartKey => $item)
                                    <article
                                        x-data="{
                                            updating: false,
                                            removing: false
                                        }"
                                        class="p-4 sm:p-6"
                                    >
                                        <div class="flex items-start gap-3 sm:gap-5">
                                            {{-- Product Image --}}
                                            <div class="grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-orange-100 via-amber-50 to-red-100 sm:h-28 sm:w-28">
                                                @if (! empty($item['image']))
                                                    <img
                                                        src="{{ \App\Support\ImageUpload::url($item['image']) }}"
                                                        alt="{{ $item['name'] }}"
                                                        class="h-full w-full object-cover"
                                                    >
                                                @else
                                                    <div class="grid h-14 w-14 place-items-center rounded-full bg-white/85 text-2xl font-black text-orange-600 shadow-lg">
                                                        {{ mb_substr($item['name'], 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Product Information --}}
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <h3 class="line-clamp-2 text-sm font-black leading-5 text-slate-950 sm:text-lg sm:leading-6">
                                                            {{ $item['name'] }}
                                                        </h3>

                                                        <p class="mt-1 text-xs font-bold text-orange-600 sm:text-sm">
                                                            Rs. {{ number_format($item['price'], 0) }} each
                                                        </p>
                                                    </div>

                                                    <form
                                                        action="{{ route('cart.remove', $cartKey) }}"
                                                        method="POST"
                                                        x-on:submit="removing = true"
                                                        class="shrink-0"
                                                    >
                                                        @csrf

                                                        <button
                                                            type="submit"
                                                            x-bind:disabled="removing"
                                                            class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-50 text-red-500 transition active:scale-95 hover:bg-red-100 disabled:opacity-60"
                                                            aria-label="Remove {{ $item['name'] }} from cart"
                                                        >
                                                            <svg
                                                                x-show="! removing"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="2"
                                                                class="h-4 w-4"
                                                            >
                                                                <path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13" />
                                                            </svg>

                                                            <svg
                                                                x-show="removing"
                                                                x-cloak
                                                                class="h-4 w-4 animate-spin"
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
                                                        </button>
                                                    </form>
                                                </div>

                                                {{-- Mobile Item Total --}}
                                                <p class="mt-2 text-base font-black text-slate-950 sm:hidden">
                                                    Rs. {{ number_format($item['total'], 0) }}
                                                </p>

                                                {{-- Desktop Description --}}
                                                <p class="mt-2 hidden line-clamp-2 text-sm font-semibold leading-6 text-slate-500 sm:block">
                                                    {{ $item['description'] ?? 'Freshly prepared with quality ingredients.' }}
                                                </p>
                                            </div>

                                            {{-- Desktop Item Total --}}
                                            <div class="hidden shrink-0 text-right sm:block">
                                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                                    Item Total
                                                </p>

                                                <p class="mt-1 text-lg font-black text-slate-950">
                                                    Rs. {{ number_format($item['total'], 0) }}
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Selected Options --}}
                                        @if (! empty($item['size_name']) || ! empty($item['addons']))
                                            <div class="mt-3 flex flex-wrap gap-2 sm:ml-[132px] sm:mt-4">
                                                @if (! empty($item['size_name']))
                                                    <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1.5 text-[10px] font-black text-orange-700 sm:text-xs">
                                                        Size: {{ $item['size_name'] }}
                                                    </span>
                                                @endif

                                                @if (! empty($item['addons']))
                                                    @foreach (collect($item['addons'])->take(2) as $addon)
                                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-black text-slate-600 sm:text-xs">
                                                            + {{ $addon['name'] }}
                                                        </span>
                                                    @endforeach

                                                    @if (collect($item['addons'])->count() > 2)
                                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-[10px] font-black text-slate-600 sm:text-xs">
                                                            +{{ collect($item['addons'])->count() - 2 }} more
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Quantity and Price Controls --}}
                                        <div class="mt-4 flex items-center justify-between gap-3 sm:ml-[132px]">
                                            <div class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                                                <form
                                                    action="{{ route('cart.update') }}"
                                                    method="POST"
                                                    x-on:submit="updating = true"
                                                >
                                                    @csrf

                                                    <input
                                                        type="hidden"
                                                        name="item"
                                                        value="{{ $cartKey }}"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="quantity"
                                                        value="{{ max(0, $item['quantity'] - 1) }}"
                                                    >

                                                    <button
                                                        type="submit"
                                                        x-bind:disabled="updating"
                                                        class="grid h-10 w-10 place-items-center rounded-lg bg-white text-slate-700 shadow-sm transition active:scale-95 hover:bg-orange-50 hover:text-orange-700 disabled:opacity-50"
                                                        aria-label="Decrease quantity"
                                                    >
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2.5"
                                                            class="h-4 w-4"
                                                        >
                                                            <path stroke-linecap="round" d="M5 12h14" />
                                                        </svg>
                                                    </button>
                                                </form>

                                                <span class="grid h-10 min-w-12 place-items-center px-2 text-sm font-black text-slate-950">
                                                    <span x-show="! updating">
                                                        {{ $item['quantity'] }}
                                                    </span>

                                                    <svg
                                                        x-show="updating"
                                                        x-cloak
                                                        class="h-4 w-4 animate-spin text-orange-600"
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
                                                </span>

                                                <form
                                                    action="{{ route('cart.update') }}"
                                                    method="POST"
                                                    x-on:submit="updating = true"
                                                >
                                                    @csrf

                                                    <input
                                                        type="hidden"
                                                        name="item"
                                                        value="{{ $cartKey }}"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="quantity"
                                                        value="{{ $item['quantity'] + 1 }}"
                                                    >

                                                    <button
                                                        type="submit"
                                                        x-bind:disabled="updating"
                                                        class="grid h-10 w-10 place-items-center rounded-lg bg-white text-slate-700 shadow-sm transition active:scale-95 hover:bg-orange-50 hover:text-orange-700 disabled:opacity-50"
                                                        aria-label="Increase quantity"
                                                    >
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2.5"
                                                            class="h-4 w-4"
                                                        >
                                                            <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>

                                            <div class="text-right sm:hidden">
                                                <p class="text-[9px] font-black uppercase tracking-[0.1em] text-slate-400">
                                                    Quantity
                                                </p>

                                                <p class="mt-0.5 text-xs font-bold text-slate-500">
                                                    {{ $item['quantity'] }}
                                                    × Rs. {{ number_format($item['price'], 0) }}
                                                </p>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                        {{-- Mobile Summary --}}
                        <section class="rounded-[1.75rem] border border-orange-100 bg-white p-4 shadow-sm lg:hidden">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                                        Order Summary
                                    </p>

                                    <h2 class="mt-1 text-lg font-black text-slate-950">
                                        Total payment
                                    </h2>
                                </div>

                                <p class="text-2xl font-black text-orange-600">
                                    Rs. {{ number_format($cart['total'], 0) }}
                                </p>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-slate-50 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                        Subtotal
                                    </p>

                                    <p class="mt-1 text-sm font-black text-slate-950">
                                        Rs. {{ number_format($cart['subtotal'], 0) }}
                                    </p>
                                </div>

                                <div class="rounded-xl bg-slate-50 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                        Delivery
                                    </p>

                                    <p class="mt-1 text-sm font-black text-slate-950">
                                        Rs. {{ number_format($cart['delivery_fee'], 0) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-3 text-xs font-bold text-emerald-700">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4 shrink-0"
                                >
                                    <rect x="3" y="6" width="18" height="12" rx="2" />
                                    <circle cx="12" cy="12" r="2" />
                                </svg>

                                Cash on delivery
                            </div>
                        </section>

                        {{-- Smart Suggestions --}}
                        @if (($suggestions ?? collect())->isNotEmpty())
                            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                                <div class="flex items-end justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                                            Complete Your Order
                                        </p>

                                        <h2 class="mt-1 text-lg font-black text-slate-950 sm:text-xl">
                                            Popular add-ons
                                        </h2>
                                    </div>

                                    <a
                                        href="{{ route('menu') }}"
                                        class="shrink-0 text-xs font-black text-orange-700 hover:text-orange-800"
                                    >
                                        View menu
                                    </a>
                                </div>

                                <div class="flex snap-x snap-mandatory gap-3 overflow-x-auto p-4 sm:grid sm:grid-cols-2 sm:overflow-visible sm:p-5">
                                    @foreach ($suggestions as $suggestion)
                                        @php
                                            $suggestionCustomizable =
                                                ($suggestion->active_sizes_count ?? 0) > 0
                                                || ($suggestion->active_addons_count ?? 0) > 0;

                                            $suggestionAvailable = (bool) ($suggestion->is_available ?? true);
                                        @endphp

                                        <article class="flex min-w-[260px] snap-start items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3 sm:min-w-0">
                                            <a
                                                href="{{ route('menu.show', $suggestion) }}"
                                                class="grid h-16 w-16 shrink-0 place-items-center overflow-hidden rounded-xl bg-gradient-to-br from-orange-100 to-red-100"
                                            >
                                                @if ($suggestion->image_url)
                                                    <img
                                                        src="{{ $suggestion->image_url }}"
                                                        alt="{{ $suggestion->name }}"
                                                        class="h-full w-full object-cover"
                                                        loading="lazy"
                                                    >
                                                @else
                                                    <span class="text-xl font-black text-orange-600">
                                                        {{ mb_substr($suggestion->name, 0, 1) }}
                                                    </span>
                                                @endif
                                            </a>

                                            <div class="min-w-0 flex-1">
                                                <a
                                                    href="{{ route('menu.show', $suggestion) }}"
                                                    class="line-clamp-1 text-sm font-black text-slate-950 hover:text-orange-700"
                                                >
                                                    {{ $suggestion->name }}
                                                </a>

                                                <p class="mt-1 text-sm font-black text-orange-600">
                                                    Rs. {{ number_format($suggestion->price, 0) }}
                                                </p>
                                            </div>

                                            @if ($suggestionAvailable && $isOpen)
                                                @if ($suggestionCustomizable)
                                                    <a
                                                        href="{{ route('menu.show', $suggestion) }}"
                                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-600 text-white shadow-sm transition active:scale-95 hover:bg-orange-700"
                                                        aria-label="Customize {{ $suggestion->name }}"
                                                    >
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            class="h-4 w-4"
                                                        >
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                                        </svg>
                                                    </a>
                                                @else
                                                    <form
                                                        action="{{ route('cart.add', $suggestion) }}"
                                                        method="POST"
                                                        x-data="{ adding: false }"
                                                        x-on:submit="adding = true"
                                                    >
                                                        @csrf

                                                        <button
                                                            type="submit"
                                                            x-bind:disabled="adding"
                                                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-orange-600 text-white shadow-sm transition active:scale-95 hover:bg-orange-700 disabled:opacity-60"
                                                            aria-label="Add {{ $suggestion->name }} to cart"
                                                        >
                                                            <svg
                                                                x-show="! adding"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="2"
                                                                class="h-4 w-4"
                                                            >
                                                                <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                                            </svg>

                                                            <svg
                                                                x-show="adding"
                                                                x-cloak
                                                                class="h-4 w-4 animate-spin"
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
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </article>
                                    @endforeach
                                </div>

                                <p class="px-4 pb-4 text-[10px] font-semibold text-slate-400 sm:hidden">
                                    Swipe to see more suggestions
                                </p>
                            </section>
                        @endif

                        {{-- Mobile Cart Management --}}
                        <div class="flex items-center justify-between gap-4 lg:hidden">
                            <a
                                href="{{ route('menu') }}"
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2.5 text-xs font-black text-orange-700 transition active:scale-[0.98]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                </svg>

                                Add Items
                            </a>

                            <form
                                action="{{ route('cart.clear') }}"
                                method="POST"
                                x-on:submit="
                                    if (! confirm('Remove all items from your cart?')) {
                                        $event.preventDefault();
                                    } else {
                                        clearing = true;
                                    }
                                "
                            >
                                @csrf

                                <button
                                    type="submit"
                                    x-bind:disabled="clearing"
                                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black text-red-600 transition hover:bg-red-50 disabled:opacity-60"
                                >
                                    <svg
                                        x-show="! clearing"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13" />
                                    </svg>

                                    <svg
                                        x-show="clearing"
                                        x-cloak
                                        class="h-4 w-4 animate-spin"
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

                                    Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Desktop Order Summary --}}
                    <aside class="sticky top-28 hidden rounded-[2rem] border border-orange-100 bg-white p-6 shadow-xl shadow-orange-900/5 lg:block">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                                    Order Summary
                                </p>

                                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                                    Review your total
                                </h2>

                                <p class="mt-1 text-sm font-semibold text-slate-500">
                                    {{ $cartCount }}
                                    {{ $cartCount === 1 ? 'item' : 'items' }}
                                    in your cart
                                </p>
                            </div>

                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-6 w-6"
                                >
                                    <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                                    <path d="M9 7h6M9 11h6M9 15h3" />
                                </svg>
                            </span>
                        </div>

                        <div class="mt-6 space-y-4 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-semibold text-slate-500">
                                    Subtotal
                                </span>

                                <span class="font-black text-slate-950">
                                    Rs. {{ number_format($cart['subtotal'], 0) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <span class="font-semibold text-slate-500">
                                    Delivery fee
                                </span>

                                <span class="font-black text-slate-950">
                                    Rs. {{ number_format($cart['delivery_fee'], 0) }}
                                </span>
                            </div>

                            <div class="border-t border-orange-100 pt-4">
                                <div class="flex items-end justify-between gap-4">
                                    <span class="text-base font-black text-slate-950">
                                        Total
                                    </span>

                                    <span class="text-2xl font-black text-orange-600">
                                        Rs. {{ number_format($cart['total'], 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @if ($minimumOrderAmount > 0)
                            <div class="mt-5 rounded-2xl border border-orange-100 bg-orange-50 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs font-black text-orange-800">
                                        Minimum order
                                    </span>

                                    <span class="text-xs font-black text-orange-900">
                                        Rs. {{ number_format($minimumOrderAmount, 0) }}
                                    </span>
                                </div>

                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-orange-100">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-orange-500 to-red-500"
                                        style="width: {{ $minimumProgress }}%"
                                    ></div>
                                </div>

                                @if ($needsMoreAmount)
                                    <p class="mt-2 text-xs font-semibold leading-5 text-orange-800">
                                        Add Rs. {{ number_format($amountNeeded, 0) }} more to continue.
                                    </p>
                                @else
                                    <p class="mt-2 text-xs font-semibold text-emerald-700">
                                        Minimum order reached.
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div class="mt-5 flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                            >
                                <rect x="3" y="6" width="18" height="12" rx="2" />
                                <circle cx="12" cy="12" r="2" />
                            </svg>

                            <div>
                                <p class="text-sm font-black text-emerald-900">
                                    Cash on Delivery
                                </p>

                                <p class="mt-1 text-xs font-semibold leading-5 text-emerald-700">
                                    Pay the rider when your order arrives.
                                </p>
                            </div>
                        </div>

                        @if ($canCheckout)
                            <a
                                href="{{ route('checkout.index') }}"
                                class="mt-5 inline-flex min-h-14 w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700"
                            >
                                Continue to Checkout

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                </svg>
                            </a>
                        @else
                            <button
                                type="button"
                                disabled
                                class="mt-5 inline-flex min-h-14 w-full cursor-not-allowed items-center justify-center rounded-2xl bg-slate-200 px-5 py-4 text-sm font-black text-slate-500"
                            >
                                Checkout Unavailable
                            </button>

                            <p class="mt-3 text-center text-xs font-semibold leading-5 text-slate-500">
                                @if (! $isOpen)
                                    Restaurant is currently closed.
                                @elseif ($needsMoreAmount)
                                    Add Rs. {{ number_format($amountNeeded, 0) }} more to checkout.
                                @else
                                    Review your cart before continuing.
                                @endif
                            </p>
                        @endif

                        <a
                            href="{{ route('menu') }}"
                            class="mt-3 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 transition hover:bg-orange-50"
                        >
                            Add More Items
                        </a>

                        <form
                            action="{{ route('cart.clear') }}"
                            method="POST"
                            class="mt-3"
                            x-on:submit="
                                if (! confirm('Remove all items from your cart?')) {
                                    $event.preventDefault();
                                } else {
                                    clearing = true;
                                }
                            "
                        >
                            @csrf

                            <button
                                type="submit"
                                x-bind:disabled="clearing"
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl text-xs font-black text-red-600 transition hover:bg-red-50 disabled:opacity-60"
                            >
                                <svg
                                    x-show="! clearing"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13" />
                                </svg>

                                <svg
                                    x-show="clearing"
                                    x-cloak
                                    class="h-4 w-4 animate-spin"
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

                                Clear Entire Cart
                            </button>
                        </form>
                    </aside>
                </div>

                {{-- Persistent Mobile Checkout Bar --}}
                <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_32px_rgba(15,23,42,0.14)] backdrop-blur lg:hidden">
                    <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                        <div class="min-w-0 shrink-0">
                            <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Total
                            </p>

                            <p class="mt-0.5 whitespace-nowrap text-lg font-black text-slate-950">
                                Rs. {{ number_format($cart['total'], 0) }}
                            </p>
                        </div>

                        @if ($canCheckout)
                            <a
                                href="{{ route('checkout.index') }}"
                                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98]"
                            >
                                Checkout

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                </svg>
                            </a>
                        @elseif ($needsMoreAmount && $isOpen)
                            <a
                                href="{{ route('menu') }}"
                                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center rounded-xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98]"
                            >
                                Add Rs. {{ number_format($amountNeeded, 0) }} More
                            </a>
                        @else
                            <button
                                type="button"
                                disabled
                                class="inline-flex min-h-12 min-w-0 flex-1 cursor-not-allowed items-center justify-center rounded-xl bg-slate-200 px-4 py-3 text-sm font-black text-slate-500"
                            >
                                Restaurant Closed
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
</main>

@endcomponent
