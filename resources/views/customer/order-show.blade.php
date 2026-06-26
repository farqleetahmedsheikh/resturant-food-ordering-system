@component('layouts.customer', ['title' => $order->order_number])
@php
$deliveryStatus = $order->delivery?->status;

    $effectiveStatus = in_array(
        $deliveryStatus,
        ['assigned', 'picked_up', 'out_for_delivery', 'delivered', 'failed'],
        true
    )
        ? $deliveryStatus
        : $order->order_status;

    $isCancelled = $order->order_status === 'cancelled'
        || $deliveryStatus === 'failed';

    $isDelivered = $order->order_status === 'delivered'
        || $deliveryStatus === 'delivered';

    $isActiveOrder = ! $isCancelled && ! $isDelivered;

    $orderStatusLabel = $isCancelled
        ? ($deliveryStatus === 'failed' ? 'Delivery Failed' : 'Cancelled')
        : (
            \App\Models\Order::STATUSES[$order->order_status]
            ?? \Illuminate\Support\Str::headline($effectiveStatus)
        );

    $itemCount = (int) $order->items->sum('quantity');

    $paymentMethod = strtoupper(
        $order->payment_method ?? 'COD'
    );

    $paymentStatus = \Illuminate\Support\Str::headline(
        $order->payment_status ?? 'pending'
    );

    $currentStage = match ($effectiveStatus) {
        'pending' => 1,
        'accepted' => 2,
        'preparing' => 2,
        'ready' => 3,
        'assigned', 'assigned_to_rider' => 3,
        'picked_up' => 4,
        'out_for_delivery' => 4,
        'delivered' => 5,
        default => 1,
    };

    $progressPercentage = match ($effectiveStatus) {
        'pending' => 10,
        'accepted' => 25,
        'preparing' => 42,
        'ready' => 58,
        'assigned', 'assigned_to_rider' => 70,
        'picked_up' => 82,
        'out_for_delivery' => 90,
        'delivered' => 100,
        default => 10,
    };

    $statusMessage = match ($effectiveStatus) {
        'pending' => 'Your order has been received and is waiting for restaurant confirmation.',
        'accepted' => 'The restaurant has accepted your order and will begin preparing it.',
        'preparing' => 'Your meal is currently being prepared by the restaurant.',
        'ready' => 'Your order is ready and waiting to be collected by a rider.',
        'assigned', 'assigned_to_rider' => 'A delivery rider has been assigned to your order.',
        'picked_up' => 'Your rider has collected the order from the restaurant.',
        'out_for_delivery' => 'Your order is on the way to your delivery address.',
        'delivered' => 'Your order has been delivered successfully.',
        'failed' => 'The rider was unable to complete this delivery.',
        default => $isCancelled
            ? 'This order has been cancelled.'
            : 'The latest progress of your order is shown below.',
    };

    $statusTheme = match (true) {
        $isCancelled => [
            'hero' => 'from-red-600 via-red-500 to-red-600',
            'soft' => 'border-red-100 bg-red-50',
            'text' => 'text-red-700',
            'dot' => 'bg-red-500',
            'progress' => 'from-red-500 to-red-500',
        ],

        $isDelivered => [
            'hero' => 'from-leaf-700 via-leaf-500 to-teal-600',
            'soft' => 'border-leaf-100 bg-leaf-50',
            'text' => 'text-leaf-700',
            'dot' => 'bg-leaf-500',
            'progress' => 'from-leaf-500 to-teal-500',
        ],

        in_array($effectiveStatus, ['picked_up', 'out_for_delivery'], true) => [
            'hero' => 'from-blue-600 via-blue-500 to-indigo-600',
            'soft' => 'border-blue-100 bg-blue-50',
            'text' => 'text-blue-700',
            'dot' => 'bg-blue-500',
            'progress' => 'from-blue-500 to-brand-500',
        ],

        in_array($effectiveStatus, ['accepted', 'preparing', 'ready'], true) => [
            'hero' => 'from-gold-500 via-food-tan to-food-brown',
            'soft' => 'border-gold-100 bg-gold-50',
            'text' => 'text-food-brown',
            'dot' => 'bg-food-tan',
            'progress' => 'from-gold-500 to-food-tan',
        ],

        default => [
            'hero' => 'from-brand-500 via-brand-600 to-brand-800',
            'soft' => 'border-warm-200 bg-brand-50',
            'text' => 'text-brand-600',
            'dot' => 'bg-brand-500',
            'progress' => 'from-brand-500 to-brand-600',
        ],
    };

    $progressStages = [
        [
            'title' => 'Order received',
            'description' => 'Your order was sent to the restaurant.',
        ],
        [
            'title' => 'Preparing',
            'description' => 'The restaurant confirms and prepares your food.',
        ],
        [
            'title' => 'Rider assigned',
            'description' => 'A rider is assigned to collect your order.',
        ],
        [
            'title' => 'On the way',
            'description' => 'The rider travels to your delivery address.',
        ],
        [
            'title' => 'Delivered',
            'description' => 'Your order arrives and payment is collected.',
        ],
    ];

    $riderPhone = $order->rider?->phone;
    $riderPhoneHref = $riderPhone
        ? preg_replace('/[^0-9+]/', '', $riderPhone)
        : null;
@endphp

<div
    x-data="{
        copied: false,

        async copyOrderNumber() {
            try {
                await navigator.clipboard.writeText(
                    @js($order->order_number)
                );

                this.copied = true;

                setTimeout(() => {
                    this.copied = false;
                }, 1800);
            } catch (error) {
                this.copied = false;
            }
        }
    }"
    class="space-y-5 pb-28 sm:space-y-7 lg:pb-8"
>
    {{-- Mobile Navigation --}}
    <div class="flex items-center justify-between gap-4 lg:hidden">
        <a
            href="{{ route('customer.orders') }}"
            class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-warm-600 shadow-sm transition active:scale-95"
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
            <p class="text-sm font-black text-warm-950">
                Order details
            </p>

            <p class="mt-0.5 truncate text-[10px] font-semibold text-warm-500">
                {{ $order->order_number }}
            </p>
        </div>

        <button
            type="button"
            x-on:click="copyOrderNumber"
            class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-warm-200 bg-white text-brand-500 shadow-sm transition active:scale-95"
            aria-label="Copy order number"
        >
            <svg
                x-show="! copied"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-5 w-5"
            >
                <rect x="9" y="9" width="11" height="11" rx="2" />
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
            </svg>

            <svg
                x-show="copied"
                x-cloak
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                class="h-5 w-5 text-leaf-700"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m5 12 4 4L19 6"
                />
            </svg>
        </button>
    </div>

    {{-- Desktop Header --}}
    <header class="hidden items-end justify-between gap-8 lg:flex">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-brand-500">
                Order Details
            </p>

            <div class="mt-2 flex items-center gap-3">
                <h1 class="break-all text-4xl font-black tracking-tight text-warm-950">
                    {{ $order->order_number }}
                </h1>

                <button
                    type="button"
                    x-on:click="copyOrderNumber"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-500 transition hover:bg-brand-100"
                    aria-label="Copy order number"
                >
                    <svg
                        x-show="! copied"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-4 w-4"
                    >
                        <rect x="9" y="9" width="11" height="11" rx="2" />
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>

                    <svg
                        x-show="copied"
                        x-cloak
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        class="h-4 w-4 text-leaf-700"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m5 12 4 4L19 6"
                        />
                    </svg>
                </button>
            </div>

            <p class="mt-2 text-sm font-semibold text-warm-500">
                Placed {{ $order->created_at->format('M d, Y · h:i A') }}
            </p>
        </div>

        <a
            href="{{ route('customer.orders') }}"
            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-white px-5 py-3 text-sm font-black text-brand-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-brand-50"
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

    {{-- Status Hero --}}
    <section class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br {{ $statusTheme['hero'] }} p-5 text-white shadow-2xl shadow-brand-900/15 sm:p-7 lg:rounded-[2rem] lg:p-8">
        <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-10 h-60 w-60 rounded-full bg-white/10 blur-3xl"></div>

        <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/15 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] backdrop-blur">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>

                        Current status
                    </span>

                    @if ($isActiveOrder)
                        <span class="rounded-full bg-white/15 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] backdrop-blur">
                            Live order
                        </span>
                    @endif
                </div>

                <h2 class="mt-3 text-2xl font-black tracking-tight sm:text-4xl">
                    {{ $orderStatusLabel }}
                </h2>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-white/90 sm:text-base sm:leading-7">
                    {{ $statusMessage }}
                </p>

                @unless ($isCancelled)
                    <div class="mt-5 max-w-2xl">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[9px] font-black uppercase tracking-[0.14em] text-white/70">
                                Overall progress
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
            </div>

            <div class="grid grid-cols-3 gap-2 sm:gap-3 lg:min-w-[360px]">
                <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70 sm:text-[10px]">
                        Items
                    </p>

                    <p class="mt-1 text-lg font-black sm:text-2xl">
                        {{ $itemCount }}
                    </p>
                </div>

                <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70 sm:text-[10px]">
                        Payment
                    </p>

                    <p class="mt-1 truncate text-sm font-black sm:text-lg">
                        {{ $paymentMethod }}
                    </p>
                </div>

                <div class="rounded-xl border border-white/20 bg-white/15 px-3 py-3 backdrop-blur sm:rounded-2xl sm:px-4 sm:py-4">
                    <p class="text-[8px] font-black uppercase tracking-[0.1em] text-white/70 sm:text-[10px]">
                        Total
                    </p>

                    <p class="mt-1 truncate text-sm font-black sm:text-lg">
                        ($order->total)
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Delivery Progress --}}
    @unless ($isCancelled)
        <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
            <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                    Delivery Progress
                </p>

                <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                    Track your order
                </h2>
            </div>

            {{-- Mobile Vertical Progress --}}
            <div class="divide-y divide-warm-100 lg:hidden">
                @foreach ($progressStages as $index => $stage)
                    @php
                        $stageNumber = $index + 1;
                        $stageCompleted = $stageNumber < $currentStage
                            || $isDelivered;

                        $stageCurrent = $stageNumber === $currentStage
                            && ! $isDelivered;
                    @endphp

                    <div class="flex items-start gap-3 p-4">
                        <div class="relative shrink-0">
                            <span
                                @class([
                                    'relative z-10 grid h-10 w-10 place-items-center rounded-full border-2 text-xs font-black',
                                    'border-leaf-500 bg-leaf-500 text-white' => $stageCompleted,
                                    'border-brand-500 bg-brand-50 text-brand-600 ring-4 ring-brand-100' => $stageCurrent,
                                    'border-warm-200 bg-white text-warm-500' => ! $stageCompleted && ! $stageCurrent,
                                ])
                            >
                                @if ($stageCompleted)
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
                                    {{ $stageNumber }}
                                @endif
                            </span>
                        </div>

                        <div class="min-w-0 pt-0.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <p
                                    @class([
                                        'text-sm font-black',
                                        'text-brand-600' => $stageCurrent,
                                        'text-warm-950' => ! $stageCurrent,
                                    ])
                                >
                                    {{ $stage['title'] }}
                                </p>

                                @if ($stageCurrent)
                                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-[0.1em] text-brand-600">
                                        Current
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                {{ $stage['description'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop Horizontal Progress --}}
            <div class="hidden p-7 lg:block">
                <div class="grid grid-cols-5">
                    @foreach ($progressStages as $index => $stage)
                        @php
                            $stageNumber = $index + 1;
                            $stageCompleted = $stageNumber < $currentStage
                                || $isDelivered;

                            $stageCurrent = $stageNumber === $currentStage
                                && ! $isDelivered;
                        @endphp

                        <div class="relative px-3 text-center">
                            @if ($index < count($progressStages) - 1)
                                <div
                                    @class([
                                        'absolute left-1/2 top-5 h-0.5 w-full',
                                        'bg-leaf-500' => $stageNumber < $currentStage || $isDelivered,
                                        'bg-warm-200' => ! ($stageNumber < $currentStage || $isDelivered),
                                    ])
                                ></div>
                            @endif

                            <span
                                @class([
                                    'relative z-10 mx-auto grid h-10 w-10 place-items-center rounded-full border-2 text-xs font-black',
                                    'border-leaf-500 bg-leaf-500 text-white' => $stageCompleted,
                                    'border-brand-500 bg-brand-50 text-brand-600 ring-4 ring-brand-100' => $stageCurrent,
                                    'border-warm-200 bg-white text-warm-500' => ! $stageCompleted && ! $stageCurrent,
                                ])
                            >
                                @if ($stageCompleted)
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
                                    {{ $stageNumber }}
                                @endif
                            </span>

                            <p class="mt-3 text-sm font-black text-warm-950">
                                {{ $stage['title'] }}
                            </p>

                            <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                                {{ $stage['description'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endunless

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_370px] lg:items-start lg:gap-7">
        {{-- Main Column --}}
        <div class="min-w-0 space-y-5">
            {{-- Mobile Rider Card --}}
            <section class="rounded-[1.5rem] border border-warm-200 bg-white p-4 shadow-sm lg:hidden">
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
                            <path d="M3 7h11v10H3z" />
                            <path d="M14 10h4l3 3v4h-7z" />
                            <circle cx="7" cy="18" r="2" />
                            <circle cx="18" cy="18" r="2" />
                        </svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500">
                            Delivery Partner
                        </p>

                        <h2 class="mt-1 text-base font-black text-warm-950">
                            @if ($order->rider)
                                {{ $order->rider->name }}
                            @else
                                Rider not assigned yet
                            @endif
                        </h2>

                        @if ($order->rider)
                            <p class="mt-0.5 truncate text-xs font-semibold text-warm-500">
                                {{ $order->rider->phone ?? $order->rider->email }}
                            </p>
                        @else
                            <p class="mt-0.5 text-xs font-semibold leading-5 text-warm-500">
                                Rider information will appear after assignment.
                            </p>
                        @endif
                    </div>

                    @if ($riderPhoneHref && $isActiveOrder)
                        <a
                            href="tel:{{ $riderPhoneHref }}"
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-500 text-white shadow-lg shadow-brand-500/20 transition active:scale-95"
                            aria-label="Call rider"
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
                </div>
            </section>

            {{-- Ordered Items --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-4 border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                            Your Meal
                        </p>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                            Ordered items
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
                                    {{ mb_substr($item->item_name, 0, 1) }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h3 class="break-words text-sm font-black leading-5 text-warm-950 sm:text-base">
                                                {{ $item->item_name }}
                                            </h3>

                                            <p class="mt-1 text-xs font-semibold text-warm-500 sm:text-sm">
                                                {{ $item->quantity }}
                                                × ($item->price)
                                            </p>
                                        </div>

                                        <p class="shrink-0 text-sm font-black text-warm-950 sm:text-base">
                                            ($item->total)
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

            {{-- Delivery Information --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
                <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
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
                                <path d="M12 22s7-6 7-13a7 7 0 1 0-14 0c0 7 7 13 7 13z" />
                                <circle cx="12" cy="9" r="2.5" />
                            </svg>
                        </span>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600">
                                Delivery Information
                            </p>

                            <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                                Delivery address
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <p class="break-words text-sm font-semibold leading-6 text-warm-600">
                        {{ $order->delivery_address }}
                    </p>

                    @if ($order->order_notes)
                        <div class="mt-4 rounded-xl border border-gold-100 bg-gold-50 p-4">
                            <p class="text-[9px] font-black uppercase tracking-[0.14em] text-gold-700">
                                Delivery instructions
                            </p>

                            <p class="mt-1.5 text-sm font-semibold leading-6 text-warm-600">
                                {{ $order->order_notes }}
                            </p>
                        </div>
                    @endif
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
                            Order total
                        </h2>
                    </div>

                    <p class="text-2xl font-black text-brand-500">
                        ($order->total)
                    </p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-warm-50 px-3 py-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-warm-500">
                            Subtotal
                        </p>

                        <p class="mt-1 text-sm font-black text-warm-950">
                            ($order->subtotal)
                        </p>
                    </div>

                    <div class="rounded-xl bg-warm-50 px-3 py-3">
                        <p class="text-[9px] font-black uppercase tracking-[0.1em] text-warm-500">
                            Delivery
                        </p>

                        <p class="mt-1 text-sm font-black text-warm-950">
                            ($order->delivery_fee)
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-leaf-50 px-3 py-3">
                    <div>
                        <p class="text-xs font-black text-leaf-900">
                            {{ $paymentMethod }}
                        </p>

                        <p class="mt-0.5 text-[10px] font-semibold text-leaf-700">
                            {{ $paymentStatus }}
                        </p>
                    </div>

                    <x-status-badge
                        :status="$order->payment_status"
                        type="payment"
                    />
                </div>
            </section>
        </div>

        {{-- Desktop Sidebar --}}
        <aside class="hidden space-y-5 lg:sticky lg:top-24 lg:block">
            {{-- Status --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-500">
                    Current Status
                </p>

                <h2 class="mt-2 text-xl font-black tracking-tight text-warm-950">
                    Order progress
                </h2>

                <div class="mt-4 flex flex-wrap gap-2">
                    <x-status-badge :status="$order->order_status" />

                    @if ($deliveryStatus)
                        <x-status-badge
                            :status="$deliveryStatus"
                            type="delivery"
                        />
                    @endif
                </div>

                <div class="mt-5 space-y-3 border-t border-warm-200 pt-5 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="font-semibold text-warm-500">
                            Payment method
                        </span>

                        <span class="font-black text-warm-950">
                            {{ $paymentMethod }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="font-semibold text-warm-500">
                            Payment status
                        </span>

                        <span class="font-black text-warm-950">
                            {{ $paymentStatus }}
                        </span>
                    </div>
                </div>
            </section>

            {{-- Rider --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-sm">
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
                            <path d="M3 7h11v10H3z" />
                            <path d="M14 10h4l3 3v4h-7z" />
                            <circle cx="7" cy="18" r="2" />
                            <circle cx="18" cy="18" r="2" />
                        </svg>
                    </span>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-brand-500">
                            Delivery Partner
                        </p>

                        <h2 class="mt-1 text-lg font-black text-warm-950">
                            Assigned rider
                        </h2>
                    </div>
                </div>

                @if ($order->rider)
                    <div class="mt-4 rounded-2xl bg-warm-50 p-4">
                        <div class="flex items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-sm font-black text-brand-600 shadow-sm">
                                {{ mb_substr($order->rider->name, 0, 1) }}
                            </span>

                            <div class="min-w-0">
                                <p class="truncate font-black text-warm-950">
                                    {{ $order->rider->name }}
                                </p>

                                <p class="mt-1 break-all text-xs font-semibold text-warm-500">
                                    {{ $order->rider->phone ?? $order->rider->email }}
                                </p>
                            </div>
                        </div>

                        @if ($riderPhoneHref && $isActiveOrder)
                            <a
                                href="tel:{{ $riderPhoneHref }}"
                                class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600"
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

                                Call Rider
                            </a>
                        @endif
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-brand-200 bg-brand-50 p-4 text-center">
                        <p class="text-sm font-semibold leading-6 text-warm-600">
                            Rider details will appear here after assignment.
                        </p>
                    </div>
                @endif
            </section>

            {{-- Price Summary --}}
            <section class="rounded-[1.75rem] border border-warm-200 bg-white p-5 shadow-xl shadow-brand-900/5">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-brand-500">
                    Payment Summary
                </p>

                <h2 class="mt-2 text-xl font-black tracking-tight text-warm-950">
                    Order total
                </h2>

                <div class="mt-5 space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="font-semibold text-warm-500">
                            Subtotal
                        </span>

                        <span class="font-black text-warm-950">
                            ($order->subtotal)
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="font-semibold text-warm-500">
                            Delivery fee
                        </span>

                        <span class="font-black text-warm-950">
                            ($order->delivery_fee)
                        </span>
                    </div>

                    <div class="border-t border-warm-200 pt-4">
                        <div class="flex items-end justify-between gap-4">
                            <span class="font-black text-warm-950">
                                Total
                            </span>

                            <span class="text-2xl font-black text-brand-500">
                                ($order->total)
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    {{-- Persistent Mobile Actions --}}
    <div class="fixed inset-x-0 bottom-0 z-50 border-t border-warm-200 bg-white/95 px-4 pt-3 shadow-[var(--shadow-bottom-nav)] backdrop-blur lg:hidden">
        <div class="mx-auto flex items-center gap-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
            <a
                href="{{ route('customer.orders') }}"
                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border border-brand-200 bg-brand-50 text-brand-600 transition active:scale-95"
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

            @if ($riderPhoneHref && $isActiveOrder)
                <a
                    href="tel:{{ $riderPhoneHref }}"
                    class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98]"
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

                        Call Rider
                    </a>
                @else
                    <a
                        href="{{ route('customer.orders') }}"
                        class="inline-flex min-h-12 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/25 transition active:scale-[0.98]"
                    >
                        Back to My Orders

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
            @endif
        </div>
    </div>
</div>

@endcomponent
