<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('comment', 500)->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            $table->unique(['product_id', 'user_id']);
            $table->index(['product_id', 'is_approved']);
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('description', 150)->nullable();
            $table->string('type', 20)->default('percent');
            $table->unsignedInteger('value');
            $table->unsignedInteger('min_spend')->default(0);
            $table->unsignedInteger('max_discount')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->after('payment_method')->constrained()->nullOnDelete();
            $table->string('voucher_code', 30)->nullable()->after('voucher_id');
            $table->unsignedInteger('discount')->default(0)->after('subtotal');
            $table->string('province', 80)->nullable()->after('address');
            $table->string('city', 80)->nullable()->after('province');
            $table->string('district', 80)->nullable()->after('city');
            $table->string('postal_code', 10)->nullable()->after('district');
            $table->string('shipping_method', 20)->default('regular')->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropColumn([
                'voucher_code',
                'discount',
                'province',
                'city',
                'district',
                'postal_code',
                'shipping_method',
            ]);
        });

        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reviews');
    }
};
