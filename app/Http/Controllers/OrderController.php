<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest('ordered_at')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);
        $order->load('items');

        return view('orders.show', compact('order'));
    }

    /**
     * Fitur simulasi demo pembayaran Virtual Account (sandbox).
     * Mengecoh status pesanan menjadi lunas (processing) tanpa perlu bayar sungguhan.
     */
    public function simulatePayment(Request $request, Order $order): \Illuminate\Http\RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if ($order->status !== 'pending') {
            return back()->with('success', 'Pesanan ini sudah lunas atau diproses.');
        }

        $order->update([
            'status' => 'processing',
        ]);

        return back()->with('success', 'Pembayaran Virtual Account berhasil disimulasikan! Status pesanan kini LUNAS & sedang diproses penjual. 🎉');
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

        $courier = $order->shipping_method === 'express' ? 'JNE YES (Kilat)' : 'JNE Regular';
        $trackingNumber = 'VP-JNE-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

        $order->update([
            'status' => 'shipped',
            'courier' => $courier,
            'tracking_number' => $trackingNumber,
            'shipped_at' => now(),
        ]);

        return back()->with('success', "Paket berhasil diserahkan ke kurir {$courier}! Nomor resi: {$trackingNumber} 🚚");
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
