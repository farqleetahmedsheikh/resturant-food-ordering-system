@component('layouts.admin', ['title' => 'Admin Dashboard'])
@php
$restaurantConfigured = (bool) $restaurant;
$restaurantOpen = (bool) ($restaurant?->is_open ?? false);

    $activeDeliveryOrders = $assignedDeliveries + $outForDeliveryOrders;

    $deliveryCompletionRate = $totalOrders > 0
        ? round(($deliveredOrders / $totalOrders) * 100)
        : 0;

    $menuCoverage = $totalCategories > 0
        ? round(($activeCategories / $totalCategories) * 100)
        : 0;
@endphp

{{-- Welcome Hero --}}
<section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-950 p-6 text-white shadow-2xl shadow-slate-950/20 sm:p-8 lg:p-10">
    <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-orange-500/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-28 left-20 h-72 w-72 rounded-full bg-red-500/20 blur-3xl"></div>

    <div class="relative grid gap-8 xl:grid-cols-[1fr_390px] xl:items-center">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-orange-300">
                    Restaurant Operations
                </p>

                @if ($restaurantConfigured)
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-black backdrop-blur">
                        <span class="h-2 w-2 rounded-full {{ $restaurantOpen ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                        {{ $restaurantOpen ? 'Restaurant Open' : 'Restaurant Closed' }}
                    </span>
                @else
                    <span class="rounded-full border border-amber-300/20 bg-amber-400/10 px-3 py-1.5 text-xs font-black text-amber-200">
                        Setup Required
                    </span>
                @endif
            </div>

            <h1 class="mt-5 max-w-3xl text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                Welcome back, {{ auth()->user()->name ?? 'Administrator' }}
            </h1>

            <p class="mt-4 max-w-2xl text-sm font-semibold leading-7 text-slate-300 sm:text-base">
                Monitor incoming orders, rider assignments, delivery performance, menu availability, and cash-on-delivery revenue from one place.
            </p>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <a
                    href="{{ route('admin.orders.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-950/30 transition hover:-translate-y-0.5 hover:bg-orange-500"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                        <path d="M9 7h6M9 11h6M9 15h3" />
                    </svg>

                    Manage Orders
                </a>

                <a
                    href="{{ route('admin.menu-items.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-6 py-3.5 text-sm font-black text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>

                    Add Menu Item
                </a>
            </div>
        </div>

        {{-- Revenue Overview --}}
        <div class="rounded-[1.75rem] border border-white/15 bg-white/10 p-5 shadow-xl backdrop-blur sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-300">
                        COD Revenue
                    </p>

                    <p class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">
                        Rs. {{ number_format($totalCodRevenue, 0) }}
                    </p>
                </div>

                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-500/20 text-orange-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="6" width="18" height="12" rx="2" />
                        <circle cx="12" cy="12" r="2" />
                        <path d="M7 9h.01M17 15h.01" />
                    </svg>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Delivered
                    </p>

                    <p class="mt-2 text-2xl font-black">
                        {{ $deliveredOrders }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">
                        Success Rate
                    </p>

                    <p class="mt-2 text-2xl font-black">
                        {{ $deliveryCompletionRate }}%
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Core Metrics --}}
<section class="mt-7 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <article class="group rounded-[1.75rem] border border-orange-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-900/5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                    Total Orders
                </p>

                <p class="mt-3 text-4xl font-black tracking-tight text-slate-950">
                    {{ $totalOrders }}
                </p>
            </div>

            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-orange-50 text-orange-600 transition group-hover:bg-orange-600 group-hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                    <path d="M9 7h6M9 11h6M9 15h3" />
                </svg>
            </div>
        </div>

        <p class="mt-4 text-sm font-semibold leading-6 text-slate-500">
            All customer orders recorded by the platform.
        </p>
    </article>

    <article class="group rounded-[1.75rem] border border-amber-100 bg-amber-50/70 p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-900/5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">
                    Pending Orders
                </p>

                <p class="mt-3 text-4xl font-black tracking-tight text-amber-950">
                    {{ $pendingOrders }}
                </p>
            </div>

            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-amber-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 7v5l3 2" />
                </svg>
            </div>
        </div>

        <p class="mt-4 text-sm font-semibold leading-6 text-amber-800/80">
            Orders currently waiting for restaurant action.
        </p>
    </article>

    <article class="group rounded-[1.75rem] border border-orange-100 bg-orange-50/70 p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-900/5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-700">
                    Preparing
                </p>

                <p class="mt-3 text-4xl font-black tracking-tight text-orange-950">
                    {{ $preparingOrders }}
                </p>
            </div>

            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-orange-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 11h14M7 11a5 5 0 0 1 10 0M4 15h16M6 19h12" />
                </svg>
            </div>
        </div>

        <p class="mt-4 text-sm font-semibold leading-6 text-orange-800/80">
            Meals currently being prepared by the kitchen.
        </p>
    </article>

    <article class="group rounded-[1.75rem] border border-blue-100 bg-blue-50/70 p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-900/5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">
                    Active Deliveries
                </p>

                <p class="mt-3 text-4xl font-black tracking-tight text-blue-950">
                    {{ $activeDeliveryOrders }}
                </p>
            </div>

            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-blue-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 7h11v10H3z" />
                    <path d="M14 10h4l3 3v4h-7z" />
                    <circle cx="7" cy="18" r="2" />
                    <circle cx="18" cy="18" r="2" />
                </svg>
            </div>
        </div>

        <p class="mt-4 text-sm font-semibold leading-6 text-blue-800/80">
            Assigned and out-for-delivery orders combined.
        </p>
    </article>
</section>

{{-- Operational Statistics --}}
<section class="mt-7 rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm sm:p-7">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                Operational Overview
            </p>

            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                Restaurant performance
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-600">
                Key figures covering deliveries, riders, categories, and menu availability.
            </p>
        </div>

        <a
            href="{{ route('admin.settings.restaurant.edit') }}"
            class="inline-flex items-center justify-center rounded-2xl border border-orange-200 bg-white px-4 py-2.5 text-sm font-black text-orange-700 shadow-sm transition hover:bg-orange-50"
        >
            Restaurant Settings
        </a>
    </div>

    <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <div class="rounded-[1.5rem] bg-slate-50 p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                    Assigned
                </p>

                <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
            </div>

            <p class="mt-3 text-3xl font-black text-slate-950">
                {{ $assignedDeliveries }}
            </p>

            <p class="mt-2 text-xs font-semibold text-slate-500">
                Rider assigned
            </p>
        </div>

        <div class="rounded-[1.5rem] bg-slate-50 p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                    On The Way
                </p>

                <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
            </div>

            <p class="mt-3 text-3xl font-black text-slate-950">
                {{ $outForDeliveryOrders }}
            </p>

            <p class="mt-2 text-xs font-semibold text-slate-500">
                Out for delivery
            </p>
        </div>

        <div class="rounded-[1.5rem] bg-slate-50 p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                    Delivered
                </p>

                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            </div>

            <p class="mt-3 text-3xl font-black text-slate-950">
                {{ $deliveredOrders }}
            </p>

            <p class="mt-2 text-xs font-semibold text-slate-500">
                Completed orders
            </p>
        </div>

        <div class="rounded-[1.5rem] bg-slate-50 p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                    Riders
                </p>

                <span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span>
            </div>

            <p class="mt-3 text-3xl font-black text-slate-950">
                {{ $totalRiders }}
            </p>

            <p class="mt-2 text-xs font-semibold text-slate-500">
                Registered riders
            </p>
        </div>

        <div class="rounded-[1.5rem] bg-slate-50 p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">
                    Restaurant
                </p>

                <span class="h-2.5 w-2.5 rounded-full {{ $restaurantOpen ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
            </div>

            <p class="mt-3 text-2xl font-black {{ $restaurantOpen ? 'text-emerald-700' : 'text-red-700' }}">
                @if (! $restaurantConfigured)
                    Not Set
                @else
                    {{ $restaurantOpen ? 'Open' : 'Closed' }}
                @endif
            </p>

            <p class="mt-2 text-xs font-semibold text-slate-500">
                Current public status
            </p>
        </div>
    </div>
</section>

{{-- Menu Statistics --}}
<section class="mt-7 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-[1.75rem] border border-orange-100 bg-white p-6 shadow-sm">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
            Total Categories
        </p>

        <div class="mt-4 flex items-end justify-between gap-4">
            <p class="text-4xl font-black text-slate-950">
                {{ $totalCategories }}
            </p>

            <a
                href="{{ route('admin.categories.index') }}"
                class="text-sm font-black text-orange-600 hover:text-orange-700"
            >
                Manage →
            </a>
        </div>
    </article>

    <article class="rounded-[1.75rem] border border-emerald-100 bg-emerald-50/70 p-6 shadow-sm">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-700">
            Active Categories
        </p>

        <div class="mt-4 flex items-end justify-between gap-4">
            <p class="text-4xl font-black text-emerald-950">
                {{ $activeCategories }}
            </p>

            <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-emerald-700">
                {{ $menuCoverage }}%
            </span>
        </div>
    </article>

    <article class="rounded-[1.75rem] border border-blue-100 bg-blue-50/70 p-6 shadow-sm">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">
            Available Items
        </p>

        <div class="mt-4 flex items-end justify-between gap-4">
            <p class="text-4xl font-black text-blue-950">
                {{ $availableMenuItems }}
            </p>

            <a
                href="{{ route('admin.menu-items.index') }}"
                class="text-sm font-black text-blue-700 hover:text-blue-800"
            >
                View →
            </a>
        </div>
    </article>

    <article class="rounded-[1.75rem] border border-orange-100 bg-orange-50/70 p-6 shadow-sm">
        <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-700">
            Featured Items
        </p>

        <div class="mt-4 flex items-end justify-between gap-4">
            <p class="text-4xl font-black text-orange-950">
                {{ $featuredMenuItems }}
            </p>

            <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-orange-600 shadow-sm">
                ★
            </span>
        </div>
    </article>
</section>

{{-- Recent Orders and Quick Actions --}}
<section class="mt-7 grid gap-7 xl:grid-cols-[minmax(0,1fr)_360px]">
    {{-- Recent Orders --}}
    <div class="overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-4 border-b border-orange-100 px-5 py-5 sm:flex-row sm:items-center sm:px-7">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                    Recent Activity
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                    Latest orders
                </h2>
            </div>

            <a
                href="{{ route('admin.orders.index') }}"
                class="inline-flex items-center justify-center rounded-2xl border border-orange-200 bg-white px-4 py-2.5 text-sm font-black text-orange-700 shadow-sm transition hover:bg-orange-50"
            >
                View All Orders
            </a>
        </div>

        @if ($recentOrders->isEmpty())
            <div class="p-8 text-center sm:p-12">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-orange-50 text-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                    </svg>
                </div>

                <h3 class="mt-5 text-xl font-black text-slate-950">
                    No orders yet
                </h3>

                <p class="mt-2 text-sm leading-6 text-slate-600">
                    New customer orders will appear here.
                </p>
            </div>
        @else
            {{-- Desktop Table --}}
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50/80">
                        <tr class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                            <th class="px-6 py-4">Order</th>
                            <th class="px-5 py-4">Customer</th>
                            <th class="px-5 py-4">Total</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Rider</th>
                            <th class="px-6 py-4 text-right">Date</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recentOrders as $order)
                            <tr class="transition hover:bg-orange-50/40">
                                <td class="px-6 py-5">
                                    <a
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="font-black text-orange-700 transition hover:text-orange-800"
                                    >
                                        {{ $order->order_number }}
                                    </a>
                                </td>

                                <td class="px-5 py-5">
                                    <p class="font-bold text-slate-950">
                                        {{ $order->customer_name }}
                                    </p>
                                </td>

                                <td class="px-5 py-5 font-black text-slate-950">
                                    Rs. {{ number_format($order->total, 0) }}
                                </td>

                                <td class="px-5 py-5">
                                    <x-status-badge :status="$order->order_status" />
                                </td>

                                <td class="px-5 py-5">
                                    @if ($order->rider)
                                        <span class="font-bold text-slate-700">
                                            {{ $order->rider->name }}
                                        </span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-black text-amber-700">
                                            Unassigned
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-5 text-right font-semibold text-slate-500">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Order Cards --}}
            <div class="space-y-4 p-4 lg:hidden">
                @foreach ($recentOrders as $order)
                    <article class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-orange-600">
                                    Order
                                </p>

                                <a
                                    href="{{ route('admin.orders.show', $order) }}"
                                    class="mt-1 block break-all text-base font-black text-slate-950"
                                >
                                    {{ $order->order_number }}
                                </a>

                                <p class="mt-1 truncate text-sm font-semibold text-slate-500">
                                    {{ $order->customer_name }}
                                </p>
                            </div>

                            <x-status-badge :status="$order->order_status" />
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white p-3">
                                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                    Total
                                </p>

                                <p class="mt-1 font-black text-slate-950">
                                    Rs. {{ number_format($order->total, 0) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-3">
                                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                    Rider
                                </p>

                                <p class="mt-1 truncate font-black text-slate-950">
                                    {{ $order->rider?->name ?? 'Unassigned' }}
                                </p>
                            </div>
                        </div>

                        <a
                            href="{{ route('admin.orders.show', $order) }}"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-orange-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-orange-600/20"
                        >
                            Open Order
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Quick Actions --}}
    <aside class="space-y-4">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-orange-600">
                Quick Actions
            </p>

            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                Manage operations
            </h2>
        </div>

        <a
            href="{{ route('admin.orders.index') }}"
            class="group flex items-start gap-4 rounded-[1.5rem] border border-orange-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
        >
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-orange-50 text-orange-600 transition group-hover:bg-orange-600 group-hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                </svg>
            </div>

            <div>
                <h3 class="font-black text-slate-950">
                    Manage Orders
                </h3>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Filter orders, assign riders, and update progress.
                </p>
            </div>
        </a>

        <a
            href="{{ route('admin.riders.index') }}"
            class="group flex items-start gap-4 rounded-[1.5rem] border border-orange-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
        >
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-blue-50 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="6" cy="18" r="2" />
                    <circle cx="18" cy="18" r="2" />
                    <path d="M8 18h8M7 16l2-6h6l3 6" />
                </svg>
            </div>

            <div>
                <h3 class="font-black text-slate-950">
                    Manage Riders
                </h3>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Create rider accounts and review delivery activity.
                </p>
            </div>
        </a>

        <a
            href="{{ route('admin.menu-items.index') }}"
            class="group flex items-start gap-4 rounded-[1.5rem] border border-orange-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
        >
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16v16H4z" />
                    <path d="M8 8h8M8 12h8M8 16h5" />
                </svg>
            </div>

            <div>
                <h3 class="font-black text-slate-950">
                    Manage Menu
                </h3>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Update menu items, prices, images, and availability.
                </p>
            </div>
        </a>

        <a
            href="{{ route('admin.categories.create') }}"
            class="group flex items-start gap-4 rounded-[1.5rem] border border-orange-100 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
        >
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-violet-50 text-violet-600 transition group-hover:bg-violet-600 group-hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" />
                    <rect x="14" y="3" width="7" height="7" />
                    <rect x="3" y="14" width="7" height="7" />
                    <path d="M17.5 14v7M14 17.5h7" />
                </svg>
            </div>

            <div>
                <h3 class="font-black text-slate-950">
                    Add Category
                </h3>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Create a category for organizing the public menu.
                </p>
            </div>
        </a>

        <a
            href="{{ route('home') }}"
            class="group flex items-start gap-4 rounded-[1.5rem] border border-orange-200 bg-gradient-to-br from-orange-50 to-red-50 p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
        >
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-orange-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 11 9-8 9 8" />
                    <path d="M5 10v10h14V10M9 20v-6h6v6" />
                </svg>
            </div>

            <div>
                <h3 class="font-black text-slate-950">
                    View Customer Site
                </h3>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Open and review the public ordering experience.
                </p>
            </div>
        </a>
    </aside>
</section>

@endcomponent
