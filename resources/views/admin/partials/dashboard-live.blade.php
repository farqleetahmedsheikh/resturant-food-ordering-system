@php
    $pendingQuickCount = $pendingQuickOrders->count();
@endphp

<div class="space-y-5">
    <section class="overflow-hidden rounded-[1.75rem] border border-gold-100 bg-white shadow-sm">
        <div class="border-b border-gold-100 bg-gradient-to-r from-gold-50 via-brand-50 to-white px-4 py-4 sm:px-6 sm:py-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-gold-100 bg-white px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] text-gold-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-leaf-500">
                                <span class="block h-2 w-2 animate-ping rounded-full bg-leaf-500"></span>
                            </span>

                            Live order desk
                        </span>

                        <span class="rounded-full border border-white bg-white/80 px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.12em] text-warm-500 shadow-sm">
                            Refreshes every 90s
                        </span>
                    </div>

                    <h2 class="mt-3 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                        Quick pending orders
                    </h2>

                    <p class="mt-1 text-xs font-semibold leading-5 text-warm-500">
                        Confirm the kitchen queue quickly, or decline with a clear reason for records.
                    </p>
                </div>

                <div class="shrink-0 rounded-2xl border border-white bg-white/85 px-4 py-3 text-left shadow-sm sm:text-right">
                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-warm-500">
                        Last sync
                    </p>

                    <p class="mt-1 text-sm font-black text-warm-950">
                        {{ $liveUpdatedAt->format('h:i:s A') }}
                    </p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                <article class="rounded-2xl border border-white bg-white/90 px-3 py-3 shadow-sm">
                    <p class="text-[8px] font-black uppercase tracking-[0.12em] text-gold-500">
                        Pending
                    </p>

                    <p class="mt-1 text-2xl font-black text-gold-700">
                        {{ $livePendingOrders }}
                    </p>
                </article>

                <article class="rounded-2xl border border-white bg-white/90 px-3 py-3 shadow-sm">
                    <p class="text-[8px] font-black uppercase tracking-[0.12em] text-sky-600">
                        Accepted
                    </p>

                    <p class="mt-1 text-2xl font-black text-sky-950">
                        {{ $liveAcceptedOrders }}
                    </p>
                </article>

                <article class="rounded-2xl border border-white bg-white/90 px-3 py-3 shadow-sm">
                    <p class="text-[8px] font-black uppercase tracking-[0.12em] text-brand-500">
                        Preparing
                    </p>

                    <p class="mt-1 text-2xl font-black text-brand-900">
                        {{ $livePreparingOrders }}
                    </p>
                </article>

                <article class="rounded-2xl border border-white bg-white/90 px-3 py-3 shadow-sm">
                    <p class="text-[8px] font-black uppercase tracking-[0.12em] text-blue-600">
                        On the way
                    </p>

                    <p class="mt-1 text-2xl font-black text-blue-950">
                        {{ $liveOutForDeliveryOrders }}
                    </p>
                </article>
            </div>
        </div>

        @if ($pendingQuickOrders->isEmpty())
            <div class="p-8 text-center sm:p-12">
                <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-leaf-50 text-leaf-700">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-8 w-8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m5 13 4 4L19 7"
                        />
                    </svg>
                </span>

                <h3 class="mt-5 text-xl font-black text-warm-950">
                    No pending orders right now
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-warm-600">
                    The live queue is clear. New pending orders will appear here automatically.
                </p>
            </div>
        @else
            <div class="divide-y divide-warm-100">
                @foreach ($pendingQuickOrders as $order)
                    <article class="px-4 py-4 sm:px-6 sm:py-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="break-all text-base font-black text-warm-950 transition hover:text-brand-600 sm:text-lg"
                                    >
                                        {{ $order->order_number }}
                                    </a>

                                    <x-status-badge :status="$order->order_status" />
                                </div>

                                <div class="mt-2 grid gap-2 text-xs font-semibold text-warm-500 sm:grid-cols-2 xl:grid-cols-4">
                                    <span class="min-w-0 truncate">
                                        {{ $order->customer_name }}
                                    </span>

                                    <span class="min-w-0 truncate">
                                        {{ $order->customer_phone }}
                                    </span>

                                    <span>
                                        @money($order->total)
                                    </span>

                                    <span>
                                        {{ $order->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <p class="mt-2 line-clamp-1 text-xs font-semibold text-warm-500">
                                    {{ $order->delivery_address }}
                                </p>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    data-confirm-order="{{ $order->id }}"
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-leaf-700 px-4 py-2 text-xs font-black text-white shadow-lg shadow-leaf-700/20 transition active:scale-[0.98] hover:bg-leaf-700"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.4"
                                        class="h-4 w-4"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m5 13 4 4L19 7"
                                        />
                                    </svg>

                                    Confirm
                                </button>

                                <button
                                    type="button"
                                    data-decline-order="{{ $order->id }}"
                                    data-order-number="{{ e($order->order_number) }}"
                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-700 transition active:scale-[0.98] hover:bg-red-100"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2.4"
                                        class="h-4 w-4"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            d="m6 6 12 12M18 6 6 18"
                                        />
                                    </svg>

                                    Decline
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($livePendingOrders > $pendingQuickCount)
                <div class="border-t border-gold-100 bg-gold-50 px-4 py-3 text-center sm:px-6">
                    <a
                        href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
                        class="text-xs font-black text-gold-700 transition hover:text-gold-700"
                    >
                        View all {{ $livePendingOrders }} pending orders
                    </a>
                </div>
            @endif
        @endif
    </section>

    <section class="overflow-hidden rounded-[1.75rem] border border-warm-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-warm-200 px-4 py-4 sm:px-6 sm:py-5">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-brand-500 sm:text-xs">
                    Recent Activity
                </p>

                <h2 class="mt-1 text-xl font-black tracking-tight text-warm-950 sm:text-2xl">
                    Latest orders
                </h2>

                <p class="mt-1 text-xs font-semibold text-warm-500">
                    This list refreshes with the live queue.
                </p>
            </div>

            <a
                href="{{ route('admin.orders.index') }}"
                class="inline-flex min-h-10 shrink-0 items-center justify-center gap-1.5 rounded-xl border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-black text-brand-600 transition hover:bg-brand-100 sm:px-4"
            >
                View All

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-3.5 w-3.5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m9 18 6-6-6-6"
                    />
                </svg>
            </a>
        </div>

        @if ($recentOrders->isEmpty())
            <div class="p-8 text-center sm:p-12">
                <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-brand-50 text-brand-500">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        class="h-8 w-8"
                    >
                        <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z" />
                    </svg>
                </span>

                <h3 class="mt-5 text-xl font-black text-warm-950">
                    No orders yet
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-warm-600">
                    New customer orders will appear here when ordering begins.
                </p>
            </div>
        @else
            <div class="divide-y divide-warm-100">
                @foreach ($recentOrders as $order)
                    @php
                        $orderNeedsAttention = in_array(
                            $order->order_status,
                            ['pending', 'accepted'],
                            true
                        );
                    @endphp

                    <a
                        href="{{ route('admin.orders.show', $order) }}"
                        class="group flex items-center gap-3 px-4 py-4 transition hover:bg-brand-50/50 sm:px-6 sm:py-5"
                    >
                        <span
                            @class([
                                'grid h-11 w-11 shrink-0 place-items-center rounded-xl text-xs font-black sm:h-12 sm:w-12',
                                'bg-gold-50 text-gold-700' => $orderNeedsAttention,
                                'bg-warm-100 text-warm-600' => ! $orderNeedsAttention,
                            ])
                        >
                            {{ mb_strtoupper(
                                mb_substr(
                                    $order->customer_name ?? 'C',
                                    0,
                                    1
                                )
                            ) }}
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="break-all text-sm font-black text-warm-950 sm:text-base">
                                    {{ $order->order_number }}
                                </span>

                                @if ($orderNeedsAttention)
                                    <span class="rounded-full bg-gold-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-[0.1em] text-gold-700">
                                        Attention
                                    </span>
                                @endif
                            </span>

                            <span class="mt-1 flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-warm-500">
                                <span class="truncate">
                                    {{ $order->customer_name }}
                                </span>

                                <span class="text-warm-300">•</span>

                                <span>
                                    @money($order->total)
                                </span>

                                <span class="text-warm-300">•</span>

                                <span class="truncate">
                                    {{ $order->rider?->name ?? 'Unassigned' }}
                                </span>
                            </span>
                        </span>

                        <span class="hidden shrink-0 sm:block">
                            <x-status-badge :status="$order->order_status" />
                        </span>

                        <span class="hidden min-w-[82px] shrink-0 text-right lg:block">
                            <span class="block text-xs font-black text-warm-600">
                                {{ $order->created_at->format('M d') }}
                            </span>

                            <span class="mt-0.5 block text-[10px] font-semibold text-warm-500">
                                {{ $order->created_at->format('h:i A') }}
                            </span>
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            class="h-4 w-4 shrink-0 text-warm-300 transition group-hover:translate-x-0.5 group-hover:text-brand-500"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m9 18 6-6-6-6"
                            />
                        </svg>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
