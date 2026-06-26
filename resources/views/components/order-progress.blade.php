@props([
'status',
'title' => 'Delivery progress',
'description' => null,
'showHeader' => true,
'standalone' => true,
])

@php
$steps = [
'pending' => 'Pending',
'accepted' => 'Accepted',
'preparing' => 'Preparing',
'ready' => 'Ready',
'assigned_to_rider' => 'Rider Assigned',
'out_for_delivery' => 'Out for Delivery',
'delivered' => 'Delivered',
];

$stepDescriptions = [
    'pending' => 'Waiting for restaurant confirmation',
    'accepted' => 'The restaurant has accepted the order',
    'preparing' => 'Your food is being freshly prepared',
    'ready' => 'The order is ready for rider pickup',
    'assigned_to_rider' => 'A delivery rider has been assigned',
    'out_for_delivery' => 'Your order is currently on the way',
    'delivered' => 'The order has been successfully delivered',
];

$keys = array_keys($steps);
$totalSteps = count($steps);

$isCancelled = $status === 'cancelled';
$isDelivered = $status === 'delivered';

$currentIndex = array_search($status, $keys, true);
$currentIndex = $currentIndex === false ? 0 : $currentIndex;

$currentLabel = $isCancelled
    ? 'Order Cancelled'
    : (
        $steps[$status]
        ?? \Illuminate\Support\Str::headline($status)
    );

$currentDescription = $isCancelled
    ? 'This order will not continue through the delivery process.'
    : (
        $stepDescriptions[$status]
        ?? 'The latest order status is shown below.'
    );

$progressPercentage = match (true) {
    $isCancelled => 0,
    $isDelivered => 100,
    default => round(
        ($currentIndex / max($totalSteps - 1, 1)) * 100
    ),
};

$completedSteps = match (true) {
    $isCancelled => 0,
    $isDelivered => $totalSteps,
    default => $currentIndex,
};

$nextStepKey = ! $isCancelled
    && ! $isDelivered
    && isset($keys[$currentIndex + 1])
        ? $keys[$currentIndex + 1]
        : null;

$nextStepLabel = $nextStepKey
    ? $steps[$nextStepKey]
    : null;

@endphp

<div
    {{ $attributes->class([
        'min-w-0',
        'rounded-[1.75rem] border border-warm-200 bg-white p-4 shadow-sm sm:p-6 lg:rounded-[2rem]' => $standalone,
    ]) }}
>
    @if ($showHeader)
        <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                <span
                    @class([
                        'grid h-11 w-11 shrink-0 place-items-center rounded-xl sm:h-12 sm:w-12 sm:rounded-2xl',
                        'bg-red-50 text-red-600' => $isCancelled,
                        'bg-leaf-50 text-leaf-700' => $isDelivered,
                        'bg-brand-50 text-brand-500' => ! $isCancelled && ! $isDelivered,
                    ])
                >
                    @if ($isCancelled)
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
                                d="m9 9 6 6M15 9l-6 6"
                            />
                        </svg>
                    @elseif ($isDelivered)
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
                            <path d="M3 7h11v10H3z" />
                            <path d="M14 10h4l3 3v4h-7z" />
                            <circle cx="7" cy="18" r="2" />
                            <circle cx="18" cy="18" r="2" />
                        </svg>
                    @endif
                </span>

            <div class="min-w-0">
                <p
                    @class([
                        'text-[10px] font-black uppercase tracking-[0.18em] sm:text-xs',
                        'text-red-600' => $isCancelled,
                        'text-leaf-700' => $isDelivered,
                        'text-brand-500' => ! $isCancelled && ! $isDelivered,
                    ])
                >
                    Order Journey
                </p>

                <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                    {{ $title }}
                </h2>

                <p class="mt-1 max-w-2xl text-xs font-semibold leading-5 text-warm-500 sm:text-sm sm:leading-6">
                    {{ $description ?: 'Track each stage from restaurant confirmation to final delivery.' }}
                </p>
            </div>
        </div>

        <span
            @class([
                'inline-flex w-fit shrink-0 items-center gap-2 rounded-full border px-3 py-2 text-[10px] font-black sm:px-4 sm:text-xs',
                'border-red-100 bg-red-50 text-red-700' => $isCancelled,
                'border-leaf-100 bg-leaf-50 text-leaf-700' => $isDelivered,
                'border-warm-200 bg-brand-50 text-brand-600' => ! $isCancelled && ! $isDelivered,
            ])
        >
            <span
                @class([
                    'h-2 w-2 rounded-full',
                    'bg-red-500' => $isCancelled,
                    'bg-leaf-500' => $isDelivered,
                    'animate-pulse bg-brand-500' => ! $isCancelled && ! $isDelivered,
                ])
            ></span>

            {{ $currentLabel }}
        </span>
    </header>
@endif

@if ($isCancelled)
    <section
        class="{{ $showHeader ? 'mt-5' : '' }} overflow-hidden rounded-[1.5rem] border border-red-100 bg-gradient-to-br from-red-50 to-white"
    >
        <div class="flex items-start gap-4 p-4 sm:p-5">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white text-red-600 shadow-sm">
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
                        d="m9 9 6 6M15 9l-6 6"
                    />
                </svg>
            </span>

            <div class="min-w-0">
                <p class="font-black text-red-900">
                    Delivery journey stopped
                </p>

                <p class="mt-1 text-sm font-semibold leading-6 text-red-700">
                    {{ $currentDescription }}
                </p>
            </div>
        </div>

        <div class="border-t border-red-100 bg-white/70 px-4 py-3 sm:px-5">
            <p class="text-xs font-semibold leading-5 text-warm-500">
                Contact the restaurant or customer support when more information about this cancellation is required.
            </p>
        </div>
    </section>
@else
    {{-- Current Stage and Progress --}}
    <section class="{{ $showHeader ? 'mt-5' : '' }} grid gap-3 lg:grid-cols-[minmax(0,1fr)_250px]">
        <div
            @class([
                'relative overflow-hidden rounded-[1.5rem] p-5 text-white shadow-lg sm:p-6',
                'bg-gradient-to-br from-leaf-700 via-leaf-500 to-teal-600 shadow-leaf-700/15' => $isDelivered,
                'bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 shadow-brand-500/20' => ! $isDelivered,
            ])
        >
            <div class="pointer-events-none absolute -right-14 -top-16 h-44 w-44 rounded-full bg-white/20 blur-3xl"></div>

            <div class="relative">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.16em] text-white/70 sm:text-[10px]">
                            {{ $isDelivered ? 'Journey Complete' : 'Current Stage' }}
                        </p>

                        <h3 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                            {{ $currentLabel }}
                        </h3>
                    </div>

                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-white/20 bg-white/15 backdrop-blur">
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
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 2" />
                            </svg>
                        @endif
                    </span>
                </div>

                <p class="mt-3 max-w-2xl text-sm font-semibold leading-6 text-white/85">
                    {{ $currentDescription }}
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[10px] font-black backdrop-blur">
                        Step {{ $currentIndex + 1 }} of {{ $totalSteps }}
                    </span>

                    @if ($nextStepLabel)
                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[10px] font-black backdrop-blur">
                            Next: {{ $nextStepLabel }}
                        </span>
                    @elseif ($isDelivered)
                        <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[10px] font-black backdrop-blur">
                            Successfully completed
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] border border-warm-200 bg-brand-50/70 p-4 sm:p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-brand-500">
                        Overall Progress
                    </p>

                    <p class="mt-1 text-3xl font-black tracking-tight text-warm-950">
                        {{ $progressPercentage }}%
                    </p>
                </div>

                <span
                    @class([
                        'grid h-10 w-10 place-items-center rounded-xl',
                        'bg-leaf-100 text-leaf-700' => $isDelivered,
                        'bg-white text-brand-500 shadow-sm' => ! $isDelivered,
                    ])
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-5 w-5"
                    >
                        <path d="M4 19V9M10 19V5M16 19v-7M22 19V2" />
                    </svg>
                </span>
            </div>

            <div
                class="mt-4 h-2.5 overflow-hidden rounded-full bg-white shadow-inner"
                role="progressbar"
                aria-label="Order delivery progress"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="{{ $progressPercentage }}"
            >
                <div
                    @class([
                        'h-full rounded-full transition-all duration-500',
                        'bg-gradient-to-r from-leaf-500 to-teal-500' => $isDelivered,
                        'bg-gradient-to-r from-brand-500 to-brand-600' => ! $isDelivered,
                    ])
                    style="width: {{ $progressPercentage }}%"
                ></div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3 text-xs">
                <span class="font-semibold text-warm-500">
                    Completed stages
                </span>

                <span class="font-black text-warm-950">
                    {{ $completedSteps }} / {{ $totalSteps }}
                </span>
            </div>
        </div>
    </section>

    {{-- Responsive Unified Timeline --}}
    <ol
        class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-7"
        aria-label="Delivery journey stages"
    >
        @foreach ($steps as $key => $label)
            @php
                $index = array_search($key, $keys, true);

                $isComplete = $isDelivered
                    ? $index <= $currentIndex
                    : $index < $currentIndex;

                $isCurrent = ! $isDelivered
                    && $index === $currentIndex;

                $isUpcoming = ! $isComplete && ! $isCurrent;
            @endphp

            <li
                @if ($isCurrent)
                    aria-current="step"
                @endif
                @class([
                    'relative overflow-hidden rounded-2xl border p-4 transition',
                    'border-leaf-100 bg-leaf-50' => $isComplete,
                    'border-brand-200 bg-brand-50 shadow-md shadow-brand-500/5' => $isCurrent,
                    'border-warm-100 bg-warm-50/80' => $isUpcoming,
                ])
            >
                @if ($isCurrent)
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 to-brand-600"></div>
                @elseif ($isComplete)
                    <div class="absolute inset-x-0 top-0 h-1 bg-leaf-500"></div>
                @endif

                <div class="flex items-start justify-between gap-3">
                    <span
                        @class([
                            'relative grid h-9 w-9 shrink-0 place-items-center rounded-xl text-xs font-black',
                            'bg-leaf-500 text-white shadow-sm' => $isComplete,
                            'bg-brand-500 text-white shadow-lg shadow-brand-500/20' => $isCurrent,
                            'border border-warm-200 bg-white text-warm-500' => $isUpcoming,
                        ])
                    >
                        @if ($isComplete)
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
                            {{ $index + 1 }}
                        @endif

                        @if ($isCurrent)
                            <span class="absolute inset-0 -z-10 animate-ping rounded-xl bg-brand-500 opacity-25"></span>
                        @endif
                    </span>

                    @if ($isCurrent)
                        <span class="rounded-full bg-brand-500 px-2 py-1 text-[8px] font-black uppercase tracking-[0.1em] text-white">
                            Current
                        </span>
                    @elseif ($isComplete)
                        <span class="rounded-full bg-leaf-100 px-2 py-1 text-[8px] font-black uppercase tracking-[0.1em] text-leaf-700">
                            Done
                        </span>
                    @else
                        <span class="rounded-full bg-white px-2 py-1 text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                            Upcoming
                        </span>
                    @endif
                </div>

                <h3
                    @class([
                        'mt-4 text-sm font-black',
                        'text-leaf-900' => $isComplete,
                        'text-brand-900' => $isCurrent,
                        'text-warm-600' => $isUpcoming,
                    ])
                >
                    {{ $label }}
                </h3>

                <p
                    @class([
                        'mt-1 text-[10px] font-semibold leading-4',
                        'text-leaf-700' => $isComplete,
                        'text-brand-600' => $isCurrent,
                        'text-warm-500' => $isUpcoming,
                    ])
                >
                    {{ $stepDescriptions[$key] }}
                </p>
            </li>
        @endforeach
    </ol>

    <p class="mt-4 text-center text-[10px] font-semibold leading-4 text-warm-500">
        Progress updates automatically whenever the restaurant or rider changes the order status.
    </p>
@endif

</div>
