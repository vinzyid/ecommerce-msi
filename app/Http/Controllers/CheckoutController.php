<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use App\Support\CartCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartCalculator $calculator)
    {
    }

    public function create(Request $request): View|RedirectResponse
    {
        $cartItems = $request->user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Cart masih kosong.']);
        }

        $shippingMethod = $request->session()->get('shipping_method', 'regular');
        $summary = $this->calculator->summarize(
            $request->user(),
            $request->session()->get('voucher_code'),
            $shippingMethod,
        );

        return view('checkout.create', [
            'cartItems' => $cartItems,
            'subtotal' => $summary['subtotal'],
            'discount' => $summary['discount'],
            'shippingCost' => $summary['shipping'],
            'total' => $summary['total'],
            'voucher' => $summary['voucher'],
            'shippingMethod' => $shippingMethod,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'address' => 'required|string|min:10|max:1000',
            'province' => 'required|string|max:80',
            'city' => 'required|string|max:80',
            'district' => 'required|string|max:80',
            'postal_code' => 'required|string|max:10',
            'shipping_method' => ['required', Rule::in(['regular', 'express'])],
            'notes' => 'nullable|string|max:500',
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
        ], [
            'customer_name.required' => 'Nama penerima wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'address.required' => 'Alamat wajib diisi.',
            'address.min' => 'Alamat minimal 10 karakter.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'district.required' => 'Kecamatan wajib diisi.',
            'postal_code.required' => 'Kode pos wajib diisi.',
            'shipping_method.required' => 'Metode pengiriman wajib dipilih.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        $user = $request->user();
        $cartItems = $user->cartItems()->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Cart masih kosong.']);
        }

        $voucherCode = $request->session()->get('voucher_code');

        $order = DB::transaction(function () use ($cartItems, $user, $validated, $voucherCode) {
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

            $voucher = $voucherCode
                ? Voucher::query()->where('code', $voucherCode)->lockForUpdate()->first()
                : null;

            $discount = 0;

            if ($voucher && $voucher->isUsable()) {
                $discount = $voucher->discountFor($subtotal);
            } else {
                $voucher = null;
            }

            $shippingCost = $this->calculator->shippingCost($subtotal, $discount, $validated['shipping_method']);

            $order = Order::query()->create([
                'order_number' => $this->orderNumber(),
                'user_id' => $user->id,
                'customer_name' => $validated['customer_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'province' => $validated['province'],
                'city' => $validated['city'],
                'district' => $validated['district'],
                'postal_code' => $validated['postal_code'],
                'shipping_method' => $validated['shipping_method'],
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'],
                'voucher_id' => $voucher?->id,
                'voucher_code' => $voucher?->code,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'total' => max(0, $subtotal - $discount) + $shippingCost,
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

            if ($voucher) {
                $voucher->increment('used_count');
            }

            $user->cartItems()->delete();

            return $order;
        });

        $request->session()->forget(['voucher_code', 'shipping_method']);

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat.');
    }

    private function orderNumber(): string
    {
        do {
            $number = 'ETL-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
