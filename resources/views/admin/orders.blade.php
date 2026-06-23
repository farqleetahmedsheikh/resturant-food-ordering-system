@component('layouts.admin', ['title' => 'Orders'])
@php
$orderCount = method_exists($orders, 'total')
? $orders->total()
: $orders->count();

    $currentStatusLabel = $currentStatus
        ? ($statuses[$currentStatus] ?? ucfirst(str_replace('_', ' ', $currentStatus)))
        : 'All Orders';
@endphp

{{-- Page Header --}}
<div class="mb-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
    <div>
        <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-600">
            Order Management
        </p>

        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Orders
        </h1>

        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
            Review incoming orders, monitor delivery progress, assign riders, and manage order statuses.
        </p>
    </div>

    <a
        href="{{ route('admin.riders.index') }}"
        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-orange-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-orange-50 hover:text-orange-700"
    >
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
            <path d="M8 18h8M7 16l2-6h6l3 6M10 10V7h4" />
        </svg>

        Manage Riders
    </a>
</div>

{{-- Summary Banner --}}
<section class="relative mb-7 overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-600 via-orange-500 to-red-600 p-6 text-white shadow-2xl shadow-orange-900/20 sm:p-8">
    <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-10 h-60 w-60 rounded-full bg-yellow-200/20 blur-3xl"></div>

    <div class="relative flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.22em] text-orange-100">
                {{ $currentStatusLabel }}
            </p>

            <h2 class="mt-3 text-3xl font-black tracking-tight">
                {{ $orderCount }}
                {{ $orderCount === 1 ? 'order' : 'orders' }}
            </h2>

            <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-orange-50">
                {{ $currentStatus
                    ? 'Showing orders currently matching the selected status.'
                    : 'Showing every customer order currently stored in FreshBite.' }}
            </p>
        </div>

        <div class="grid h-16 w-16 shrink-0 place-items-center rounded-[1.4rem] border border-white/20 bg-white/15 shadow-xl backdrop-blur">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-8 w-8"
            >
                <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                <path d="M9 7h6M9 11h6M9 15h3" />
            </svg>
        </div>
    </div>
</section>

{{-- Status Filters --}}
<section class="mb-7 rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between gap-4">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-600">
                Filter Orders
            </p>

            <h2 class="mt-1 text-xl font-black text-slate-950">
                Order status
            </h2>
        </div>

        @if ($currentStatus)
            <a
                href="{{ route('admin.orders.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-700"
            >
                Clear Filter
            </a>
        @endif
    </div>

    <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-2">
        <a
            href="{{ route('admin.orders.index') }}"
            class="inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-full border px-4 py-2.5 text-sm font-black transition
                {{ $currentStatus
                    ? 'border-slate-200 bg-white text-slate-700 hover:border-orange-300 hover:bg-orange-50'
                    : 'border-orange-600 bg-orange-600 text-white shadow-lg shadow-orange-600/20' }}"
        >
            All Orders
        </a>

        @foreach ($statuses as $value => $label)
            <a
                href="{{ route('admin.orders.index', ['status' => $value]) }}"
                class="inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-full border px-4 py-2.5 text-sm font-black transition
                    {{ $currentStatus === $value
                        ? 'border-orange-600 bg-orange-600 text-white shadow-lg shadow-orange-600/20'
                        : 'border-slate-200 bg-white text-slate-700 hover:border-orange-300 hover:bg-orange-50' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>
</section>

@if ($orders->isEmpty())
    {{-- Empty State --}}
    <section class="rounded-[2rem] border border-dashed border-orange-200 bg-white p-8 text-center shadow-sm sm:p-12">
        <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-50 text-orange-600">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-9 w-9"
            >
                <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                <path d="M9 7h6M9 11h6M9 15h3" />
            </svg>
        </div>

        <h2 class="mt-6 text-2xl font-black tracking-tight text-slate-950">
            No orders found
        </h2>

        <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-600">
            @if ($currentStatus)
                There are currently no orders with the “{{ $currentStatusLabel }}” status.
            @else
                Customer orders will appear here after they complete checkout.
            @endif
        </p>

        @if ($currentStatus)
            <a
                href="{{ route('admin.orders.index') }}"
                class="mt-7 inline-flex items-center justify-center rounded-2xl bg-orange-600 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700"
            >
                View All Orders
            </a>
        @else
            <a
                href="{{ route('home') }}"
                class="mt-7 inline-flex items-center justify-center rounded-2xl bg-orange-600 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:-translate-y-0.5 hover:bg-orange-700"
            >
                Open Customer Website
            </a>
        @endif
    </section>
@else
    {{-- Desktop Orders Table --}}
    <section class="hidden overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm xl:block">
        <div class="flex items-center justify-between gap-4 border-b border-orange-100 px-6 py-5">
            <div>
                <h2 class="text-xl font-black text-slate-950">
                    Order directory
                </h2>

                <p class="mt-1 text-sm font-semibold text-slate-500">
                    Open an order to manage its rider, delivery, and status.
                </p>
            </div>

            <span class="rounded-full bg-orange-50 px-4 py-2 text-xs font-black uppercase tracking-[0.16em] text-orange-700">
                {{ $orderCount }} Results
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-100 bg-slate-50/80">
                    <tr class="text-xs font-black uppercase tracking-[0.13em] text-slate-500">
                        <th class="px-6 py-4">Order</th>
                        <th class="px-5 py-4">Customer</th>
                        <th class="px-5 py-4">Contact</th>
                        <th class="px-5 py-4">Total</th>
                        <th class="px-5 py-4">Payment</th>
                        <th class="px-5 py-4">Order Status</th>
                        <th class="px-5 py-4">Rider</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach ($orders as $order)
                        <tr class="group transition hover:bg-orange-50/40">
                            {{-- Order --}}
                            <td class="px-6 py-5">
                                <a
                                    href="{{ route('admin.orders.show', $order) }}"
                                    class="font-black text-orange-700 transition hover:text-orange-800"
                                >
                                    {{ $order->order_number }}
                                </a>

                                <p class="mt-1 text-xs font-semibold text-slate-400">
                                    {{ strtoupper($order->payment_method ?? 'COD') }}
                                </p>
                            </td>

                            {{-- Customer --}}
                            <td class="px-5 py-5">
                                <p class="max-w-[180px] truncate font-black text-slate-950">
                                    {{ $order->customer_name }}
                                </p>

                                <p class="mt-1 max-w-[180px] truncate text-xs font-semibold text-slate-500">
                                    {{ $order->customer_email ?? 'No email' }}
                                </p>
                            </td>

                            {{-- Contact --}}
                            <td class="px-5 py-5">
                                <a
                                    href="tel:{{ $order->customer_phone }}"
                                    class="font-bold text-slate-700 transition hover:text-orange-700"
                                >
                                    {{ $order->customer_phone }}
                                </a>
                            </td>

                            {{-- Total --}}
                            <td class="px-5 py-5">
                                <p class="font-black text-slate-950">
                                    Rs. {{ number_format($order->total, 0) }}
                                </p>
                            </td>

                            {{-- Payment --}}
                            <td class="px-5 py-5">
                                <x-status-badge :status="$order->payment_status" />
                            </td>

                            {{-- Order Status --}}
                            <td class="px-5 py-5">
                                <x-status-badge :status="$order->order_status" />
                            </td>

                            {{-- Rider --}}
                            <td class="px-5 py-5">
                                @if ($order->rider)
                                    <div class="flex items-center gap-3">
                                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-orange-50 text-xs font-black text-orange-700">
                                            {{ mb_substr($order->rider->name, 0, 1) }}
                                        </div>

                                        <p class="max-w-[130px] truncate font-bold text-slate-700">
                                            {{ $order->rider->name }}
                                        </p>
                                    </div>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700">
                                        Unassigned
                                    </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="px-5 py-5">
                                <p class="font-semibold text-slate-700">
                                    {{ $order->created_at->format('M d, Y') }}
                                </p>

                                <p class="mt-1 text-xs font-semibold text-slate-400">
                                    {{ $order->created_at->format('h:i A') }}
                                </p>
                            </td>

                            {{-- Action --}}
                            <td class="px-6 py-5 text-right">
                                <a
                                    href="{{ route('admin.orders.show', $order) }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-2.5 text-xs font-black text-orange-700 transition hover:border-orange-600 hover:bg-orange-600 hover:text-white"
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Mobile and Tablet Cards --}}
    <div class="grid gap-5 lg:grid-cols-2 xl:hidden">
        @foreach ($orders as $order)
            @php
                $deliveryStatus = $order->delivery?->status ?? 'pending';

                $statusAccent = match ($order->order_status) {
                    'delivered' => 'bg-emerald-500',
                    'cancelled' => 'bg-red-500',
                    'out_for_delivery', 'assigned_to_rider' => 'bg-blue-500',
                    'accepted', 'preparing', 'ready' => 'bg-amber-500',
                    default => 'bg-orange-500',
                };
            @endphp

            <article class="relative overflow-hidden rounded-[1.75rem] border border-orange-100 bg-white shadow-sm">
                <div class="absolute inset-y-0 left-0 w-1.5 {{ $statusAccent }}"></div>

                <div class="p-5 pl-6 sm:p-6 sm:pl-7">
                    {{-- Card Header --}}
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                        <div class="min-w-0">
                            <p class="text-xs font-black uppercase tracking-[0.17em] text-orange-600">
                                Order Number
                            </p>

                            <a
                                href="{{ route('admin.orders.show', $order) }}"
                                class="mt-2 block break-all text-lg font-black tracking-tight text-slate-950"
                            >
                                {{ $order->order_number }}
                            </a>

                            <p class="mt-2 text-sm font-semibold text-slate-500">
                                {{ $order->created_at->format('M d, Y \a\t h:i A') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-status-badge :status="$order->order_status" />
                            <x-status-badge :status="$deliveryStatus" type="delivery" />
                        </div>
                    </div>

                    {{-- Customer --}}
                    <div class="mt-5 flex items-center gap-4 rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-sm font-black text-orange-700 shadow-sm">
                            {{ mb_substr($order->customer_name, 0, 1) }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate font-black text-slate-950">
                                {{ $order->customer_name }}
                            </p>

                            <a
                                href="tel:{{ $order->customer_phone }}"
                                class="mt-1 block truncate text-sm font-semibold text-orange-700"
                            >
                                {{ $order->customer_phone }}
                            </a>
                        </div>
                    </div>

                    {{-- Order Information --}}
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Total
                            </p>

                            <p class="mt-1 text-lg font-black text-slate-950">
                                Rs. {{ number_format($order->total, 0) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Payment
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-950">
                                {{ strtoupper($order->payment_method ?? 'COD') }}
                            </p>

                            <p class="mt-1 text-xs font-bold text-slate-500">
                                {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                            </p>
                        </div>

                        <div class="col-span-2 rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                Assigned Rider
                            </p>

                            @if ($order->rider)
                                <p class="mt-1 truncate font-black text-slate-950">
                                    {{ $order->rider->name }}
                                </p>
                            @else
                                <p class="mt-1 font-black text-amber-700">
                                    Rider not assigned
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Action --}}
                    <a
                        href="{{ route('admin.orders.show', $order) }}"
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-600/20 transition hover:bg-orange-700"
                    >
                        Manage Order

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if ($orders->hasPages())
        <div class="mt-8 rounded-[1.5rem] border border-orange-100 bg-white p-4 shadow-sm">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
@endif

@endcomponent
