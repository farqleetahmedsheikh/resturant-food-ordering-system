@component('layouts.rider', ['title' => 'Assigned Orders'])
@php
$riderName = auth()->user()->name ?? 'Rider';

    $firstName = \Illuminate\Support\Str::before(
        $riderName,
        ' '
    );

    /*
     * Support both paginated results and regular collections.
     */
    $visibleOrders = collect(
        method_exists($assignedOrders, 'items')
            ? $assignedOrders->items()
            : $assignedOrders
    );

    $totalOrders = method_exists($assignedOrders, 'total')
        ? (int) $assignedOrders->total()
        : $visibleOrders->count();

    $isClosedOrder = function ($order): bool {
        $deliveryStatus = $order->delivery?->status ?? 'assigned';

        return in_array(
            $deliveryStatus,
            ['delivered', 'failed'],
            true
        ) || in_array(
            $order->order_status,
            ['delivered', 'cancelled'],
            true
        );
    };

    /*
     * Sort the current page by operational priority.
     */
    $sortedOrders = $visibleOrders
        ->sortBy(function ($order) use ($isClosedOrder) {
            $deliveryStatus = $order->delivery?->status ?? 'assigned';

            if ($isClosedOrder($order)) {
                return match ($deliveryStatus) {
                    'delivered' => 90,
                    'failed' => 99,
                    default => 95,
                };
            }

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

                default => 50,
            };
        })
        ->values();

    $activeOrders = $sortedOrders
        ->reject($isClosedOrder)
        ->values();

    $closedOrders = $sortedOrders
        ->filter($isClosedOrder)
        ->values();

    $nextOrder = $activeOrders->first();

    $remainingActiveOrders = $activeOrders
        ->skip(1)
        ->values();

    $activeOrdersOnPage = $activeOrders->count();

    $deliveredOrdersOnPage = $visibleOrders
        ->filter(function ($order) {
            $deliveryStatus = $order->delivery?->status;

            return $deliveryStatus === 'delivered'
                || $order->order_status === 'delivered';
        })
        ->count();

    $failedOrdersOnPage = $visibleOrders
        ->filter(function ($order) {
            $deliveryStatus = $order->delivery?->status;

            return $deliveryStatus === 'failed'
                || $order->order_status === 'cancelled';
        })
        ->count();

    $collectableAmountOnPage = $activeOrders
        ->sum(fn ($order) => (float) $order->total);

    $hasPages = method_exists($assignedOrders, 'hasPages')
        && $assignedOrders->hasPages();

    /*
     * Visual configuration for each operational state.
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
                'message' => 'This order is currently on the way to the customer.',
                'accent' => 'bg-blue-500',
                'badge' => 'border-blue-200 bg-blue-50 text-blue-700',
                'icon' => 'bg-blue-50 text-blue-600',
                'button' => 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20',
                'closed' => false,
            ],

            $deliveryStatus === 'picked_up' => [
                'label' => 'Picked Up',
                'message' => 'The order has been collected and should be delivered next.',
                'accent' => 'bg-cyan-500',
                'badge' => 'border-cyan-200 bg-cyan-50 text-cyan-700',
                'icon' => 'bg-cyan-50 text-cyan-600',
                'button' => 'bg-cyan-600 hover:bg-cyan-700 shadow-cyan-600/20',
                'closed' => false,
            ],

            in_array(
                $deliveryStatus,
                ['assigned', 'assigned_to_rider'],
                true
            ) => [
                'label' => 'Pickup Required',
                'message' => 'Review the order and prepare for restaurant pickup.',
                'accent' => 'bg-indigo-500',
                'badge' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
                'icon' => 'bg-indigo-50 text-indigo-600',
                'button' => 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/20',
                'closed' => false,
            ],

            $order->order_status === 'ready' => [
                'label' => 'Ready for Pickup',
                'message' => 'The restaurant has finished preparing this order.',
                'accent' => 'bg-violet-500',
                'badge' => 'border-violet-200 bg-violet-50 text-violet-700',
                'icon' => 'bg-violet-50 text-violet-600',
                'button' => 'bg-violet-600 hover:bg-violet-700 shadow-violet-600/20',
                'closed' => false,
            ],

            $order->order_status === 'preparing' => [
                'label' => 'Preparing',
                'message' => 'The restaurant is still preparing this order.',
                'accent' => 'bg-amber-500',
                'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
                'icon' => 'bg-amber-50 text-amber-600',
                'button' => 'bg-orange-600 hover:bg-orange-700 shadow-orange-600/20',
                'closed' => false,
            ],

            $isDelivered => [
                'label' => 'Delivered',
                'message' => 'The order was delivered successfully.',
                'accent' => 'bg-emerald-500',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'icon' => 'bg-emerald-50 text-emerald-600',
                'button' => 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20',
                'closed' => true,
            ],

            $isFailed => [
                'label' => 'Closed',
                'message' => 'The delivery failed or the order was cancelled.',
                'accent' => 'bg-red-500',
                'badge' => 'border-red-200 bg-red-50 text-red-700',
                'icon' => 'bg-red-50 text-red-600',
                'button' => 'bg-slate-800 hover:bg-slate-900 shadow-slate-800/20',
                'closed' => true,
            ],

            default => [
                'label' => 'Assigned',
                'message' => 'Review this assigned delivery.',
                'accent' => 'bg-orange-500',
                'badge' => 'border-orange-200 bg-orange-50 text-orange-700',
                'icon' => 'bg-orange-50 text-orange-600',
                'button' => 'bg-orange-600 hover:bg-orange-700 shadow-orange-600/20',
                'closed' => false,
            ],
        };
    };
@endphp

<div class="space-y-5 pb-24 sm:space-y-6 lg:pb-8">
    {{-- Mobile Header --}}
    <div class="flex items-center justify-between gap-4 lg:hidden">
        <a
            href="{{ route('rider.dashboard') }}"
            class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-orange-100 bg-white text-slate-700 shadow-sm transition active:scale-95"
            aria-label="Back to rider dashboard"
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

        <div class="min-w-0 text-center">
            <p class="text-sm font-black text-slate-950">
                Assigned deliveries
            </p>

            <p class="mt-0.5 text-[10px] font-semibold text-slate-500">
                {{ $activeOrdersOnPage }} active on this page
            </p>
        </div>

        <a
            href="{{ route('rider.dashboard') }}"
            class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-orange-50 text-orange-600 transition active:scale-95"
            aria-label="Rider dashboard"
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
        </a>
    </div>

    {{-- Desktop Header --}}
    <header class="hidden items-end justify-between gap-8 lg:flex">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                Rider Deliveries
            </p>

            <h1 class="mt-2 text-4xl font-black tracking-tight text-slate-950">
                Assigned orders
            </h1>

            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-600">
                Handle active deliveries first, contact customers quickly, and keep every delivery status accurate.
            </p>
        </div>

        <a
            href="{{ route('rider.dashboard') }}"
            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50"
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

            Rider Dashboard
        </a>
    </header>

    {{-- Queue Overview --}}
    <section class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-5 text-white shadow-xl shadow-slate-950/20 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-orange-500/30 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-12 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative grid gap-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>

                        {{ $activeOrdersOnPage > 0 ? 'Queue active' : 'Queue clear' }}
                    </span>

                    <span class="rounded-full bg-orange-500 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em]">
                        {{ $totalOrders }}
                        {{ $totalOrders === 1 ? 'order' : 'orders' }}
                    </span>
                </div>

                <h2 class="mt-4 text-2xl font-black tracking-tight sm:text-4xl">
                    Hi, {{ $firstName }}
                </h2>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-300">
                    @if ($activeOrdersOnPage > 0)
                        You have {{ $activeOrdersOnPage }}
                        {{ $activeOrdersOnPage === 1 ? 'delivery' : 'deliveries' }}
                        requiring attention on this page.
                    @else
                        There are no active deliveries requiring action on this page.
                    @endif
                </p>
            </div>

            <div class="grid grid-cols-3 gap-2 sm:gap-3 xl:min-w-[480px]">
                <div class="rounded-xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/60 sm:text-[10px]">
                        Active
                    </p>

                    <p class="mt-1 text-xl font-black text-orange-300 sm:text-2xl">
                        {{ $activeOrdersOnPage }}
                    </p>

                    <p class="mt-0.5 hidden text-[9px] font-semibold text-white/50 sm:block">
                        This page
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/60 sm:text-[10px]">
                        Delivered
                    </p>

                    <p class="mt-1 text-xl font-black text-emerald-300 sm:text-2xl">
                        {{ $deliveredOrdersOnPage }}
                    </p>

                    <p class="mt-0.5 hidden text-[9px] font-semibold text-white/50 sm:block">
                        This page
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/60 sm:text-[10px]">
                        Collect
                    </p>

                    <p class="mt-1 truncate text-sm font-black sm:text-lg">
                        Rs. {{ number_format($collectableAmountOnPage, 0) }}
                    </p>

                    <p class="mt-0.5 hidden text-[9px] font-semibold text-white/50 sm:block">
                        Active value
                    </p>
                </div>
            </div>
        </div>
    </section>

    @if ($visibleOrders->isEmpty())
        {{-- Empty State --}}
        <section class="rounded-[1.75rem] border border-dashed border-orange-200 bg-white p-7 text-center shadow-sm sm:p-12">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-orange-50 text-orange-600 sm:h-20 sm:w-20">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-8 w-8 sm:h-9 sm:w-9"
                >
                    <path d="M3 7h11v10H3z" />
                    <path d="M14 10h4l3 3v4h-7z" />
                    <circle cx="7" cy="18" r="2" />
                    <circle cx="18" cy="18" r="2" />
                </svg>
            </div>

            <h2 class="mt-5 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                No assigned deliveries
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                New delivery assignments will appear here after an administrator assigns an order to your account.
            </p>

            <a
                href="{{ route('rider.dashboard') }}"
                class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-orange-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition active:scale-[0.98] hover:bg-orange-700 sm:w-auto sm:rounded-2xl"
            >
                Return to Dashboard
            </a>
        </section>
    @else
        {{-- Next Priority Delivery --}}
        @if ($nextOrder)
            @php
                $nextUi = $getOrderUi($nextOrder);

                $nextDeliveryStatus = $nextOrder->delivery?->status
                    ?? 'assigned';

                $nextPhone = $nextOrder->customer_phone
                    ? preg_replace(
                        '/[^0-9+]/',
                        '',
                        $nextOrder->customer_phone
                    )
                    : null;

                $nextMapsUrl = 'https://www.google.com/maps/search/?api=1&query='
                    . rawurlencode($nextOrder->delivery_address ?? '');
            @endphp

            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="flex items-start justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                                Next Priority
                            </p>

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] text-red-700">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-500"></span>

                                Handle first
                            </span>
                        </div>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                            Your next delivery
                        </h2>
                    </div>

                    <span class="hidden rounded-full border px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em] sm:inline-flex {{ $nextUi['badge'] }}">
                        {{ $nextUi['label'] }}
                    </span>
                </div>

                <article class="relative p-4 sm:p-6">
                    <div class="absolute inset-y-0 left-0 w-1.5 {{ $nextUi['accent'] }}"></div>

                    <div class="pl-2 sm:pl-3">
                        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 sm:hidden">
                                    <span class="inline-flex rounded-full border px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em] {{ $nextUi['badge'] }}">
                                        {{ $nextUi['label'] }}
                                    </span>

                                    <x-status-badge
                                        :status="$nextDeliveryStatus"
                                        type="delivery"
                                    />
                                </div>

                                <a
                                    href="{{ route('rider.orders.show', $nextOrder) }}"
                                    class="mt-3 block break-all text-xl font-black tracking-tight text-slate-950 transition hover:text-orange-700 sm:mt-0 sm:text-2xl"
                                >
                                    {{ $nextOrder->order_number }}
                                </a>

                                <p class="mt-1.5 text-sm font-semibold leading-6 text-slate-600">
                                    {{ $nextUi['message'] }}
                                </p>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
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
                                            <span class="block text-[9px] font-black uppercase tracking-[0.12em] text-orange-700">
                                                Delivery address
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
                            </div>

                            <div class="xl:w-[280px]">
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-xl bg-slate-50 px-3 py-3">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                            Total
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-950">
                                            Rs. {{ number_format($nextOrder->total, 0) }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-slate-50 px-3 py-3">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                            Payment
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-950">
                                            {{ strtoupper($nextOrder->payment_method ?? 'COD') }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-slate-50 px-3 py-3">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                            Assigned
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-950">
                                            {{ $nextOrder->assigned_at?->format('h:i A') ?? '—' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-[auto_auto_1fr] gap-2">
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
                                        aria-label="Open directions"
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
                    </div>
                </article>
            </section>
        @endif

        {{-- Remaining Active Queue --}}
        @if ($remainingActiveOrders->isNotEmpty())
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
                        {{ $remainingActiveOrders->count() }}
                        remaining
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($remainingActiveOrders as $order)
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
                                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
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

                                        <div class="mt-2 flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-slate-500">
                                            <span class="truncate">
                                                {{ $order->customer_name }}
                                            </span>

                                            <span class="text-slate-300">•</span>

                                            <span>
                                                Rs. {{ number_format($order->total, 0) }}
                                            </span>

                                            <span class="text-slate-300">•</span>

                                            <span>
                                                {{ strtoupper($order->payment_method ?? 'COD') }}
                                            </span>
                                        </div>

                                        <p class="mt-2 line-clamp-1 text-xs font-semibold text-slate-500">
                                            {{ $order->delivery_address }}
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-[auto_auto_1fr] gap-2 lg:flex">
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
                                            aria-label="Open delivery address in maps"
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
                                            class="inline-flex min-h-11 min-w-0 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 text-xs font-black text-white transition active:scale-[0.98] hover:bg-slate-800 lg:min-w-[112px]"
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
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Completed / Closed Orders --}}
        @if ($closedOrders->isNotEmpty())
            <details class="group overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-4 sm:px-6 sm:py-5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                            Recent Activity
                        </p>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Completed and closed deliveries
                        </h2>

                        <p class="mt-1 text-xs font-semibold text-slate-500">
                            {{ $closedOrders->count() }}
                            {{ $closedOrders->count() === 1 ? 'order' : 'orders' }}
                            on this page
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

                <div class="divide-y divide-slate-100 border-t border-orange-100">
                    @foreach ($closedOrders as $order)
                        @php
                            $orderUi = $getOrderUi($order);

                            $deliveryStatus = $order->delivery?->status
                                ?? $order->order_status;
                        @endphp

                        <a
                            href="{{ route('rider.orders.show', $order) }}"
                            class="group/item flex items-center gap-3 px-4 py-4 transition hover:bg-slate-50 sm:px-6"
                        >
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $orderUi['icon'] }}">
                                @if (
                                    $deliveryStatus === 'delivered'
                                    || $order->order_status === 'delivered'
                                )
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
                                @else
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
                                @endif
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-black text-slate-950">
                                    {{ $order->order_number }}
                                </span>

                                <span class="mt-0.5 block truncate text-xs font-semibold text-slate-500">
                                    {{ $order->customer_name }}
                                    · Rs. {{ number_format($order->total, 0) }}
                                </span>
                            </span>

                            <span class="hidden sm:block">
                                <x-status-badge
                                    :status="$deliveryStatus"
                                    type="delivery"
                                />
                            </span>

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-4 w-4 shrink-0 text-slate-300 transition group-hover/item:translate-x-0.5 group-hover/item:text-orange-600"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m9 18 6-6-6-6"
                                />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </details>
        @endif

        {{-- Pagination --}}
        @if ($hasPages)
            <div class="rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
                {{ $assignedOrders->withQueryString()->links() }}
            </div>
        @endif
    @endif

    {{-- Persistent Mobile Action --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_rgba(15,23,42,0.14)] backdrop-blur lg:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('rider.dashboard') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-orange-200 bg-orange-50 text-orange-700 transition active:scale-95"
                aria-label="Rider dashboard"
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
            </a>

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
                    href="{{ route('rider.dashboard') }}"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-900/20 transition active:scale-[0.98]"
                >
                    Return to Dashboard
                </a>
            @endif
        </div>
    </div>
</div>

@endcomponent
