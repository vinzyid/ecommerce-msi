<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

/**
 * Rekap kinerja per bulan, tersimpan di database kedua (koneksi "eis").
 */
class MonthlySummary extends Model
{
    protected $connection = 'eis';

    protected $table = 'eis_monthly_summaries';

    protected $primaryKey = 'period';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'revenue' => 'decimal:2',
            'orders_count' => 'integer',
            'paid_orders_count' => 'integer',
            'cancelled_count' => 'integer',
            'items_sold' => 'integer',
            'active_customers' => 'integer',
            'new_customers' => 'integer',
        ];
    }
}
