<?php

namespace App\Console\Commands;

use App\Services\EisSyncService;
use Illuminate\Console\Command;
use Throwable;

class EisSync extends Command
{
    protected $signature = 'eis:sync';

    protected $description = 'Rekap data transaksi dari database utama ke database EIS';

    public function handle(EisSyncService $sync): int
    {
        try {
            $result = $sync->run();
        } catch (Throwable $e) {
            report($e);
            $this->error('Sinkronisasi EIS gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Sinkronisasi EIS selesai.');
        $this->table(['Pesanan dibaca', 'Rekap bulanan', 'Rekap produk'], [[
            $result['source_orders'], $result['summary_rows'], $result['product_rows'],
        ]]);

        return self::SUCCESS;
    }
}
