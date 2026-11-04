<?php

namespace App\Services;

use App\Models\Eis\MonthlySummary;
use App\Models\Eis\ProductMonthly;
use App\Models\Eis\SyncLog;
use App\Models\Order;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EisReportService
{
    public const PERIODS = [1 => '1 bulan', 3 => '3 bulan', 6 => '6 bulan', 12 => '12 bulan'];

    /** Di atas angka ini, pembatalan dianggap perlu perhatian pimpinan (%). */
    public const CANCEL_RATE_ALERT = 10.0;

    /** Penurunan pendapatan yang memicu peringatan (%). */
    public const REVENUE_DROP_ALERT = 10.0;

    public const LOW_STOCK = 5;

    public const CACHE_MINUTES = 5;

    private const CACHE_VERSION_KEY = 'eis.overview.version';

    /**
     * Laporan menjalankan belasan query ke dua database, jadi hasilnya disimpan sebentar
     * agar halaman tetap cepat saat koneksi ke database sedang lambat.
     */
    public function cachedOverview(int $months): array
    {
        return Cache::remember(
            'eis.overview.'.Cache::get(self::CACHE_VERSION_KEY, 0).'.'.$months.'.'.now()->format('Y-m'),
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->overview($months)
        );
    }

    /**
     * Dipanggil setelah sinkronisasi agar rekap terbaru langsung tampil.
     */
    public static function forgetCache(): void
    {
        Cache::forever(self::CACHE_VERSION_KEY, (string) microtime(true));
    }

    /**
     * Kinerja dibandingkan pada bulan penuh, sebab bulan berjalan belum selesai
     * dan akan selalu tampak turun bila dibandingkan dengan bulan sebelumnya.
     */
    public function overview(int $months): array
    {
        $end = CarbonImmutable::now()->startOfMonth()->subMonth();
        $start = $end->subMonths($months - 1);
        $previousEnd = $start->subMonth();
        $previousStart = $previousEnd->subMonths($months - 1);

        $current = $this->summaries($start, $end);
        $previous = $this->summaries($previousStart, $previousEnd);
        $topProducts = $this->topProducts($start, $end);
        $kpis = $this->kpis($this->totals($current), $this->totals($previous));

        return [
            'months' => $months,
            'range' => $this->rangeLabel($start, $end),
            'previousRange' => $this->rangeLabel($previousStart, $previousEnd),
            'kpis' => $kpis,
            'trend' => $this->trend(max($months, 6)),
            'topProducts' => $topProducts,
            'categories' => $this->categories($start, $end),
            'actions' => $this->actions($topProducts, $kpis),
            'lastSync' => SyncLog::query()->where('status', 'success')->latest('finished_at')->first(),
            'lastFailure' => SyncLog::query()->where('status', 'failed')->latest('id')->first(),
            'reconciliation' => $this->reconciliation(),
            'connections' => $this->connections(),
            'hasData' => MonthlySummary::query()->exists(),
        ];
    }

    /**
     * Rincian satu bulan: rekap dari database EIS, daftar pesanan dari database utama.
     */
    public function period(CarbonImmutable $month): array
    {
        $summary = MonthlySummary::query()->whereDate('period', $month->toDateString())->first();

        return [
            'summary' => $summary,
            'products' => ProductMonthly::query()
                ->whereDate('period', $month->toDateString())
                ->orderByDesc('sales')
                ->get(),
        ];
    }

    private function summaries(CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return MonthlySummary::query()
            ->whereBetween('period', [$start->toDateString(), $end->toDateString()])
            ->orderBy('period')
            ->get();
    }

    private function totals(Collection $rows): array
    {
        $revenue = (float) $rows->sum(fn (MonthlySummary $row) => (float) $row->revenue);
        $paid = (int) $rows->sum('paid_orders_count');
        $orders = (int) $rows->sum('orders_count');

        return [
            'hasData' => $orders > 0,
            'revenue' => $revenue,
            'paid' => $paid,
            'aov' => $paid > 0 ? $revenue / $paid : 0.0,
            'cancelRate' => $orders > 0 ? $rows->sum('cancelled_count') / $orders * 100 : 0.0,
            'newCustomers' => (int) $rows->sum('new_customers'),
        ];
    }

    private function kpis(array $now, array $before): array
    {
        return [
            $this->kpi('revenue', 'Pendapatan', self::shortRupiah($now['revenue']), $now, $before),
            $this->kpi('paid', 'Pesanan berjalan & selesai', number_format($now['paid'], 0, ',', '.'), $now, $before),
            $this->kpi('aov', 'Rata-rata nilai pesanan', self::shortRupiah($now['aov']), $now, $before),
            $this->kpi('cancelRate', 'Tingkat pembatalan', number_format($now['cancelRate'], 1, ',', '.').'%', $now, $before, lowerIsBetter: true),
            $this->kpi('newCustomers', 'Pelanggan baru', number_format($now['newCustomers'], 0, ',', '.'), $now, $before),
        ];
    }

    private function kpi(string $key, string $label, string $value, array $now, array $before, bool $lowerIsBetter = false): array
    {
        $kpi = [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'raw' => $now[$key],
            'direction' => null,
            'good' => null,
            'change' => null,
        ];

        if (! $before['hasData']) {
            return $kpi;
        }

        // Pembatalan sudah berupa persen, jadi selisihnya ditulis dalam poin.
        if ($key === 'cancelRate') {
            $delta = $now[$key] - $before[$key];
            $text = number_format(abs($delta), 1, ',', '.').' poin';
        } elseif ($before[$key] > 0) {
            $delta = ($now[$key] - $before[$key]) / $before[$key] * 100;
            $text = number_format(abs($delta), 1, ',', '.').'%';
        } else {
            return $kpi;
        }

        if (abs($delta) < 0.05) {
            return ['direction' => 'flat', 'change' => 'Tetap', 'good' => null] + $kpi;
        }

        return [
            'direction' => $delta > 0 ? 'up' : 'down',
            'change' => $text,
            'delta' => $delta,
            'good' => $lowerIsBetter ? $delta < 0 : $delta > 0,
        ] + $kpi;
    }

    /**
     * Beberapa bulan penuh terakhir ditambah bulan berjalan (ditandai belum selesai).
     */
    private function trend(int $months): array
    {
        $current = CarbonImmutable::now()->startOfMonth();
        $start = $current->subMonths($months);
        $rows = $this->summaries($start, $current)->keyBy(fn (MonthlySummary $row) => $row->period->format('Y-m'));

        return collect(range(0, $months))->map(function (int $offset) use ($start, $rows, $current) {
            $month = $start->addMonths($offset);
            $key = $month->format('Y-m');
            $row = $rows->get($key);
            $paid = (int) ($row?->paid_orders_count ?? 0);
            $revenue = (float) ($row?->revenue ?? 0);

            return [
                'key' => $key,
                'label' => $month->translatedFormat('M'),
                'fullLabel' => $month->translatedFormat('F Y'),
                'partial' => $month->equalTo($current),
                'revenue' => $revenue,
                'paid' => $paid,
                'aov' => $paid > 0 ? $revenue / $paid : 0.0,
                'cancelled' => (int) ($row?->cancelled_count ?? 0),
                'newCustomers' => (int) ($row?->new_customers ?? 0),
            ];
        })->all();
    }

    /**
     * Peringkat dari database EIS digabung dengan stok terkini dari database utama.
     * Keduanya database berbeda, jadi penggabungan dilakukan di aplikasi berdasarkan SKU.
     */
    private function topProducts(CarbonImmutable $start, CarbonImmutable $end, int $limit = 5): Collection
    {
        $rows = ProductMonthly::query()
            ->selectRaw('sku, MAX(product_name) as product_name, MAX(category_name) as category_name, SUM(quantity) as quantity, SUM(sales) as sales')
            ->whereBetween('period', [$start->toDateString(), $end->toDateString()])
            ->groupBy('sku')
            ->orderByDesc('sales')
            ->limit($limit)
            ->toBase()
            ->get();

        $stock = Product::query()->whereIn('sku', $rows->pluck('sku'))->pluck('stock', 'sku');

        return $rows->map(fn ($row) => [
            'sku' => $row->sku,
            'name' => $row->product_name,
            'category' => $row->category_name,
            'quantity' => (int) $row->quantity,
            'sales' => (float) $row->sales,
            'stock' => $stock->get($row->sku),
        ]);
    }

    private function categories(CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        $rows = ProductMonthly::query()
            ->selectRaw('category_name, SUM(sales) as sales')
            ->whereBetween('period', [$start->toDateString(), $end->toDateString()])
            ->groupBy('category_name')
            ->orderByDesc('sales')
            ->toBase()
            ->get();
        $total = max(1.0, (float) $rows->sum('sales'));

        return $rows->map(fn ($row) => [
            'name' => $row->category_name,
            'sales' => (float) $row->sales,
            'share' => (float) $row->sales / $total * 100,
        ]);
    }

    /**
     * Temuan yang perlu ditindaklanjuti, berdasarkan aturan sederhana yang bisa dijelaskan.
     */
    private function actions(Collection $topProducts, array $kpis): array
    {
        $actions = [];
        $kpi = collect($kpis)->keyBy('key');

        foreach ($topProducts as $product) {
            if ($product['stock'] !== null && $product['stock'] <= self::LOW_STOCK) {
                $actions[] = [
                    'level' => $product['stock'] === 0 ? 'critical' : 'warning',
                    'title' => $product['stock'] === 0
                        ? "Produk terlaris habis: {$product['name']}"
                        : "Stok produk terlaris menipis: {$product['name']}",
                    'detail' => "Tersisa {$product['stock']} unit. Pertimbangkan menambah stok.",
                    'url' => route('admin.products.index', ['q' => $product['name']]),
                    'cta' => 'Lihat produk',
                ];
            }
        }

        if ($kpi['cancelRate']['raw'] >= self::CANCEL_RATE_ALERT) {
            $actions[] = [
                'level' => 'warning',
                'title' => 'Tingkat pembatalan '.$kpi['cancelRate']['value'],
                'detail' => 'Melewati batas perhatian '.(int) self::CANCEL_RATE_ALERT.'%. Periksa alasan pesanan dibatalkan.',
                'url' => route('admin.orders.index', ['status' => 'cancelled']),
                'cta' => 'Lihat pesanan',
            ];
        }

        if (($kpi['revenue']['delta'] ?? 0) <= -self::REVENUE_DROP_ALERT) {
            $actions[] = [
                'level' => 'warning',
                'title' => 'Pendapatan turun '.$kpi['revenue']['change'],
                'detail' => 'Dibandingkan periode sebelumnya. Pertimbangkan promo pada produk terlaris.',
                'url' => route('admin.vouchers.index'),
                'cta' => 'Kelola voucher',
            ];
        }

        $pending = Order::query()->where('status', 'pending')->count();
        if ($pending > 0) {
            $actions[] = [
                'level' => 'info',
                'title' => "{$pending} pesanan menunggu pembayaran atau proses",
                'detail' => 'Data langsung dari database transaksi.',
                'url' => route('admin.orders.index', ['status' => 'pending']),
                'cta' => 'Lihat pesanan',
            ];
        }

        return $actions;
    }

    /**
     * Membandingkan total kedua database untuk memastikan rekap masih sesuai.
     */
    private function reconciliation(): array
    {
        $source = (float) Order::query()
            ->whereNotNull('ordered_at')
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        $warehouse = (float) MonthlySummary::query()->sum('revenue');

        return [
            'source' => $source,
            'warehouse' => $warehouse,
            'matches' => abs($source - $warehouse) < 0.01,
        ];
    }

    private function connections(): array
    {
        $main = DB::connection();
        $eis = DB::connection('eis');

        return [
            [
                'title' => 'Database 1, transaksi',
                'connection' => $main->getName(),
                'database' => $main->getDatabaseName(),
                'detail' => number_format(Order::query()->count(), 0, ',', '.').' pesanan, '
                    .number_format(Product::query()->count(), 0, ',', '.').' produk',
            ],
            [
                'title' => 'Database 2, rekap EIS',
                'connection' => $eis->getName(),
                'database' => $eis->getDatabaseName(),
                'detail' => number_format(MonthlySummary::query()->count(), 0, ',', '.').' rekap bulanan, '
                    .number_format(ProductMonthly::query()->count(), 0, ',', '.').' rekap produk',
            ],
        ];
    }

    private function rangeLabel(CarbonImmutable $start, CarbonImmutable $end): string
    {
        return $start->equalTo($end)
            ? $start->translatedFormat('F Y')
            : $start->translatedFormat('M Y').' sampai '.$end->translatedFormat('M Y');
    }

    public static function shortRupiah(float $value): string
    {
        return match (true) {
            $value >= 1_000_000_000 => 'Rp'.number_format($value / 1_000_000_000, 1, ',', '.').' M',
            $value >= 1_000_000 => 'Rp'.number_format($value / 1_000_000, 1, ',', '.').' jt',
            $value >= 1_000 => 'Rp'.number_format($value / 1_000, 0, ',', '.').' rb',
            default => 'Rp'.number_format($value, 0, ',', '.'),
        };
    }
}
