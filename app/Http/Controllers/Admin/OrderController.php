<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
        ])['status'] ?? null;

        $orders = Order::with('user')
            ->withCount('items')
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->latest('ordered_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'status'));
    }

    public function show(Order $order): View
    {
        $order->load('items', 'user');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        if (in_array($order->status, ['completed', 'cancelled'], true) && $validated['status'] !== $order->status) {
            return back()->withErrors(['status' => 'Status pesanan ini sudah final.']);
        }

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Status pesanan diperbarui.');
    }
}
