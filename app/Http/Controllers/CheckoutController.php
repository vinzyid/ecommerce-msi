<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $cartItems = $request->user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Cart masih kosong.']);
        }

        $subtotal = $cartItems->sum(fn (CartItem $item) => (int) $item->product->price * $item->quantity);
        $shippingCost = $this->shippingCost($subtotal);

        return view('checkout.create', compact('cartItems', 'subtotal', 'shippingCost'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'address' => 'required|string|min:10|max:1000',
            'notes' => 'nullable|string|max:500',
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
        ], [
            'customer_name.required' => 'Nama penerima wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'address.required' => 'Alamat wajib diisi.',
            'address.min' => 'Alamat minimal 10 karakter.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        $user = $request->user();
        $cartItems = $user->cartItems()->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Cart masih kosong.']);
        }

        $order = DB::transaction(function () use ($cartItems, $user, $validated) {
            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);

                if (! $product || ! $product->is_active || $item->quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok untuk salah satu produk berubah. Periksa cart Anda.',
                    ]);
                }

                $subtotal += (int) $product->price * $item->quantity;
            }

            $shippingCost = $this->shippingCost($subtotal);
            $order = Order::query()->create([
                'order_number' => $this->orderNumber(),
                'user_id' => $user->id,
                'customer_name' => $validated['customer_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $shippingCost,
                'status' => 'pending',
                'ordered_at' => now(),
            ]);

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);
                $itemSubtotal = (int) $product->price * $item->quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $itemSubtotal,
                ]);

                $product->decrement('stock', $item->quantity);
            }

            $user->cartItems()->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat.');
    }

    private function shippingCost(int $subtotal): int
    {
        return $subtotal >= 300000 ? 0 : 15000;
    }

    private function orderNumber(): string
    {
        do {
            $number = 'ETL-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
