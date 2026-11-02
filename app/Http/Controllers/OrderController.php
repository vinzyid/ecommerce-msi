<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'q' => 'nullable|string|max:100',
        ]);
        $status = $filters['status'] ?? null;
        $search = trim((string) ($filters['q'] ?? ''));
        $user = $request->user();

        $orders = $user->orders()
            ->with('items.product:id,slug,image_url')
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

                $query->where(fn ($query) => $query
                    ->where('order_number', $operator, $like)
                    ->orWhereHas('items', fn ($query) => $query->where('product_name', $operator, $like)));
            })
            ->latest('ordered_at')
            ->paginate(10)
            ->withQueryString();

        $counts = $user->orders()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $reviews = $user->reviews()
            ->whereIn('product_id', $orders->getCollection()->flatMap->items->pluck('product_id')->filter()->unique())
            ->get()
            ->keyBy('product_id');

        return view('orders.index', compact('orders', 'status', 'search', 'counts', 'reviews'));
    }

    /**
     * Masukkan kembali barang pesanan ke keranjang, sesuai stok yang masih tersedia.
     */
    public function reorder(Request $request, Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $added = 0;
        $skipped = 0;

        foreach ($order->items()->with('product')->get() as $item) {
            $product = $item->product;

            if (! $product || ! $product->is_active || $product->stock < 1) {
                $skipped++;

                continue;
            }

            $cartItem = $request->user()->cartItems()->firstOrNew(['product_id' => $product->id]);
            $cartItem->quantity = min(($cartItem->exists ? $cartItem->quantity : 0) + $item->quantity, $product->stock, 99);
            $cartItem->save();
            $added++;
        }

        if ($added === 0) {
            return back()->withErrors(['reorder' => 'Barang pada pesanan ini sudah tidak tersedia.']);
        }

        $message = "{$added} barang ditambahkan ke keranjang.";

        return redirect()->route('cart.index')->with('success', $skipped > 0 ? "{$message} {$skipped} barang tidak tersedia." : $message);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);
        $order->load('items.product:id,slug,image_url');

        // Admin bisa membuka pesanan siapa pun, tapi hanya pembelinya yang bisa mengulas.
        $isBuyer = $order->user_id === $request->user()->id;
        $reviews = $isBuyer
            ? $request->user()->reviews()->whereIn('product_id', $order->items->pluck('product_id')->filter())->get()->keyBy('product_id')
            : collect();

        return view('orders.show', compact('order', 'isBuyer', 'reviews'));
    }

    public function invoice(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);
        $order->load('items');

        return view('orders.invoice', compact('order'));
    }

    /**
     * Fitur simulasi demo pembayaran Virtual Account & QRIS (sandbox).
     * Mengecoh status pesanan menjadi lunas (processing) tanpa perlu bayar sungguhan.
     */
    public function simulatePayment(Request $request, Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if ($order->status !== 'pending') {
            return back()->with('success', 'Pesanan ini sudah lunas atau diproses.');
        }

        if ($order->payment_method === 'qris' && $order->isQrisExpired()) {
            return back()->withErrors(['status' => 'Kode QR sudah kedaluwarsa.']);
        }

        $order->update([
            'status' => 'processing',
        ]);

        if ($order->payment_method === 'cod') {
            return back()->with('success', 'Pesanan COD dikonfirmasi dan sedang diproses penjual.');
        }

        return redirect()->route('orders.paid', $order);
    }

    /**
     * Halaman konfirmasi setelah pembayaran online berhasil.
     */
    public function paid(Request $request, Order $order): View|\Illuminate\Http\RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if ($order->payment_method === 'cod' || in_array($order->status, ['pending', 'cancelled'], true)) {
            return redirect()->route('orders.show', $order);
        }

        return view('orders.paid', compact('order'));
    }

    /**
     * Pembeli membatalkan pesanan yang belum dikirim.
     */
    public function cancel(Request $request, Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if (! $order->isCancellable()) {
            return back()->withErrors(['status' => 'Pesanan yang sudah dikirim atau selesai tidak bisa dibatalkan.']);
        }

        $order->cancel();

        return back()->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
    }

    /**
     * Simulasi kirim pesanan (penjual mengirim paket & menerbitkan nomor resi).
     */
    public function simulateShip(Request $request, Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if ($order->status !== 'processing') {
            return back()->withErrors(['status' => 'Pesanan harus dalam status Diproses sebelum dikirim.']);
        }

        $order->markShipped();

        return back()->with('success', "Paket berhasil diserahkan ke kurir {$order->courier}! Nomor resi: {$order->tracking_number} 🚚");
    }

    /**
     * Pembeli mengonfirmasi paket telah diterima dan menyelesaikan pesanan.
     */
    public function complete(Request $request, Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if ($order->status !== 'shipped') {
            return back()->withErrors(['status' => 'Pesanan belum dalam status Dikirim.']);
        }

        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Terima kasih! Pesanan telah selesai diterima. Sekarang Anda dapat menulis ulasan produk. ⭐');
    }
}
