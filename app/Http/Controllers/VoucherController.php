<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function apply(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30',
        ], [
            'code.required' => 'Masukkan kode promo.',
        ]);

        $voucher = Voucher::query()->where('code', strtoupper(trim($validated['code'])))->first();

        if (! $voucher || ! $voucher->isUsable()) {
            return back()->withErrors(['code' => 'Kode promo tidak valid atau sudah kedaluwarsa.']);
        }

        $subtotal = $request->user()->cartItems()
            ->with('product')
            ->get()
            ->sum(fn (CartItem $item) => (int) $item->product->price * $item->quantity);

        if ($subtotal < $voucher->min_spend) {
            return back()->withErrors([
                'code' => 'Minimal belanja Rp'.number_format($voucher->min_spend, 0, ',', '.').' untuk memakai kode ini.',
            ]);
        }

        $request->session()->put('voucher_code', $voucher->code);

        return back()->with('success', 'Kode promo '.$voucher->code.' diterapkan.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->session()->forget('voucher_code');

        return back()->with('success', 'Kode promo dihapus.');
    }
}
