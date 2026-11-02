<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CommerceTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_only_shows_active_products_and_supports_search(): void
    {
        $category = $this->category();
        $visible = $this->product($category, ['name' => 'Lampu Meja', 'slug' => 'lampu-meja', 'sku' => 'TES-001']);
        $this->product($category, ['name' => 'Produk Nonaktif', 'slug' => 'produk-nonaktif', 'sku' => 'TES-002', 'is_active' => false]);

        $this->get('/?q=Lampu')
            ->assertOk()
            ->assertSee($visible->name)
            ->assertDontSee('Produk Nonaktif');
    }

    public function test_customer_can_add_and_update_cart_within_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), ['stock' => 5]);

        $this->actingAs($user)->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect('/cart');

        $item = CartItem::firstOrFail();
        $this->actingAs($user)->patch("/cart/{$item->id}", ['quantity' => 4])->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 4]);
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), ['stock' => 2]);

        $this->actingAs($user)->from('/products/'.$product->slug)->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_cannot_change_another_customers_cart(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $product = $this->product($this->category());
        $item = CartItem::create(['user_id' => $owner->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($other)->patch("/cart/{$item->id}", ['quantity' => 2])->assertForbidden();
    }

    public function test_checkout_creates_order_snapshot_reduces_stock_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), ['price' => 100000, 'stock' => 8]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($user)->post('/checkout', $this->checkoutPayload([
            'notes' => 'Titip ke satpam',
        ]));

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('215000.00', $order->total);
        $this->assertSame('Jalan Merdeka nomor 10', $order->address);
        $this->assertSame('Yogyakarta', $order->city);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'subtotal' => 200000,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 6]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_applies_voucher_discount(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), ['price' => 100000, 'stock' => 8]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        \App\Models\Voucher::create([
            'code' => 'HEMAT10',
            'type' => 'percent',
            'value' => 10,
            'min_spend' => 100000,
            'max_discount' => 20000,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post('/voucher', ['code' => 'hemat10'])->assertRedirect();

        $this->actingAs($user)->post('/checkout', $this->checkoutPayload())->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(20000, (int) $order->discount);
        $this->assertSame('HEMAT10', $order->voucher_code);
        // subtotal 200000 - diskon 20000 = 180000, ongkir gratis (>= 300000? tidak) => 180000 + 15000
        $this->assertSame('195000.00', $order->total);
    }

    public function test_expired_voucher_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), ['price' => 100000]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        \App\Models\Voucher::create([
            'code' => 'KADALUARSA',
            'type' => 'fixed',
            'value' => 10000,
            'min_spend' => 0,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($user)->from('/cart')->post('/voucher', ['code' => 'KADALUARSA'])
            ->assertSessionHasErrors('code');
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jalan Merdeka nomor 10',
            'province' => 'DI Yogyakarta',
            'city' => 'Yogyakarta',
            'district' => 'Gondokusuman',
            'postal_code' => '55221',
            'shipping_method' => 'regular',
            'payment_method' => 'cod',
        ], $overrides);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->order($owner);

        $this->actingAs($other)->get("/orders/{$order->id}")->assertForbidden();
    }

    public function test_customer_can_manage_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());

        $this->actingAs($user)->post("/wishlist/{$product->slug}")->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)->get('/wishlist')
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Kosongkan wishlist');

        $this->actingAs($user)->delete("/wishlist/{$product->slug}")->assertRedirect();
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_guest_cannot_access_wishlist(): void
    {
        $this->get('/wishlist')->assertRedirect('/login');
    }

    public function test_customer_can_write_and_update_a_review(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());

        // Ulasan hanya boleh untuk produk yang pesanannya sudah selesai.
        $this->orderedProduct($user, $product, 'completed');

        $this->actingAs($user)->post("/products/{$product->slug}/reviews", [
            'rating' => 5,
            'comment' => 'Produk sangat bagus.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', ['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5]);

        // Ulasan kedua dari user yang sama mengubah nilai, bukan menambah baris
        $this->actingAs($user)->post("/products/{$product->slug}/reviews", [
            'rating' => 3,
            'comment' => 'Setelah dipakai, cukup.',
        ])->assertRedirect();

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', ['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 3]);
    }

    public function test_checkout_accepts_qris_and_shows_demo_qr_code(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), ['price' => 100000, 'stock' => 8]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/checkout', $this->checkoutPayload(['payment_method' => 'qris']))->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame('qris', $order->payment_method);
        $this->assertSame('QRIS', $order->paymentLabel());
        $this->assertSame('Belum dibayar', $order->paymentStatusLabel());

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Pembayaran QRIS')
            ->assertSee('Mode Demo / Sandbox')
            ->assertSee('<svg xmlns="http://www.w3.org/2000/svg"', false)
            ->assertDontSee('<?xml', false)
            ->assertDontSee('Bank Central Asia');
    }

    public function test_unknown_payment_method_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/checkout', $this->checkoutPayload(['payment_method' => 'gopay']))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_simulated_qris_payment_marks_order_as_paid(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $order->update(['payment_method' => 'qris']);

        $this->actingAs($user)->post(route('orders.simulatePayment', $order))->assertRedirect(route('orders.paid', $order));

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame('Lunas', $order->fresh()->paymentStatusLabel());

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertSee('Pembayaran QRIS Berhasil');

        $this->assertSame('Pembayaran QRIS berhasil diverifikasi.', $order->fresh()->trackingTimeline()[0]['desc']);
    }

    public function test_expired_qris_cannot_be_paid(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $order->update(['payment_method' => 'qris']);

        $this->travel(Order::QRIS_VALID_HOURS + 1)->hours();

        $this->actingAs($user)->post(route('orders.simulatePayment', $order))->assertSessionHasErrors('status');
        $this->assertSame('pending', $order->fresh()->status);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertSee('Kedaluwarsa')
            ->assertDontSee('<svg xmlns="http://www.w3.org/2000/svg"', false)
            ->assertDontSee('Bayar &amp; Tandai Lunas', false);
    }

    public function test_customer_can_open_invoice_with_order_details(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());
        $this->orderedProduct($user, $product, 'completed');
        $order = Order::firstOrFail();
        $order->update(['voucher_code' => 'HEMAT10', 'discount' => 5000, 'shipping_cost' => 0]);

        $this->actingAs($user)->get(route('orders.show', $order))->assertSee('Cetak Invoice');

        $this->actingAs($user)->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertSee('INVOICE')
            ->assertSee($order->order_number)
            ->assertSee('Pelanggan Uji')
            ->assertSee($product->name)
            ->assertSee($product->sku)
            ->assertSee('Diskon (HEMAT10)')
            ->assertSee('-Rp5.000')
            ->assertSee('Gratis')
            ->assertSee('Rp65.000')
            ->assertSee('Cetak / Simpan PDF');
    }

    public function test_invoice_is_only_available_to_owner_and_admin(): void
    {
        $owner = User::factory()->create();
        $order = $this->order($owner);

        $this->get(route('orders.invoice', $order))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get(route('orders.invoice', $order))->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('orders.invoice', $order))->assertOk();
    }

    #[DataProvider('paymentStatuses')]
    public function test_invoice_payment_status_label(string $method, string $status, string $label): void
    {
        $order = $this->order(User::factory()->create());
        $order->update(['payment_method' => $method, 'status' => $status]);

        $this->assertSame($label, $order->paymentStatusLabel());
    }

    public static function paymentStatuses(): array
    {
        return [
            ['bank_transfer', 'pending', 'Belum dibayar'],
            ['bank_transfer', 'shipped', 'Lunas'],
            ['qris', 'processing', 'Lunas'],
            ['qris', 'cancelled', 'Dibatalkan'],
            ['cod', 'pending', 'Bayar saat barang diterima'],
            ['cod', 'shipped', 'Bayar saat barang diterima'],
            ['cod', 'completed', 'Lunas'],
        ];
    }

    #[DataProvider('unfinishedOrderStatuses')]
    public function test_customer_cannot_review_before_order_is_completed(string $status): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());
        $this->orderedProduct($user, $product, $status);

        $this->actingAs($user)->post("/products/{$product->slug}/reviews", ['rating' => 5])
            ->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 0);
        $this->actingAs($user)->get('/products/'.$product->slug)->assertDontSee('Kirim ulasan');
    }

    public static function unfinishedOrderStatuses(): array
    {
        return [['pending'], ['processing'], ['shipped'], ['cancelled']];
    }

    public function test_customer_sees_review_form_after_order_is_completed(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());
        $this->orderedProduct($user, $product, 'completed');

        $this->actingAs($user)->get('/products/'.$product->slug)->assertSee('Kirim ulasan');
    }

    public function test_completed_order_of_another_product_does_not_allow_review(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $bought = $this->product($category);
        $other = $this->product($category, ['name' => 'Produk Lain', 'slug' => 'produk-lain', 'sku' => 'LAIN-001']);
        $this->orderedProduct($user, $bought, 'completed');

        $this->actingAs($user)->post("/products/{$other->slug}/reviews", ['rating' => 5])
            ->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_without_order_cannot_review(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category());

        $this->actingAs($user)->post("/products/{$product->slug}/reviews", ['rating' => 5])
            ->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_admin_cannot_review_without_buying_but_can_delete_reviews(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $product = $this->product($this->category());
        $review = $product->reviews()->create(['user_id' => $customer->id, 'rating' => 1, 'comment' => 'Spam.']);

        $this->actingAs($admin)->post("/products/{$product->slug}/reviews", ['rating' => 5])
            ->assertSessionHasErrors('review');

        $this->assertDatabaseMissing('reviews', ['user_id' => $admin->id]);

        $this->actingAs($admin)->delete("/reviews/{$review->id}")->assertRedirect();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_product_detail_shows_rating_and_information(): void
    {
        $user = User::factory()->create();
        $product = $this->product($this->category(), ['material' => 'Aluminium', 'color' => 'Silver']);
        $product->reviews()->create(['user_id' => $user->id, 'rating' => 4, 'comment' => 'Bagus.']);

        $this->get('/products/'.$product->slug)
            ->assertOk()
            ->assertSee('Aluminium')
            ->assertSee('Spesifikasi')
            ->assertSee('4,0');
    }

    public function test_promo_page_lists_discounted_products(): void
    {
        $category = $this->category();
        $this->product($category, ['name' => 'Produk Promo', 'slug' => 'produk-promo', 'sku' => 'PRO-001', 'price' => 80000, 'compare_at_price' => 100000]);
        $this->product($category, ['name' => 'Produk Normal', 'slug' => 'produk-normal', 'sku' => 'PRO-002', 'price' => 50000]);

        $this->get('/promo')
            ->assertOk()
            ->assertSee('Produk Promo')
            ->assertDontSee('Produk Normal');
    }

    private function category(): Category
    {
        return Category::create([
            'name' => 'Uji Kategori',
            'slug' => 'uji-kategori',
            'is_active' => true,
        ]);
    }

    private function product(Category $category, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Produk Uji',
            'slug' => 'produk-uji',
            'sku' => 'UJI-001',
            'description' => 'Deskripsi produk untuk pengujian.',
            'price' => 50000,
            'stock' => 10,
            'is_active' => true,
            'is_featured' => false,
        ], $attributes));
    }

    private function orderedProduct(User $user, Product $product, string $status): void
    {
        $order = $this->order($user);
        $order->update(['status' => $status]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'quantity' => 1,
            'subtotal' => $product->price,
        ]);
    }

    private function order(User $user): Order
    {
        return Order::create([
            'order_number' => 'ETL-TEST-001',
            'user_id' => $user->id,
            'customer_name' => 'Pelanggan Uji',
            'phone' => '08123456789',
            'address' => 'Alamat pelanggan untuk pengujian',
            'payment_method' => 'cod',
            'subtotal' => 50000,
            'shipping_cost' => 15000,
            'total' => 65000,
            'status' => 'pending',
            'ordered_at' => now(),
        ]);
    }
}
