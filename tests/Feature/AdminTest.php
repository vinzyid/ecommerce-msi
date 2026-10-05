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

    public function test_customer_cannot_access_user_management(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_search_and_view_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'username' => 'admin']);
        $budi = User::factory()->create(['username' => 'budi']);
        User::factory()->create(['username' => 'sari']);

        $this->actingAs($admin)->get('/admin/users?q=budi')
            ->assertOk()
            ->assertSee('budi')
            ->assertDontSee('sari');

        $this->actingAs($admin)->get("/admin/users/{$budi->id}")
            ->assertOk()
            ->assertSee('budi');
    }

    public function test_admin_dashboard_shows_statistics(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('Produk per kategori')
            ->assertSee('Pendapatan bulanan')
            ->assertSee('Status pesanan')
            ->assertSee('Pesanan Terakhir');
    }

    public function test_admin_can_create_a_voucher(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/vouchers', [
            'code' => 'ujicoba20',
            'description' => 'Diskon uji coba',
            'type' => 'percent',
            'value' => 20,
            'min_spend' => 50000,
            'max_discount' => 30000,
            'is_active' => '1',
        ])->assertRedirect('/admin/vouchers');

        $this->assertDatabaseHas('vouchers', [
            'code' => 'UJICOBA20',
            'type' => 'percent',
            'value' => 20,
            'is_active' => true,
        ]);
    }

    public function test_customer_cannot_manage_vouchers(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/vouchers')
            ->assertForbidden();
    }

    public function test_admin_can_promote_customer_but_not_demote_self(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)->patch("/admin/users/{$customer->id}/role", ['is_admin' => '1'])
            ->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'is_admin' => true]);

        $this->actingAs($admin)->patch("/admin/users/{$admin->id}/role", ['is_admin' => '0'])
            ->assertSessionHasErrors('is_admin');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_admin' => true]);
    }
}
