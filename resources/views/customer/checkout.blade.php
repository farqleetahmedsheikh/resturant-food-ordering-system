@component('layouts.public', ['title' => 'Checkout'])
@php
$isOpen = (bool) ($restaurant?->is_open ?? true);

    $cartItems = collect($cart['items'] ?? []);
    $cartItemCount = (int) $cartItems->sum('quantity');

    $subtotal = (float) ($cart['subtotal'] ?? 0);
    $deliveryFeeAmount = (float) ($cart['delivery_fee'] ?? 0);
    $total = (float) ($cart['total'] ?? 0);

    $restaurantDeliveryFee = (float) ($restaurant?->delivery_fee ?? 0);
    $minimumOrderAmount = (float) ($restaurant?->minimum_order_amount ?? 0);

    $suggestedItems = collect($suggestions ?? []);
    $canPlaceOrder = $isOpen && $cartItemCount > 0;
@endphp

<main
    x-data="{ submitting: false }"
    class="min-h-screen bg-[var(--color-surface-warm)] pb-28 lg:pb-0"
>
    <section class="py-4 sm:py-8 lg:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Mobile Top Bar --}}
            <div class="mb-5 flex items-center justify-between lg:hidden">
                <a
                    href="{{ route('cart.index') }}"
                    class="grid h-11 w-11 place-items-center rounded-full border border-orange-100 bg-white text-slate-700 shadow-sm transition active:scale-95"
                    aria-label="Return to cart"
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

                <div class="text-center">
                    <p class="text-sm font-black text-slate-950">
                        Checkout
                    </p>

                    <p class="mt-0.5 text-[10px] font-semibold text-slate-500">
                        Secure cash-on-delivery order
                    </p>
                </div>

                <span class="grid h-11 w-11 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <rect x="5" y="10" width="14" height="11" rx="2" />
                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                    </svg>
                </span>
            </div>

            {{-- Desktop Header --}}
            <header class="mb-6 hidden lg:block">
                <div class="flex items-end justify-between gap-8">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
                            Checkout
                        </p>

                        <h1 class="mt-2 text-4xl font-black tracking-tight text-slate-950">
                            Complete your order
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-slate-600">
                            Confirm your contact and delivery information before placing your order.
                        </p>
                    </div>

                    <a
                        href="{{ route('cart.index') }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50"
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

                        Edit Cart
                    </a>
                </div>

                {{-- Desktop Checkout Progress --}}
                <div class="mt-7 grid grid-cols-3 overflow-hidden rounded-2xl border border-orange-100 bg-white shadow-sm">
                    <div class="flex items-center gap-3 border-r border-orange-100 px-5 py-4">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-emerald-500 text-white">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                class="h-4 w-4"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m5 12 4 4L19 6"
                                />
                            </svg>
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-emerald-600">
                                Completed
                            </p>

                            <p class="mt-0.5 text-sm font-black text-slate-950">
                                Shopping cart
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 border-r border-orange-100 bg-orange-50 px-5 py-4">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-orange-600 text-xs font-black text-white">
                            2
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-orange-600">
                                Current step
                            </p>

                            <p class="mt-0.5 text-sm font-black text-slate-950">
                                Delivery details
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 px-5 py-4">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-400">
                            3
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Final step
                            </p>

                            <p class="mt-0.5 text-sm font-black text-slate-600">
                                Order confirmation
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Compact Mobile Heading --}}
            <header class="mb-5 lg:hidden">
                <h1 class="text-2xl font-black tracking-tight text-slate-950">
                    Complete your order
                </h1>

                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                    Confirm where the rider should deliver your food.
                </p>
            </header>

            {{-- Restaurant Information Strip --}}
            @if ($restaurant)
                <section class="mb-5 grid grid-cols-2 divide-x divide-orange-100 rounded-2xl border border-orange-100 bg-white shadow-sm sm:grid-cols-4">
                    <div class="px-3 py-3.5 sm:px-4">
                        <p class="text-[8px] font-black uppercase tracking-[0.12em] text-slate-400 sm:text-[10px]">
                            Restaurant
                        </p>

                        <div class="mt-1 flex items-center gap-1.5">
                            <span
                                @class([
                                    'h-2 w-2 rounded-full',
                                    'animate-pulse bg-emerald-500' => $isOpen,
                                    'bg-amber-500' => ! $isOpen,
                                ])
                            ></span>

                            <p
                                @class([
                                    'truncate text-xs font-black sm:text-sm',
                                    'text-emerald-700' => $isOpen,
                                    'text-amber-700' => ! $isOpen,
                                ])
                            >
                                {{ $isOpen ? 'Open now' : 'Closed' }}
                            </p>
                        </div>
                    </div>

                    <div class="px-3 py-3.5 sm:px-4">
                        <p class="text-[8px] font-black uppercase tracking-[0.12em] text-slate-400 sm:text-[10px]">
                            Delivery
                        </p>

                        <p class="mt-1 text-xs font-black text-slate-950 sm:text-sm">
                            Rs. {{ number_format($restaurantDeliveryFee, 0) }}
                        </p>
                    </div>

                    <div class="hidden px-4 py-3.5 sm:block">
                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                            Minimum Order
                        </p>

                        <p class="mt-1 text-sm font-black text-slate-950">
                            Rs. {{ number_format($minimumOrderAmount, 0) }}
                        </p>
                    </div>

                    <div class="hidden px-4 py-3.5 sm:block">
                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                            Payment
                        </p>

                        <p class="mt-1 text-sm font-black text-emerald-700">
                            Cash on Delivery
                        </p>
                    </div>
                </section>
            @endif

            {{-- Restaurant Closed --}}
            @if (! $isOpen)
                <div
                    role="alert"
                    class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm"
                >
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
                                Checkout is currently unavailable
                            </p>

                            <p class="mt-1 text-xs font-semibold leading-5 text-amber-800 sm:text-sm">
                                The restaurant is closed. Your cart remains saved until ordering becomes available.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Validation Alert --}}
            @if ($errors->any())
                <div
                    role="alert"
                    class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm"
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
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 8v5M12 17h.01" />
                            </svg>
                        </span>

                        <div>
                            <p class="text-sm font-black text-red-900">
                                Some information needs your attention
                            </p>

                            <p class="mt-1 text-xs font-semibold leading-5 text-red-700 sm:text-sm">
                                Review the highlighted fields before placing your order.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form
                id="checkout-form"
                action="{{ route('checkout.store') }}"
                method="POST"
                class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_400px] lg:items-start lg:gap-8"
                x-on:submit="submitting = true"
            >
                @csrf

                {{-- Checkout Form --}}
                <div class="min-w-0 space-y-5">
                    {{-- Mobile Order Review --}}
                    <details class="group overflow-hidden rounded-2xl border border-orange-100 bg-white shadow-sm lg:hidden">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                                        <path d="M9 7h6M9 11h6M9 15h3" />
                                    </svg>
                                </span>

                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-950">
                                        Order summary
                                    </p>

                                    <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                        {{ $cartItemCount }}
                                        {{ $cartItemCount === 1 ? 'item' : 'items' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <span class="text-base font-black text-orange-600">
                                    Rs. {{ number_format($total, 0) }}
                                </span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5 text-slate-400 transition group-open:rotate-180"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m6 9 6 6 6-6"
                                    />
                                </svg>
                            </div>
                        </summary>

                        <div class="border-t border-orange-100 p-4">
                            <div class="max-h-64 space-y-2 overflow-y-auto overscroll-contain">
                                @foreach ($cartItems as $item)
                                    <div class="flex items-start justify-between gap-3 rounded-xl bg-slate-50 p-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start gap-2">
                                                <span class="grid h-7 min-w-7 shrink-0 place-items-center rounded-lg bg-white px-1 text-xs font-black text-orange-600 shadow-sm">
                                                    {{ $item['quantity'] }}
                                                </span>

                                                <div class="min-w-0">
                                                    <p class="line-clamp-2 text-sm font-black leading-5 text-slate-950">
                                                        {{ $item['name'] }}
                                                    </p>

                                                    @if (! empty($item['size_name']))
                                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                                            {{ $item['size_name'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <p class="shrink-0 text-sm font-black text-slate-950">
                                            Rs. {{ number_format($item['total'], 0) }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 space-y-3 border-t border-orange-100 pt-4 text-sm">
                                <div class="flex justify-between gap-4">
                                    <span class="font-semibold text-slate-500">
                                        Subtotal
                                    </span>

                                    <span class="font-black text-slate-950">
                                        Rs. {{ number_format($subtotal, 0) }}
                                    </span>
                                </div>

                                <div class="flex justify-between gap-4">
                                    <span class="font-semibold text-slate-500">
                                        Delivery fee
                                    </span>

                                    <span class="font-black text-slate-950">
                                        Rs. {{ number_format($deliveryFeeAmount, 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </details>

                    {{-- Contact Information --}}
                    <section class="overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white shadow-sm sm:rounded-[2rem]">
                        <div class="border-b border-orange-100 p-4 sm:p-6">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600 sm:h-12 sm:w-12 sm:rounded-2xl">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5 sm:h-6 sm:w-6"
                                    >
                                        <circle cx="12" cy="8" r="4" />
                                        <path d="M4 21a8 8 0 0 1 16 0" />
                                    </svg>
                                </span>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                                        Contact Information
                                    </p>

                                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950 sm:text-2xl">
                                        Who should the rider contact?
                                    </h2>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500 sm:text-sm">
                                        Use a name and phone number that will be reachable during delivery.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-5 p-4 sm:grid-cols-2 sm:p-6">
                            {{-- Name --}}
                            <div>
                                <label
                                    for="customer_name"
                                    class="block text-sm font-black text-slate-800"
                                >
                                    Full name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="customer_name"
                                    name="customer_name"
                                    type="text"
                                    value="{{ old('customer_name', $user?->name) }}"
                                    required
                                    autocomplete="name"
                                    placeholder="Enter your full name"
                                    @class([
                                        'mt-2 min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-base font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 sm:rounded-2xl sm:text-sm',
                                        'border-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100' => $errors->has('customer_name'),
                                        'border-slate-200 focus:border-orange-400 focus:ring-4 focus:ring-orange-100' => ! $errors->has('customer_name'),
                                    ])
                                >

                                @error('customer_name')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label
                                    for="customer_phone"
                                    class="block text-sm font-black text-slate-800"
                                >
                                    Phone number
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="customer_phone"
                                    name="customer_phone"
                                    type="tel"
                                    inputmode="tel"
                                    value="{{ old('customer_phone', $user?->phone) }}"
                                    required
                                    autocomplete="tel"
                                    placeholder="03XX XXXXXXX"
                                    @class([
                                        'mt-2 min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-base font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 sm:rounded-2xl sm:text-sm',
                                        'border-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100' => $errors->has('customer_phone'),
                                        'border-slate-200 focus:border-orange-400 focus:ring-4 focus:ring-orange-100' => ! $errors->has('customer_phone'),
                                    ])
                                >

                                <p class="mt-2 text-xs font-semibold text-slate-400">
                                    The rider may call when nearby.
                                </p>

                                @error('customer_phone')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="sm:col-span-2">
                                <div class="flex items-center justify-between gap-3">
                                    <label
                                        for="customer_email"
                                        class="block text-sm font-black text-slate-800"
                                    >
                                        Email address
                                    </label>

                                    <span class="text-xs font-semibold text-slate-400">
                                        Optional
                                    </span>
                                </div>

                                <input
                                    id="customer_email"
                                    name="customer_email"
                                    type="email"
                                    inputmode="email"
                                    value="{{ old('customer_email', $user?->email) }}"
                                    autocomplete="email"
                                    placeholder="you@example.com"
                                    @class([
                                        'mt-2 min-h-12 w-full rounded-xl border bg-white px-4 py-3 text-base font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 sm:rounded-2xl sm:text-sm',
                                        'border-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100' => $errors->has('customer_email'),
                                        'border-slate-200 focus:border-orange-400 focus:ring-4 focus:ring-orange-100' => ! $errors->has('customer_email'),
                                    ])
                                >

                                @error('customer_email')
                                    <p class="mt-2 text-xs font-semibold text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    {{-- Delivery Address --}}
                    <section class="overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white shadow-sm sm:rounded-[2rem]">
                        <div class="border-b border-orange-100 p-4 sm:p-6">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600 sm:h-12 sm:w-12 sm:rounded-2xl">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5 sm:h-6 sm:w-6"
                                    >
                                        <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                        <circle cx="12" cy="9" r="2.5" />
                                    </svg>
                                </span>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600">
                                        Delivery Address
                                    </p>

                                    <h2 class="mt-1 text-lg font-black tracking-tight text-slate-950 sm:text-2xl">
                                        Where should we deliver?
                                    </h2>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500 sm:text-sm">
                                        Include your house number, street, area, city, and a nearby landmark.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6">
                            <label
                                for="delivery_address"
                                class="block text-sm font-black text-slate-800"
                            >
                                Complete address
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                id="delivery_address"
                                name="delivery_address"
                                rows="4"
                                required
                                autocomplete="street-address"
                                placeholder="House number, street, area, city and nearby landmark"
                                @class([
                                    'mt-2 w-full resize-y rounded-xl border bg-white px-4 py-3 text-base font-semibold leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 sm:rounded-2xl sm:text-sm',
                                    'border-red-300 focus:border-red-400 focus:ring-4 focus:ring-red-100' => $errors->has('delivery_address'),
                                    'border-slate-200 focus:border-orange-400 focus:ring-4 focus:ring-orange-100' => ! $errors->has('delivery_address'),
                                ])
                            >{{ old('delivery_address') }}</textarea>

                            @error('delivery_address')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            {{-- Optional Instructions --}}
                            <details
                                @if (old('order_notes') || $errors->has('order_notes'))
                                    open
                                @endif
                                class="group mt-4 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 sm:rounded-2xl"
                            >
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-3.5">
                                    <div>
                                        <p class="text-sm font-black text-slate-800">
                                            Delivery instructions
                                        </p>

                                        <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                            Optional notes for the restaurant or rider
                                        </p>
                                    </div>

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5 shrink-0 text-slate-400 transition group-open:rotate-180"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m6 9 6 6 6-6"
                                        />
                                    </svg>
                                </summary>

                                <div class="border-t border-slate-200 p-4">
                                    <label
                                        for="order_notes"
                                        class="sr-only"
                                    >
                                        Order notes
                                    </label>

                                    <textarea
                                        id="order_notes"
                                        name="order_notes"
                                        rows="3"
                                        placeholder="Example: Call when outside, apartment 4B, no onions..."
                                        class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-base font-semibold leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-100 sm:text-sm"
                                    >{{ old('order_notes') }}</textarea>

                                    @error('order_notes')
                                        <p class="mt-2 text-xs font-semibold text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </details>
                        </div>
                    </section>

                    {{-- Payment --}}
                    <section class="rounded-[1.5rem] border border-emerald-100 bg-white p-4 shadow-sm sm:rounded-[2rem] sm:p-6">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 sm:h-12 sm:w-12 sm:rounded-2xl">
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

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-base font-black text-slate-950 sm:text-lg">
                                        Cash on Delivery
                                    </h2>

                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] text-emerald-700">
                                        Selected
                                    </span>
                                </div>

                                <p class="mt-1 text-xs font-semibold leading-5 text-slate-500 sm:text-sm">
                                    Pay the rider in cash after receiving your order.
                                </p>
                            </div>

                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                checked
                                class="mt-1 h-5 w-5 shrink-0 border-emerald-300 text-emerald-600 focus:ring-emerald-500"
                            >
                        </div>

                        @error('payment_method')
                            <p class="mt-2 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </section>

                    {{-- Suggestions --}}
                    @if ($suggestedItems->isNotEmpty())
                        <details class="group overflow-hidden rounded-[1.5rem] border border-orange-100 bg-white shadow-sm">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4 sm:p-5">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600">
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
                                    </span>

                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-slate-950">
                                            Add something extra
                                        </p>

                                        <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                            Optional drinks and desserts
                                        </p>
                                    </div>
                                </div>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5 shrink-0 text-slate-400 transition group-open:rotate-180"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m6 9 6 6 6-6"
                                    />
                                </svg>
                            </summary>

                            <div class="space-y-3 border-t border-orange-100 p-4 sm:grid sm:grid-cols-2 sm:gap-3 sm:space-y-0">
                                @foreach ($suggestedItems as $suggestion)
                                    <a
                                        href="{{ route('menu.show', $suggestion) }}"
                                        class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 transition active:scale-[0.99] hover:border-orange-200 hover:bg-orange-50"
                                    >
                                        <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-gradient-to-br from-orange-100 to-red-100">
                                            @if ($suggestion->image_url)
                                                <img
                                                    src="{{ $suggestion->image_url }}"
                                                    alt="{{ $suggestion->name }}"
                                                    class="h-full w-full object-cover"
                                                    loading="lazy"
                                                >
                                            @else
                                                <span class="text-lg font-black text-orange-600">
                                                    {{ mb_substr($suggestion->name, 0, 1) }}
                                                </span>
                                            @endif
                                        </span>

                                        <span class="min-w-0 flex-1">
                                            <span class="line-clamp-1 block text-sm font-black text-slate-950">
                                                {{ $suggestion->name }}
                                            </span>

                                            <span class="mt-1 block text-sm font-black text-orange-600">
                                                Rs. {{ number_format($suggestion->price, 0) }}
                                            </span>
                                        </span>

                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white text-orange-600 shadow-sm">
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
                                                    d="m9 18 6-6-6-6"
                                                />
                                            </svg>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    {{-- Mobile Confirmation --}}
                    <div class="flex items-start gap-3 rounded-2xl bg-blue-50 p-4 lg:hidden">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-blue-600 shadow-sm">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 11v5M12 8h.01" />
                            </svg>
                        </span>

                        <p class="text-xs font-semibold leading-5 text-blue-800">
                            By placing your order, you confirm that the phone number and delivery address are correct.
                        </p>
                    </div>
                </div>

                {{-- Desktop Order Summary --}}
                <aside class="hidden lg:sticky lg:top-24 lg:block">
                    <div class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-xl shadow-orange-900/5">
                        <div class="border-b border-orange-100 p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                                        Order Summary
                                    </p>

                                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                                        Review your order
                                    </h2>

                                    <p class="mt-1 text-sm font-semibold text-slate-500">
                                        {{ $cartItemCount }}
                                        {{ $cartItemCount === 1 ? 'item' : 'items' }}
                                    </p>
                                </div>

                                <a
                                    href="{{ route('cart.index') }}"
                                    class="shrink-0 rounded-xl bg-orange-50 px-3 py-2 text-xs font-black text-orange-700 transition hover:bg-orange-100"
                                >
                                    Edit
                                </a>
                            </div>
                        </div>

                        <div class="max-h-[330px] space-y-2 overflow-y-auto p-4">
                            @foreach ($cartItems as $item)
                                <div class="flex items-start justify-between gap-3 rounded-xl bg-slate-50 p-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start gap-2">
                                            <span class="grid h-7 min-w-7 shrink-0 place-items-center rounded-lg bg-white px-1 text-xs font-black text-orange-600 shadow-sm">
                                                {{ $item['quantity'] }}
                                            </span>

                                            <div class="min-w-0">
                                                <p class="line-clamp-2 text-sm font-black leading-5 text-slate-950">
                                                    {{ $item['name'] }}
                                                </p>

                                                @if (! empty($item['size_name']))
                                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                                        {{ $item['size_name'] }}
                                                    </p>
                                                @endif

                                                @if (! empty($item['addons']))
                                                    <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">
                                                        {{ collect($item['addons'])->pluck('name')->join(', ') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <p class="shrink-0 text-sm font-black text-slate-950">
                                        Rs. {{ number_format($item['total'], 0) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-orange-100 p-6">
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between gap-4">
                                    <span class="font-semibold text-slate-500">
                                        Subtotal
                                    </span>

                                    <span class="font-black text-slate-950">
                                        Rs. {{ number_format($subtotal, 0) }}
                                    </span>
                                </div>

                                <div class="flex justify-between gap-4">
                                    <span class="font-semibold text-slate-500">
                                        Delivery fee
                                    </span>

                                    <span class="font-black text-slate-950">
                                        Rs. {{ number_format($deliveryFeeAmount, 0) }}
                                    </span>
                                </div>

                                <div class="border-t border-orange-100 pt-4">
                                    <div class="flex items-end justify-between gap-4">
                                        <span class="font-black text-slate-950">
                                            Total
                                        </span>

                                        <span class="text-2xl font-black text-orange-600">
                                            Rs. {{ number_format($total, 0) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 flex items-start gap-3 rounded-2xl bg-emerald-50 p-4">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                                >
                                    <rect x="5" y="10" width="14" height="11" rx="2" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>

                                <div>
                                    <p class="text-sm font-black text-emerald-900">
                                        Cash on Delivery
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-emerald-700">
                                        Payment will be collected when your food arrives.
                                    </p>
                                </div>
                            </div>

                            @if ($canPlaceOrder)
                                <button
                                    type="submit"
                                    x-bind:disabled="submitting"
                                    class="mt-5 inline-flex min-h-14 w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-4 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-70"
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

                                    <span x-text="submitting ? 'Placing order...' : 'Place Order'"></span>

                                    <svg
                                        x-show="! submitting"
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
                                            d="m9 18 6-6-6-6"
                                        />
                                    </svg>
                                </button>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="mt-5 inline-flex min-h-14 w-full cursor-not-allowed items-center justify-center rounded-2xl bg-slate-200 px-5 py-4 text-sm font-black text-slate-500"
                                >
                                    Restaurant Closed
                                </button>
                            @endif

                            <p class="mt-3 text-center text-xs font-semibold leading-5 text-slate-500">
                                Please review your contact information before placing the order.
                            </p>
                        </div>
                    </div>
                </aside>
            </form>
        </div>
    </section>

    {{-- Mobile Persistent Action --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_rgba(15,23,42,0.14)] backdrop-blur lg:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <div class="min-w-0 shrink-0">
                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Total
                </p>

                <p class="mt-0.5 whitespace-nowrap text-lg font-black text-slate-950">
                    Rs. {{ number_format($total, 0) }}
                </p>
            </div>

            @if ($canPlaceOrder)
                <button
                    type="submit"
                    form="checkout-form"
                    x-bind:disabled="submitting"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
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

                    <span x-text="submitting ? 'Placing...' : 'Place Order'"></span>

                    <svg
                        x-show="! submitting"
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
                            d="m9 18 6-6-6-6"
                        />
                    </svg>
                </button>
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
</main>

@endcomponent
