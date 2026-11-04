<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

/**
 * Penjualan per produk per bulan, tersimpan di database kedua (koneksi "eis").
 */
class ProductMonthly extends Model
{
    protected $connection = 'eis';

    protected $table = 'eis_product_monthly';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'quantity' => 'integer',
            'sales' => 'decimal:2',
        ];
    }
}
