<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dijalankan terpisah pada database kedua:
     * php artisan migrate --database=eis --path=database/migrations/eis
     */
    public function up(): void
    {
        Schema::create('eis_monthly_summaries', function (Blueprint $table) {
            $table->date('period')->primary();
            $table->decimal('revenue', 15, 2)->default(0);
            $table->unsignedInteger('orders_count')->default(0);
            $table->unsignedInteger('paid_orders_count')->default(0);
            $table->unsignedInteger('cancelled_count')->default(0);
            $table->unsignedInteger('items_sold')->default(0);
            $table->unsignedInteger('active_customers')->default(0);
            $table->unsignedInteger('new_customers')->default(0);
        });

        Schema::create('eis_product_monthly', function (Blueprint $table) {
            $table->id();
            $table->date('period')->index();
            $table->string('sku', 50);
            $table->string('product_name', 120);
            $table->string('category_name', 80);
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('sales', 15, 2)->default(0);

            $table->unique(['period', 'sku']);
        });

        Schema::create('eis_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 20)->default('running');
            $table->unsignedInteger('source_orders')->default(0);
            $table->unsignedInteger('summary_rows')->default(0);
            $table->unsignedInteger('product_rows')->default(0);
            $table->string('error', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eis_sync_logs');
        Schema::dropIfExists('eis_product_monthly');
        Schema::dropIfExists('eis_monthly_summaries');
    }
};
