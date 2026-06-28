@component('layouts.customer', ['title' => 'Customer Dashboard'])
@php
$user = auth()->user();

    $firstName = \Illuminate\Support\Str::before(
        $user->name ?? 'Customer',
        ' '
    );

    $userInitial = strtoupper(
        substr($user->name ?? 'C', 0, 1)
    );

    $cartCount = \App\Support\Cart::count();

    $deliveryStatus = $latestOrder?->delivery?->status;

    $effectiveStatus = $latestOrder
        ? (
            in_array(
                $deliveryStatus,
                [
                    'assigned',
                    'picked_up',
                    'out_for_delivery',
                    'delivered',
                    'failed',
                ],
                true
            )
                ? $deliveryStatus
                : $latestOrder->order_status
        )
        : null;

    $isActiveOrder = $latestOrder
        && ! in_array(
            $effectiveStatus,
            ['delivered', 'cancelled', 'failed'],
            true
        );

    $statusLabel = $latestOrder
        ? match ($effectiveStatus) {
            'pending' => 'Awaiting Confirmation',
            'accepted' => 'Order Accepted',
            'preparing' => 'Preparing Your Food',
            'ready' => 'Ready for Pickup',
            'assigned', 'assigned_to_rider' => 'Rider Assigned',
            'picked_up' => 'Picked Up',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'failed' => 'Delivery Failed',
            default => \Illuminate\Support\Str::headline(
                $effectiveStatus ?? 'processing'
            ),
        }
        : 'No orders yet';

    $statusMessage = match ($effectiveStatus) {
        'pending' => 'The restaurant is reviewing your order.',
        'accepted' => 'Your order was accepted and will be prepared shortly.',
        'preparing' => 'The restaurant is preparing your food now.',
        'ready' => 'Your food is ready and waiting for rider pickup.',
        'assigned', 'assigned_to_rider' => 'A delivery rider has been assigned.',
        'picked_up' => 'Your rider has collected the order.',
        'out_for_delivery' => 'Your order is on the way to your address.',
        'delivered' => 'Your order was delivered successfully.',
        'cancelled' => 'This order was cancelled.',
        'failed' => 'The delivery could not be completed.',
        default => 'Place an order and track its progress here.',
    };

    $orderProgress = match ($effectiveStatus) {
        'pending' => 10,
        'accepted' => 25,
        'preparing' => 45,
        'ready' => 60,
        'assigned', 'assigned_to_rider' => 72,
        'picked_up' => 84,
        'out_for_delivery' => 92,
        'delivered' => 100,
        default => 0,
    };

    $statusTheme = match (true) {
        in_array($effectiveStatus, ['cancelled', 'failed'], true) => [
            'gradient' => 'from-red-600 via-red-500 to-red-600',
            'badge' => 'border-white/20 bg-white/15 text-white',
            'progress' => 'bg-white',
            'soft' => 'border-red-100 bg-red-50 text-red-700',
        ],

        $effectiveStatus === 'delivered' => [
            'gradient' => 'from-leaf-700 via-leaf-500 to-teal-600',
            'badge' => 'border-white/20 bg-white/15 text-white',
            'progress' => 'bg-white',
            'soft' => 'border-leaf-100 bg-leaf-50 text-leaf-700',
        ],

        in_array(
            $effectiveStatus,
            [
                'assigned',
                'assigned_to_rider',
                'picked_up',
                'out_for_delivery',
            ],
            true
        ) => [
            'gradient' => 'from-blue-600 via-blue-500 to-indigo-600',
            'badge' => 'border-white/20 bg-white/15 text-white',
            'progress' => 'bg-white',
            'soft' => 'border-blue-100 bg-blue-50 text-blue-700',
        ],

        in_array(
            $effectiveStatus,
            ['accepted', 'preparing', 'ready'],
            true
        ) => [
            'gradient' => 'from-gold-500 via-food-tan to-food-brown',
            'badge' => 'border-white/20 bg-white/15 text-white',
            'progress' => 'bg-white',
            'soft' => 'border-gold-100 bg-gold-50 text-food-brown',
        ],

        default => [
            'gradient' => 'from-brand-500 via-brand-600 to-brand-800',
            'badge' => 'border-white/20 bg-white/15 text-white',
            'progress' => 'bg-white',
            'soft' => 'border-warm-200 bg-brand-50 text-brand-600',
        ],
    };
@endphp

<div class="space-y-5 pb-8 sm:space-y-6">
    {{-- Welcome Header --}}
    <header class="relative overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white p-4 shadow-sm sm:p-6 lg:rounded-[1.75rem]">
        <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-brand-100/70 blur-3xl"></div>

        <div class="relative flex items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-lg font-black text-white shadow-lg shadow-brand-500/20 sm:h-14 sm:w-14 sm:text-xl">
                    {{ $userInitial }}
                </div>

                <div class="min-w-0">
                    <p class="text-[9px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                        Welcome back
                    </p>

                    <h1 class="mt-1 truncate text-xl font-black tracking-tight text-warm-950 sm:text-2xl lg:text-3xl">
                        Hi, {{ $firstName }}
                    </h1>

                    <p class="mt-1 hidden text-sm font-semibold text-warm-500 sm:block">
                        Track your delivery or start another order.
                    </p>
                </div>
            </div>

            {{-- Desktop Header Actions --}}
            <div class="hidden shrink-0 items-center gap-3 lg:flex">
                <a
                    href="{{ route('customer.orders') }}"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-white px-5 py-3 text-sm font-black text-brand-600 transition hover:bg-brand-50"
                >
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

                    My Orders
                </a>

                <a
                    href="{{ route('menu') }}"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-600"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M4 3h16v18H4z" />
                        <path d="M8 7h8M8 11h8M8 15h5" />
                    </svg>

                    Browse Menu
                </a>
            </div>

            {{-- Mobile Menu Shortcut --}}
            <a
                href="{{ route('menu') }}"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-500 transition active:scale-95 lg:hidden"
                aria-label="Browse menu"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <path d="M4 3h16v18H4z" />
                    <path d="M8 7h8M8 11h8M8 15h5" />
                </svg>
            </a>
        </div>
    </header>

    {{-- Main Adaptive Dashboard --}}
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start lg:gap-6">
        {{-- Main Column --}}
        <div class="min-w-0 space-y-5">
            {{-- Latest / Active Order --}}
            @if ($latestOrder)
                <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                    {{-- Status Area --}}
                    <div class="relative overflow-hidden bg-gradient-to-br {{ $statusTheme['gradient'] }} p-5 text-white sm:p-6 lg:p-7">
                        <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-24 left-4 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>

                        <div class="relative">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/15 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] backdrop-blur">
                                        @if ($isActiveOrder)
                                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
                                            Live order
                                        @else
                                            Latest order
                                        @endif
                                    </span>
                                </div>

                                <span class="rounded-full border px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] {{ $statusTheme['badge'] }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                            <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                                <div class="min-w-0">
                                    <p class="text-[9px] font-black uppercase tracking-[0.16em] text-white/70">
                                        Order number
                                    </p>

                                    <a
                                        href="{{ route('customer.orders.show', $latestOrder) }}"
                                        class="mt-1 block break-all text-xl font-black tracking-tight transition hover:text-brand-50 sm:text-2xl"
                                    >
                                        {{ $latestOrder->order_number }}
                                    </a>

                                    <h2 class="mt-4 text-2xl font-black tracking-tight sm:text-3xl">
                                        {{ $statusLabel }}
                                    </h2>

                                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-white/90">
                                        {{ $statusMessage }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-3 gap-2 lg:min-w-[300px]">
                                    <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70">
                                            Total
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black">
                                            ((float) ($latestOrder->total ?? 0))
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70">
                                            Payment
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black">
                                            {{ $latestOrder->payment_method_label }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70">
                                            Placed
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black">
                                            {{ $latestOrder->created_at?->format('M d') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if (! in_array($effectiveStatus, ['cancelled', 'failed'], true))
                                <div class="mt-5">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="text-[9px] font-black uppercase tracking-[0.14em] text-white/70">
                                            Order progress
                                        </p>

                                        <p class="text-xs font-black">
                                            {{ $orderProgress }}%
                                        </p>
                                    </div>

                                    <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/20">
                                        <div
                                            class="h-full rounded-full {{ $statusTheme['progress'] }} shadow-sm transition-all duration-500"
                                            style="width: {{ $orderProgress }}%"
                                        ></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Order Actions --}}
                    <div class="p-4 sm:p-5">
                        <div class="grid grid-cols-[1fr_auto] gap-3">
                            <a
                                href="{{ route('customer.orders.show', $latestOrder) }}"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.98] hover:bg-brand-600 sm:rounded-2xl"
                            >
                                {{ $isActiveOrder ? 'Track My Order' : 'View Order Details' }}

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
                            </a>

                            <a
                                href="{{ route('customer.orders') }}"
                                class="grid h-12 w-12 place-items-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-95 hover:bg-brand-100 sm:rounded-2xl"
                                aria-label="View all orders"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M8 6h13M8 12h13M8 18h13" />
                                    <path d="M3 6h.01M3 12h.01M3 18h.01" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </section>
            @else
                {{-- First Order Empty State --}}
                <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm sm:p-7 lg:p-8">
                    <div class="grid gap-6 sm:grid-cols-[auto_minmax(0,1fr)] sm:items-center">
                        <div class="grid h-16 w-16 place-items-center rounded-2xl bg-brand-50 text-brand-500 sm:h-20 sm:w-20">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-7 w-7 sm:h-9 sm:w-9"
                            >
                                <path d="M4 3h16v18H4z" />
                                <path d="M8 7h8M8 11h8M8 15h5" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Start Your First Order
                            </p>

                            <h2 class="mt-2 text-2xl font-black tracking-tight text-warm-950">
                                What would you like to eat?
                            </h2>

                            <p class="mt-2 text-sm font-semibold leading-6 text-warm-600">
                                Browse fresh meals, add your favourites, and pay when your food arrives.
                            </p>

                            <a
                                href="{{ route('menu') }}"
                                class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.98] hover:bg-brand-600 sm:w-auto sm:rounded-2xl"
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
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>
                            </a>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Cart Continuation --}}
            @if ($cartCount > 0)
                <section class="relative overflow-hidden rounded-[1.5rem] border border-brand-200 bg-gradient-to-r from-brand-50 to-brand-100 p-4 shadow-sm sm:p-5">
                    <div class="pointer-events-none absolute -right-12 -top-16 h-40 w-40 rounded-full bg-brand-200/60 blur-3xl"></div>

                    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="relative grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-white text-brand-500 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-5 w-5"
                                >
                                    <path d="M3 4h2l2 11h10l2-8H7" />
                                    <circle cx="9" cy="20" r="1" />
                                    <circle cx="17" cy="20" r="1" />
                                </svg>

                                <span class="absolute -right-1.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full bg-brand-500 px-1 text-[9px] font-black text-white">
                                    {{ $cartCount > 99 ? '99+' : $cartCount }}
                                </span>
                            </span>

                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.16em] text-brand-500">
                                    Cart Saved
                                </p>

                                <h2 class="mt-1 text-base font-black text-warm-950 sm:text-lg">
                                    Continue your unfinished order
                                </h2>

                                <p class="mt-1 text-xs font-semibold text-warm-600">
                                    {{ $cartCount }}
                                    {{ $cartCount === 1 ? 'item is' : 'items are' }}
                                    waiting in your cart.
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('cart.index') }}"
                            class="inline-flex min-h-11 w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.98] hover:bg-brand-600 sm:w-auto"
                        >
                            Review Cart

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
                        </a>
                    </div>
                </section>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-5 lg:sticky lg:top-24">
            {{-- Quick Actions --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                            Quick Actions
                        </p>

                        <h2 class="mt-1 text-lg font-black text-warm-950">
                            What do you need?
                        </h2>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    {{-- Menu --}}
                    <a
                        href="{{ route('menu') }}"
                        class="group flex min-h-[118px] flex-col justify-between rounded-[1.25rem] border border-warm-200 bg-brand-50 p-4 transition active:scale-[0.98] hover:border-brand-200 hover:bg-brand-100"
                    >
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-brand-500 shadow-sm transition group-hover:bg-brand-600 group-hover:text-white">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M4 3h16v18H4z" />
                                <path d="M8 7h8M8 11h8M8 15h5" />
                            </svg>
                        </span>

                        <span>
                            <span class="block text-sm font-black text-warm-950">
                                Browse Menu
                            </span>

                            <span class="mt-1 block text-[10px] font-semibold leading-4 text-warm-500">
                                Find your next meal
                            </span>
                        </span>
                    </a>

                    {{-- Orders --}}
                    <a
                        href="{{ route('customer.orders') }}"
                        class="group flex min-h-[118px] flex-col justify-between rounded-[1.25rem] border border-blue-100 bg-blue-50 p-4 transition active:scale-[0.98] hover:border-blue-200 hover:bg-blue-100"
                    >
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-blue-600 shadow-sm transition group-hover:bg-blue-600 group-hover:text-white">
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

                        <span>
                            <span class="block text-sm font-black text-warm-950">
                                My Orders
                            </span>

                            <span class="mt-1 block text-[10px] font-semibold leading-4 text-warm-500">
                                Track order history
                            </span>
                        </span>
                    </a>

                    {{-- Cart --}}
                    <a
                        href="{{ route('cart.index') }}"
                        class="group relative flex min-h-[118px] flex-col justify-between rounded-[1.25rem] border border-leaf-100 bg-leaf-50 p-4 transition active:scale-[0.98] hover:border-leaf-100 hover:bg-leaf-100"
                    >
                        @if ($cartCount > 0)
                            <span class="absolute right-3 top-3 grid h-6 min-w-6 place-items-center rounded-full bg-leaf-700 px-1 text-[9px] font-black text-white shadow-sm">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif

                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-leaf-700 shadow-sm transition group-hover:bg-leaf-700 group-hover:text-white">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M3 4h2l2 11h10l2-8H7" />
                                <circle cx="9" cy="20" r="1" />
                                <circle cx="17" cy="20" r="1" />
                            </svg>
                        </span>

                        <span>
                            <span class="block text-sm font-black text-warm-950">
                                My Cart
                            </span>

                            <span class="mt-1 block text-[10px] font-semibold leading-4 text-warm-500">
                                Review selected items
                            </span>
                        </span>
                    </a>

                    {{-- Support --}}
                    <a
                        href="{{ route('contact') }}"
                        class="group flex min-h-[118px] flex-col justify-between rounded-[1.25rem] border border-violet-100 bg-violet-50 p-4 transition active:scale-[0.98] hover:border-violet-200 hover:bg-violet-100"
                    >
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-violet-600 shadow-sm transition group-hover:bg-violet-600 group-hover:text-white">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
                            </svg>
                        </span>

                        <span>
                            <span class="block text-sm font-black text-warm-950">
                                Get Support
                            </span>

                            <span class="mt-1 block text-[10px] font-semibold leading-4 text-warm-500">
                                Contact the restaurant
                            </span>
                        </span>
                    </a>
                </div>
            </section>

            {{-- Account Activity --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                            Account Activity
                        </p>

                        <h2 class="mt-1 text-lg font-black text-warm-950">
                            Your overview
                        </h2>
                    </div>

                    <a
                        href="{{ route('customer.orders') }}"
                        class="text-xs font-black text-brand-600 hover:text-brand-800"
                    >
                        History
                    </a>
                </div>

                <div class="mt-4 divide-y divide-warm-100">
                    <div class="flex items-center justify-between gap-4 py-3 first:pt-0">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                                </svg>
                            </span>

                            <div>
                                <p class="text-sm font-black text-warm-950">
                                    Total orders
                                </p>

                                <p class="mt-0.5 text-xs font-semibold text-warm-500">
                                    All completed and active orders
                                </p>
                            </div>
                        </div>

                        <p class="text-xl font-black text-warm-950">
                            {{ $totalOrders }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="M3 7h11v10H3z" />
                                    <path d="M14 10h4l3 3v4h-7z" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-sm font-black text-warm-950">
                                    Latest status
                                </p>

                                <p class="mt-0.5 truncate text-xs font-semibold text-warm-500">
                                    {{ $latestOrder ? $statusLabel : 'No order placed' }}
                                </p>
                            </div>
                        </div>

                        @if ($latestOrder)
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $statusTheme['soft'] }}"></span>
                        @else
                            <span class="text-sm font-black text-warm-500">
                                —
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-4 py-3 last:pb-0">
                        <div class="flex items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-leaf-50 text-leaf-700">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="M3 4h2l2 11h10l2-8H7" />
                                    <circle cx="9" cy="20" r="1" />
                                    <circle cx="17" cy="20" r="1" />
                                </svg>
                            </span>

                            <div>
                                <p class="text-sm font-black text-warm-950">
                                    Cart items
                                </p>

                                <p class="mt-0.5 text-xs font-semibold text-warm-500">
                                    Items waiting for checkout
                                </p>
                            </div>
                        </div>

                        <p class="text-xl font-black text-warm-950">
                            {{ $cartCount }}
                        </p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>

@endcomponent
