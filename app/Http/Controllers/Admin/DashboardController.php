<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
