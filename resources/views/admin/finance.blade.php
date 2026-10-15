@component('layouts.admin', ['title' => 'Finance & Analytics'])
    @php
        $maxRevenue = max(1, (float) $daily->max(fn ($point) => max($point['sales'], $point['comparison_sales'])));
        $maxWeekdayOrders = max(1, (int) $ordersByDay->max('count'));
        $maxWeeklySales = max(1, (float) $comparisonWeeks->max(fn ($week) => max($week['sales'], $week['comparison_sales'])));
        $categoryTotal = max(0.01, (float) $categorySales->sum('sales'));
        $donutColors = ['#ed1020', '#ff8733', '#f5bd28', '#3bae62', '#20a5cf', '#8954dc'];
        $donutOffset = 0;
        $money = fn ($amount) => \App\Support\Money::format($amount);
        $growth = function (float $current, float $previous): ?float {
            return $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : null;
        };
        $salesGrowth = $growth($salesTotal, $previousSales);
        $netGrowth = $growth($netSales, $previousNetSales);
        $ordersGrowth = $growth((float) $orderCount, (float) $previousOrderCount);
        $averageGrowth = $growth((float) $averageOrder, (float) $previousAverageOrder);
        $comparisonLabel = $compare === 'previous_month' ? 'Previous month' : 'Previous period';
        $currentPoints = [];
        $comparisonPoints = [];
        $lastIndex = max(1, $daily->count() - 1);
        foreach ($daily as $index => $point) {
            $x = round(26 + ($index / $lastIndex) * 730, 1);
            $currentPoints[] = $x . ',' . round(174 - ($point['sales'] / $maxRevenue) * 142, 1);
            $comparisonPoints[] = $x . ',' . round(174 - ($point['comparison_sales'] / $maxRevenue) * 142, 1);
        }
        $currentLine = implode(' ', $currentPoints);
        $comparisonLine = implode(' ', $comparisonPoints);
    @endphp

    <div class="space-y-4 pb-8" @if ($printReport) data-print-report @endif>
        <section class="relative overflow-hidden rounded-xl bg-[#181312] px-5 py-5 text-white shadow-sm sm:px-7 lg:px-8">
            <img src="{{ asset('images/menu-hero/mixed-platter.jpg') }}" alt="" aria-hidden="true" class="absolute inset-y-0 right-0 h-full w-[48%] object-cover object-center opacity-45">
            <div class="absolute inset-0 bg-gradient-to-r from-[#181312] via-[#181312]/95 to-[#181312]/45"></div>
            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(450px,.95fr)] xl:items-end">
                <div class="max-w-2xl">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-red-300">Finance overview</p>
                    <h2 class="mt-2 text-xl font-black leading-tight sm:text-2xl">A clear view of your restaurant's performance</h2>
                    <p class="mt-1 text-xs font-medium text-white/75 sm:text-sm">Track confirmed sales, order activity and popular menu items.</p>
                    <p class="mt-3 text-[11px] font-semibold text-white/60">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }} <span class="px-1">·</span> Compare with {{ $compareFrom->format('d M') }} – {{ $compareTo->format('d M Y') }}</p>
                </div>
                <div class="space-y-3">
                    <form method="GET" action="{{ route('admin.finance') }}" class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_minmax(165px,.75fr)_auto] sm:items-end">
                        <input type="hidden" name="range" value="custom">
                        <label class="grid gap-1 text-[10px] font-bold text-white/75">
                            Date from
                            <input type="date" name="date_from" value="{{ $from->toDateString() }}" class="h-10 min-w-0 rounded-lg border border-white/20 bg-white px-3 text-xs font-bold text-warm-950 focus:outline-none focus:ring-2 focus:ring-red-300">
                        </label>
                        <label class="grid gap-1 text-[10px] font-bold text-white/75">
                            Date to
                            <input type="date" name="date_to" value="{{ $to->toDateString() }}" class="h-10 min-w-0 rounded-lg border border-white/20 bg-white px-3 text-xs font-bold text-warm-950 focus:outline-none focus:ring-2 focus:ring-red-300">
                        </label>
                        <label class="grid gap-1 text-[10px] font-bold text-white/75">
                            Compare with
                            <select name="compare" class="h-10 min-w-0 rounded-lg border border-white/20 bg-white px-3 text-xs font-bold text-warm-950 focus:outline-none focus:ring-2 focus:ring-red-300">
                                <option value="previous_period" @selected($compare === 'previous_period')>Previous period</option>
                                <option value="previous_month" @selected($compare === 'previous_month')>Previous month</option>
                            </select>
                        </label>
                        <button class="sr-only">Apply date range</button>
                    </form>
                    <div class="flex flex-wrap gap-1.5" aria-label="Quick date ranges">
                        @foreach ([['today', 'Today'], ['7_days', '7 Days'], ['30_days', '30 Days'], ['this_month', 'This Month'], ['last_month', 'Last Month']] as [$preset, $label])
                            <a href="{{ route('admin.finance', ['range' => $preset, 'compare' => $compare]) }}" @class(['rounded-full px-3 py-1.5 text-[10px] font-black transition', 'bg-brand-600 text-white' => $range === $preset, 'bg-white/10 text-white hover:bg-white/20' => $range !== $preset])>{{ $label }}</a>
                        @endforeach
                        <button type="button" onclick="this.closest('section').querySelector('form').requestSubmit()" class="ml-auto rounded-full bg-white px-4 py-1.5 text-[10px] font-black text-warm-950 hover:bg-red-50">Apply dates</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
            @foreach ([
                ['Total sales', $salesTotal, $salesGrowth, 'cart', 'bg-red-50 text-red-600'],
                ['Net sales', $netSales, $netGrowth, 'receipt', 'bg-green-50 text-green-700'],
                ['Paid orders', $orderCount, $ordersGrowth, 'orders', 'bg-sky-50 text-sky-700'],
                ['Average order', $averageOrder, $averageGrowth, 'average', 'bg-amber-50 text-amber-700'],
                ['Delivery fees', $deliveryTotal, $growth($deliveryTotal, $previousDeliveryTotal), 'scooter', 'bg-orange-50 text-orange-700'],
            ] as [$label, $value, $change, $icon, $iconClass])
                <article class="min-w-0 rounded-xl border border-warm-200 bg-white p-3.5 shadow-sm sm:p-4">
                    <div class="flex items-center gap-2.5">
                        <span @class(['grid h-9 w-9 shrink-0 place-items-center rounded-lg', $iconClass])>
                            @if ($icon === 'orders')<x-ui-icon name="receipt" class="h-4 w-4" />
                            @elseif ($icon === 'average')<x-ui-icon name="chart" class="h-4 w-4" />
                            @else<x-ui-icon :name="$icon" class="h-4 w-4" />@endif
                        </span>
                        <p class="truncate text-[11px] font-bold text-warm-600">{{ $label }}</p>
                    </div>
                    <p class="mt-3 truncate text-xl font-black text-warm-950 sm:text-2xl">{{ $label === 'Paid orders' ? number_format($value) : $money($value) }}</p>
                    <p class="mt-1 flex items-center gap-1 text-[10px] font-bold {{ $change !== null && $change < 0 ? 'text-red-600' : 'text-green-700' }}">
                        <span>{{ $change === null ? '—' : ($change >= 0 ? '↑ ' : '↓ ') . abs($change) . '%' }}</span><span class="truncate font-medium text-warm-500">vs {{ strtolower($comparisonLabel) }}</span>
                    </p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-3 xl:grid-cols-[minmax(0,1.65fr)_minmax(340px,.9fr)]">
            <article class="min-w-0 rounded-xl border border-warm-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><h3 class="text-base font-black text-warm-950">Revenue trend</h3><p class="mt-0.5 text-xs text-warm-500">Daily paid revenue compared with {{ strtolower($comparisonLabel) }}</p></div>
                    <div class="flex gap-3 text-[10px] font-semibold text-warm-600"><span class="inline-flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-brand-600"></i>This period</span><span class="inline-flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-warm-400"></i>{{ $comparisonLabel }}</span></div>
                </div>
                @if ($daily->isNotEmpty())
                    <div class="mt-4 overflow-x-auto">
                        <svg viewBox="0 0 780 230" role="img" aria-label="Daily sales trend compared to previous period" class="h-[210px] min-w-[620px] w-full">
                            @foreach ([0, 1, 2, 3] as $line)
                                <line x1="26" x2="765" y1="{{ 32 + ($line * 47) }}" y2="{{ 32 + ($line * 47) }}" stroke="#eee8e4" stroke-width="1" />
                                <text x="2" y="{{ 36 + ($line * 47) }}" fill="#8a817c" font-size="9">{{ $money($maxRevenue * (1 - $line / 3)) }}</text>
                            @endforeach
                            @if ($comparisonPoints)<polyline points="{{ $comparisonLine }}" fill="none" stroke="#a8a29e" stroke-width="2" stroke-dasharray="6 5" stroke-linecap="round" stroke-linejoin="round" />@endif
                            <polyline points="{{ $currentLine }}" fill="none" stroke="#ed1020" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            @foreach ($daily as $index => $point)
                                <circle cx="{{ 26 + ($index / $lastIndex) * 730 }}" cy="{{ 174 - ($point['sales'] / $maxRevenue) * 142 }}" r="3" fill="#ed1020" />
                                <circle cx="{{ 26 + ($index / $lastIndex) * 730 }}" cy="{{ 174 - ($point['comparison_sales'] / $maxRevenue) * 142 }}" r="2.5" fill="#a8a29e" />
                                @if ($index === 0 || $index === $daily->count() - 1 || $index % max(1, (int) ceil($daily->count() / 7)) === 0)
                                    <text x="{{ 26 + ($index / $lastIndex) * 730 }}" y="211" text-anchor="middle" fill="#716862" font-size="9">{{ $point['label'] }}</text>
                                @endif
                            @endforeach
                        </svg>
                    </div>
                @else
                    <p class="mt-5 rounded-lg bg-warm-50 p-5 text-sm text-warm-500">No paid orders in this period.</p>
                @endif
            </article>

            <article class="min-w-0 rounded-xl border border-warm-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-2"><div><h3 class="text-base font-black text-warm-950">This period vs previous</h3><p class="mt-0.5 text-xs text-warm-500">{{ $comparisonLabel }}</p></div><span class="rounded-full bg-green-50 px-2.5 py-1 text-[10px] font-black text-green-700">{{ $salesGrowth === null ? 'New period' : (($salesGrowth >= 0 ? '+' : '') . $salesGrowth . '%') }}</span></div>
                <div class="mt-4 grid grid-cols-2 divide-x divide-warm-200">
                    <div class="pr-3"><p class="text-[10px] font-semibold text-warm-500">This period</p><p class="mt-1 text-lg font-black text-warm-950">{{ $money($salesTotal) }}</p><p class="text-[10px] text-warm-500">{{ number_format($orderCount) }} orders</p></div>
                    <div class="pl-3"><p class="text-[10px] font-semibold text-warm-500">{{ $comparisonLabel }}</p><p class="mt-1 text-lg font-black text-warm-950">{{ $money($previousSales) }}</p><p class="text-[10px] text-warm-500">{{ number_format($previousOrderCount) }} orders</p></div>
                </div>
                <div class="mt-5 flex h-28 items-end gap-3 border-b border-l border-warm-200 px-3 pb-0">
                    @foreach ($comparisonWeeks as $week)
                        <div class="flex h-full flex-1 items-end justify-center gap-1.5">
                            <div title="This period: {{ $money($week['sales']) }}" class="w-3 rounded-t bg-brand-600 sm:w-5" style="height: {{ max(3, ($week['sales'] / $maxWeeklySales) * 100) }}%"></div>
                            <div title="{{ $comparisonLabel }}: {{ $money($week['comparison_sales']) }}" class="w-3 rounded-t bg-warm-300 sm:w-5" style="height: {{ max(3, ($week['comparison_sales'] / $maxWeeklySales) * 100) }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-center gap-4 text-[9px] font-medium text-warm-500"><span>Current</span><span>{{ $comparisonLabel }}</span></div>
            </article>
        </section>

        <section class="grid gap-3 lg:grid-cols-3">
            <article class="rounded-xl border border-warm-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-base font-black text-warm-950">Sales by category</h3><p class="mt-0.5 text-xs text-warm-500">Food item revenue share</p>
                @if ($categorySales->isNotEmpty())
                    <div class="mt-4 grid grid-cols-[125px_minmax(0,1fr)] items-center gap-3 sm:grid-cols-[145px_minmax(0,1fr)]">
                        <div class="relative mx-auto h-[125px] w-[125px] rounded-full" style="background: conic-gradient(@foreach ($categorySales as $index => $category) {{ $donutColors[$index % count($donutColors)] }} {{ $categorySales->slice(0, $index)->sum(fn ($previous) => $previous['sales']) / $categoryTotal * 100 }}% {{ $categorySales->slice(0, $index + 1)->sum(fn ($previous) => $previous['sales']) / $categoryTotal * 100 }}%{{ $loop->last ? '' : ',' }} @endforeach)">
                            <div class="absolute inset-[22px] grid place-content-center rounded-full bg-white text-center"><strong class="text-xs font-black text-warm-950">{{ $money($categoryTotal) }}</strong><span class="text-[9px] text-warm-500">Food sales</span></div>
                        </div>
                        <div class="space-y-2">
                            @foreach ($categorySales as $index => $category)
                                <div class="flex min-w-0 items-center gap-1.5 text-[10px]"><i class="h-2 w-2 shrink-0 rounded-full" style="background: {{ $donutColors[$index % count($donutColors)] }}"></i><span class="min-w-0 flex-1 truncate font-semibold text-warm-800">{{ $category['name'] }}</span><span class="text-warm-500">{{ number_format($category['sales'] / $categoryTotal * 100, 0) }}%</span><strong class="text-warm-900">{{ $money($category['sales']) }}</strong></div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="mt-5 rounded-lg bg-warm-50 p-4 text-xs text-warm-500">Category breakdown appears when order items are linked to menu categories.</p>
                @endif
            </article>

            <article class="rounded-xl border border-warm-200 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="text-base font-black text-warm-950">Orders by day</h3><p class="mt-0.5 text-xs text-warm-500">Paid order count by weekday</p>
                <div class="mt-5 flex h-[150px] items-end gap-2 border-b border-l border-warm-200 px-2">
                    @foreach ($ordersByDay as $day)
                        <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1.5">
                            <span class="text-[9px] font-bold text-warm-700">{{ $day['count'] }}</span>
                            <div title="{{ $day['count'] }} paid orders" class="w-full max-w-7 rounded-t bg-gradient-to-t from-brand-700 to-brand-500" style="height: {{ max($day['count'] ? 7 : 2, $day['count'] / $maxWeekdayOrders * 100) }}%"></div>
                            <span class="-mb-5 text-[9px] text-warm-500">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="h-5"></div>
            </article>

            <article class="rounded-xl border border-warm-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-center justify-between gap-2"><div><h3 class="text-base font-black text-warm-950">Top selling products</h3><p class="mt-0.5 text-xs text-warm-500">By quantity sold</p></div><span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-50 text-amber-600"><x-ui-icon name="chart" class="h-4 w-4" /></span></div>
                <div class="mt-3 divide-y divide-warm-100">
                    @forelse ($topProducts as $product)
                        <div class="flex items-center gap-2 py-2">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-warm-100 text-[10px] font-black text-warm-700">{{ $loop->iteration }}</span>
                            <div class="grid h-9 w-9 shrink-0 place-items-center overflow-hidden rounded-lg bg-[#fff2e9] text-brand-600">@if ($product['image'])<img src="{{ $product['image'] }}" alt="" loading="lazy" class="h-full w-full object-cover">@else<x-ui-icon name="utensils" class="h-4 w-4" />@endif</div>
                            <span class="min-w-0 flex-1 truncate text-[10px] font-bold text-warm-800">{{ $product['name'] }}<small class="block font-medium text-warm-500">{{ $product['quantity'] }} sold</small></span>
                            <strong class="shrink-0 text-[10px] text-warm-950">{{ $money($product['sales']) }}</strong>
                        </div>
                    @empty
                        <p class="py-8 text-center text-xs font-semibold text-warm-500">No item sales in this period.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-xl border border-warm-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-warm-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div><h3 class="text-base font-black text-warm-950">Paid orders & transactions</h3><p class="mt-0.5 text-xs text-warm-500">Recent confirmed payments. Download the CSV for all matching transactions.</p></div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.finance', ['range' => $range, 'date_from' => $from->toDateString(), 'date_to' => $to->toDateString(), 'compare' => $compare, 'export' => 'csv']) }}" class="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-lg border border-warm-200 px-3 text-[10px] font-black text-warm-700 hover:bg-warm-50"><x-ui-icon name="download" class="h-3.5 w-3.5" /> Export CSV</a>
                    <a href="{{ route('admin.finance', ['range' => $range, 'date_from' => $from->toDateString(), 'date_to' => $to->toDateString(), 'compare' => $compare, 'report' => 1]) }}" target="_blank" class="inline-flex min-h-9 items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-3 text-[10px] font-black text-white hover:bg-brand-700"><x-ui-icon name="receipt" class="h-3.5 w-3.5" /> Download report</a>
                    <label class="sr-only" for="finance-range">Period</label>
                    <select id="finance-range" onchange="location.href=this.value" class="h-9 rounded-lg border border-warm-200 bg-white px-3 text-[10px] font-bold text-warm-700">
                        @foreach ([['today','Today'],['7_days','Last 7 days'],['30_days','Last 30 days'],['this_month','This month'],['last_month','Last month']] as [$preset,$label])
                            <option value="{{ route('admin.finance', ['range' => $preset, 'compare' => $compare]) }}" @selected($range === $preset)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-left text-xs">
                    <thead class="bg-[#faf7f5] text-[10px] font-black text-warm-600"><tr><th class="px-4 py-3">#</th><th class="px-4 py-3">Order ID</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Date & time</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Payment method</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Action</th></tr></thead>
                    <tbody class="divide-y divide-warm-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-warm-50/70"><td class="px-4 py-3 text-warm-500">{{ $loop->iteration }}</td><td class="whitespace-nowrap px-4 py-3 font-black text-warm-900">{{ $order->order_number }}</td><td class="max-w-40 truncate px-4 py-3 font-semibold text-warm-700">{{ $order->customer_name }}</td><td class="whitespace-nowrap px-4 py-3 text-warm-600">{{ ($order->paid_at ?? $order->created_at)->timezone($restaurant?->timezone ?: config('app.timezone'))->format('d M Y, g:i A') }}</td><td class="whitespace-nowrap px-4 py-3 font-black text-warm-950">{{ $money($order->total) }}</td><td class="whitespace-nowrap px-4 py-3 text-warm-600">{{ $order->payment_method_label }}</td><td class="px-4 py-3"><span class="rounded-full bg-green-50 px-2.5 py-1 text-[9px] font-black text-green-700">Paid</span></td><td class="px-4 py-3"><a href="{{ route('admin.orders.invoice', $order) }}" class="rounded-md border border-warm-200 px-2.5 py-1.5 text-[10px] font-bold text-warm-700 hover:bg-white">View invoice</a></td></tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-sm font-semibold text-warm-500">No paid orders found for this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-warm-100 px-4 py-3 text-[10px] text-warm-500"><span>Showing {{ $orders->count() }} of {{ number_format($orderCount) }} matching orders</span><span>All amounts in {{ $currency }}</span></div>
        </section>
    </div>

    @if ($printReport)
        @push('scripts')<script>window.addEventListener('load', () => window.print());</script>@endpush
    @endif
    @push('head')
        <style>
            @media print {
                aside, body > div > header, body > div > nav, body > div > div > main > header, [data-print-report] a, [data-print-report] button, [data-print-report] select, [data-print-report] form { display: none !important; }
                body, body > div, body > div > div, body > div > div > main { background: #fff !important; color: #111 !important; padding: 0 !important; margin: 0 !important; width: 100% !important; }
                [data-print-report] { display: block !important; }
                [data-print-report] section, [data-print-report] article { break-inside: avoid; box-shadow: none !important; }
            }
        </style>
    @endpush
@endcomponent
