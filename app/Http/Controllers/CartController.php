<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Support\CartCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartCalculator $calculator)
    {
    }

    public function index(Request $request): View
    {
        $cartItems = $request->user()->cartItems()->with('product.category')->get();
        $summary = $this->calculator->summarize($request->user(), $request->session()->get('voucher_code'));

        return view('cart.index', [
            'cartItems' => $cartItems,
            'subtotal' => $summary['subtotal'],
            'discount' => $summary['discount'],
            'shipping' => $summary['shipping'],
            'total' => $summary['total'],
            'voucher' => $summary['voucher'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
        ], [
            'quantity.required' => 'Jumlah produk wajib diisi.',
            'quantity.min' => 'Jumlah produk minimal 1.',
            'quantity.max' => 'Jumlah produk maksimal 99.',
        ]);

        $product = Product::query()->where('is_active', true)->findOrFail($validated['product_id']);
        $cartItem = $request->user()->cartItems()->firstOrNew(['product_id' => $product->id]);
        $newQuantity = ($cartItem->exists ? $cartItem->quantity : 0) + $validated['quantity'];

        if ($newQuantity > $product->stock) {
            return back()->withErrors(['quantity' => "Stok {$product->name} hanya {$product->stock}."]);
        }

        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.create')->with('success', 'Produk siap dibeli.');
        }

        return redirect()->route('cart.index')->with('success', 'Produk ditambahkan ke cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->ensureOwner($request, $cartItem);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        if ($validated['quantity'] > $cartItem->product->stock) {
            return back()->withErrors(['cart' => "Stok {$cartItem->product->name} hanya {$cartItem->product->stock}."]);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Jumlah produk diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->ensureOwner($request, $cartItem);
        $cartItem->delete();

        return back()->with('success', 'Produk dihapus dari cart.');
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $request->user()->cartItems()->delete();

        return back()->with('success', 'Cart dikosongkan.');
    }

    private function ensureOwner(Request $request, CartItem $cartItem): void
    {
        abort_unless($cartItem->user_id === $request->user()->id, 403);
    }
}
