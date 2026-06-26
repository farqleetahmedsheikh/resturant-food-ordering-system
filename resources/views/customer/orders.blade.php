@component('layouts.customer', ['title' => 'My Orders'])
@php
$totalOrderCount = method_exists($orders, 'total')
? $orders->total()
: $orders->count();

    $pageOrders = collect(
        method_exists($orders, 'items')
            ? $orders->items()
            : $orders
    );

    /*
     * Active orders are placed first based on urgency:
     * Out for delivery → picked up → assigned → ready → preparing → accepted → pending.
     */
    $sortedOrders = $pageOrders
        ->sortBy(function ($order) {
            $deliveryStatus = $order->delivery?->status;

            $effectiveStatus = in_array(
                $deliveryStatus,
                ['assigned', 'picked_up', 'out_for_delivery', 'delivered', 'failed'],
                true
            )
                ? $deliveryStatus
                : $order->order_status;

            return match ($effectiveStatus) {
                'out_for_delivery' => 1,
                'picked_up' => 2,
                'assigned', 'assigned_to_rider' => 3,
                'ready' => 4,
                'preparing' => 5,
                'accepted' => 6,
                'pending' => 7,
                'delivered' => 90,
                'cancelled', 'failed' => 99,
                default => 50,
            };
        })
        ->values();

    $activeOrders = $sortedOrders
        ->reject(function ($order) {
            $deliveryStatus = $order->delivery?->status;

            return in_array(
                $order->order_status,
                ['delivered', 'cancelled'],
                true
            ) || in_array(
                $deliveryStatus,
                ['delivered', 'failed'],
                true
            );
        })
        ->values();

    $pastOrders = $sortedOrders
        ->filter(function ($order) {
            $deliveryStatus = $order->delivery?->status;

            return in_array(
                $order->order_status,
                ['delivered', 'cancelled'],
                true
            ) || in_array(
                $deliveryStatus,
                ['delivered', 'failed'],
                true
            );
        })
        ->values();
@endphp

<div class="space-y-5 pb-6 sm:space-y-7">
    {{-- Compact Page Header --}}
    <header class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand-500 sm:text-xs">
                Order History
            </p>

            <h1 class="mt-1 text-2xl font-black tracking-tight text-warm-950 sm:mt-2 sm:text-4xl">
                My orders
            </h1>

            <p class="mt-2 max-w-xl text-sm font-semibold leading-6 text-warm-600">
                Track active deliveries and review your previous orders.
            </p>
        </div>

        <a
            href="{{ route('menu') }}"
            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-500 text-white shadow-lg shadow-brand-500/20 transition active:scale-95 hover:bg-brand-600 sm:h-auto sm:w-auto sm:rounded-2xl sm:px-5 sm:py-3 sm:text-sm sm:font-black"
            aria-label="Browse menu"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                class="h-5 w-5"
            >
                <path d="M4 3h16v18H4z" />
                <path d="M8 7h8M8 11h8M8 15h5" />
            </svg>

            <span class="ml-2 hidden sm:inline">
                Browse Menu
            </span>
        </a>
    </header>

    {{-- Compact Account Summary --}}
    <section class="relative overflow-hidden rounded-[1.5rem] bg-gradient-to-r from-brand-500 to-brand-800 p-4 text-white shadow-xl shadow-brand-900/15 sm:rounded-[2rem] sm:p-7">
        <div class="pointer-events-none absolute -right-14 -top-16 h-44 w-44 rounded-full bg-white/20 blur-3xl"></div>

        <div class="relative flex items-center justify-between gap-5">
            <div>
                <p class="text-[9px] font-black uppercase tracking-[0.18em] text-brand-100 sm:text-xs">
                    Your Arcade Kebab House Activity
                </p>

                <p class="mt-1 text-2xl font-black sm:mt-2 sm:text-3xl">
                    {{ $totalOrderCount }}
                    {{ $totalOrderCount === 1 ? 'order' : 'orders' }}
                </p>

                @if ($activeOrders->isNotEmpty())
                    <p class="mt-1 text-xs font-semibold text-brand-50 sm:text-sm">
                        {{ $activeOrders->count() }}
                        {{ $activeOrders->count() === 1 ? 'active order requires' : 'active orders require' }}
                        your attention.
                    </p>
                @else
                    <p class="mt-1 text-xs font-semibold text-brand-50 sm:text-sm">
                        No active deliveries right now.
                    </p>
                @endif
            </div>

            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl border border-white/20 bg-white/15 backdrop-blur sm:h-16 sm:w-16">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-6 w-6 sm:h-8 sm:w-8"
                >
                    <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                    <path d="M9 7h6M9 11h6M9 15h3" />
                </svg>
            </div>
        </div>
    </section>

    @if ($orders->isEmpty())
        {{-- Empty State --}}
        <section class="rounded-[1.75rem] border border-dashed border-brand-200 bg-white p-7 text-center shadow-sm sm:p-12">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-brand-50 text-brand-500 sm:h-20 sm:w-20">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-7 w-7 sm:h-9 sm:w-9"
                >
                    <path d="M4 3h16v18H4z" />
                    <path d="M8 7h8M8 11h8M8 15h5" />
                </svg>
            </div>

            <h2 class="mt-5 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                Your order history is empty
            </h2>

            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-warm-600">
                Browse the menu and place your first Arcade Kebab House order.
            </p>

            <a
                href="{{ route('menu') }}"
                class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.98] hover:bg-brand-600 sm:w-auto sm:rounded-2xl"
            >
                Browse Menu

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
        </section>
    @else
        {{-- Active Orders --}}
        @if ($activeOrders->isNotEmpty())
            <section>
                <div class="mb-3 flex items-end justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-brand-500"></span>

                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                                Active Orders
                            </p>
                        </div>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                            Track your delivery
                        </h2>
                    </div>

                    <span class="rounded-full bg-brand-50 px-3 py-1.5 text-[10px] font-black text-brand-600">
                        {{ $activeOrders->count() }} active
                    </span>
                </div>

                <div class="space-y-4">
                    @foreach ($activeOrders as $order)
                        @php
                            $deliveryStatus = $order->delivery?->status;

                            $effectiveStatus = in_array(
                                $deliveryStatus,
                                ['assigned', 'picked_up', 'out_for_delivery'],
                                true
                            )
                                ? $deliveryStatus
                                : $order->order_status;

                            $statusMessage = match ($effectiveStatus) {
                                'pending' => 'Waiting for restaurant confirmation.',
                                'accepted' => 'The restaurant accepted your order.',
                                'preparing' => 'Your food is being prepared.',
                                'ready' => 'Your order is ready for pickup.',
                                'assigned', 'assigned_to_rider' => 'A rider has been assigned.',
                                'picked_up' => 'Your rider has collected the order.',
                                'out_for_delivery' => 'Your order is on the way.',
                                default => 'Your order is currently being processed.',
                            };

                            $progress = match ($effectiveStatus) {
                                'pending' => 12,
                                'accepted' => 28,
                                'preparing' => 48,
                                'ready' => 62,
                                'assigned', 'assigned_to_rider' => 76,
                                'picked_up' => 84,
                                'out_for_delivery' => 92,
                                default => 10,
                            };

                            $accentClasses = match ($effectiveStatus) {
                                'out_for_delivery' => [
                                    'bar' => 'bg-blue-500',
                                    'progress' => 'from-blue-500 to-brand-500',
                                    'icon' => 'bg-blue-50 text-blue-600',
                                ],

                                'picked_up', 'assigned', 'assigned_to_rider' => [
                                    'bar' => 'bg-indigo-500',
                                    'progress' => 'from-indigo-500 to-brand-500',
                                    'icon' => 'bg-indigo-50 text-indigo-600',
                                ],

                                'ready', 'preparing', 'accepted' => [
                                    'bar' => 'bg-gold-500',
                                    'progress' => 'from-gold-500 to-food-tan',
                                    'icon' => 'bg-gold-50 text-food-brown',
                                ],

                                default => [
                                    'bar' => 'bg-brand-500',
                                    'progress' => 'from-brand-500 to-brand-600',
                                    'icon' => 'bg-brand-50 text-brand-500',
                                ],
                            };
                        @endphp

                        <article class="relative overflow-hidden rounded-[1.5rem] border border-warm-200 bg-white shadow-sm sm:rounded-[1.75rem]">
                            <div class="absolute inset-y-0 left-0 w-1.5 {{ $accentClasses['bar'] }}"></div>

                            <div class="p-4 pl-6 sm:p-6 sm:pl-8">
                                {{-- Order Heading --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-[9px] font-black uppercase tracking-[0.16em] text-warm-500 sm:text-xs">
                                            Order Number
                                        </p>

                                        <a
                                            href="{{ route('customer.orders.show', $order) }}"
                                            class="mt-1 block break-all text-lg font-black tracking-tight text-warm-950 transition hover:text-brand-600 sm:text-xl"
                                        >
                                            {{ $order->order_number }}
                                        </a>

                                        <p class="mt-1 text-xs font-semibold text-warm-500">
                                            {{ $order->created_at->format('M d, Y · h:i A') }}
                                        </p>
                                    </div>

                                    <div class="flex max-w-[55%] flex-col items-end gap-2">
                                        <x-status-badge :status="$order->order_status" />

                                        @if ($deliveryStatus)
                                            <x-status-badge
                                                :status="$deliveryStatus"
                                                type="delivery"
                                            />
                                        @endif
                                    </div>
                                </div>

                                {{-- Current Status --}}
                                <div class="mt-4 flex items-center gap-3 rounded-2xl bg-warm-50 p-3.5">
                                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $accentClasses['icon'] }}">
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
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-warm-950">
                                            {{ \Illuminate\Support\Str::headline($effectiveStatus) }}
                                        </p>

                                        <p class="mt-0.5 text-xs font-semibold leading-5 text-warm-500">
                                            {{ $statusMessage }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Progress --}}
                                <div class="mt-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="text-[9px] font-black uppercase tracking-[0.14em] text-warm-500">
                                            Order progress
                                        </p>

                                        <p class="text-xs font-black text-warm-600">
                                            {{ $progress }}%
                                        </p>
                                    </div>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-warm-100">
                                        <div
                                            class="h-full rounded-full bg-gradient-to-r {{ $accentClasses['progress'] }}"
                                            style="width: {{ $progress }}%"
                                        ></div>
                                    </div>
                                </div>

                                {{-- Compact Metadata --}}
                                <div class="mt-4 grid grid-cols-3 gap-2 sm:gap-3">
                                    <div class="rounded-xl bg-warm-50 px-3 py-3 sm:rounded-2xl">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                            Total
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-warm-950 sm:text-base">
                                            ($order->total)
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-warm-50 px-3 py-3 sm:rounded-2xl">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                            Payment
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-warm-950 sm:text-base">
                                            {{ strtoupper($order->payment_method ?? 'COD') }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-warm-50 px-3 py-3 sm:rounded-2xl">
                                        <p class="text-[8px] font-black uppercase tracking-[0.1em] text-warm-500">
                                            Date
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-warm-950 sm:text-base">
                                            {{ $order->created_at->format('M d') }}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('customer.orders.show', $order) }}"
                                    class="mt-4 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition active:scale-[0.98] hover:bg-brand-600 sm:rounded-2xl"
                                >
                                    Track Order

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
            </section>
        @endif

        {{-- Previous Orders --}}
        @if ($pastOrders->isNotEmpty())
            <section>
                <div class="mb-3 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-warm-500 sm:text-xs">
                            Previous Orders
                        </p>

                        <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                            Order history
                        </h2>
                    </div>

                    <span class="rounded-full bg-warm-100 px-3 py-1.5 text-[10px] font-black text-warm-600">
                        {{ $pastOrders->count() }} shown
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach ($pastOrders as $order)
                        @php
                            $deliveryStatus = $order->delivery?->status;

                            $isCancelled = $order->order_status === 'cancelled'
                                || $deliveryStatus === 'failed';

                            $pastAccent = $isCancelled
                                ? 'bg-red-500'
                                : 'bg-leaf-500';
                        @endphp

                        <article class="relative overflow-hidden rounded-[1.4rem] border border-warm-200 bg-white shadow-sm">
                            <div class="absolute inset-y-0 left-0 w-1 {{ $pastAccent }}"></div>

                            <div class="p-4 pl-5 sm:p-5 sm:pl-7">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <a
                                            href="{{ route('customer.orders.show', $order) }}"
                                            class="block break-all text-base font-black text-warm-950 transition hover:text-brand-600 sm:text-lg"
                                        >
                                            {{ $order->order_number }}
                                        </a>

                                        <p class="mt-1 text-xs font-semibold text-warm-500">
                                            {{ $order->created_at->format('M d, Y · h:i A') }}
                                        </p>
                                    </div>

                                    <x-status-badge :status="$order->order_status" />
                                </div>

                                <div class="mt-4 flex items-center justify-between gap-4 border-t border-warm-100 pt-4">
                                    <div class="flex items-center gap-5">
                                        <div>
                                            <p class="text-[8px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Total
                                            </p>

                                            <p class="mt-1 text-sm font-black text-warm-950">
                                                ($order->total)
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-[8px] font-black uppercase tracking-[0.12em] text-warm-500">
                                                Payment
                                            </p>

                                            <p class="mt-1 text-sm font-black text-warm-950">
                                                {{ strtoupper($order->payment_method ?? 'COD') }}
                                            </p>
                                        </div>
                                    </div>

                                    <a
                                        href="{{ route('customer.orders.show', $order) }}"
                                        class="inline-flex min-h-10 shrink-0 items-center justify-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-black text-brand-600 transition active:scale-[0.97] hover:bg-brand-100"
                                    >
                                        Details

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            class="h-3.5 w-3.5"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Pagination --}}
        @if ($orders->hasPages())
            <div class="rounded-[1.5rem] border border-warm-200 bg-white p-4 shadow-sm">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    @endif
</div>

@endcomponent
