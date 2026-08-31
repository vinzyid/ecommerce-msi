<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jalan Merdeka nomor 10, Jakarta',
            'notes' => 'Titip ke satpam',
            'payment_method' => 'cod',
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('215000.00', $order->total);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'subtotal' => 200000,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 6]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->order($owner);

        $this->actingAs($other)->get("/orders/{$order->id}")->assertForbidden();
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
