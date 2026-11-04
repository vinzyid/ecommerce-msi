<?php

namespace App\Services;

use App\Models\Eis\MonthlySummary;
use App\Models\Eis\ProductMonthly;
use App\Models\Eis\SyncLog;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Throwable;

class EisSyncService
{
    /**
     * Baca data transaksi dari database utama, rekap per bulan, lalu tulis ulang
     * seluruh rekap ke database EIS dalam satu transaksi. Database utama hanya dibaca.
     *
     * @return array{source_orders: int, summary_rows: int, product_rows: int}
     *
     * @throws Throwable
     */
    public function run(): array
    {
        $log = SyncLog::query()->create(['started_at' => now()]);

        try {
            [$summaries, $products, $sourceOrders] = $this->extract();

            DB::connection('eis')->transaction(function () use ($summaries, $products) {
                ProductMonthly::query()->delete();
                MonthlySummary::query()->delete();

                foreach (array_chunk($summaries, 200) as $chunk) {
                    MonthlySummary::query()->insert($chunk);
                }
                foreach (array_chunk($products, 200) as $chunk) {
                    ProductMonthly::query()->insert($chunk);
                }
            });

            $result = [
                'source_orders' => $sourceOrders,
                'summary_rows' => count($summaries),
                'product_rows' => count($products),
            ];

            $log->update($result + ['status' => 'success', 'finished_at' => now()]);
            EisReportService::forgetCache();

            return $result;
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);

            throw $e;
        }
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>, 2: int}
     */
    private function extract(): array
    {
        $months = [];
        $customers = [];
        $products = [];
        $sourceOrders = 0;

        Order::query()
            ->with('items.product.category')
            ->whereNotNull('ordered_at')
            ->chunkById(200, function ($orders) use (&$months, &$customers, &$products, &$sourceOrders) {
                foreach ($orders as $order) {
                    $sourceOrders++;
                    $key = $order->ordered_at->format('Y-m');
                    $months[$key] ??= $this->emptyMonth();
                    $months[$key]['orders_count']++;

                    if ($order->status === 'cancelled') {
                        $months[$key]['cancelled_count']++;

                        continue;
                    }

                    $months[$key]['paid_orders_count']++;
                    $months[$key]['revenue'] += (float) $order->total;
                    $customers[$key][$order->user_id] = true;

                    foreach ($order->items as $item) {
                        $months[$key]['items_sold'] += $item->quantity;

                        $row = &$products[$key][$item->sku];
                        $row ??= [
                            'sku' => $item->sku,
                            'product_name' => $item->product_name,
                            'category_name' => $item->product?->category?->name ?? 'Tanpa kategori',
                            'quantity' => 0,
                            'sales' => 0.0,
                        ];
                        $row['quantity'] += $item->quantity;
                        $row['sales'] += (float) $item->subtotal;
                        unset($row);
                    }
                }
            });

        User::query()->where('is_admin', false)->whereNotNull('created_at')->pluck('created_at')
            ->each(function ($createdAt) use (&$months) {
                $key = $createdAt->format('Y-m');
                $months[$key] ??= $this->emptyMonth();
                $months[$key]['new_customers']++;
            });

        return [$this->summaryRows($months, $customers), $this->productRows($products), $sourceOrders];
    }

    private function emptyMonth(): array
    {
        return [
            'revenue' => 0.0,
            'orders_count' => 0,
            'paid_orders_count' => 0,
            'cancelled_count' => 0,
            'items_sold' => 0,
            'new_customers' => 0,
        ];
    }

    /**
     * Bulan tanpa transaksi tetap dibuat agar tren tidak melompat.
     */
    private function summaryRows(array $months, array $customers): array
    {
        if ($months === []) {
            return [];
        }

        $rows = [];
        $cursor = CarbonImmutable::createFromFormat('!Y-m', min(array_keys($months)));
        $last = now()->startOfMonth();

        while ($cursor->lessThanOrEqualTo($last)) {
            $key = $cursor->format('Y-m');
            $rows[] = ($months[$key] ?? $this->emptyMonth()) + [
                'period' => $cursor->toDateString(),
                'active_customers' => count($customers[$key] ?? []),
            ];
            $cursor = $cursor->addMonth();
        }

        return $rows;
    }

    private function productRows(array $products): array
    {
        $rows = [];

        foreach ($products as $key => $items) {
            foreach ($items as $item) {
                $rows[] = $item + ['period' => $key.'-01'];
            }
        }

        return $rows;
    }
}
