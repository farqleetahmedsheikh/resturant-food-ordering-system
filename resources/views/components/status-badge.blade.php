@props([
'status',
'type' => 'order',
'showDot' => true,
'size' => 'md',
'animate' => true,
])

@php
/*
|--------------------------------------------------------------------------
| Normalize Input
|--------------------------------------------------------------------------
*/

$normalizedType = \Illuminate\Support\Str::of($type ?: 'order')
    ->trim()
    ->lower()
    ->toString();

$normalizedStatus = \Illuminate\Support\Str::of($status ?: 'unknown')
    ->trim()
    ->lower()
    ->replace([' ', '-'], '_')
    ->toString();

/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

$labels = match ($normalizedType) {
    'delivery' => \App\Models\Delivery::STATUSES,

    'payment' => [
        'pending' => 'Pending Payment',
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    default => \App\Models\Order::STATUSES,
};

$label = $labels[$normalizedStatus]
    ?? \Illuminate\Support\Str::headline($normalizedStatus);

/*
|--------------------------------------------------------------------------
| Visual Status Configuration
|--------------------------------------------------------------------------
*/

$statusConfig = match ($normalizedStatus) {
    'pending', 'pending_payment' => [
        'badge' => 'border-gold-100 bg-gold-50 text-gold-700',
        'dot' => 'bg-gold-500',
        'pulse' => 'bg-gold-500',
        'active' => true,
    ],

    'accepted', 'confirmed' => [
        'badge' => 'border-sky-200 bg-sky-50 text-sky-800',
        'dot' => 'bg-sky-500',
        'pulse' => 'bg-sky-400',
        'active' => true,
    ],

    'preparing', 'processing' => [
        'badge' => 'border-gold-100 bg-gold-50 text-food-brown',
        'dot' => 'bg-food-tan',
        'pulse' => 'bg-gold-500',
        'active' => true,
    ],

    'ready' => [
        'badge' => 'border-violet-200 bg-violet-50 text-violet-800',
        'dot' => 'bg-violet-500',
        'pulse' => 'bg-violet-400',
        'active' => true,
    ],

    'assigned_to_rider', 'assigned' => [
        'badge' => 'border-indigo-200 bg-indigo-50 text-indigo-800',
        'dot' => 'bg-indigo-500',
        'pulse' => 'bg-indigo-400',
        'active' => true,
    ],

    'picked_up', 'out_for_delivery' => [
        'badge' => 'border-blue-200 bg-blue-50 text-blue-800',
        'dot' => 'bg-blue-500',
        'pulse' => 'bg-blue-400',
        'active' => true,
    ],

    'delivered', 'paid', 'completed', 'successful' => [
        'badge' => 'border-leaf-100 bg-leaf-50 text-leaf-900',
        'dot' => 'bg-leaf-500',
        'pulse' => 'bg-leaf-500',
        'active' => false,
    ],

    'cancelled', 'failed', 'unpaid', 'rejected' => [
        'badge' => 'border-red-200 bg-red-50 text-red-800',
        'dot' => 'bg-red-500',
        'pulse' => 'bg-red-400',
        'active' => false,
    ],

    'refunded' => [
        'badge' => 'border-purple-200 bg-purple-50 text-purple-800',
        'dot' => 'bg-purple-500',
        'pulse' => 'bg-purple-400',
        'active' => false,
    ],

    default => [
        'badge' => 'border-warm-200 bg-warm-50 text-warm-600',
        'dot' => 'bg-warm-500',
        'pulse' => 'bg-warm-300',
        'active' => false,
    ],
};

/*
|--------------------------------------------------------------------------
| Responsive Size Configuration
|--------------------------------------------------------------------------
*/

$sizeConfig = match ($size) {
    'sm' => [
        'badge' => 'gap-1.5 px-2.5 py-1 text-[10px]',
        'dot' => 'h-1.5 w-1.5',
    ],

    'lg' => [
        'badge' => 'gap-2.5 px-4 py-2 text-sm',
        'dot' => 'h-2.5 w-2.5',
    ],

    default => [
        'badge' => 'gap-2 px-3 py-1.5 text-xs',
        'dot' => 'h-2 w-2',
    ],
};

$statusPrefix = match ($normalizedType) {
    'delivery' => 'Delivery status',
    'payment' => 'Payment status',
    default => 'Order status',
};

$shouldAnimate = (bool) $animate
    && $statusConfig['active'];

@endphp

<span
{{ $attributes->class([
'inline-flex w-fit max-w-full items-center rounded-full border',
'font-black leading-none whitespace-nowrap',
'shadow-xs',
'transition duration-200 ease-out',
$statusConfig['badge'],
$sizeConfig['badge'],
]) }}
role="status"
aria-label="{{ $statusPrefix }}: {{ $label }}"
title="{{ $statusPrefix }}: {{ $label }}"
data-status="{{ $normalizedStatus }}"
data-status-type="{{ $normalizedType }}"

>

@if ($showDot)

    <span
        class="relative inline-flex shrink-0"
        aria-hidden="true"
    >
        @if ($shouldAnimate)
            <span
                class="absolute inline-flex h-full w-full rounded-full opacity-60 motion-safe:animate-ping {{ $statusConfig['pulse'] }}"
            ></span>
        @endif

        <span
            class="relative inline-flex shrink-0 rounded-full {{ $statusConfig['dot'] }} {{ $sizeConfig['dot'] }}"
        ></span>
    </span>
@endif

<span class="min-w-0 max-w-[10rem] truncate sm:max-w-none">
    {{ $label }}
</span>

</span>
