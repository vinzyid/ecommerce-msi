<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Services\EisReportService;
use App\Services\EisSyncService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EisController extends Controller
{
    public function __construct(private readonly EisReportService $report)
    {
    }

    public function index(Request $request): View
    {
        $months = (int) $request->query('months', 1);
        $months = array_key_exists($months, EisReportService::PERIODS) ? $months : 1;

        try {
            return view('admin.eis.index', ['eis' => $this->report->cachedOverview($months), 'unavailable' => false, 'months' => $months]);
        } catch (Throwable $e) {
            // Database kedua boleh bermasalah tanpa membuat seluruh panel admin ikut gagal.
            report($e);

            return view('admin.eis.index', ['eis' => null, 'unavailable' => true, 'months' => $months]);
        }
    }

    public function sync(Request $request, EisSyncService $sync): RedirectResponse
    {
        try {
            $result = $sync->run();
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Sinkronisasi gagal. Periksa koneksi database EIS.');
        }

        AuditLog::log('eis.synced', "Rekap EIS diperbarui dari {$result['source_orders']} pesanan.", $request, $result);

        return back()->with('success', "Rekap EIS diperbarui dari {$result['source_orders']} pesanan.");
    }

    public function show(Request $request, string $period): View
    {
        $month = CarbonImmutable::createFromFormat('!Y-m', $period);
        abort_unless($month && $month->format('Y-m') === $period, 404);

        try {
            $detail = $this->report->period($month);
        } catch (Throwable $e) {
            report($e);
            $detail = ['summary' => null, 'products' => collect()];
        }

        // Rekap dari database EIS, sedangkan rincian pesanan dari database transaksi.
        $orders = Order::query()
            ->with('user')
            ->whereBetween('ordered_at', [$month->startOfMonth(), $month->endOfMonth()])
            ->latest('ordered_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.eis.show', $detail + ['month' => $month, 'orders' => $orders]);
    }
}
