@component('layouts.admin', ['title' => $rider->name])
@php
    $activeCount = $activeOrders->count();
    $historyCount = method_exists($deliveryHistory, 'total') ? $deliveryHistory->total() : $deliveryHistory->count();
    $lastLocation = $rider->last_known_latitude !== null && $rider->last_known_longitude !== null
        ? number_format((float) $rider->last_known_latitude, 6).', '.number_format((float) $rider->last_known_longitude, 6)
        : null;
@endphp

<div class="space-y-5 pb-8 sm:space-y-6">
    <header class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-warm-950 via-warm-900 to-brand-900 p-5 text-white shadow-xl shadow-warm-950/20 sm:p-7 lg:p-8">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-200">
                    Rider profile
                </p>

                <h1 class="mt-2 break-words text-3xl font-black tracking-tight sm:text-5xl">
                    {{ $rider->name }}
                </h1>

                <p class="mt-2 text-sm font-semibold text-white/70">
                    {{ $rider->phone ?? $rider->email }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('admin.riders.edit', $rider) }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-brand-950/20 transition hover:bg-brand-600"
                >
                    Edit Rider
                </a>

                <a
                    href="{{ route('admin.riders.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-black text-white transition hover:bg-white/20"
                >
                    Back to Riders
                </a>
            </div>
        </div>

        <div class="relative mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-white/50">
                    Active
                </p>
                <p class="mt-1 text-2xl font-black">{{ $activeCount }}</p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-white/50">
                    History
                </p>
                <p class="mt-1 text-2xl font-black">{{ $historyCount }}</p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 p-4">
                <p class="text-[9px] font-black uppercase tracking-[0.12em] text-white/50">
                    Status
                </p>
                <p class="mt-1 text-lg font-black {{ $rider->is_active ? 'text-leaf-300' : 'text-red-200' }}">
                    {{ $rider->is_active ? 'Active' : 'Disabled' }}
                </p>
            </div>
        </div>
    </header>

    @if ($lastLocation)
        <section class="rounded-[1.5rem] border border-blue-100 bg-blue-50 p-4 shadow-sm sm:p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.16em] text-blue-700">
                Last known location
            </p>

            <p class="mt-1 font-mono text-sm font-black text-blue-950">
                {{ $lastLocation }}
            </p>

            <p class="mt-1 text-xs font-semibold text-blue-700">
                Updated {{ $rider->last_location_updated_at?->diffForHumans() ?? 'recently' }}
            </p>
        </section>
    @endif

    <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
        <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                Assigned now
            </p>
            <h2 class="mt-1 text-xl font-black text-warm-950 sm:text-2xl">
                Active deliveries
            </h2>
        </div>

        @if ($activeOrders->isEmpty())
            <div class="p-8 text-center text-sm font-semibold text-warm-500">
                This rider has no active deliveries.
            </div>
        @else
            <div class="divide-y divide-warm-100">
                @foreach ($activeOrders as $order)
                    <a
                        href="{{ route('admin.orders.show', $order) }}"
                        class="grid gap-3 p-4 transition hover:bg-brand-50/40 sm:grid-cols-[minmax(0,1fr)_auto] sm:p-5"
                    >
                        <span class="min-w-0">
                            <span class="block break-all text-sm font-black text-warm-950">
                                {{ $order->order_number }}
                            </span>
                            <span class="mt-1 block truncate text-xs font-semibold text-warm-500">
                                {{ $order->customer_name }} · {{ $order->delivery_address }}
                            </span>
                        </span>

                        <span class="flex flex-wrap items-center gap-2 sm:justify-end">
                            <x-status-badge :status="$order->order_status" size="sm" />
                            <x-status-badge :status="$order->delivery?->status ?? 'assigned'" type="delivery" size="sm" />
                            <span class="text-sm font-black text-brand-500">@money($order->total)</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
        <div class="border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500">
                Past work
            </p>
            <h2 class="mt-1 text-xl font-black text-warm-950 sm:text-2xl">
                Delivery history
            </h2>
        </div>

        @if ($deliveryHistory->isEmpty())
            <div class="p-8 text-center text-sm font-semibold text-warm-500">
                No completed delivery history yet.
            </div>
        @else
            <div class="divide-y divide-warm-100">
                @foreach ($deliveryHistory as $order)
                    <a
                        href="{{ route('admin.orders.show', $order) }}"
                        class="grid gap-3 p-4 transition hover:bg-brand-50/40 sm:grid-cols-[minmax(0,1fr)_auto] sm:p-5"
                    >
                        <span class="min-w-0">
                            <span class="block break-all text-sm font-black text-warm-950">
                                {{ $order->order_number }}
                            </span>
                            <span class="mt-1 block truncate text-xs font-semibold text-warm-500">
                                {{ $order->created_at->format('M d, Y · h:i A') }}
                            </span>
                        </span>

                        <span class="flex flex-wrap items-center gap-2 sm:justify-end">
                            <x-status-badge :status="$order->order_status" size="sm" />
                            <x-status-badge :status="$order->delivery?->status ?? 'delivered'" type="delivery" size="sm" />
                            <span class="text-sm font-black text-brand-500">@money($order->total)</span>
                        </span>
                    </a>
                @endforeach
            </div>

            @if ($deliveryHistory->hasPages())
                <div class="border-t border-warm-100 px-4 py-4 sm:px-6">
                    {{ $deliveryHistory->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endcomponent
