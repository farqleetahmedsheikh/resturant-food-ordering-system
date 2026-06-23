@props([
'status',
'type' => 'order',
'showDot' => true,
])

@php
$normalizedStatus = (string) ($status ?: 'unknown');

$labels = match ($type) {
    'delivery' => \App\Models\Delivery::STATUSES,
    'payment' => [
        'pending' => 'Pending',
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ],
    default => \App\Models\Order::STATUSES,
};

$label = $labels[$normalizedStatus]
    ?? \Illuminate\Support\Str::headline($normalizedStatus);

[$badgeClasses, $dotClasses] = match ($normalizedStatus) {
    'pending' => [
        'border-slate-200 bg-slate-100 text-slate-700',
        'bg-slate-500',
    ],

    'accepted' => [
        'border-sky-100 bg-sky-50 text-sky-700',
        'bg-sky-500',
    ],

    'preparing' => [
        'border-amber-100 bg-amber-50 text-amber-800',
        'bg-amber-500',
    ],

    'ready' => [
        'border-violet-100 bg-violet-50 text-violet-700',
        'bg-violet-500',
    ],

    'assigned_to_rider', 'assigned' => [
        'border-indigo-100 bg-indigo-50 text-indigo-700',
        'bg-indigo-500',
    ],

    'picked_up', 'out_for_delivery' => [
        'border-orange-100 bg-orange-50 text-orange-800',
        'bg-orange-500',
    ],

    'delivered', 'paid' => [
        'border-emerald-100 bg-emerald-50 text-emerald-700',
        'bg-emerald-500',
    ],

    'cancelled', 'failed', 'unpaid' => [
        'border-red-100 bg-red-50 text-red-700',
        'bg-red-500',
    ],

    'refunded' => [
        'border-purple-100 bg-purple-50 text-purple-700',
        'bg-purple-500',
    ],

    default => [
        'border-slate-200 bg-slate-100 text-slate-700',
        'bg-slate-400',
    ],
};

@endphp

<span
{{ $attributes->class([
'inline-flex w-fit max-w-full items-center gap-2 rounded-full border px-3 py-1.5',
'text-xs font-black leading-none whitespace-nowrap',
$badgeClasses,
]) }}
aria-label="{{ $type === 'delivery' ? 'Delivery status' : ucfirst($type) . ' status' }}: {{ $label }}"

>

@if ($showDot)

    <span class="h-2 w-2 shrink-0 rounded-full {{ $dotClasses }}"></span>
@endif

<span class="truncate">
    {{ $label }}
</span>

</span>
