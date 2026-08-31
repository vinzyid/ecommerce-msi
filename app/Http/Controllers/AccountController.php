<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->withCount('items')
            ->latest('ordered_at')
            ->limit(4)
            ->get();

        $stats = [
            'total' => $user->orders()->count(),
            'active' => $user->orders()->whereIn('status', ['pending', 'processing', 'shipped'])->count(),
            'spent' => (int) $user->orders()->where('status', '!=', 'cancelled')->sum('total'),
        ];

        $cartCount = (int) $user->cartItems()->sum('quantity');

        return view('account.show', compact('user', 'orders', 'stats', 'cartCount'));
    }
}
