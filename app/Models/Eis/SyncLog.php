<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

/**
 * Riwayat proses pemindahan data dari database transaksi ke database EIS.
 */
class SyncLog extends Model
{
    protected $connection = 'eis';

    protected $table = 'eis_sync_logs';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'source_orders' => 'integer',
            'summary_rows' => 'integer',
            'product_rows' => 'integer',
        ];
    }
}
