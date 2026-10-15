<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminFinanceController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $restaurant = Restaurant::current();
        $timezone = $restaurant?->timezone ?: config('app.timezone', 'UTC');
        [$from, $to, $range] = $this->dateRange($request, $timezone);
        $periodDays = $from->diffInDays($to) + 1;

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($restaurant?->id, $from, $to, $timezone);
        }

        $compare = $request->query('compare') === 'previous_month' ? 'previous_month' : 'previous_period';
        if ($compare === 'previous_month') {
            $compareFrom = $to->subMonthNoOverflow()->startOfMonth();
            $compareTo = $to->subMonthNoOverflow()->endOfMonth();
        } else {
            $compareTo = $from->subDay()->endOfDay();
            $compareFrom = $compareTo->subDays(max(0, $periodDays - 1))->startOfDay();
        }

        $orders = $this->paidOrders($restaurant?->id, $from, $to)
            ->with('items.menuItem.category')
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();
        $comparisonOrders = $this->paidOrders($restaurant?->id, $compareFrom, $compareTo)->get();
        $ordersByDate = $orders->groupBy(fn (Order $order): string => $this->orderDate($order, $timezone)->toDateString());
        $comparisonOrdersByDate = $comparisonOrders->groupBy(fn (Order $order): string => $this->orderDate($order, $timezone)->toDateString());

        $daily = collect();
        for ($day = $from->startOfDay(); $day->lte($to->startOfDay()); $day = $day->addDay()) {
            $offset = $from->diffInDays($day);
            $comparisonDay = $compareFrom->addDays($offset);
            $dayOrders = $ordersByDate->get($day->toDateString(), collect());
            $comparisonDayOrders = $comparisonOrdersByDate->get($comparisonDay->toDateString(), collect());
            $daily->push([
                'label' => $periodDays > 14 ? $day->format('d M') : $day->format('D'),
                'date' => $day->toDateString(),
                'sales' => round((float) $dayOrders->sum('total'), 2),
                'comparison_sales' => round((float) $comparisonDayOrders->sum('total'), 2),
                'orders' => $dayOrders->count(),
            ]);
        }

        $items = $orders->flatMap(fn (Order $order) => $order->items);
        $topProducts = $items
            ->groupBy('item_name')
            ->map(fn ($productItems, string $name): array => [
                'name' => $name,
                'quantity' => (int) $productItems->sum('quantity'),
                'sales' => round((float) $productItems->sum('total'), 2),
                'image' => $productItems->first()?->menuItem?->image_url,
            ])
            ->sortByDesc('quantity')
            ->take(5)
            ->values();

        $categorySales = $items
            ->groupBy(fn (OrderItem $item): string => $item->menuItem?->category?->name ?? 'Other')
            ->map(fn ($categoryItems, string $name): array => [
                'name' => $name,
                'sales' => round((float) $categoryItems->sum('total'), 2),
            ])
            ->sortByDesc('sales')
            ->take(6)
            ->values();

        $ordersByDay = collect(range(1, 7))->mapWithKeys(function (int $isoDay) use ($orders, $timezone): array {
            $name = CarbonImmutable::now()->startOfWeek()->addDays($isoDay - 1)->format('D');

            return [$isoDay => [
                'label' => $name,
                'count' => $orders->filter(fn (Order $order): bool => $this->orderDate($order, $timezone)->dayOfWeekIso === $isoDay)->count(),
            ]];
        })->values();

        $comparisonWeeks = collect(range(0, 3))->map(function (int $bucket) use ($daily, $periodDays): array {
            $points = $daily->filter(
                fn (array $point, int $index): bool => min(3, (int) floor($index * 4 / $periodDays)) === $bucket,
            );

            return [
                'label' => 'Period ' . ($bucket + 1),
                'sales' => round((float) $points->sum('sales'), 2),
                'comparison_sales' => round((float) $points->sum('comparison_sales'), 2),
            ];
        });

        $salesTotal = round((float) $orders->sum('total'), 2);
        $netSales = round((float) $orders->sum('subtotal'), 2);
        $previousSales = round((float) $comparisonOrders->sum('total'), 2);
        $previousNetSales = round((float) $comparisonOrders->sum('subtotal'), 2);

        return view('admin.finance', [
            'restaurant' => $restaurant,
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'compare' => $compare,
            'compareFrom' => $compareFrom,
            'compareTo' => $compareTo,
            'orders' => $orders->take(12),
            'daily' => $daily,
            'categorySales' => $categorySales,
            'ordersByDay' => $ordersByDay,
            'comparisonWeeks' => $comparisonWeeks,
            'topProducts' => $topProducts,
            'salesTotal' => $salesTotal,
            'netSales' => $netSales,
            'deliveryTotal' => round((float) $orders->sum('delivery_fee'), 2),
            'orderCount' => $orders->count(),
            'averageOrder' => $orders->count() ? round((float) $orders->avg('total'), 2) : 0,
            'previousSales' => $previousSales,
            'previousNetSales' => $previousNetSales,
            'previousDeliveryTotal' => round((float) $comparisonOrders->sum('delivery_fee'), 2),
            'previousOrderCount' => $comparisonOrders->count(),
            'previousAverageOrder' => $comparisonOrders->count() ? round((float) $comparisonOrders->avg('total'), 2) : 0,
            'currency' => $orders->first()?->currency ?? $comparisonOrders->first()?->currency ?? 'AUD',
            'printReport' => $request->boolean('report'),
        ]);
    }

    private function dateRange(Request $request, string $timezone): array
    {
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $range = (string) $request->query('range', 'this_month');
        $range = in_array($range, ['today', '7_days', '30_days', 'this_month', 'last_month', 'custom'], true)
            ? $range
            : 'this_month';

        [$defaultFrom, $defaultTo] = match ($range) {
            'today' => [$today, $today->endOfDay()],
            '7_days' => [$today->subDays(6), $today->endOfDay()],
            '30_days' => [$today->subDays(29), $today->endOfDay()],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            default => [$today->startOfMonth(), $today->endOfDay()],
        };

        $from = $this->date($request->input('date_from'), $timezone)?->startOfDay() ?? $defaultFrom;
        $to = $this->date($request->input('date_to'), $timezone)?->endOfDay() ?? $defaultTo;

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        if (! $request->filled('range') && ($request->filled('date_from') || $request->filled('date_to'))) {
            $range = 'custom';
        }

        return [$from, $to, $range];
    }

    private function exportCsv(?int $restaurantId, CarbonImmutable $from, CarbonImmutable $to, string $timezone): StreamedResponse
    {
        $orders = $this->paidOrders($restaurantId, $from, $to)
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();
        $filename = 'paid-orders-' . $from->toDateString() . '-to-' . $to->toDateString() . '.csv';

        return Response::streamDownload(function () use ($orders, $timezone): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Order ID', 'Customer', 'Date & Time', 'Amount', 'Payment Method', 'Status']);

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->order_number,
                    $order->customer_name,
                    $this->orderDate($order, $timezone)->format('Y-m-d H:i:s'),
                    number_format((float) $order->total, 2, '.', ''),
                    $order->payment_method_label,
                    'Paid',
                ]);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function paidOrders(?int $restaurantId, CarbonImmutable $from, CarbonImmutable $to)
    {
        $utcFrom = $from->setTimezone('UTC');
        $utcTo = $to->setTimezone('UTC');

        return Order::query()
            ->when($restaurantId, fn ($query) => $query->where('restaurant_id', $restaurantId))
            ->where('payment_status', 'paid')
            ->whereNotIn('order_status', ['cancelled'])
            ->where(function ($query) use ($utcFrom, $utcTo): void {
                $query->whereBetween('paid_at', [$utcFrom, $utcTo])
                    ->orWhere(fn ($fallback) => $fallback
                        ->whereNull('paid_at')
                        ->whereBetween('created_at', [$utcFrom, $utcTo]));
            });
    }

    private function date(mixed $value, string $timezone): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value, $timezone);
        } catch (\Throwable) {
            return null;
        }
    }

    private function orderDate(Order $order, string $timezone): CarbonImmutable
    {
        return CarbonImmutable::parse($order->paid_at ?? $order->created_at)->setTimezone($timezone);
    }
}
