<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->string('badge', 30)->nullable();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->string('material', 100)->nullable();
            $table->string('color', 60)->nullable();
            $table->string('dimensions', 80)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'compare_at_price',
                'badge',
                'weight_grams',
                'material',
                'color',
                'dimensions',
            ]);
        });
    }
};
