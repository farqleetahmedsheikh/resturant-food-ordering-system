@component('layouts.admin', ['title' => 'Orders'])
@php
/*
* Supports both paginated results and normal collections.
*/
$visibleOrders = collect(
method_exists($orders, 'items')
? $orders->items()
: $orders
);

    $orderCount = method_exists($orders, 'total')
        ? (int) $orders->total()
        : $visibleOrders->count();

    $pageOrderCount = $visibleOrders->count();

    $currentStatusLabel = $currentStatus
        ? (
            $statuses[$currentStatus]
            ?? \Illuminate\Support\Str::headline($currentStatus)
        )
        : 'All Orders';

    /*
     * These statistics describe only the currently loaded page.
     */
    $pendingOrdersOnPage = $visibleOrders
        ->filter(
            fn ($order) => $order->order_status === 'pending'
        )
        ->count();

    $unassignedOrdersOnPage = $visibleOrders
        ->filter(function ($order) {
            return ! $order->rider
                && ! in_array(
                    $order->order_status,
                    ['delivered', 'cancelled'],
                    true
                );
        })
        ->count();

    $activeDeliveriesOnPage = $visibleOrders
        ->filter(function ($order) {
            $deliveryStatus = $order->delivery?->status;

            return in_array(
                $deliveryStatus,
                [
                    'assigned',
                    'assigned_to_rider',
                    'picked_up',
                    'out_for_delivery',
                ],
                true
            ) || in_array(
                $order->order_status,
                ['assigned_to_rider', 'out_for_delivery'],
                true
            );
        })
        ->count();

    $pageOrderValue = (float) $visibleOrders->sum('total');

    $hasPages = method_exists($orders, 'hasPages')
        && $orders->hasPages();
@endphp

<div class="space-y-5 pb-8 sm:space-y-6">
    {{-- Hero --}}
    <header class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-5 text-white shadow-xl shadow-slate-950/20 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-orange-500/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-12 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative grid gap-7 xl:grid-cols-[minmax(0,1fr)_500px] xl:items-center">
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
                            <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                            <path d="M9 7h6M9 11h6M9 15h3" />
                        </svg>

                        Order Management
                    </span>

                    <span class="rounded-full bg-orange-500 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em]">
                        {{ $currentStatusLabel }}
                    </span>
                </div>

                <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                    Orders
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-300 sm:text-base sm:leading-7">
                    Review new orders, contact customers, assign riders, and monitor fulfilment from one operational queue.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ route('admin.riders.index') }}"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-950/30 transition active:scale-[0.98] hover:-translate-y-0.5 hover:bg-orange-500 sm:rounded-2xl"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <circle cx="6" cy="18" r="2" />
                            <circle cx="18" cy="18" r="2" />
                            <path d="M8 18h8M7 16l2-6h6l3 6" />
                        </svg>

                        Manage Riders
                    </a>

                    <a
                        href="{{ route('home') }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition active:scale-[0.98] hover:bg-white/20 sm:rounded-2xl"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-5 w-5"
                        >
                            <path d="m3 11 9-8 9 8" />
                            <path d="M5 10v10h14V10" />
                        </svg>

                        Customer Website
                    </a>
                </div>
            </div>

            {{-- Page Statistics --}}
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-2">
                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Results
                            </p>

                            <p class="mt-1 text-2xl font-black">
                                {{ $orderCount }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-orange-400"></span>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        Current filter
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Pending
                            </p>

                            <p class="mt-1 text-2xl font-black text-amber-300">
                                {{ $pendingOrdersOnPage }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        On this page
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                                Unassigned
                            </p>

                            <p class="mt-1 text-2xl font-black text-red-300">
                                {{ $unassignedOrdersOnPage }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-red-400"></span>
                    </div>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        Need a rider
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Page Value
                    </p>

                    <p class="mt-1 truncate text-lg font-black text-emerald-300 sm:text-xl">
                        Rs. {{ number_format($pageOrderValue, 0) }}
                    </p>

                    <p class="mt-1 text-[9px] font-semibold text-white/45">
                        {{ $activeDeliveriesOnPage }} active deliveries
                    </p>
                </div>
            </div>
        </div>
    </header>

    {{-- Status Filters --}}
    <section class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                    Order Queue
                </p>

                <h2 class="mt-1 text-xl font-black text-slate-950">
                    Filter by fulfilment status
                </h2>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Select a status to focus the operational queue.
                </p>
            </div>

            @if ($currentStatus)
                <a
                    href="{{ route('admin.orders.index') }}"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-black text-orange-700 transition hover:bg-orange-100"
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
                        <path
                            stroke-linecap="round"
                            d="M3 4v6h6"
                        />
                    </svg>

                    Clear Filter
                </a>
            @endif
        </div>

        <nav
            class="-mx-1 mt-4 flex gap-2 overflow-x-auto px-1 pb-2"
            aria-label="Order status filters"
        >
            <a
                href="{{ route('admin.orders.index') }}"
                @class([
                    'inline-flex min-h-10 shrink-0 items-center gap-2 whitespace-nowrap rounded-full border px-4 py-2 text-xs font-black transition',
                    'border-orange-600 bg-orange-600 text-white shadow-lg shadow-orange-600/20' => ! $currentStatus,
                    'border-slate-200 bg-white text-slate-700 hover:border-orange-300 hover:bg-orange-50' => $currentStatus,
                ])
            >
                <span
                    @class([
                        'h-1.5 w-1.5 rounded-full',
                        'bg-white' => ! $currentStatus,
                        'bg-slate-400' => $currentStatus,
                    ])
                ></span>

                All Orders
            </a>

            @foreach ($statuses as $value => $label)
                <a
                    href="{{ route('admin.orders.index', ['status' => $value]) }}"
                    @class([
                        'inline-flex min-h-10 shrink-0 items-center gap-2 whitespace-nowrap rounded-full border px-4 py-2 text-xs font-black transition',
                        'border-orange-600 bg-orange-600 text-white shadow-lg shadow-orange-600/20' => $currentStatus === $value,
                        'border-slate-200 bg-white text-slate-700 hover:border-orange-300 hover:bg-orange-50' => $currentStatus !== $value,
                    ])
                >
                    <span
                        @class([
                            'h-1.5 w-1.5 rounded-full',
                            'bg-white' => $currentStatus === $value,
                            'bg-slate-400' => $currentStatus !== $value,
                        ])
                    ></span>

                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </section>

    @if ($visibleOrders->isEmpty())
        {{-- Empty State --}}
        <section class="rounded-[1.75rem] border border-dashed border-orange-200 bg-white p-7 text-center shadow-sm sm:p-12">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-orange-50 text-orange-600 sm:h-20 sm:w-20">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-8 w-8 sm:h-9 sm:w-9"
                >
                    <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                    <path d="M9 7h6M9 11h6M9 15h3" />
                </svg>
            </span>

            <h2 class="mt-5 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                @if ($currentStatus)
                    No {{ strtolower($currentStatusLabel) }} orders
                @else
                    No customer orders yet
                @endif
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                @if ($currentStatus)
                    No orders currently match this status. Select another status or return to the complete order queue.
                @else
                    Orders will appear here after customers complete checkout on the public website.
                @endif
            </p>

            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                @if ($currentStatus)
                    <a
                        href="{{ route('admin.orders.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-xl border border-orange-200 bg-orange-50 px-5 py-3 text-sm font-black text-orange-700 transition hover:bg-orange-100"
                    >
                        View All Orders
                    </a>
                @endif

                <a
                    href="{{ route('home') }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                >
                    Open Customer Website
                </a>
            </div>
        </section>
    @else
        {{-- Unified Responsive Order Directory --}}
        <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                        Order Directory
                    </p>

                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                        {{ $currentStatusLabel }}
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Showing {{ $pageOrderCount }}
                        {{ $pageOrderCount === 1 ? 'order' : 'orders' }}
                        on this page
                    </p>
                </div>

                <a
                    href="{{ route('admin.riders.index') }}"
                    class="hidden min-h-10 items-center justify-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-black text-orange-700 transition hover:bg-orange-100 sm:inline-flex"
                >
                    Manage Riders
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach ($orders as $order)
                    @php
                        $deliveryStatus = $order->delivery?->status
                            ?? 'pending';

                        $orderStatusLabel = $statuses[$order->order_status]
                            ?? \Illuminate\Support\Str::headline(
                                $order->order_status
                            );

                        $deliveryStatusLabel = \Illuminate\Support\Str::headline(
                            $deliveryStatus
                        );

                        $paymentMethod = strtoupper(
                            $order->payment_method ?? 'COD'
                        );

                        $paymentStatusLabel = \Illuminate\Support\Str::headline(
                            $order->payment_status ?? 'pending'
                        );

                        $customerPhoneHref = $order->customer_phone
                            ? preg_replace(
                                '/[^0-9+]/',
                                '',
                                $order->customer_phone
                            )
                            : null;

                        $isClosed = in_array(
                            $order->order_status,
                            ['delivered', 'cancelled'],
                            true
                        );

                        $needsRider = ! $order->rider
                            && ! $isClosed;

                        $needsAttention = $order->order_status === 'pending'
                            || (
                                $needsRider
                                && in_array(
                                    $order->order_status,
                                    [
                                        'accepted',
                                        'preparing',
                                        'ready',
                                        'assigned_to_rider',
                                    ],
                                    true
                                )
                            );

                        $statusAccent = match ($order->order_status) {
                            'delivered' => 'bg-emerald-500',
                            'cancelled' => 'bg-red-500',
                            'out_for_delivery' => 'bg-blue-500',
                            'assigned_to_rider' => 'bg-indigo-500',
                            'ready' => 'bg-violet-500',
                            'preparing' => 'bg-orange-500',
                            'accepted' => 'bg-amber-500',
                            default => 'bg-slate-400',
                        };
                    @endphp

                    <article class="group relative p-4 transition hover:bg-orange-50/30 sm:p-5">
                        <div class="absolute inset-y-0 left-0 w-1 {{ $statusAccent }}"></div>

                        <div class="grid gap-4 pl-2 md:grid-cols-[54px_minmax(0,1fr)] md:pl-3 xl:grid-cols-[54px_minmax(0,1fr)_160px_185px_auto] xl:items-center">
                            {{-- Customer Avatar --}}
                            <span class="grid h-12 w-12 place-items-center rounded-xl bg-slate-100 text-sm font-black text-slate-700 shadow-sm md:h-13 md:w-13">
                                {{ mb_strtoupper(
                                    mb_substr(
                                        $order->customer_name ?? 'C',
                                        0,
                                        1
                                    )
                                ) }}
                            </span>

                            {{-- Main Information --}}
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge :status="$order->order_status" />

                                    <x-status-badge
                                        :status="$deliveryStatus"
                                        type="delivery"
                                    />

                                    @if ($needsAttention)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.1em] text-red-700">
                                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-500"></span>

                                            Attention
                                        </span>
                                    @elseif ($needsRider)
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[8px] font-black uppercase tracking-[0.1em] text-amber-700">
                                            Rider needed
                                        </span>
                                    @endif
                                </div>

                                <a
                                    href="{{ route('admin.orders.show', $order) }}"
                                    class="mt-2 block break-all text-base font-black tracking-tight text-slate-950 transition hover:text-orange-700 sm:text-lg"
                                >
                                    {{ $order->order_number }}
                                </a>

                                <div class="mt-1 flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-500">
                                    <span class="truncate font-black text-slate-700">
                                        {{ $order->customer_name }}
                                    </span>

                                    <span class="text-slate-300">•</span>

                                    <span>
                                        {{ $order->created_at->format('M d, Y') }}
                                    </span>

                                    <span class="text-slate-300">•</span>

                                    <span>
                                        {{ $order->created_at->format('h:i A') }}
                                    </span>
                                </div>

                                @if ($order->delivery_address)
                                    <p class="mt-2 line-clamp-1 text-xs font-semibold text-slate-400">
                                        {{ $order->delivery_address }}
                                    </p>
                                @endif
                            </div>

                            {{-- Payment Summary --}}
                            <div class="rounded-xl bg-orange-50 px-4 py-3 md:col-span-2 xl:col-span-1">
                                <p class="text-[8px] font-black uppercase tracking-[0.1em] text-orange-600">
                                    Order Value
                                </p>

                                <p class="mt-1 text-lg font-black text-orange-950">
                                    Rs. {{ number_format($order->total, 0) }}
                                </p>

                                <div class="mt-1 flex items-center gap-1.5 text-[10px] font-bold text-orange-700">
                                    <span>{{ $paymentMethod }}</span>
                                    <span class="text-orange-300">•</span>
                                    <span>{{ $paymentStatusLabel }}</span>
                                </div>
                            </div>

                            {{-- Delivery Summary --}}
                            <div class="rounded-xl bg-slate-50 px-4 py-3 md:col-span-2 xl:col-span-1">
                                <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                    Delivery
                                </p>

                                @if ($order->rider)
                                    <div class="mt-2 flex min-w-0 items-center gap-2">
                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-white text-[10px] font-black text-blue-700 shadow-sm">
                                            {{ mb_strtoupper(
                                                mb_substr(
                                                    $order->rider->name,
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </span>

                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-black text-slate-950">
                                                {{ $order->rider->name }}
                                            </p>

                                            <p class="mt-0.5 truncate text-[9px] font-semibold text-slate-500">
                                                {{ $deliveryStatusLabel }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                class="h-4 w-4"
                                            >
                                                <circle cx="12" cy="12" r="9" />
                                                <path d="M12 7v5M12 16h.01" />
                                            </svg>
                                        </span>

                                        <div>
                                            <p class="text-xs font-black text-amber-800">
                                                Unassigned
                                            </p>

                                            <p class="mt-0.5 text-[9px] font-semibold text-slate-500">
                                                {{ $deliveryStatusLabel }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="grid grid-cols-[auto_1fr] gap-2 md:col-span-2 xl:col-span-1 xl:flex xl:justify-end">
                                @if ($customerPhoneHref)
                                    <a
                                        href="tel:{{ $customerPhoneHref }}"
                                        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-blue-100 bg-blue-50 text-blue-700 transition active:scale-95 hover:border-blue-600 hover:bg-blue-600 hover:text-white"
                                        aria-label="Call {{ $order->customer_name }}"
                                        title="{{ $order->customer_phone }}"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-4 w-4"
                                        >
                                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3" />
                                        </svg>
                                    </a>
                                @endif

                                <a
                                    href="{{ route('admin.orders.show', $order) }}"
                                    class="inline-flex min-h-11 min-w-0 items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-xs font-black text-white shadow-lg shadow-orange-600/15 transition active:scale-[0.98] hover:bg-orange-700 xl:min-w-[104px]"
                                >
                                    Manage

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
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Pagination --}}
        @if ($hasPages)
            <div class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    @endif
</div>

@endcomponent
