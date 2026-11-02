<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        ['status' => $status, 'q' => $search] = $this->filters($request);

        $orders = Order::with('user')
            ->withCount('items')
            ->tap(fn (Builder $query) => $this->applyFilters($query, $status, $search))
            ->latest('ordered_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'status', 'search'));
    }

    /**
     * Unduh daftar pesanan sebagai CSV untuk laporan penjualan.
     */
    public function export(Request $request): StreamedResponse
    {
        ['status' => $status, 'q' => $search] = $this->filters($request);

        return response()->streamDownload(function () use ($status, $search) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Nomor', 'Tanggal', 'Pelanggan', 'Status', 'Pembayaran', 'Subtotal', 'Diskon', 'Ongkir', 'Total']);

            Order::query()
                ->tap(fn (Builder $query) => $this->applyFilters($query, $status, $search))
                ->latest('ordered_at')
                ->lazy()
                ->each(fn (Order $order) => fputcsv($output, [
                    $order->order_number,
                    $order->ordered_at->format('Y-m-d H:i'),
                    $this->safeCell($order->customer_name),
                    $order->statusLabel(),
                    $order->paymentLabel(),
                    (int) $order->subtotal,
                    (int) $order->discount,
                    (int) $order->shipping_cost,
                    (int) $order->total,
                ]));

            fclose($output);
        }, 'pesanan-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{status: ?string, q: ?string}
     */
    private function filters(Request $request): array
    {
        return $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'q' => 'nullable|string|max:100',
        ]) + ['status' => null, 'q' => null];
    }

    /**
     * Cari berdasarkan nomor pesanan, nama penerima, atau email/username akun.
     */
    private function applyFilters(Builder $query, ?string $status, ?string $search): void
    {
        $query->when($status, fn ($query, $status) => $query->where('status', $status));

        if (blank($search)) {
            return;
        }

        $like = '%'.addcslashes(trim($search), '%_\\').'%';
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query->where(fn (Builder $query) => $query
            ->where('order_number', $operator, $like)
            ->orWhere('customer_name', $operator, $like)
            ->orWhereHas('user', fn (Builder $query) => $query
                ->where('email', $operator, $like)
                ->orWhere('username', $operator, $like)));
    }

    /**
     * Nama pelanggan yang diawali =, +, -, atau @ bisa dijalankan sebagai rumus oleh Excel.
     */
    private function safeCell(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
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

        // Alur hanya boleh maju; pembatalan selalu lewat Order::cancel() agar stok kembali.
        $progress = ['pending', 'processing', 'shipped', 'completed'];
        $target = array_search($validated['status'], $progress, true);
        if ($target !== false && $target < array_search($order->status, $progress, true)) {
            return back()->withErrors(['status' => 'Status pesanan tidak bisa dikembalikan ke tahap sebelumnya.']);
        }

        $oldStatus = $order->status;

        match ($validated['status']) {
            'cancelled' => $order->cancel(),
            'shipped' => $order->markShipped(),
            'completed' => $order->update(['status' => 'completed', 'completed_at' => $order->completed_at ?? now()]),
            default => $order->update(['status' => $validated['status']]),
        };

        AuditLog::log('order.status_changed', "Status pesanan {$order->order_number} diubah dari {$oldStatus} ke {$validated['status']}.", $request, [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'from' => $oldStatus,
            'to' => $validated['status'],
        ]);

        return back()->with('success', 'Status pesanan diperbarui.');
    }
}
