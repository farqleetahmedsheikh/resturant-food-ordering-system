@component('layouts.admin', ['title' => $order->order_number])
@php
$deliveryStatus = $order->delivery?->status ?? 'pending';

    $orderStatusLabel = \App\Models\Order::STATUSES[$order->order_status]
        ?? \Illuminate\Support\Str::headline($order->order_status);

    $deliveryStatusLabel = \Illuminate\Support\Str::headline(
        $deliveryStatus
    );

    $itemCount = (int) $order->items->sum('quantity');

    $isDelivered = $order->order_status === 'delivered'
        || $deliveryStatus === 'delivered';

    $isCancelled = $order->order_status === 'cancelled';

    $isCompleted = $isDelivered || $isCancelled;

    $isFailed = $deliveryStatus === 'failed';

    $customerPhone = $order->customer_phone;

    $customerPhoneHref = $customerPhone
        ? preg_replace('/[^0-9+]/', '', $customerPhone)
        : null;

    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='
        . rawurlencode($order->delivery_address ?? '');

    $paymentMethod = strtoupper(
        $order->payment_method ?? 'COD'
    );

    $paymentStatus = \Illuminate\Support\Str::headline(
        $order->payment_status ?? 'pending'
    );

    $statusMessage = match ($order->order_status) {
        'pending' => 'This order requires confirmation before preparation can begin.',
        'accepted' => 'The restaurant accepted this order and can begin preparing it.',
        'preparing' => 'The kitchen is currently preparing the customer’s order.',
        'ready' => 'The order is ready and should be assigned to a rider for pickup.',
        'assigned_to_rider' => 'A rider has been assigned and should collect the order.',
        'out_for_delivery' => 'The rider is currently delivering the order to the customer.',
        'delivered' => 'The order was delivered successfully.',
        'cancelled' => 'This order was cancelled and no further action is required.',
        default => 'Review the order details and manage its current progress.',
    };

    $statusTheme = match (true) {
        $isDelivered => [
            'gradient' => 'from-emerald-700 via-emerald-600 to-teal-700',
            'accent' => 'bg-emerald-500',
            'soft' => 'border-emerald-100 bg-emerald-50',
            'text' => 'text-emerald-700',
        ],

        $isCancelled || $isFailed => [
            'gradient' => 'from-red-700 via-red-600 to-rose-700',
            'accent' => 'bg-red-500',
            'soft' => 'border-red-100 bg-red-50',
            'text' => 'text-red-700',
        ],

        $order->order_status === 'out_for_delivery' => [
            'gradient' => 'from-blue-800 via-blue-700 to-indigo-800',
            'accent' => 'bg-blue-500',
            'soft' => 'border-blue-100 bg-blue-50',
            'text' => 'text-blue-700',
        ],

        $order->order_status === 'preparing' => [
            'gradient' => 'from-orange-700 via-orange-600 to-red-700',
            'accent' => 'bg-orange-500',
            'soft' => 'border-orange-100 bg-orange-50',
            'text' => 'text-orange-700',
        ],

        default => [
            'gradient' => 'from-slate-950 via-slate-900 to-orange-950',
            'accent' => 'bg-orange-500',
            'soft' => 'border-orange-100 bg-orange-50',
            'text' => 'text-orange-700',
        ],
    };

    $assignedAt = $order->assigned_at
        ?? $order->delivery?->created_at;

    $pickedUpAt = $order->picked_up_at
        ?? $order->delivery?->pickup_time;

    $deliveredAt = $order->delivered_at
        ?? $order->delivery?->delivered_time;

    $timeline = [
        [
            'label' => 'Order Placed',
            'description' => 'The customer submitted the order.',
            'time' => $order->created_at,
            'state' => 'complete',
        ],
        [
            'label' => 'Rider Assigned',
            'description' => $order->rider
                ? $order->rider->name . ' was assigned.'
                : 'No rider has been assigned.',
            'time' => $assignedAt,
            'state' => $assignedAt ? 'complete' : 'pending',
        ],
        [
            'label' => 'Order Picked Up',
            'description' => 'The rider collected the order.',
            'time' => $pickedUpAt,
            'state' => $pickedUpAt ? 'complete' : 'pending',
        ],
        [
            'label' => 'Delivery Completed',
            'description' => $isDelivered
                ? 'The order was handed to the customer.'
                : 'Waiting for successful delivery.',
            'time' => $deliveredAt,
            'state' => $deliveredAt ? 'complete' : 'pending',
        ],
    ];
@endphp

<div
    x-data="{
        updatingStatus: false,
        assigningRider: false,
        unassigningRider: false
    }"
    class="space-y-5 pb-28 sm:space-y-6 xl:pb-8"
>
    {{-- Mobile Navigation --}}
    <div class="flex items-center justify-between gap-4 xl:hidden">
        <a
            href="{{ route('admin.orders.index') }}"
            class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-orange-100 bg-white text-slate-700 shadow-sm transition active:scale-95"
            aria-label="Back to orders"
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
                Order details
            </p>

            <p class="mt-0.5 truncate text-[10px] font-semibold text-slate-500">
                {{ $order->order_number }}
            </p>
        </div>

        @if ($customerPhoneHref)
            <a
                href="tel:{{ $customerPhoneHref }}"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-orange-600 text-white shadow-lg shadow-orange-600/20 transition active:scale-95"
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
                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7" />
                </svg>
            </a>
        @else
            <span class="h-11 w-11"></span>
        @endif
    </div>

    {{-- Desktop Header --}}
    <header class="hidden items-end justify-between gap-8 xl:flex">
        <div class="min-w-0">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                Order Management
            </p>

            <h1 class="mt-2 break-all text-4xl font-black tracking-tight text-slate-950">
                {{ $order->order_number }}
            </h1>

            <p class="mt-2 text-sm font-semibold text-slate-500">
                Placed by {{ $order->customer_name }}
                <span class="mx-2 text-slate-300">•</span>
                {{ $order->created_at->format('M d, Y · h:i A') }}
            </p>
        </div>

        <a
            href="{{ route('admin.orders.index') }}"
            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-orange-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50"
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

            Back to Orders
        </a>
    </header>

    {{-- Order Status Hero --}}
    <section class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br {{ $statusTheme['gradient'] }} p-5 text-white shadow-2xl shadow-slate-950/20 sm:p-7 xl:rounded-[2rem] xl:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-white/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-12 h-72 w-72 rounded-full bg-orange-300/10 blur-3xl"></div>

        <div class="relative grid gap-6 xl:grid-cols-[minmax(0,1fr)_480px] xl:items-center">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-white {{ ! $isCompleted ? 'animate-pulse' : '' }}"></span>

                        Current order status
                    </span>

                    <x-status-badge :status="$order->order_status" />

                    <x-status-badge
                        :status="$deliveryStatus"
                        type="delivery"
                    />
                </div>

                <h2 class="mt-4 text-2xl font-black tracking-tight sm:text-4xl">
                    {{ $orderStatusLabel }}
                </h2>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-white/80 sm:text-base sm:leading-7">
                    {{ $statusMessage }}
                </p>

                <div class="mt-5 grid grid-cols-2 gap-2 sm:flex">
                    @if ($customerPhoneHref)
                        <a
                            href="tel:{{ $customerPhoneHref }}"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-black text-slate-950 shadow-lg transition active:scale-[0.98] hover:bg-slate-100 sm:rounded-2xl sm:px-5"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1 19.3 19.3 0 0 1-6-6A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3" />
                            </svg>

                            Call Customer
                        </a>
                    @endif

                    <a
                        href="{{ $mapsUrl }}"
                        target="_blank"
                        rel="noopener"
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
                            <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                            <circle cx="12" cy="9" r="2.5" />
                        </svg>

                        View Address
                    </a>
                </div>
            </div>

            {{-- Hero Metrics --}}
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-2">
                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Order Total
                    </p>

                    <p class="mt-1 text-xl font-black text-orange-200 sm:text-2xl">
                        Rs. {{ number_format($order->total, 0) }}
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Items
                    </p>

                    <p class="mt-1 text-xl font-black sm:text-2xl">
                        {{ $itemCount }}
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Payment
                    </p>

                    <p class="mt-1 truncate text-lg font-black">
                        {{ $paymentMethod }}
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur sm:rounded-2xl">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/55 sm:text-[10px]">
                        Rider
                    </p>

                    <p class="mt-1 truncate text-lg font-black">
                        {{ $order->rider?->name ?? 'Unassigned' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Fulfilment Progress --}}
    <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600 sm:text-xs">
                    Order Journey
                </p>

                <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                    Fulfilment progress
                </h2>
            </div>

            <span class="rounded-full px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.1em] {{ $statusTheme['soft'] }} {{ $statusTheme['text'] }}">
                {{ $orderStatusLabel }}
            </span>
        </div>

        <div class="overflow-x-auto px-4 py-5 sm:px-6">
            <div class="min-w-[650px]">
                <x-order-progress :status="$order->order_status" />
            </div>
        </div>
    </section>

    {{-- Main Workspace --}}
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_390px] xl:items-start xl:gap-6">
        {{-- Main Content --}}
        <main class="min-w-0 space-y-5">
            {{-- Customer and Delivery --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 21a8 8 0 0 1 16 0" />
                            </svg>
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                                Customer and Delivery
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                                Contact and destination
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-4 sm:p-6">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        {{-- Customer --}}
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-sm font-black text-orange-700 shadow-sm">
                                {{ mb_strtoupper(
                                    mb_substr(
                                        $order->customer_name ?? 'C',
                                        0,
                                        1
                                    )
                                ) }}
                            </span>

                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    Customer
                                </p>

                                <p class="mt-1 truncate text-sm font-black text-slate-950">
                                    {{ $order->customer_name }}
                                </p>
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.7 19.7 0 0 1-8.6-3.1A19.3 19.3 0 0 1 5.2 12.8 19.7 19.7 0 0 1 2.1 4.2" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    Phone
                                </p>

                                @if ($customerPhoneHref)
                                    <a
                                        href="tel:{{ $customerPhoneHref }}"
                                        class="mt-1 block truncate text-sm font-black text-orange-700"
                                    >
                                        {{ $customerPhone }}
                                    </a>
                                @else
                                    <p class="mt-1 text-sm font-black text-slate-400">
                                        Not provided
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4 sm:col-span-2 lg:col-span-1">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4"
                                >
                                    <rect x="3" y="5" width="18" height="14" rx="2" />
                                    <path d="m3 7 9 6 9-6" />
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    Email
                                </p>

                                @if ($order->customer_email)
                                    <a
                                        href="mailto:{{ $order->customer_email }}"
                                        class="mt-1 block truncate text-sm font-black text-orange-700"
                                    >
                                        {{ $order->customer_email }}
                                    </a>
                                @else
                                    <p class="mt-1 text-sm font-black text-slate-400">
                                        Not provided
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Address --}}
                    <a
                        href="{{ $mapsUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="group flex items-start gap-3 rounded-2xl border border-orange-100 bg-orange-50 p-4 transition hover:bg-orange-100 sm:p-5"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
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
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block text-[9px] font-black uppercase tracking-[0.14em] text-orange-700">
                                Delivery address
                            </span>

                            <span class="mt-1 block text-sm font-semibold leading-6 text-slate-700">
                                {{ $order->delivery_address }}
                            </span>

                            <span class="mt-2 inline-flex items-center gap-1.5 text-xs font-black text-orange-700">
                                Open in maps

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-3.5 w-3.5 transition group-hover:translate-x-0.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>
                            </span>
                        </span>
                    </a>

                    @if ($order->order_notes)
                        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-amber-600 shadow-sm">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-4 w-4"
                                    >
                                        <path d="M4 4h16v13H8l-4 4V4z" />
                                    </svg>
                                </span>

                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-amber-700">
                                        Customer instructions
                                    </p>

                                    <p class="mt-1 text-sm font-semibold leading-6 text-slate-700">
                                        {{ $order->order_notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Ordered Items --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                            Order Contents
                        </p>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                            Ordered items
                        </h2>
                    </div>

                    <span class="rounded-full bg-orange-50 px-3 py-1.5 text-[10px] font-black text-orange-700">
                        {{ $itemCount }}
                        {{ $itemCount === 1 ? 'item' : 'items' }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($order->items as $item)
                        <article class="p-4 sm:p-5">
                            <div class="flex items-start gap-3 sm:gap-4">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-orange-50 text-lg font-black text-orange-600 sm:h-14 sm:w-14 sm:rounded-2xl">
                                    {{ mb_strtoupper(
                                        mb_substr(
                                            $item->item_name,
                                            0,
                                            1
                                        )
                                    ) }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="break-words text-sm font-black leading-5 text-slate-950 sm:text-base">
                                                {{ $item->item_name }}
                                            </h3>

                                            <p class="mt-1 text-xs font-semibold text-slate-500 sm:text-sm">
                                                {{ $item->quantity }}
                                                × Rs. {{ number_format($item->price, 0) }}
                                            </p>
                                        </div>

                                        <div class="shrink-0 text-right">
                                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                                Total
                                            </p>

                                            <p class="mt-1 text-sm font-black text-slate-950 sm:text-base">
                                                Rs. {{ number_format($item->total, 0) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <x-order-item-options :item="$item" />
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- Delivery Timeline --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="border-b border-orange-100 px-4 py-4 sm:px-6 sm:py-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                        Delivery History
                    </p>

                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950 sm:text-2xl">
                        Activity timeline
                    </h2>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="space-y-0">
                        @foreach ($timeline as $index => $event)
                            @php
                                $eventComplete = $event['state'] === 'complete';
                            @endphp

                            <div class="relative flex gap-4 pb-6 last:pb-0">
                                @if ($index < count($timeline) - 1)
                                    <div
                                        @class([
                                            'absolute left-[19px] top-10 h-[calc(100%-24px)] w-0.5',
                                            'bg-emerald-200' => $eventComplete,
                                            'bg-slate-200' => ! $eventComplete,
                                        ])
                                    ></div>
                                @endif

                                <span
                                    @class([
                                        'relative z-10 grid h-10 w-10 shrink-0 place-items-center rounded-full border-2',
                                        'border-emerald-500 bg-emerald-500 text-white' => $eventComplete,
                                        'border-slate-200 bg-white text-slate-400' => ! $eventComplete,
                                    ])
                                >
                                    @if ($eventComplete)
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
                                    @else
                                        <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                                    @endif
                                </span>

                                <div class="min-w-0 pt-0.5">
                                    <p
                                        @class([
                                            'text-sm font-black',
                                            'text-slate-950' => $eventComplete,
                                            'text-slate-500' => ! $eventComplete,
                                        ])
                                    >
                                        {{ $event['label'] }}
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                        {{ $event['description'] }}
                                    </p>

                                    <p class="mt-1 text-[10px] font-bold text-slate-400">
                                        {{ $event['time']
                                            ? $event['time']->format('M d, Y · h:i A')
                                            : 'Not completed yet' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($order->delivery?->notes)
                        <div class="mt-6 rounded-2xl border border-red-100 bg-red-50 p-4">
                            <p class="text-[9px] font-black uppercase tracking-[0.14em] text-red-700">
                                Delivery notes
                            </p>

                            <p class="mt-1 text-sm font-semibold leading-6 text-red-800">
                                {{ $order->delivery->notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </section>
        </main>

        {{-- Management Sidebar --}}
        <aside class="order-first space-y-5 xl:order-none xl:sticky xl:top-24">
            {{-- Status Management --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-xl shadow-orange-900/5">
                <div class="border-b border-orange-100 px-5 py-4">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-orange-50 text-orange-600">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                class="h-5 w-5"
                            >
                                <path d="M4 6h16M4 12h16M4 18h10" />
                            </svg>
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                                Order Status
                            </p>

                            <h2 class="mt-1 text-xl font-black text-slate-950">
                                Update progress
                            </h2>
                        </div>
                    </div>
                </div>

                <form
                    id="order-status-form"
                    action="{{ route('admin.orders.status', $order) }}"
                    method="POST"
                    class="p-5"
                    x-on:submit="
                        if (
                            ! confirm('Update this order to the selected status?')
                        ) {
                            $event.preventDefault();
                        } else {
                            updatingStatus = true;
                        }
                    "
                >
                    @csrf
                    @method('PATCH')

                    <div class="rounded-2xl {{ $statusTheme['soft'] }} p-4">
                        <p class="text-[9px] font-black uppercase tracking-[0.12em] {{ $statusTheme['text'] }}">
                            Current status
                        </p>

                        <p class="mt-1 text-sm font-black text-slate-950">
                            {{ $orderStatusLabel }}
                        </p>
                    </div>

                    <label
                        for="order_status"
                        class="mt-4 block text-sm font-black text-slate-800"
                    >
                        Change Status
                    </label>

                    <div class="relative mt-2">
                        <select
                            id="order_status"
                            name="order_status"
                            class="min-h-12 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-semibold text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                        >
                            @foreach ($statuses as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected($order->order_status === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m6 9 6 6 6-6"
                            />
                        </svg>
                    </div>

                    @error('order_status')
                        <p class="mt-2 text-xs font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <button
                        type="submit"
                        x-bind:disabled="updatingStatus"
                        class="mt-4 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <svg
                            x-show="updatingStatus"
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

                        <span
                            x-text="updatingStatus
                                ? 'Updating status...'
                                : 'Save Order Status'"
                        ></span>
                    </button>
                </form>
            </section>

            {{-- Rider Assignment --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="border-b border-orange-100 px-5 py-4">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
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
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600">
                                Delivery Rider
                            </p>

                            <h2 class="mt-1 text-xl font-black text-slate-950">
                                Rider assignment
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[9px] font-black uppercase tracking-[0.12em] text-slate-400">
                            Current rider
                        </p>

                        @if ($order->rider)
                            <div class="mt-3 flex items-center gap-3">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-sm font-black text-blue-700 shadow-sm">
                                    {{ mb_strtoupper(
                                        mb_substr(
                                            $order->rider->name,
                                            0,
                                            1
                                        )
                                    ) }}
                                </span>

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-950">
                                        {{ $order->rider->name }}
                                    </p>

                                    <p class="mt-0.5 truncate text-xs font-semibold text-slate-500">
                                        {{ $order->rider->phone ?? $order->rider->email }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="mt-3 flex items-center gap-3">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="h-5 w-5"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5M12 16h.01" />
                                    </svg>
                                </span>

                                <div>
                                    <p class="text-sm font-black text-amber-800">
                                        No rider assigned
                                    </p>

                                    <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                        Select an active rider below.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if (! $isCompleted)
                        <form
                            action="{{ route('admin.orders.assign-rider', $order) }}"
                            method="POST"
                            class="mt-4"
                            x-on:submit="assigningRider = true"
                        >
                            @csrf

                            <label
                                for="rider_id"
                                class="block text-sm font-black text-slate-800"
                            >
                                Active Rider
                            </label>

                            <div class="relative mt-2">
                                <select
                                    id="rider_id"
                                    name="rider_id"
                                    required
                                    class="min-h-12 w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm font-semibold text-slate-900 outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                >
                                    <option value="">Choose a rider</option>

                                    @foreach ($activeRiders as $rider)
                                        <option
                                            value="{{ $rider->id }}"
                                            @selected($order->rider_id === $rider->id)
                                        >
                                            {{ $rider->name }}
                                            — {{ $rider->phone ?? $rider->email }}
                                        </option>
                                    @endforeach
                                </select>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m6 9 6 6 6-6"
                                    />
                                </svg>
                            </div>

                            @error('rider_id')
                                <p class="mt-2 text-xs font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <button
                                type="submit"
                                x-bind:disabled="assigningRider"
                                class="mt-3 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-70"
                            >
                                <span
                                    x-text="assigningRider
                                        ? 'Saving rider...'
                                        : @js($order->rider_id ? 'Change Rider' : 'Assign Rider')"
                                ></span>
                            </button>
                        </form>

                        @if ($order->rider_id)
                            <form
                                action="{{ route('admin.orders.unassign-rider', $order) }}"
                                method="POST"
                                class="mt-3"
                                x-on:submit="
                                    if (
                                        ! confirm('Remove the assigned rider from this order?')
                                    ) {
                                        $event.preventDefault();
                                    } else {
                                        unassigningRider = true;
                                    }
                                "
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    x-bind:disabled="unassigningRider"
                                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-red-100 bg-red-50 px-5 py-3 text-sm font-black text-red-600 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    <span
                                        x-text="unassigningRider
                                            ? 'Removing rider...'
                                            : 'Unassign Rider'"
                                    ></span>
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold leading-5 text-slate-600">
                                Delivered or cancelled orders cannot be reassigned.
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Payment Summary --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-5 py-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-orange-600">
                            Payment Summary
                        </p>

                        <h2 class="mt-1 text-xl font-black text-slate-950">
                            Order totals
                        </h2>
                    </div>

                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-orange-50 text-orange-600">
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
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-slate-50 px-3 py-3">
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                Method
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                {{ $paymentMethod }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 px-3 py-3">
                            <p class="text-[8px] font-black uppercase tracking-[0.1em] text-slate-400">
                                Status
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                {{ $paymentStatus }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="font-semibold text-slate-500">
                                Subtotal
                            </span>

                            <span class="font-black text-slate-950">
                                Rs. {{ number_format($order->subtotal, 0) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="font-semibold text-slate-500">
                                Delivery fee
                            </span>

                            <span class="font-black text-slate-950">
                                Rs. {{ number_format($order->delivery_fee, 0) }}
                            </span>
                        </div>

                        <div class="border-t border-orange-100 pt-4">
                            <div class="flex items-end justify-between gap-4">
                                <span class="font-black text-slate-950">
                                    Total
                                </span>

                                <span class="text-2xl font-black text-orange-600">
                                    Rs. {{ number_format($order->total, 0) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($paymentMethod === 'COD')
                        <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                            <p class="text-xs font-semibold leading-5 text-emerald-800">
                                The rider should collect
                                <strong>Rs. {{ number_format($order->total, 0) }}</strong>
                                from the customer upon delivery.
                            </p>
                        </div>
                    @endif
                </div>
            </section>
        </aside>
    </div>

    {{-- Persistent Mobile Actions --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-orange-100 bg-white/95 px-4 pt-3 shadow-[0_-12px_30px_rgba(15,23,42,0.14)] backdrop-blur xl:hidden">
        <div class="mx-auto flex items-center gap-2 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('admin.orders.index') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-orange-200 bg-orange-50 text-orange-700 transition active:scale-95"
                aria-label="Back to orders"
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

            @if ($customerPhoneHref)
                <a
                    href="tel:{{ $customerPhoneHref }}"
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-blue-200 bg-blue-50 text-blue-700 transition active:scale-95"
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
                        <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.7 19.7 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3" />
                    </svg>
                </a>
            @endif

            <button
                type="submit"
                form="order-status-form"
                x-bind:disabled="updatingStatus"
                class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-950/20 transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg
                    x-show="updatingStatus"
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

                <span
                    x-text="updatingStatus
                        ? 'Updating...'
                        : 'Save Order Status'"
                ></span>
            </button>
        </div>
    </div>
</div>

@endcomponent
