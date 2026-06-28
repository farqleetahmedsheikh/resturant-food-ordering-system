@component('layouts.rider', ['title' => $order->order_number])
@php
$deliveryStatus = $order->delivery?->status ?? 'assigned';

    $orderStatusLabel = \App\Models\Order::STATUSES[$order->order_status]
        ?? \Illuminate\Support\Str::headline($order->order_status);

    $deliveryStatusLabel = \Illuminate\Support\Str::headline(
        $deliveryStatus
    );

    $isDelivered = $deliveryStatus === 'delivered'
        || $order->order_status === 'delivered';

    $isFailed = $deliveryStatus === 'failed';

    $isCancelled = $order->order_status === 'cancelled';

    $isLocked = $isDelivered
        || $isFailed
        || $isCancelled;

    $itemCount = (int) $order->items->sum('quantity');

    $customerPhone = $order->customer_phone;

    $customerPhoneHref = $customerPhone
        ? preg_replace('/[^0-9+]/', '', $customerPhone)
        : null;

    $mapsUrl = 'https://www.google.com/maps/search/?api=1&query='
        . rawurlencode($order->delivery_address ?? '');

    $assignedAt = $order->assigned_at?->format('M d, Y · h:i A')
        ?? $order->delivery?->created_at?->format('M d, Y · h:i A');

    $pickedUpAt = $order->picked_up_at?->format('M d, Y · h:i A')
        ?? $order->delivery?->pickup_time?->format('M d, Y · h:i A');

    $outForDeliveryAt = $order->out_for_delivery_at?->format('M d, Y · h:i A')
        ?? null;

    $deliveredAt = $order->delivered_at?->format('M d, Y · h:i A')
        ?? $order->delivery?->delivered_time?->format('M d, Y · h:i A');

    $currentStep = match ($deliveryStatus) {
        'assigned', 'assigned_to_rider' => 1,
        'picked_up' => 2,
        'out_for_delivery' => 3,
        'delivered' => 4,
        default => 1,
    };

    $progressPercentage = match ($deliveryStatus) {
        'assigned', 'assigned_to_rider' => 15,
        'picked_up' => 45,
        'out_for_delivery' => 75,
        'delivered' => 100,
        default => 10,
    };

    $statusMessage = match (true) {
        $isDelivered => 'This order was delivered successfully. No further delivery updates are required.',
        $isFailed => 'This delivery was marked as failed. Review the delivery notes for more information.',
        $isCancelled => 'This order was cancelled and can no longer be delivered.',

        $deliveryStatus === 'out_for_delivery' =>
            'The order is on the way. Navigate to the customer and confirm delivery after handing it over.',

        $deliveryStatus === 'picked_up' =>
            'The order has been collected from the restaurant. Start the delivery when you are ready to leave.',

        in_array(
            $deliveryStatus,
            ['assigned', 'assigned_to_rider'],
            true
        ) =>
            'This delivery is assigned to you. Review the order and confirm pickup after collecting it.',

        default =>
            'Review the delivery details and continue with the next available action.',
    };

    $statusTheme = match (true) {
        $isDelivered => [
            'gradient' => 'from-leaf-700 via-leaf-500 to-teal-600',
            'soft' => 'border-leaf-100 bg-leaf-50',
            'text' => 'text-leaf-700',
            'dot' => 'bg-leaf-500',
        ],

        $isFailed || $isCancelled => [
            'gradient' => 'from-red-600 via-red-500 to-red-600',
            'soft' => 'border-red-100 bg-red-50',
            'text' => 'text-red-700',
            'dot' => 'bg-red-500',
        ],

        $deliveryStatus === 'out_for_delivery' => [
            'gradient' => 'from-blue-700 via-blue-600 to-indigo-700',
            'soft' => 'border-blue-100 bg-blue-50',
            'text' => 'text-blue-700',
            'dot' => 'bg-blue-500',
        ],

        $deliveryStatus === 'picked_up' => [
            'gradient' => 'from-sky-600 via-blue-600 to-indigo-700',
            'soft' => 'border-sky-100 bg-sky-50',
            'text' => 'text-sky-700',
            'dot' => 'bg-sky-500',
        ],

        default => [
            'gradient' => 'from-warm-950 via-warm-900 to-brand-900',
            'soft' => 'border-warm-200 bg-brand-50',
            'text' => 'text-brand-600',
            'dot' => 'bg-brand-500',
        ],
    };

    $nextAction = match (true) {
        $isLocked => null,

        in_array(
            $deliveryStatus,
            ['assigned', 'assigned_to_rider'],
            true
        ) => [
            'status' => 'picked_up',
            'label' => 'Confirm Order Pickup',
            'short_label' => 'Confirm Pickup',
            'description' => 'Use this after collecting the complete order from the restaurant.',
            'button' => 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/25',
            'icon_bg' => 'bg-indigo-500',
            'confirmation' => 'Confirm that you have collected this order from the restaurant?',
        ],

        $deliveryStatus === 'picked_up' => [
            'status' => 'out_for_delivery',
            'label' => 'Start Delivery',
            'short_label' => 'Start Delivery',
            'description' => 'Confirm that you are leaving the restaurant and heading to the customer.',
            'button' => 'bg-brand-500 hover:bg-brand-600 shadow-brand-500/25',
            'icon_bg' => 'bg-brand-500',
            'confirmation' => 'Confirm that you are now heading to the customer?',
        ],

        $deliveryStatus === 'out_for_delivery' => [
            'status' => 'delivered',
            'label' => 'Complete Delivery',
            'short_label' => 'Mark Delivered',
            'description' => $order->payment_method === 'cod'
                ? 'Use this only after handing the order to the customer and collecting payment.'
                : 'Use this only after handing the prepaid order to the customer.',
            'button' => 'bg-leaf-700 hover:bg-leaf-700 shadow-leaf-700/25',
            'icon_bg' => 'bg-leaf-500',
            'confirmation' => $order->payment_method === 'cod'
                ? 'Confirm that the order was delivered and payment was collected?'
                : 'Confirm that the prepaid order was delivered?',
        ],

        default => null,
    };

    $deliverySteps = [
        [
            'title' => 'Assigned',
            'description' => 'Delivery assigned to you.',
            'time' => $assignedAt,
        ],
        [
            'title' => 'Picked Up',
            'description' => 'Collected from the restaurant.',
            'time' => $pickedUpAt,
        ],
        [
            'title' => 'On the Way',
            'description' => 'Travelling to the customer.',
            'time' => $outForDeliveryAt,
        ],
        [
            'title' => 'Delivered',
            'description' => 'Handed to the customer.',
            'time' => $deliveredAt,
        ],
    ];

    $paymentMethod = $order->payment_method_label;

    $paymentStatus = $order->payment_status_label;
@endphp

<div
    x-data="{
        submitting: false,
        failureOpen: {{ $errors->has('notes') ? 'true' : 'false' }},
        statusConfirmOpen: false,
        failureConfirmOpen: false
    }"
    class="space-y-5 pb-28 sm:space-y-6 lg:pb-8"
>
    {{-- Mobile Navigation --}}
    <div class="flex items-center justify-between gap-4 lg:hidden">
        <a
            href="{{ route('rider.orders') }}"
            class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-warm-600 shadow-sm transition active:scale-95"
            aria-label="Back to deliveries"
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
            <p class="text-sm font-black text-warm-950">
                Delivery details
            </p>

            <p class="mt-0.5 truncate text-[10px] font-semibold text-warm-500">
                {{ $order->order_number }}
            </p>
        </div>

        @if ($customerPhoneHref)
            <a
                href="tel:{{ $customerPhoneHref }}"
                class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-brand-500 text-white shadow-lg shadow-brand-500/20 transition active:scale-95"
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
        @else
            <span class="h-11 w-11"></span>
        @endif
    </div>

    {{-- Desktop Header --}}
    <header class="hidden items-end justify-between gap-8 lg:flex">
        <div class="min-w-0">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-500">
                Delivery Details
            </p>

            <h1 class="mt-2 break-all text-4xl font-black tracking-tight text-warm-950">
                {{ $order->order_number }}
            </h1>

            <p class="mt-2 text-sm font-semibold text-warm-500">
                Assigned to you for delivery
            </p>
        </div>

        <a
            href="{{ route('rider.orders') }}"
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

            Back to Deliveries
        </a>
    </header>

    {{-- Status Hero --}}
    <section class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br {{ $statusTheme['gradient'] }} p-5 text-white shadow-2xl shadow-warm-950/20 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-white/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-28 left-12 h-72 w-72 rounded-full bg-brand-200/10 blur-3xl"></div>

        <div class="relative grid gap-6 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-white {{ ! $isLocked ? 'animate-pulse' : '' }}"></span>

                        Delivery status
                    </span>

                    <x-status-badge
                        :status="$order->order_status"
                    />

                    <x-status-badge
                        :status="$deliveryStatus"
                        type="delivery"
                    />
                </div>

                <h2 class="mt-4 text-2xl font-black tracking-tight sm:text-4xl">
                    {{ $deliveryStatusLabel }}
                </h2>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-white/85 sm:text-base sm:leading-7">
                    {{ $statusMessage }}
                </p>

                @unless ($isFailed || $isCancelled)
                    <div class="mt-5 max-w-2xl">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[9px] font-black uppercase tracking-[0.14em] text-white/65">
                                Delivery progress
                            </span>

                            <span class="text-xs font-black">
                                {{ $progressPercentage }}%
                            </span>
                        </div>

                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/20">
                            <div
                                class="h-full rounded-full bg-white shadow-sm transition-all duration-500"
                                style="width: {{ $progressPercentage }}%"
                            ></div>
                        </div>
                    </div>
                @endunless

                <div class="mt-5 grid grid-cols-2 gap-2 sm:flex">
                    @if ($customerPhoneHref)
                        <a
                            href="tel:{{ $customerPhoneHref }}"
                            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-black text-warm-950 shadow-lg transition active:scale-[0.98] hover:bg-warm-100 sm:rounded-2xl sm:px-5"
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

                        Directions
                    </a>
                </div>
            </div>

            {{-- Hero Metrics --}}
            <div class="grid grid-cols-3 gap-2 xl:min-w-[390px]">
                <div class="rounded-xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/60 sm:text-[10px]">
                        Items
                    </p>

                    <p class="mt-1 text-lg font-black sm:text-2xl">
                        {{ $itemCount }}
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/60 sm:text-[10px]">
                        Payment
                    </p>

                    <p class="mt-1 truncate text-sm font-black sm:text-lg">
                        {{ $paymentMethod }}
                    </p>
                </div>

                <div class="rounded-xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/60 sm:text-[10px]">
                        Collect
                    </p>

                    <p class="mt-1 truncate text-sm font-black text-brand-200 sm:text-lg">
                        @money($order->total)
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start lg:gap-6">
        {{-- Main Column --}}
        <div class="min-w-0 space-y-5">
            {{-- Customer and Address --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500">
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
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Customer
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Delivery information
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="grid gap-3 sm:grid-cols-2">
                        {{-- Customer --}}
                        <div class="flex items-center gap-3 rounded-2xl bg-warm-50 p-4">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-white text-base font-black text-brand-600 shadow-sm">
                                {{ mb_strtoupper(
                                    mb_substr(
                                        $order->customer_name ?? 'C',
                                        0,
                                        1
                                    )
                                ) }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-warm-500">
                                    Customer name
                                </p>

                                <p class="mt-1 truncate text-sm font-black text-warm-950">
                                    {{ $order->customer_name }}
                                </p>

                                @if ($customerPhone)
                                    <p class="mt-0.5 truncate text-xs font-semibold text-warm-500">
                                        {{ $customerPhone }}
                                    </p>
                                @endif
                            </div>

                            @if ($customerPhoneHref)
                                <a
                                    href="tel:{{ $customerPhoneHref }}"
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-100 text-brand-600 transition active:scale-95 hover:bg-brand-600 hover:text-white"
                                    aria-label="Call customer"
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

                        {{-- Collection --}}
                        <div class="flex items-center gap-3 rounded-2xl bg-leaf-50 p-4">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-white text-leaf-700 shadow-sm">
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

                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-leaf-700">
                                    Amount to collect
                                </p>

                                <p class="mt-1 text-lg font-black text-leaf-900">
                                    @money($order->total)
                                </p>

                                <p class="mt-0.5 text-xs font-semibold text-leaf-700">
                                    {{ $paymentMethod }} · {{ $paymentStatus }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Address --}}
                    <a
                        href="{{ $mapsUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="group mt-3 flex items-start gap-3 rounded-2xl border border-warm-200 bg-brand-50 p-4 transition hover:bg-brand-100"
                    >
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-brand-500 shadow-sm">
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
                            <span class="block text-[9px] font-black uppercase tracking-[0.14em] text-brand-600">
                                Delivery address
                            </span>

                            <span class="mt-1 block text-sm font-semibold leading-6 text-warm-600">
                                {{ $order->delivery_address }}
                            </span>

                            <span class="mt-2 inline-flex items-center gap-1.5 text-xs font-black text-brand-600">
                                Open directions

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
                        <div class="mt-3 rounded-2xl border border-gold-100 bg-gold-50 p-4">
                            <p class="text-[9px] font-black uppercase tracking-[0.14em] text-gold-700">
                                Customer instructions
                            </p>

                            <p class="mt-1.5 text-sm font-semibold leading-6 text-warm-600">
                                {{ $order->order_notes }}
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Delivery Progress --}}
            @unless ($isFailed || $isCancelled)
                <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                    <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                            Delivery Journey
                        </p>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                            Current progress
                        </h2>
                    </div>

                    {{-- Mobile Progress --}}
                    <div class="divide-y divide-warm-100 lg:hidden">
                        @foreach ($deliverySteps as $index => $step)
                            @php
                                $stepNumber = $index + 1;

                                $stepComplete = $stepNumber < $currentStep
                                    || $isDelivered;

                                $stepCurrent = $stepNumber === $currentStep
                                    && ! $isDelivered;
                            @endphp

                            <div class="flex items-start gap-3 p-4">
                                <span
                                    @class([
                                        'grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 text-xs font-black',
                                        'border-leaf-500 bg-leaf-500 text-white' => $stepComplete,
                                        'border-brand-500 bg-brand-50 text-brand-600 ring-4 ring-brand-100' => $stepCurrent,
                                        'border-warm-200 bg-white text-warm-500' => ! $stepComplete && ! $stepCurrent,
                                    ])
                                >
                                    @if ($stepComplete)
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
                                        {{ $stepNumber }}
                                    @endif
                                </span>

                                <div class="min-w-0 pt-0.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p
                                            @class([
                                                'text-sm font-black',
                                                'text-brand-600' => $stepCurrent,
                                                'text-warm-950' => ! $stepCurrent,
                                            ])
                                        >
                                            {{ $step['title'] }}
                                        </p>

                                        @if ($stepCurrent)
                                            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-[0.1em] text-brand-600">
                                                Current
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                        {{ $step['description'] }}
                                    </p>

                                    @if ($step['time'])
                                        <p class="mt-1 text-[10px] font-bold text-warm-500">
                                            {{ $step['time'] }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Desktop Progress --}}
                    <div class="hidden p-7 lg:block">
                        <div class="grid grid-cols-4">
                            @foreach ($deliverySteps as $index => $step)
                                @php
                                    $stepNumber = $index + 1;

                                    $stepComplete = $stepNumber < $currentStep
                                        || $isDelivered;

                                    $stepCurrent = $stepNumber === $currentStep
                                        && ! $isDelivered;
                                @endphp

                                <div class="relative px-3 text-center">
                                    @if ($index < count($deliverySteps) - 1)
                                        <div
                                            @class([
                                                'absolute left-1/2 top-5 h-0.5 w-full',
                                                'bg-leaf-500' => $stepNumber < $currentStep || $isDelivered,
                                                'bg-warm-200' => ! ($stepNumber < $currentStep || $isDelivered),
                                            ])
                                        ></div>
                                    @endif

                                    <span
                                        @class([
                                            'relative z-10 mx-auto grid h-10 w-10 place-items-center rounded-full border-2 text-xs font-black',
                                            'border-leaf-500 bg-leaf-500 text-white' => $stepComplete,
                                            'border-brand-500 bg-brand-50 text-brand-600 ring-4 ring-brand-100' => $stepCurrent,
                                            'border-warm-200 bg-white text-warm-500' => ! $stepComplete && ! $stepCurrent,
                                        ])
                                    >
                                        @if ($stepComplete)
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
                                            {{ $stepNumber }}
                                        @endif
                                    </span>

                                    <p class="mt-3 text-sm font-black text-warm-950">
                                        {{ $step['title'] }}
                                    </p>

                                    <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                        {{ $step['description'] }}
                                    </p>

                                    @if ($step['time'])
                                        <p class="mt-1.5 text-[10px] font-bold text-warm-500">
                                            {{ $step['time'] }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endunless

            {{-- Ordered Items --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                            Order Contents
                        </p>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                            Items to deliver
                        </h2>
                    </div>

                    <span class="rounded-full bg-brand-50 px-3 py-1.5 text-[10px] font-black text-brand-600">
                        {{ $itemCount }}
                        {{ $itemCount === 1 ? 'item' : 'items' }}
                    </span>
                </div>

                <div class="divide-y divide-warm-100">
                    @foreach ($order->items as $item)
                        <article class="p-4 sm:p-5">
                            <div class="flex items-start gap-3 sm:gap-4">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-lg font-black text-brand-500 sm:h-14 sm:w-14 sm:rounded-2xl">
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
                                            <h3 class="break-words text-sm font-black leading-5 text-warm-950 sm:text-base">
                                                {{ $item->item_name }}
                                            </h3>

                                            <p class="mt-1 text-xs font-semibold text-warm-500 sm:text-sm">
                                                {{ $item->quantity }}
                                                × @money($item->price)
                                            </p>
                                        </div>

                                        <p class="shrink-0 text-sm font-black text-warm-950 sm:text-base">
                                            @money($item->total)
                                        </p>
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

            {{-- Mobile Payment Summary --}}
            <section class="rounded-[1.5rem] border border-warm-200 bg-white p-4 shadow-sm lg:hidden">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-brand-500">
                            Payment Summary
                        </p>

                        <h2 class="mt-1 text-lg font-black text-warm-950">
                            Amount to collect
                        </h2>
                    </div>

                    <p class="text-2xl font-black text-brand-500">
                        @money($order->total)
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-warm-50 px-3 py-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-warm-500">
                            Subtotal
                        </p>

                        <p class="mt-1 text-sm font-black text-warm-950">
                            @money($order->subtotal)
                        </p>
                    </div>

                    <div class="rounded-xl bg-warm-50 px-3 py-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-warm-500">
                            Delivery
                        </p>

                        <p class="mt-1 text-sm font-black text-warm-950">
                            @money($order->delivery_fee)
                        </p>
                    </div>
                </div>
            </section>
        </div>

        {{-- Action Sidebar --}}
        <aside class="order-first space-y-5 lg:order-none lg:sticky lg:top-24">
            {{-- Next Required Action --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-xl shadow-brand-900/5">
                <div class="border-b border-warm-200 p-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500">
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
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                                Next Required Action
                            </p>

                            <h2 class="mt-1 text-xl font-black text-warm-950">
                                @if ($nextAction)
                                    {{ $nextAction['label'] }}
                                @else
                                    Delivery updates complete
                                @endif
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    @if ($nextAction)
                        <p class="text-sm font-semibold leading-6 text-warm-600">
                            {{ $nextAction['description'] }}
                        </p>

                        <form
                            id="next-status-form"
                            x-ref="nextStatusForm"
                            action="{{ route('rider.orders.update-status', $order) }}"
                            method="POST"
                            class="mt-5"
                            x-on:submit.prevent="statusConfirmOpen = true"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="status"
                                value="{{ $nextAction['status'] }}"
                            >

                            <button
                                type="submit"
                                x-bind:disabled="submitting"
                                class="inline-flex min-h-14 w-full items-center justify-center gap-2 rounded-2xl px-5 py-4 text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70 {{ $nextAction['button'] }}"
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

                                <span
                                    x-text="submitting
                                        ? 'Updating status...'
                                        : @js($nextAction['label'])"
                                ></span>

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
                        </form>

                        <div
                            x-show="statusConfirmOpen"
                            x-cloak
                            x-transition.opacity
                            class="fixed inset-0 z-[300] grid place-items-center bg-warm-950/55 p-4 backdrop-blur-sm"
                            role="dialog"
                            aria-modal="true"
                        >
                            <div
                                x-show="statusConfirmOpen"
                                x-transition
                                x-on:click.outside="statusConfirmOpen = false"
                                class="w-full max-w-md rounded-[1.5rem] border border-warm-200 bg-white p-5 shadow-2xl shadow-warm-950/20"
                            >
                                <div class="flex items-start gap-4">
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-500">
                                        <x-ui-icon name="scooter" class="h-5 w-5" />
                                    </span>

                                    <div>
                                        <h2 class="text-lg font-black text-warm-950">
                                            Confirm delivery update?
                                        </h2>

                                        <p class="mt-2 text-sm font-semibold leading-6 text-warm-600">
                                            {{ $nextAction['confirmation'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                    <button
                                        type="button"
                                        x-on:click="statusConfirmOpen = false"
                                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-warm-200 bg-white px-4 py-2 text-sm font-black text-warm-700 transition hover:bg-warm-50"
                                    >
                                        Cancel
                                    </button>

                                    <button
                                        type="button"
                                        x-on:click="submitting = true; statusConfirmOpen = false; $refs.nextStatusForm.submit()"
                                        class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-500 px-4 py-2 text-sm font-black text-white transition hover:bg-brand-600"
                                    >
                                        Confirm Update
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Failure Flow --}}
                        <div class="mt-5 border-t border-warm-100 pt-5">
                            <button
                                type="button"
                                x-on:click="failureOpen = ! failureOpen"
                                class="flex w-full items-center justify-between gap-4 rounded-xl px-2 py-2 text-left text-sm font-black text-red-600 transition hover:bg-red-50"
                            >
                                <span>Unable to complete delivery?</span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-4 w-4 transition"
                                    x-bind:class="failureOpen ? 'rotate-180' : ''"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m6 9 6 6 6-6"
                                    />
                                </svg>
                            </button>

                            <form
                                x-show="failureOpen"
                                x-collapse
                                x-ref="failureForm"
                                action="{{ route('rider.orders.update-status', $order) }}"
                                method="POST"
                                class="mt-3 rounded-2xl border border-red-100 bg-red-50 p-4"
                                x-on:submit.prevent="
                                    if ($el.reportValidity()) {
                                        failureConfirmOpen = true;
                                    }
                                "
                            >
                                @csrf

                                <input
                                    type="hidden"
                                    name="status"
                                    value="failed"
                                >

                                <label
                                    for="failed_delivery_notes"
                                    class="block text-sm font-black text-red-900"
                                >
                                    Failure reason
                                </label>

                                <p class="mt-1 text-xs font-semibold leading-5 text-red-700">
                                    Provide a clear reason for the administrator.
                                </p>

                                <textarea
                                    id="failed_delivery_notes"
                                    name="notes"
                                    rows="3"
                                    required
                                    minlength="5"
                                    placeholder="Example: Customer did not answer after multiple calls."
                                    class="mt-3 w-full resize-y rounded-xl border border-red-200 bg-white px-4 py-3 text-sm font-semibold leading-6 text-warm-900 outline-none transition placeholder:text-warm-500 focus:border-red-400 focus:ring-4 focus:ring-red-100"
                                >{{ old('notes') }}</textarea>

                                @error('notes')
                                    <p class="mt-2 text-xs font-semibold text-red-700">
                                        {{ $message }}
                                    </p>
                                @enderror

                                <button
                                    type="submit"
                                    class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-red-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700"
                                >
                                    Mark Delivery as Failed
                                </button>
                            </form>

                            <div
                                x-show="failureConfirmOpen"
                                x-cloak
                                x-transition.opacity
                                class="fixed inset-0 z-[300] grid place-items-center bg-warm-950/55 p-4 backdrop-blur-sm"
                                role="dialog"
                                aria-modal="true"
                            >
                                <div
                                    x-show="failureConfirmOpen"
                                    x-transition
                                    x-on:click.outside="failureConfirmOpen = false"
                                    class="w-full max-w-md rounded-[1.5rem] border border-warm-200 bg-white p-5 shadow-2xl shadow-warm-950/20"
                                >
                                    <div class="flex items-start gap-4">
                                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-red-50 text-red-600">
                                            <x-ui-icon name="trash" class="h-5 w-5" />
                                        </span>

                                        <div>
                                            <h2 class="text-lg font-black text-warm-950">
                                                Mark delivery as failed?
                                            </h2>

                                            <p class="mt-2 text-sm font-semibold leading-6 text-warm-600">
                                                This will notify the admin that the delivery could not be completed and save your reason.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                        <button
                                            type="button"
                                            x-on:click="failureConfirmOpen = false"
                                            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-warm-200 bg-white px-4 py-2 text-sm font-black text-warm-700 transition hover:bg-warm-50"
                                        >
                                            Cancel
                                        </button>

                                        <button
                                            type="button"
                                            x-on:click="submitting = true; failureConfirmOpen = false; $refs.failureForm.submit()"
                                            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-black text-white transition hover:bg-red-700"
                                        >
                                            Mark Failed
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl {{ $statusTheme['soft'] }} p-4">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white {{ $statusTheme['text'] }} shadow-sm">
                                    @if ($isDelivered)
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
                                            <rect x="5" y="10" width="14" height="11" rx="2" />
                                            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                        </svg>
                                    @endif
                                </span>

                                <div>
                                    <p class="font-black text-warm-950">
                                        Status updates locked
                                    </p>

                                    <p class="mt-1 text-sm font-semibold leading-6 text-warm-600">
                                        {{ $statusMessage }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a
                            href="{{ route('rider.orders') }}"
                            class="mt-4 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-warm-900 px-5 py-3 text-sm font-black text-white transition hover:bg-warm-900"
                        >
                            Return to Deliveries
                        </a>
                    @endif
                </div>
            </section>

            {{-- Desktop Payment Summary --}}
            <section class="hidden rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm lg:block">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                    Payment Summary
                </p>

                <h2 class="mt-1 text-xl font-black text-warm-950">
                    Amount to collect
                </h2>

                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="font-semibold text-warm-500">
                            Subtotal
                        </span>

                        <span class="font-black text-warm-950">
                            @money($order->subtotal)
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="font-semibold text-warm-500">
                            Delivery fee
                        </span>

                        <span class="font-black text-warm-950">
                            @money($order->delivery_fee)
                        </span>
                    </div>

                    <div class="border-t border-warm-200 pt-4">
                        <div class="flex items-end justify-between gap-4">
                            <span class="font-black text-warm-950">
                                Collectable total
                            </span>

                            <span class="text-2xl font-black text-brand-500">
                                @money($order->total)
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl bg-leaf-50 p-4">
                    <div class="flex items-start gap-3">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="mt-0.5 h-5 w-5 shrink-0 text-leaf-700"
                        >
                            <rect x="3" y="6" width="18" height="12" rx="2" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>

                        <div>
                            <p class="text-sm font-black text-leaf-900">
                                {{ $paymentMethod }}
                            </p>

                            <p class="mt-1 text-xs font-semibold text-leaf-700">
                                Payment status: {{ $paymentStatus }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Delivery Notes --}}
            @if ($order->delivery?->notes)
                <section class="rounded-[1.75rem] border border-red-100 bg-red-50 p-5 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-red-700">
                        Delivery Notes
                    </p>

                    <p class="mt-2 text-sm font-semibold leading-6 text-red-800">
                        {{ $order->delivery->notes }}
                    </p>
                </section>
            @endif
        </aside>
    </div>

    {{-- Persistent Mobile Controls --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur lg:hidden">
        <div class="mx-auto flex items-center gap-2 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            @if ($customerPhoneHref)
                <a
                    href="tel:{{ $customerPhoneHref }}"
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-95"
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
                href="{{ $mapsUrl }}"
                target="_blank"
                rel="noopener"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-blue-200 bg-blue-50 text-blue-700 transition active:scale-95"
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

            @if ($nextAction)
                <button
                    type="submit"
                    form="next-status-form"
                    x-bind:disabled="submitting"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-black text-white shadow-lg transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-70 {{ $nextAction['button'] }}"
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

                    <span
                        x-text="submitting
                            ? 'Updating...'
                            : @js($nextAction['short_label'])"
                    ></span>
                </button>
            @else
                <a
                    href="{{ route('rider.orders') }}"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center rounded-xl bg-warm-900 px-4 py-3 text-sm font-black text-white transition active:scale-[0.98]"
                >
                    Back to Deliveries
                </a>
            @endif
        </div>
    </div>
</div>

@endcomponent
