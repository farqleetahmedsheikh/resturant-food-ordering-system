<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFinanceController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = Restaurant::current();
        $timezone = $restaurant?->timezone ?: config('app.timezone', 'UTC');
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $from = $this->date($request->input('date_from'), $timezone)?->startOfDay() ?? $today->subDays(29);
        $to = $this->date($request->input('date_to'), $timezone)?->endOfDay() ?? $today->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        $orders = $this->paidOrders($restaurant?->id, $from, $to)
            ->with('items')
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->get();

        $daily = collect();
        for ($day = $from->startOfDay(); $day->lte($to->startOfDay()); $day = $day->addDay()) {
            $key = $day->toDateString();
            $dayOrders = $orders->filter(fn (Order $order): bool => $this->orderDate($order, $timezone)->toDateString() === $key);
            $daily->push([
                'label' => $day->format($from->diffInDays($to) > 14 ? 'd M' : 'D'),
                'date' => $key,
                'orders' => $dayOrders->count(),
                'sales' => round((float) $dayOrders->sum('total'), 2),
            ]);
        }

        $topProducts = $orders
            ->flatMap(fn (Order $order) => $order->items)
            ->groupBy('item_name')
            ->map(fn ($items, string $name): array => [
                'name' => $name,
                'quantity' => (int) $items->sum('quantity'),
                'sales' => round((float) $items->sum('total'), 2),
            ])
            ->sortByDesc('sales')
            ->take(8)
            ->values();

        return view('admin.finance', [
            'restaurant' => $restaurant,
            'from' => $from,
            'to' => $to,
            'orders' => $orders->take(12),
            'daily' => $daily,
            'topProducts' => $topProducts,
            'salesTotal' => round((float) $orders->sum('total'), 2),
            'subtotalTotal' => round((float) $orders->sum('subtotal'), 2),
            'deliveryTotal' => round((float) $orders->sum('delivery_fee'), 2),
            'orderCount' => $orders->count(),
            'averageOrder' => $orders->count() ? round((float) $orders->avg('total'), 2) : 0,
            'currency' => $orders->first()?->currency ?? 'AUD',
        ]);
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
            return CarbonImmutable::createFromFormat('Y-m-d', $value, $timezone);
        } catch (\Throwable) {
            return null;
        }
    }

    private function orderDate(Order $order, string $timezone): CarbonImmutable
    {
        return CarbonImmutable::parse($order->paid_at ?? $order->created_at)->setTimezone($timezone);
    }
}
