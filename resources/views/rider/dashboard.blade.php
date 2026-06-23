@component('layouts.rider', ['title' => 'Rider Dashboard'])
@php
$riderName = auth()->user()->name ?? 'Rider';

    $firstName = \Illuminate\Support\Str::before(
        $riderName,
        ' '
    );

    $completionRate = $totalAssignedOrders > 0
        ? min(
            100,
            round(
                ($deliveredOrders / $totalAssignedOrders) * 100
            )
        )
        : 0;

    /*
     * Support both collections and paginator results.
     */
    $ordersCollection = $latestOrders instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($latestOrders->items())
        : collect($latestOrders);

    /*
     * Active delivery priority:
     * 1. Out for delivery
     * 2. Picked up
     * 3. Assigned to rider
     * 4. Ready for pickup
     * 5. Preparing
     * 6. Accepted
     * 7. Pending
     */
    $priorityOrders = $ordersCollection
        ->sortBy(function ($order) {
            $deliveryStatus = $order->delivery?->status ?? 'assigned';

            return match (true) {
                $deliveryStatus === 'out_for_delivery' => 1,
                $deliveryStatus === 'picked_up' => 2,

                in_array(
                    $deliveryStatus,
                    ['assigned', 'assigned_to_rider'],
                    true
                ) => 3,

                $order->order_status === 'ready' => 4,
                $order->order_status === 'preparing' => 5,
                $order->order_status === 'accepted' => 6,
                $order->order_status === 'pending' => 7,

                $deliveryStatus === 'delivered'
                    || $order->order_status === 'delivered' => 90,

                $deliveryStatus === 'failed'
                    || $order->order_status === 'cancelled' => 99,

                default => 50,
            };
        })
        ->values();

    $activeOrders = $priorityOrders
        ->filter(function ($order) {
            $deliveryStatus = $order->delivery?->status ?? 'assigned';

            return ! in_array(
                $deliveryStatus,
                ['delivered', 'failed'],
                true
            ) && ! in_array(
                $order->order_status,
                ['delivered', 'cancelled'],
                true
            );
        })
        ->values();

    $closedOrders = $priorityOrders
        ->reject(function ($order) {
            $deliveryStatus = $order->delivery?->status ?? 'assigned';

            return ! in_array(
                $deliveryStatus,
                ['delivered', 'failed'],
                true
            ) && ! in_array(
                $order->order_status,
                ['delivered', 'cancelled'],
                true
            );
        })
        ->take(4)
        ->values();

    $nextOrder = $activeOrders->first();

    $queueOrders = $activeOrders
        ->skip(1)
        ->take(5)
        ->values();

    $remainingQueueCount = max(
        0,
        $activeOrders->count() - 6
    );

    /*
     * Centralized visual and content configuration for order states.
     */
    $getOrderUi = function ($order): array {
        $deliveryStatus = $order->delivery?->status ?? 'assigned';

        $isDelivered = $deliveryStatus === 'delivered'
            || $order->order_status === 'delivered';

        $isFailed = $deliveryStatus === 'failed'
            || $order->order_status === 'cancelled';

        return match (true) {
            $deliveryStatus === 'out_for_delivery' => [
                'label' => 'Deliver Now',
                'message' => 'Customer is waiting for this delivery.',
                'badge' => 'border-blue-200 bg-blue-50 text-blue-700',
                'accent' => 'bg-blue-500',
                'icon' => 'bg-blue-50 text-blue-600',
                'button' => 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20',
                'is_completed' => false,
                'is_failed' => false,
            ],

            $deliveryStatus === 'picked_up' => [
                'label' => 'Picked Up',
                'message' => 'Order collected. Deliver to the customer next.',
                'badge' => 'border-cyan-200 bg-cyan-50 text-cyan-700',
                'accent' => 'bg-cyan-500',
                'icon' => 'bg-cyan-50 text-cyan-600',
                'button' => 'bg-cyan-600 hover:bg-cyan-700 shadow-cyan-600/20',
                'is_completed' => false,
                'is_failed' => false,
            ],

            in_array(
                $deliveryStatus,
                ['assigned', 'assigned_to_rider'],
                true
            ) => [
                'label' => 'Pickup Required',
                'message' => 'Review the order and prepare for restaurant pickup.',
                'badge' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
                'accent' => 'bg-indigo-500',
                'icon' => 'bg-indigo-50 text-indigo-600',
                'button' => 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/20',
                'is_completed' => false,
                'is_failed' => false,
            ],

            $order->order_status === 'ready' => [
                'label' => 'Ready for Pickup',
                'message' => 'The restaurant has prepared this order.',
                'badge' => 'border-violet-200 bg-violet-50 text-violet-700',
                'accent' => 'bg-violet-500',
                'icon' => 'bg-violet-50 text-violet-600',
                'button' => 'bg-violet-600 hover:bg-violet-700 shadow-violet-600/20',
                'is_completed' => false,
                'is_failed' => false,
            ],

            $order->order_status === 'preparing' => [
                'label' => 'Preparing',
                'message' => 'The restaurant is still preparing this order.',
                'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
                'accent' => 'bg-amber-500',
                'icon' => 'bg-amber-50 text-amber-600',
                'button' => 'bg-orange-600 hover:bg-orange-700 shadow-orange-600/20',
                'is_completed' => false,
                'is_failed' => false,
            ],

            $order->order_status === 'accepted' => [
                'label' => 'Accepted',
                'message' => 'The restaurant accepted the order.',
                'badge' => 'border-orange-200 bg-orange-50 text-orange-700',
                'accent' => 'bg-orange-500',
                'icon' => 'bg-orange-50 text-orange-600',
                'button' => 'bg-orange-600 hover:bg-orange-700 shadow-orange-600/20',
                'is_completed' => false,
                'is_failed' => false,
            ],

            $isDelivered => [
                'label' => 'Delivered',
                'message' => 'Delivery completed successfully.',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'accent' => 'bg-emerald-500',
                'icon' => 'bg-emerald-50 text-emerald-600',
                'button' => 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20',
                'is_completed' => true,
                'is_failed' => false,
            ],

            $isFailed => [
                'label' => 'Closed',
                'message' => 'Delivery failed or the order was cancelled.',
                'badge' => 'border-red-200 bg-red-50 text-red-700',
                'accent' => 'bg-red-500',
                'icon' => 'bg-red-50 text-red-600',
                'button' => 'bg-slate-700 hover:bg-slate-800 shadow-slate-700/20',
                'is_completed' => false,
                'is_failed' => true,
            ],

            default => [
                'label' => 'Assigned',
                'message' => 'Review the delivery information.',
                'badge' => 'border-slate-200 bg-slate-50 text-slate-700',
                'accent' => 'bg-slate-400',
                'icon' => 'bg-slate-100 text-slate-600',
                'button' => 'bg-orange-600 hover:bg-orange-700 shadow-orange-600/20',
                'is_completed' => false,
                'is_failed' => false,
            ],
        };
    };
@endphp

<div class="space-y-5 pb-24 sm:space-y-6 lg:pb-8">
    {{-- Dashboard Header --}}
    <header class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-5 text-white shadow-xl shadow-slate-950/15 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-orange-500/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-8 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl border border-white/15 bg-white/10 text-xl font-black shadow-lg backdrop-blur sm:h-16 sm:w-16 sm:text-2xl">
                    {{ mb_strtoupper(mb_substr($riderName, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-3.5 w-3.5 text-orange-300"
                            >
                                <path d="M3 7h11v10H3z" />
                                <path d="M14 10h4l3 3v4h-7z" />
                            </svg>

                            Delivery workspace
                        </span>

                        @if ($activeDeliveries > 0)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-500 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em]">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>

                                {{ $activeDeliveries }} active
                            </span>
                        @else
                            <span class="rounded-full bg-emerald-500/20 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] text-emerald-200">
                                Queue clear
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-3 truncate text-2xl font-black tracking-tight sm:text-4xl">
                        Welcome, {{ $firstName }}
                    </h1>

                    <p class="mt-1.5 max-w-2xl text-xs font-semibold leading-5 text-slate-300 sm:text-sm sm:leading-6">
                        Focus on the next delivery, contact customers quickly, and keep every status accurate.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                <a
                    href="{{ route('rider.orders') }}"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-950/30 transition active:scale-[0.98] hover:bg-orange-500 sm:rounded-2xl sm:px-5"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M3 7h11v10H3z" />
                        <path d="M14 10h4l3 3v4h-7z" />
                        <circle cx="7" cy="18" r="2" />
                        <circle cx="18" cy="18" r="2" />
                    </svg>

                    Deliveries
                </a>

                <a
                    href="{{ route('home') }}"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-black text-white backdrop-blur transition active:scale-[0.98] hover:bg-white/20 sm:rounded-2xl sm:px-5"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M3 11 12 3l9 8" />
                        <path d="M5 10v10h14V10" />
                    </svg>

                    Website
                </a>
            </div>
        </div>
    </header>

    {{-- Compact Metrics --}}
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        {{-- Active --}}
        <article class="rounded-[1.35rem] border border-orange-100 bg-orange-50 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-[0.13em] text-orange-700">
                        Active
                    </p>

                    <p class="mt-2 text-3xl font-black tracking-tight text-orange-950">
                        {{ $activeDeliveries }}
                    </p>

                    <p class="mt-1 text-[10px] font-semibold text-orange-700">
                        Requiring action
                    </p>
                </div>

                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M3 7h11v10H3z" />
                        <path d="M14 10h4l3 3v4h-7z" />
                    </svg>
                </span>
            </div>
        </article>

        {{-- Assigned --}}
        <article class="rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-[0.13em] text-slate-400">
                        Assigned
                    </p>

                    <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        {{ $totalAssignedOrders }}
                    </p>

                    <p class="mt-1 text-[10px] font-semibold text-slate-500">
                        Total deliveries
                    </p>
                </div>

                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">
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
            </div>
        </article>

        {{-- Delivered --}}
        <article class="rounded-[1.35rem] border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-[0.13em] text-emerald-700">
                        Delivered
                    </p>

                    <p class="mt-2 text-3xl font-black tracking-tight text-emerald-950">
                        {{ $deliveredOrders }}
                    </p>

                    <p class="mt-1 text-[10px] font-semibold text-emerald-700">
                        Completed successfully
                    </p>
                </div>

                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-emerald-600 shadow-sm">
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
                            stroke-linejoin="round"
                            d="m5 12 4 4L19 6"
                        />
                    </svg>
                </span>
            </div>
        </article>

        {{-- Failed --}}
        <article class="rounded-[1.35rem] border border-red-100 bg-red-50 p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-[0.13em] text-red-700">
                        Failed
                    </p>

                    <p class="mt-2 text-3xl font-black tracking-tight text-red-950">
                        {{ $failedDeliveries }}
                    </p>

                    <p class="mt-1 text-[10px] font-semibold text-red-700">
                        Needs review
                    </p>
                </div>

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
                        <path
                            stroke-linecap="round"
                            d="m9 9 6 6m0-6-6 6"
                        />
                    </svg>
                </span>
            </div>
        </article>
    </section>

    {{-- Main Command Centre --}}
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start lg:gap-6">
        {{-- Delivery Workspace --}}
        <div class="min-w-0 space-y-5">
            @if ($nextOrder)
                @php
                    $nextUi = $getOrderUi($nextOrder);

                    $nextPhone = $nextOrder->customer_phone
                        ? preg_replace(
                            '/[^0-9+]/',
                            '',
                            $nextOrder->customer_phone
                        )
                        : null;

                    $nextMapsUrl = 'https://www.google.com/maps/search/?api=1&query='
                        . rawurlencode($nextOrder->delivery_address ?? '');

                    $nextDeliveryStatus = $nextOrder->delivery?->status
                        ?? 'assigned';
                @endphp

                {{-- Next Delivery --}}
                <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                    <div class="border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                                        Next Delivery
                                    </p>

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] text-red-700">
                                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-500"></span>

                                        Priority
                                    </span>
                                </div>

                                <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                                    Handle this order first
                                </h2>
                            </div>

                            <a
                                href="{{ route('rider.orders') }}"
                                class="hidden shrink-0 text-xs font-black text-orange-700 hover:text-orange-800 sm:inline-flex"
                            >
                                View queue
                            </a>
                        </div>
                    </div>

                    <div class="relative p-4 sm:p-6">
                        <div class="absolute inset-y-0 left-0 w-1.5 {{ $nextUi['accent'] }}"></div>

                        <div class="pl-2 sm:pl-3">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <span class="inline-flex rounded-full border px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] {{ $nextUi['badge'] }}">
                                        {{ $nextUi['label'] }}
                                    </span>

                                    <a
                                        href="{{ route('rider.orders.show', $nextOrder) }}"
                                        class="mt-2 block break-all text-lg font-black tracking-tight text-slate-950 transition hover:text-orange-700 sm:text-xl"
                                    >
                                        {{ $nextOrder->order_number }}
                                    </a>
                                </div>

                                <x-status-badge
                                    :status="$nextDeliveryStatus"
                                    type="delivery"
                                />
                            </div>

                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                {{ $nextUi['message'] }}
                            </p>

                            <div class="mt-5 grid gap-3 md:grid-cols-2">
                                {{-- Customer --}}
                                <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-sm font-black text-orange-700 shadow-sm">
                                        {{ mb_strtoupper(
                                            mb_substr(
                                                $nextOrder->customer_name ?? 'C',
                                                0,
                                                1
                                            )
                                        ) }}
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-black text-slate-950">
                                            {{ $nextOrder->customer_name }}
                                        </p>

                                        @if ($nextOrder->customer_phone)
                                            <p class="mt-0.5 truncate text-xs font-semibold text-slate-500">
                                                {{ $nextOrder->customer_phone }}
                                            </p>
                                        @else
                                            <p class="mt-0.5 text-xs font-semibold text-slate-400">
                                                No phone provided
                                            </p>
                                        @endif
                                    </div>

                                    @if ($nextPhone)
                                        <a
                                            href="tel:{{ $nextPhone }}"
                                            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-100 text-orange-700 transition active:scale-95 hover:bg-orange-600 hover:text-white"
                                            aria-label="Call {{ $nextOrder->customer_name }}"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="h-4 w-4"
                                            >
                                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 2.1 2.4z" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>

                                {{-- Address --}}
                                <a
                                    href="{{ $nextMapsUrl }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="group flex items-start gap-3 rounded-2xl bg-orange-50 p-4 transition hover:bg-orange-100"
                                >
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4"
                                        >
                                            <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                            <circle cx="12" cy="9" r="2.5" />
                                        </svg>
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[9px] font-black uppercase tracking-[0.12em] text-orange-600">
                                            Delivery Address
                                        </span>

                                        <span class="mt-1 line-clamp-2 block text-xs font-semibold leading-5 text-slate-700">
                                            {{ $nextOrder->delivery_address }}
                                        </span>
                                    </span>

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="mt-2 h-4 w-4 shrink-0 text-orange-400 transition group-hover:translate-x-0.5 group-hover:text-orange-700"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m9 18 6-6-6-6"
                                        />
                                    </svg>
                                </a>
                            </div>

                            {{-- Delivery Metadata --}}
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <div class="rounded-xl border border-slate-100 bg-white px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                        Total
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-950">
                                        Rs. {{ number_format($nextOrder->total, 0) }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-100 bg-white px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                        Payment
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-950">
                                        {{ strtoupper($nextOrder->payment_method ?? 'COD') }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-100 bg-white px-3 py-3">
                                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                        Placed
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-950">
                                        {{ $nextOrder->created_at?->format('h:i A') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Primary Actions --}}
                            <div class="mt-4 grid grid-cols-[auto_auto_1fr] gap-2">
                                @if ($nextPhone)
                                    <a
                                        href="tel:{{ $nextPhone }}"
                                        class="grid h-12 w-12 place-items-center rounded-xl border border-orange-200 bg-orange-50 text-orange-700 transition active:scale-95 hover:bg-orange-100"
                                        aria-label="Call customer"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-5 w-5"
                                        >
                                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 2.1 2.4z" />
                                        </svg>
                                    </a>
                                @endif

                                <a
                                    href="{{ $nextMapsUrl }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="grid h-12 w-12 place-items-center rounded-xl border border-blue-200 bg-blue-50 text-blue-700 transition active:scale-95 hover:bg-blue-100"
                                    aria-label="Open delivery address in maps"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                        <circle cx="12" cy="9" r="2.5" />
                                    </svg>
                                </a>

                                <a
                                    href="{{ route('rider.orders.show', $nextOrder) }}"
                                    class="inline-flex min-h-12 min-w-0 items-center justify-center gap-2 rounded-xl px-4 text-sm font-black text-white shadow-lg transition active:scale-[0.98] {{ $nextUi['button'] }}"
                                >
                                    Handle Delivery

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
                    </div>
                </section>
            @else
                {{-- Empty Active Queue --}}
                <section class="rounded-[1.75rem] border border-dashed border-emerald-200 bg-white p-6 text-center shadow-sm sm:p-10">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            class="h-8 w-8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 12 4 4L19 6"
                            />
                        </svg>
                    </div>

                    <h2 class="mt-5 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                        You are all caught up
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                        There are no active deliveries requiring action. New assignments will appear here.
                    </p>

                    <a
                        href="{{ route('rider.orders') }}"
                        class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition active:scale-[0.98] hover:bg-orange-700 sm:w-auto sm:rounded-2xl"
                    >
                        View Delivery History
                    </a>
                </section>
            @endif

            {{-- Remaining Active Queue --}}
            @if ($queueOrders->isNotEmpty())
                <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                                Active Queue
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                                Upcoming deliveries
                            </h2>
                        </div>

                        <span class="rounded-full bg-orange-50 px-3 py-1.5 text-[10px] font-black text-orange-700">
                            {{ $activeOrders->count() - 1 }} remaining
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($queueOrders as $order)
                            @php
                                $orderUi = $getOrderUi($order);

                                $deliveryStatus = $order->delivery?->status
                                    ?? 'assigned';

                                $phoneHref = $order->customer_phone
                                    ? preg_replace(
                                        '/[^0-9+]/',
                                        '',
                                        $order->customer_phone
                                    )
                                    : null;

                                $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='
                                    . rawurlencode($order->delivery_address ?? '');
                            @endphp

                            <article class="relative p-4 sm:p-5">
                                <div class="absolute inset-y-0 left-0 w-1 {{ $orderUi['accent'] }}"></div>

                                <div class="pl-2 sm:pl-3">
                                    <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.1em] {{ $orderUi['badge'] }}">
                                                    {{ $orderUi['label'] }}
                                                </span>

                                                <x-status-badge
                                                    :status="$deliveryStatus"
                                                    type="delivery"
                                                />
                                            </div>

                                            <a
                                                href="{{ route('rider.orders.show', $order) }}"
                                                class="mt-2 block break-all text-base font-black text-slate-950 transition hover:text-orange-700"
                                            >
                                                {{ $order->order_number }}
                                            </a>

                                            <div class="mt-2 flex min-w-0 items-center gap-2 text-xs font-semibold text-slate-500">
                                                <span class="truncate">
                                                    {{ $order->customer_name }}
                                                </span>

                                                <span class="shrink-0 text-slate-300">•</span>

                                                <span class="truncate">
                                                    Rs. {{ number_format($order->total, 0) }}
                                                </span>

                                                <span class="shrink-0 text-slate-300">•</span>

                                                <span class="truncate">
                                                    {{ strtoupper($order->payment_method ?? 'COD') }}
                                                </span>
                                            </div>

                                            <p class="mt-2 line-clamp-1 text-xs font-semibold text-slate-500">
                                                {{ $order->delivery_address }}
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-[auto_auto_1fr] gap-2 sm:flex">
                                            @if ($phoneHref)
                                                <a
                                                    href="tel:{{ $phoneHref }}"
                                                    class="grid h-11 w-11 place-items-center rounded-xl bg-orange-50 text-orange-700 transition active:scale-95 hover:bg-orange-100"
                                                    aria-label="Call {{ $order->customer_name }}"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        class="h-4 w-4"
                                                    >
                                                        <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 2.1 2.4z" />
                                                    </svg>
                                                </a>
                                            @endif

                                            <a
                                                href="{{ $mapsUrl }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-blue-700 transition active:scale-95 hover:bg-blue-100"
                                                aria-label="Open address in maps"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    class="h-4 w-4"
                                                >
                                                    <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                                    <circle cx="12" cy="9" r="2.5" />
                                                </svg>
                                            </a>

                                            <a
                                                href="{{ route('rider.orders.show', $order) }}"
                                                class="inline-flex min-h-11 min-w-0 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 text-xs font-black text-white transition active:scale-[0.98] hover:bg-slate-800 sm:min-w-[110px]"
                                            >
                                                Open

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
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="border-t border-orange-100 p-4">
                        <a
                            href="{{ route('rider.orders') }}"
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-black text-orange-700 transition hover:bg-orange-100"
                        >
                            View All Deliveries

                            @if ($remainingQueueCount > 0)
                                <span class="rounded-full bg-orange-600 px-2 py-0.5 text-[9px] text-white">
                                    +{{ $remainingQueueCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </section>
            @endif
        </div>

        {{-- Desktop / Mobile Sidebar --}}
        <aside class="space-y-5 lg:sticky lg:top-24">
            {{-- Performance --}}
            <section class="rounded-[1.75rem] border border-orange-100 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                            Performance
                        </p>

                        <h2 class="mt-1 text-lg font-black text-slate-950">
                            Delivery completion
                        </h2>
                    </div>

                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-sm font-black text-emerald-700">
                        {{ $completionRate }}%
                    </span>
                </div>

                <div class="mt-5 h-2.5 overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-orange-500 transition-all duration-500"
                        style="width: {{ $completionRate }}%"
                    ></div>
                </div>

                <p class="mt-3 text-xs font-semibold leading-5 text-slate-500">
                    {{ $deliveredOrders }} of {{ $totalAssignedOrders }} assigned deliveries completed successfully.
                </p>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-emerald-50 px-3 py-3">
                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-emerald-700">
                            Successful
                        </p>

                        <p class="mt-1 text-lg font-black text-emerald-950">
                            {{ $deliveredOrders }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-red-50 px-3 py-3">
                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-red-700">
                            Failed
                        </p>

                        <p class="mt-1 text-lg font-black text-red-950">
                            {{ $failedDeliveries }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- Quick Navigation --}}
            <section class="rounded-[1.75rem] border border-orange-100 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                    Quick Access
                </p>

                <h2 class="mt-1 text-lg font-black text-slate-950">
                    Rider tools
                </h2>

                <div class="mt-4 space-y-2">
                    <a
                        href="{{ route('rider.orders') }}"
                        class="group flex min-h-12 items-center gap-3 rounded-xl bg-orange-50 px-3.5 py-3 transition hover:bg-orange-100"
                    >
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
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

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-950">
                                All Deliveries
                            </span>

                            <span class="mt-0.5 block text-[10px] font-semibold text-slate-500">
                                View active and completed orders
                            </span>
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-orange-600"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m9 18 6-6-6-6"
                            />
                        </svg>
                    </a>

                    <a
                        href="{{ route('home') }}"
                        class="group flex min-h-12 items-center gap-3 rounded-xl bg-slate-50 px-3.5 py-3 transition hover:bg-slate-100"
                    >
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-slate-600 shadow-sm">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4"
                            >
                                <path d="M3 11 12 3l9 8" />
                                <path d="M5 10v10h14V10" />
                            </svg>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-black text-slate-950">
                                Restaurant Website
                            </span>

                            <span class="mt-0.5 block text-[10px] font-semibold text-slate-500">
                                Open the customer-facing website
                            </span>
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-1 group-hover:text-slate-600"
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

            {{-- Recent Activity --}}
            @if ($closedOrders->isNotEmpty())
                <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                    <div class="border-b border-orange-100 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                            Recent Activity
                        </p>

                        <h2 class="mt-1 text-lg font-black text-slate-950">
                            Closed deliveries
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($closedOrders as $order)
                            @php
                                $closedUi = $getOrderUi($order);

                                $closedStatus = $order->delivery?->status
                                    ?? $order->order_status;
                            @endphp

                            <a
                                href="{{ route('rider.orders.show', $order) }}"
                                class="group flex items-center gap-3 px-5 py-4 transition hover:bg-slate-50"
                            >
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $closedUi['icon'] }}">
                                    @if ($closedUi['is_completed'])
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
                                    @else
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4"
                                        >
                                            <circle cx="12" cy="12" r="9" />
                                            <path
                                                stroke-linecap="round"
                                                d="m9 9 6 6m0-6-6 6"
                                            />
                                        </svg>
                                    @endif
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-black text-slate-950">
                                        {{ $order->order_number }}
                                    </span>

                                    <span class="mt-0.5 block truncate text-[10px] font-semibold text-slate-500">
                                        {{ $order->customer_name }}
                                        · Rs. {{ number_format($order->total, 0) }}
                                    </span>
                                </span>

                                <x-status-badge
                                    :status="$closedStatus"
                                    type="delivery"
                                />
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>

    {{-- Persistent Mobile Action --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_rgba(15,23,42,0.14)] backdrop-blur lg:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <div class="min-w-0 shrink-0">
                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Active
                </p>

                <p class="mt-0.5 text-lg font-black text-slate-950">
                    {{ $activeDeliveries }}
                </p>
            </div>

            @if ($nextOrder)
                <a
                    href="{{ route('rider.orders.show', $nextOrder) }}"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/25 transition active:scale-[0.98]"
                >
                    Handle Next Delivery

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
            @else
                <a
                    href="{{ route('rider.orders') }}"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-900/20 transition active:scale-[0.98]"
                >
                    View Delivery History
                </a>
            @endif
        </div>
    </div>
</div>

@endcomponent
