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
'assigned_to_rider' => 'Assigned',
'out_for_delivery' => 'Out for Delivery',
'delivered' => 'Delivered',
];

$stepDescriptions = [
    'pending' => 'Waiting for confirmation',
    'accepted' => 'Order accepted',
    'preparing' => 'Food is being prepared',
    'ready' => 'Ready for rider pickup',
    'assigned_to_rider' => 'Rider has been assigned',
    'out_for_delivery' => 'Order is on the way',
    'delivered' => 'Successfully delivered',
];

$keys = array_keys($steps);
$isCancelled = $status === 'cancelled';

$currentIndex = array_search($status, $keys, true);
$currentIndex = $currentIndex === false ? 0 : $currentIndex;

$currentLabel = $isCancelled
    ? 'Cancelled'
    : ($steps[$status] ?? ucfirst(str_replace('_', ' ', $status)));

$progressPercentage = $isCancelled
    ? 0
    : ($currentIndex / max(count($steps) - 1, 1)) * 100;

@endphp

<div
    {{ $attributes->class([
        'min-w-0',
        'rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7' => $standalone,
    ]) }}
>
    @if ($showHeader)
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                    Order Journey
                </p>

            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                {{ $title }}
            </h2>

            <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">
                {{ $description ?: 'Follow the order from confirmation through final delivery.' }}
            </p>
        </div>

        <span
            @class([
                'inline-flex w-fit items-center gap-2 rounded-full border px-4 py-2 text-xs font-black',
                'border-red-100 bg-red-50 text-red-700' => $isCancelled,
                'border-orange-100 bg-orange-50 text-orange-700' => ! $isCancelled,
            ])
        >
            <span
                @class([
                    'h-2.5 w-2.5 rounded-full',
                    'bg-red-500' => $isCancelled,
                    'animate-pulse bg-orange-500' => ! $isCancelled && $status !== 'delivered',
                    'bg-emerald-500' => $status === 'delivered',
                ])
            ></span>

            {{ $currentLabel }}
        </span>
    </div>
@endif

@if ($isCancelled)
    <div class="{{ $showHeader ? 'mt-6' : '' }} rounded-[1.5rem] border border-red-100 bg-red-50 p-5">
        <div class="flex items-start gap-4">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-red-600 shadow-sm">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-5 w-5"
                >
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" d="m9 9 6 6M15 9l-6 6" />
                </svg>
            </div>

            <div>
                <p class="font-black text-red-800">
                    Order cancelled
                </p>

                <p class="mt-1 text-sm font-semibold leading-6 text-red-700">
                    This order will not continue through the delivery process.
                </p>
            </div>
        </div>
    </div>
@else
    {{-- Overall Progress --}}
    <div class="{{ $showHeader ? 'mt-7' : '' }} rounded-2xl border border-orange-100 bg-orange-50/70 p-4">
        <div class="flex items-center justify-between gap-4">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-orange-700">
                Overall Progress
            </p>

            <p class="text-sm font-black text-orange-700">
                {{ round($progressPercentage) }}%
            </p>
        </div>

        <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-white shadow-inner">
            <div
                class="h-full rounded-full bg-gradient-to-r from-orange-500 to-red-500 transition-all duration-500"
                style="width: {{ $progressPercentage }}%"
            ></div>
        </div>
    </div>

    {{-- Mobile Vertical Timeline --}}
    <div class="mt-6 space-y-0 md:hidden">
        @foreach ($steps as $key => $label)
            @php
                $index = array_search($key, $keys, true);
                $isComplete = $index < $currentIndex;
                $isCurrent = $index === $currentIndex;
                $isUpcoming = $index > $currentIndex;
                $isLast = $index === count($steps) - 1;
            @endphp

            <div class="relative flex gap-4">
                {{-- Timeline Indicator --}}
                <div class="flex w-11 shrink-0 flex-col items-center">
                    <span
                        @class([
                            'relative z-10 grid h-11 w-11 place-items-center rounded-full border-2 text-xs font-black transition',
                            'border-emerald-500 bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' => $isComplete,
                            'border-orange-600 bg-orange-600 text-white shadow-lg shadow-orange-600/25' => $isCurrent,
                            'border-slate-200 bg-white text-slate-400' => $isUpcoming,
                        ])
                    >
                        @if ($isComplete)
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="3"
                                class="h-5 w-5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                        @else
                            {{ $index + 1 }}
                        @endif

                        @if ($isCurrent)
                            <span class="absolute inset-0 -z-10 animate-ping rounded-full bg-orange-400 opacity-30"></span>
                        @endif
                    </span>

                    @unless ($isLast)
                        <span
                            @class([
                                'min-h-10 w-0.5 flex-1',
                                'bg-emerald-400' => $index < $currentIndex,
                                'bg-slate-200' => $index >= $currentIndex,
                            ])
                        ></span>
                    @endunless
                </div>

                {{-- Step Content --}}
                <div class="{{ $isLast ? 'pb-0' : 'pb-6' }} min-w-0 flex-1 pt-1">
                    <div
                        @class([
                            'rounded-2xl border p-4 transition',
                            'border-emerald-100 bg-emerald-50' => $isComplete,
                            'border-orange-200 bg-orange-50 shadow-sm' => $isCurrent,
                            'border-slate-100 bg-slate-50' => $isUpcoming,
                        ])
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p
                                    @class([
                                        'text-sm font-black',
                                        'text-emerald-800' => $isComplete,
                                        'text-orange-900' => $isCurrent,
                                        'text-slate-500' => $isUpcoming,
                                    ])
                                >
                                    {{ $label }}
                                </p>

                                <p
                                    @class([
                                        'mt-1 text-xs font-semibold leading-5',
                                        'text-emerald-700' => $isComplete,
                                        'text-orange-700' => $isCurrent,
                                        'text-slate-400' => $isUpcoming,
                                    ])
                                >
                                    {{ $stepDescriptions[$key] }}
                                </p>
                            </div>

                            @if ($isCurrent)
                                <span class="shrink-0 rounded-full bg-orange-600 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-white">
                                    Current
                                </span>
                            @elseif ($isComplete)
                                <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-emerald-700">
                                    Done
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Desktop Horizontal Timeline --}}
    <div class="mt-7 hidden overflow-x-auto pb-3 md:block">
        <div class="min-w-[900px] px-2 py-3">
            <div class="relative">
                {{-- Base Connector --}}
                <div class="absolute left-[7%] right-[7%] top-6 h-1 rounded-full bg-slate-200"></div>

                {{-- Completed Connector --}}
                <div
                    class="absolute left-[7%] top-6 h-1 rounded-full bg-gradient-to-r from-emerald-500 to-orange-500 transition-all duration-500"
                    style="width: {{ $progressPercentage * 0.86 }}%"
                ></div>

                <div class="relative grid grid-cols-7 gap-3">
                    @foreach ($steps as $key => $label)
                        @php
                            $index = array_search($key, $keys, true);
                            $isComplete = $index < $currentIndex;
                            $isCurrent = $index === $currentIndex;
                            $isUpcoming = $index > $currentIndex;
                        @endphp

                        <div class="flex min-w-0 flex-col items-center text-center">
                            <span
                                @class([
                                    'relative z-10 grid h-12 w-12 place-items-center rounded-full border-2 text-sm font-black transition',
                                    'border-emerald-500 bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' => $isComplete,
                                    'border-orange-600 bg-orange-600 text-white shadow-lg shadow-orange-600/25' => $isCurrent,
                                    'border-slate-200 bg-white text-slate-400' => $isUpcoming,
                                ])
                            >
                                @if ($isComplete)
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="h-5 w-5"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                    </svg>
                                @else
                                    {{ $index + 1 }}
                                @endif

                                @if ($isCurrent)
                                    <span class="absolute inset-0 -z-10 animate-ping rounded-full bg-orange-400 opacity-30"></span>
                                @endif
                            </span>

                            <p
                                @class([
                                    'mt-4 text-xs font-black',
                                    'text-emerald-700' => $isComplete,
                                    'text-orange-700' => $isCurrent,
                                    'text-slate-500' => $isUpcoming,
                                ])
                            >
                                {{ $label }}
                            </p>

                            <p class="mt-1 max-w-28 text-[10px] font-semibold leading-4 text-slate-400">
                                {{ $stepDescriptions[$key] }}
                            </p>

                            @if ($isCurrent)
                                <span class="mt-2 rounded-full bg-orange-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-orange-700">
                                    Current
                                </span>
                            @elseif ($isComplete)
                                <span class="mt-2 rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-emerald-700">
                                    Completed
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

</div>
