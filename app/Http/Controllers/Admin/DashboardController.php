<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'products' => Product::query()->count(),
            'low_stock' => Product::query()->where('stock', '<=', 5)->count(),
            'pending_orders' => Order::query()->whereIn('status', ['pending', 'processing'])->count(),
            'customers' => User::query()->where('is_admin', false)->count(),
        ];

        $recentOrders = Order::with('user')->latest('ordered_at')->limit(8)->get();

        $productsByCategory = Category::query()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->get();

        $ordersByStatus = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $revenueByMonth = $this->revenueByMonth();

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'productsByCategory',
            'ordersByStatus',
            'revenueByMonth',
        ));
    }

    private function revenueByMonth(): Collection
    {
        $months = collect(range(5, 0))->map(fn (int $offset) => now()->subMonths($offset)->startOfMonth());

        $totals = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('ordered_at', '>=', $months->first())
            ->get(['total', 'ordered_at'])
            ->groupBy(fn (Order $order) => $order->ordered_at->format('Y-m'))
            ->map(fn (Collection $orders) => (int) $orders->sum('total'));

        return $months->map(fn ($month) => [
            'label' => $month->translatedFormat('M'),
            'year' => $month->year,
            'total' => $totals->get($month->format('Y-m'), 0),
        ]);
    }
}
