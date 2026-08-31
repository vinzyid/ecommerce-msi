<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name' => 'Rumah',
            'slug' => 'rumah',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Lampu Meja',
            'sku' => 'RMH-100',
            'description' => 'Lampu meja untuk membaca dan bekerja.',
            'price' => 175000,
            'stock' => 12,
            'is_active' => '1',
            'is_featured' => '1',
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'name' => 'Lampu Meja',
            'slug' => 'lampu-meja',
            'stock' => 12,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_order_status_but_cannot_reopen_final_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'ETL-TEST-002',
            'user_id' => $customer->id,
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

        $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);

        $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", ['status' => 'processing'])
            ->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }
}
