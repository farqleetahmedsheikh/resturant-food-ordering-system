@component('layouts.admin', ['title' => 'Finance'])
    @php
        $maxSales = max(1, (float) $daily->max('sales'));
        $maxProductSales = max(1, (float) $topProducts->max('sales'));
        $periodDays = $from->diffInDays($to) + 1;
    @endphp

    <div class="space-y-6 pb-8">
        <header class="rounded-[2rem] bg-warm-950 p-6 text-white shadow-xl shadow-warm-950/15 sm:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-brand-300">Business performance</p>
                    <h1 class="mt-2 text-3xl font-black sm:text-4xl">Finance</h1>
                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-warm-300">Track confirmed sales, order value, and best-selling menu items across any date range.</p>
                </div>
                <form method="GET" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    <label class="grid gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-[0.12em] text-warm-400">From</span>
                        <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="rounded-xl border-0 bg-white px-3 py-2.5 text-sm font-bold text-warm-900 outline-none ring-2 ring-transparent focus:ring-brand-300">
                    </label>
                    <label class="grid gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-[0.12em] text-warm-400">To</span>
                        <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="rounded-xl border-0 bg-white px-3 py-2.5 text-sm font-bold text-warm-900 outline-none ring-2 ring-transparent focus:ring-brand-300">
                    </label>
                    <button class="min-h-11 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-black text-white transition hover:bg-brand-600">Apply</button>
                </form>
            </div>
            <div class="mt-5 flex flex-wrap gap-2 text-xs font-black">
                <a href="{{ route('admin.finance', ['date_from' => now($restaurant?->timezone ?: config('app.timezone'))->toDateString(), 'date_to' => now($restaurant?->timezone ?: config('app.timezone'))->toDateString()]) }}" class="rounded-full bg-white/10 px-3 py-2 text-white hover:bg-white/20">Today</a>
                <a href="{{ route('admin.finance', ['date_from' => now($restaurant?->timezone ?: config('app.timezone'))->subDays(6)->toDateString(), 'date_to' => now($restaurant?->timezone ?: config('app.timezone'))->toDateString()]) }}" class="rounded-full bg-white/10 px-3 py-2 text-white hover:bg-white/20">Last 7 days</a>
                <a href="{{ route('admin.finance', ['date_from' => now($restaurant?->timezone ?: config('app.timezone'))->startOfMonth()->toDateString(), 'date_to' => now($restaurant?->timezone ?: config('app.timezone'))->toDateString()]) }}" class="rounded-full bg-white/10 px-3 py-2 text-white hover:bg-white/20">This month</a>
            </div>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Sales', $salesTotal, 'text-brand-600', 'bg-brand-50'], ['Orders', $orderCount, 'text-warm-950', 'bg-warm-100'], ['Average order', $averageOrder, 'text-leaf-700', 'bg-leaf-50'], ['Delivery fees', $deliveryTotal, 'text-gold-700', 'bg-gold-50']] as [$label, $value, $tone, $background])
                <div class="rounded-3xl border border-warm-200 {{ $background }} p-5">
                    <p class="text-xs font-black uppercase tracking-[0.15em] text-warm-500">{{ $label }}</p>
                    <p class="mt-3 text-2xl font-black {{ $tone }}">{{ $label === 'Orders' ? number_format($value) : \App\Support\Money::format($value) }}</p>
                    <p class="mt-1 text-xs font-semibold text-warm-500">{{ $periodDays }} day period</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,0.8fr)]">
            <div class="rounded-[2rem] border border-warm-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div><h2 class="text-xl font-black text-warm-950">Sales trend</h2><p class="mt-1 text-sm font-semibold text-warm-500">Confirmed revenue by day</p></div>
                    <span class="rounded-full bg-leaf-50 px-3 py-1.5 text-xs font-black text-leaf-700">{{ $from->format('d M') }} – {{ $to->format('d M Y') }}</span>
                </div>
                <div class="mt-7 flex h-64 items-end gap-1.5 overflow-x-auto border-b border-l border-warm-200 px-2 pb-0 pt-5 sm:gap-2">
                    @foreach ($daily as $point)
                        <div class="group flex h-full min-w-7 flex-1 flex-col justify-end gap-2 sm:min-w-9">
                            <div class="relative flex-1">
                                <div class="absolute bottom-0 left-1/2 w-full -translate-x-1/2 rounded-t-lg bg-brand-500 transition group-hover:bg-brand-600" style="height: {{ max($point['sales'] > 0 ? 6 : 1, ($point['sales'] / $maxSales) * 100) }}%">
                                    <span class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-warm-950 px-2 py-1 text-[10px] font-black text-white group-hover:block">{{ \App\Support\Money::format($point['sales']) }}</span>
                                </div>
                            </div>
                            <span class="truncate text-center text-[9px] font-black text-warm-500">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[2rem] border border-warm-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-xl font-black text-warm-950">Top products</h2>
                <p class="mt-1 text-sm font-semibold text-warm-500">Sales contribution in this period</p>
                <div class="mt-5 space-y-4">
                    @forelse ($topProducts as $product)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm"><span class="min-w-0 truncate font-black text-warm-800">{{ $product['name'] }}</span><span class="shrink-0 font-black text-warm-950">{{ \App\Support\Money::format($product['sales']) }}</span></div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-warm-100"><div class="h-full rounded-full bg-leaf-500" style="width: {{ max(4, ($product['sales'] / $maxProductSales) * 100) }}%"></div></div>
                            <p class="mt-1 text-[11px] font-semibold text-warm-500">{{ $product['quantity'] }} sold</p>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-warm-50 p-5 text-sm font-bold text-warm-500">No paid orders in this period.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-warm-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="text-xl font-black text-warm-950">Paid orders</h2><p class="mt-1 text-sm font-semibold text-warm-500">Open any order to view or download its invoice.</p></div><span class="rounded-full bg-leaf-50 px-3 py-1.5 text-xs font-black text-leaf-700">{{ $orderCount }} confirmed</span></div>
            <div class="mt-5 divide-y divide-warm-100 rounded-2xl border border-warm-200">
                @forelse ($orders as $order)
                    <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-black text-warm-950">{{ $order->order_number }}</p><p class="mt-1 text-xs font-semibold text-warm-500">{{ $order->customer_name }} · {{ ($order->paid_at ?? $order->created_at)->format('d M Y, h:i A') }}</p></div><div class="flex items-center justify-between gap-4 sm:justify-end"><span class="text-lg font-black text-brand-600">{{ \App\Support\Money::format($order->total) }}</span><a href="{{ route('admin.orders.invoice', $order) }}" class="rounded-xl bg-warm-950 px-3 py-2 text-xs font-black text-white">Invoice</a></div></div>
                @empty
                    <p class="px-4 py-10 text-center text-sm font-bold text-warm-500">No paid orders in this period.</p>
                @endforelse
            </div>
        </section>
    </div>
@endcomponent
