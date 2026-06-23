@component('layouts.admin', ['title' => $order->order_number])
@php
$deliveryStatus = $order->delivery?->status ?? 'pending';

    $orderStatusLabel = \App\Models\Order::STATUSES[$order->order_status]
        ?? ucfirst(str_replace('_', ' ', $order->order_status));

    $itemCount = $order->items->sum('quantity');

    $isCompleted = in_array(
        $order->order_status,
        ['delivered', 'cancelled'],
        true
    );

    $statusMessage = match ($order->order_status) {
        'pending' => 'This order is waiting for confirmation.',
        'accepted' => 'The restaurant has accepted this order.',
        'preparing' => 'The kitchen is currently preparing this order.',
        'ready' => 'The order is ready for rider pickup.',
        'assigned_to_rider' => 'A delivery rider has been assigned.',
        'out_for_delivery' => 'The rider is delivering this order.',
        'delivered' => 'This order has been delivered successfully.',
        'cancelled' => 'This order has been cancelled.',
        default => 'Review and manage the current order progress.',
    };
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div class="min-w-0">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Order Management
        </p>

        <h1 class="mt-3 break-all text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            {{ $order->order_number }}
        </h1>

        <p class="mt-3 text-sm font-semibold leading-6 text-slate-500">
            {{ $order->customer_name }}
            <span class="mx-2 text-slate-300">•</span>
            {{ $order->created_at->format('M d, Y \a\t h:i A') }}
        </p>
    </div>

    <a
        href="{{ route('admin.orders.index') }}"
        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50 hover:text-orange-700"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            class="h-4 w-4"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
        </svg>

        Back to Orders
    </a>
</div>

{{-- Order Summary Hero --}}
<section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-6 text-white shadow-2xl shadow-slate-950/20 sm:p-8">
    <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-orange-500/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-28 left-16 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

    <div class="relative grid gap-7 xl:grid-cols-[1fr_auto] xl:items-center">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <x-status-badge :status="$order->order_status" />

                <x-status-badge
                    :status="$deliveryStatus"
                    type="delivery"
                />
            </div>

            <h2 class="mt-5 text-3xl font-black tracking-tight sm:text-4xl">
                {{ $orderStatusLabel }}
            </h2>

            <p class="mt-3 max-w-2xl text-sm font-semibold leading-7 text-slate-300">
                {{ $statusMessage }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[560px]">
            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Items
                </p>

                <p class="mt-2 text-2xl font-black">
                    {{ $itemCount }}
                </p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Payment
                </p>

                <p class="mt-2 text-xl font-black">
                    {{ strtoupper($order->payment_method) }}
                </p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Rider
                </p>

                <p class="mt-2 truncate text-lg font-black">
                    {{ $order->rider?->name ?? 'Unassigned' }}
                </p>
            </div>

            <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Total
                </p>

                <p class="mt-2 text-xl font-black text-orange-300">
                    Rs. {{ number_format($order->total, 0) }}
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Order Progress --}}
<section class="mt-7 rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
    <div class="mb-6">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
            Order Progress
        </p>

        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
            Fulfilment journey
        </h2>
    </div>

    <div class="overflow-x-auto pb-2">
        <div class="min-w-[650px]">
            <x-order-progress :status="$order->order_status" />
        </div>
    </div>
</section>

<div class="mt-7 grid gap-7 xl:grid-cols-[minmax(0,1fr)_400px]">
    {{-- Main Information --}}
    <div class="space-y-7">
        {{-- Customer Information --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
                <div class="flex items-start gap-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-6 w-6"
                        >
                            <circle cx="12" cy="8" r="4" />
                            <path stroke-linecap="round" d="M4 21c0-4 3.5-7 8-7s8 3 8 7" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                            Customer
                        </p>

                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                            Customer details
                        </h2>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-status-badge :status="$order->order_status" />

                    <x-status-badge
                        :status="$deliveryStatus"
                        type="delivery"
                    />
                </div>
            </div>

            <div class="mt-7 grid gap-4 sm:grid-cols-3">
                <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Full Name
                    </p>

                    <p class="mt-2 break-words font-black text-slate-950">
                        {{ $order->customer_name }}
                    </p>
                </div>

                <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Phone Number
                    </p>

                    <a
                        href="tel:{{ $order->customer_phone }}"
                        class="mt-2 block break-all font-black text-orange-700 hover:text-orange-800"
                    >
                        {{ $order->customer_phone }}
                    </a>
                </div>

                <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Email Address
                    </p>

                    @if ($order->customer_email)
                        <a
                            href="mailto:{{ $order->customer_email }}"
                            class="mt-2 block break-all font-black text-orange-700 hover:text-orange-800"
                        >
                            {{ $order->customer_email }}
                        </a>
                    @else
                        <p class="mt-2 font-black text-slate-500">
                            No email
                        </p>
                    @endif
                </div>
            </div>

            {{-- Delivery Address --}}
            <div class="mt-5 rounded-[1.5rem] border border-orange-100 bg-[var(--color-surface-warm)] p-5">
                <div class="flex items-start gap-4">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-orange-600 shadow-sm">
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
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-700">
                            Delivery Address
                        </p>

                        <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                            {{ $order->delivery_address }}
                        </p>
                    </div>
                </div>
            </div>

            @if ($order->order_notes)
                <div class="mt-4 rounded-[1.5rem] border border-amber-100 bg-amber-50 p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">
                        Customer Notes
                    </p>

                    <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                        {{ $order->order_notes }}
                    </p>
                </div>
            @endif
        </section>

        {{-- Ordered Items --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Order Contents
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Ordered items
                    </h2>
                </div>

                <span class="rounded-full bg-orange-50 px-4 py-2 text-xs font-black text-orange-700">
                    {{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}
                </span>
            </div>

            <div class="mt-6 space-y-3">
                @foreach ($order->items as $item)
                    <article class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4 sm:p-5">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                            <div class="flex min-w-0 items-center gap-4">
                                <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-white text-xl font-black text-orange-600 shadow-sm">
                                    {{ mb_substr($item->item_name, 0, 1) }}
                                </div>

                                <div class="min-w-0">
                                    <p class="break-words text-base font-black text-slate-950">
                                        {{ $item->item_name }}
                                    </p>

                                    <p class="mt-1 text-sm font-semibold text-slate-500">
                                        {{ $item->quantity }} × Rs. {{ number_format($item->price, 0) }}
                                    </p>

                                    <x-order-item-options :item="$item" />
                                </div>
                            </div>

                            <div class="shrink-0 sm:text-right">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                                    Item Total
                                </p>

                                <p class="mt-1 text-lg font-black text-slate-950">
                                    Rs. {{ number_format($item->total, 0) }}
                                </p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Delivery Timeline --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
            <div class="flex items-start gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 7v5l3 2" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                        Delivery History
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Delivery timeline
                    </h2>
                </div>
            </div>

            <div class="mt-7 grid gap-4 md:grid-cols-3">
                <div class="relative rounded-[1.5rem] border border-indigo-100 bg-indigo-50 p-5">
                    <span class="absolute right-4 top-4 h-2.5 w-2.5 rounded-full bg-indigo-500"></span>

                    <p class="text-xs font-black uppercase tracking-[0.16em] text-indigo-700">
                        Rider Assigned
                    </p>

                    <p class="mt-3 text-sm font-black text-slate-950">
                        {{ $order->assigned_at?->format('M d, Y h:i A') ?? 'Not assigned yet' }}
                    </p>
                </div>

                <div class="relative rounded-[1.5rem] border border-blue-100 bg-blue-50 p-5">
                    <span class="absolute right-4 top-4 h-2.5 w-2.5 rounded-full bg-blue-500"></span>

                    <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-700">
                        Picked Up
                    </p>

                    <p class="mt-3 text-sm font-black text-slate-950">
                        {{ $order->picked_up_at?->format('M d, Y h:i A')
                            ?? $order->delivery?->pickup_time?->format('M d, Y h:i A')
                            ?? 'Not picked up yet' }}
                    </p>
                </div>

                <div class="relative rounded-[1.5rem] border border-emerald-100 bg-emerald-50 p-5">
                    <span class="absolute right-4 top-4 h-2.5 w-2.5 rounded-full bg-emerald-500"></span>

                    <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-700">
                        Delivered
                    </p>

                    <p class="mt-3 text-sm font-black text-slate-950">
                        {{ $order->delivered_at?->format('M d, Y h:i A')
                            ?? $order->delivery?->delivered_time?->format('M d, Y h:i A')
                            ?? 'Not delivered yet' }}
                    </p>
                </div>
            </div>

            @if ($order->delivery?->notes)
                <div class="mt-5 rounded-[1.5rem] border border-red-100 bg-red-50 p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-red-700">
                        Delivery Notes
                    </p>

                    <p class="mt-2 text-sm font-semibold leading-7 text-red-700">
                        {{ $order->delivery->notes }}
                    </p>
                </div>
            @endif
        </section>
    </div>

    {{-- Management Sidebar --}}
    <aside class="h-fit space-y-5 xl:sticky xl:top-28">
        {{-- Rider Assignment --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-6 w-6"
                    >
                        <circle cx="6" cy="18" r="2" />
                        <circle cx="18" cy="18" r="2" />
                        <path d="M8 18h8M7 16l2-6h6l3 6M10 10V7h4" />
                    </svg>
                </div>

                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                        Delivery Rider
                    </p>

                    <h2 class="mt-1 text-xl font-black text-slate-950">
                        Assign rider
                    </h2>
                </div>
            </div>

            <div class="mt-5 rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Current Rider
                </p>

                @if ($order->rider)
                    <div class="mt-3 flex items-center gap-3">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white text-sm font-black text-orange-700 shadow-sm">
                            {{ mb_substr($order->rider->name, 0, 1) }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-black text-slate-950">
                                {{ $order->rider->name }}
                            </p>

                            <p class="mt-1 break-all text-xs font-semibold text-slate-500">
                                {{ $order->rider->phone ?? $order->rider->email }}
                            </p>
                        </div>
                    </div>
                @else
                    <p class="mt-2 font-black text-amber-700">
                        Unassigned
                    </p>
                @endif
            </div>

            @if (! $isCompleted)
                <form
                    action="{{ route('admin.orders.assign-rider', $order) }}"
                    method="POST"
                    class="mt-5 space-y-3"
                >
                    @csrf

                    <div>
                        <label for="rider_id" class="block text-sm font-black text-slate-800">
                            Select Active Rider
                        </label>

                        <select
                            id="rider_id"
                            name="rider_id"
                            required
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                        >
                            <option value="">Choose a rider</option>

                            @foreach ($activeRiders as $rider)
                                <option
                                    value="{{ $rider->id }}"
                                    @selected($order->rider_id === $rider->id)
                                >
                                    {{ $rider->name }} — {{ $rider->phone ?? $rider->email }}
                                </option>
                            @endforeach
                        </select>

                        @error('rider_id')
                            <p class="mt-2 text-sm font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                    >
                        {{ $order->rider_id ? 'Change Rider' : 'Assign Rider' }}
                    </button>
                </form>

                @if ($order->rider_id)
                    <form
                        action="{{ route('admin.orders.unassign-rider', $order) }}"
                        method="POST"
                        class="mt-3"
                        onsubmit="return confirm('Remove the assigned rider from this order?');"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="w-full rounded-2xl border border-red-100 bg-red-50 px-5 py-3 text-sm font-black text-red-600 transition hover:bg-red-100"
                        >
                            Unassign Rider
                        </button>
                    </form>
                @endif
            @else
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold leading-6 text-slate-600">
                        Delivered or cancelled orders cannot be reassigned.
                    </p>
                </div>
            @endif
        </section>

        {{-- Status Update --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-6 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                Order Status
            </p>

            <h2 class="mt-2 text-xl font-black text-slate-950">
                Update progress
            </h2>

            <form
                action="{{ route('admin.orders.status', $order) }}"
                method="POST"
                class="mt-5 space-y-4"
            >
                @csrf
                @method('PATCH')

                <div>
                    <label for="order_status" class="block text-sm font-black text-slate-800">
                        New Status
                    </label>

                    <select
                        id="order_status"
                        name="order_status"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
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

                    @error('order_status')
                        <p class="mt-2 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-slate-800"
                >
                    Save Order Status
                </button>
            </form>
        </section>

        {{-- Payment --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                        Payment
                    </p>

                    <h2 class="mt-2 text-xl font-black text-slate-950">
                        Payment details
                    </h2>
                </div>

                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-orange-50 text-orange-600">
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
                </div>
            </div>

            <div class="mt-5 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                    <span class="font-semibold text-slate-500">
                        Method
                    </span>

                    <span class="font-black text-slate-950">
                        {{ strtoupper($order->payment_method) }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                    <span class="font-semibold text-slate-500">
                        Status
                    </span>

                    <span class="font-black text-slate-950">
                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                    </span>
                </div>
            </div>
        </section>

        {{-- Totals --}}
        <section class="rounded-[2rem] border border-orange-100 bg-white p-6 shadow-xl shadow-orange-900/5">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                Order Summary
            </p>

            <h2 class="mt-2 text-xl font-black text-slate-950">
                Payment totals
            </h2>

            <div class="mt-6 space-y-4 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="font-semibold text-slate-500">
                        Subtotal
                    </span>

                    <span class="font-black text-slate-950">
                        Rs. {{ number_format($order->subtotal, 0) }}
                    </span>
                </div>

                <div class="flex justify-between gap-4">
                    <span class="font-semibold text-slate-500">
                        Delivery fee
                    </span>

                    <span class="font-black text-slate-950">
                        Rs. {{ number_format($order->delivery_fee, 0) }}
                    </span>
                </div>

                <div class="border-t border-orange-100 pt-4">
                    <div class="flex justify-between gap-4">
                        <span class="text-lg font-black text-slate-950">
                            Total
                        </span>

                        <span class="text-xl font-black text-orange-600">
                            Rs. {{ number_format($order->total, 0) }}
                        </span>
                    </div>
                </div>
            </div>
        </section>
    </aside>
</div>

@endcomponent
