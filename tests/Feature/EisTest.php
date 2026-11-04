<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Eis\MonthlySummary;
use App\Models\Eis\ProductMonthly;
use App\Models\Eis\SyncLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EisTest extends TestCase
{
    use RefreshDatabase;

    private int $orderNumber = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 11, 15)->setTime(10, 0));
        $this->artisan('migrate', ['--database' => 'eis', '--path' => 'database/migrations/eis']);
    }

    public function test_tests_never_touch_the_real_eis_database(): void
    {
        $this->assertSame('sqlite', DB::connection('eis')->getDriverName());
        $this->assertSame(':memory:', DB::connection('eis')->getDatabaseName());
    }

    public function test_sync_builds_monthly_summary_from_main_database(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 3]);

        $this->order($user, $product, 2, 200000, 'completed', '2026-09-10');
        $this->order($user, $product, 1, 100000, 'pending', '2026-09-20');
        $this->order($user, $product, 5, 500000, 'cancelled', '2026-09-25');

        $this->artisan('eis:sync')->assertSuccessful();

        $september = MonthlySummary::query()->whereDate('period', '2026-09-01')->firstOrFail();
        $this->assertSame(300000.0, (float) $september->revenue);
        $this->assertSame(3, $september->orders_count);
        $this->assertSame(2, $september->paid_orders_count);
        $this->assertSame(1, $september->cancelled_count);
        $this->assertSame(3, $september->items_sold);
        $this->assertSame(1, $september->active_customers);

        // Produk dari pesanan yang dibatalkan tidak ikut dihitung.
        $row = ProductMonthly::query()->where('sku', $product->sku)->firstOrFail();
        $this->assertSame(3, $row->quantity);
        $this->assertSame('Uji Kategori', $row->category_name);
    }

    public function test_sync_fills_empty_months_and_can_run_repeatedly(): void
    {
        $this->order(User::factory()->create(), $this->product(), 1, 100000, 'completed', '2026-08-05');

        $this->artisan('eis:sync')->assertSuccessful();
        $this->artisan('eis:sync')->assertSuccessful();

        // Agustus sampai November, tanpa data ganda.
        $this->assertSame(4, MonthlySummary::query()->count());
        $this->assertSame(1, ProductMonthly::query()->count());
        $this->assertSame(0.0, (float) MonthlySummary::query()->whereDate('period', '2026-09-01')->value('revenue'));
        $this->assertSame(2, SyncLog::query()->where('status', 'success')->count());
    }

    public function test_sync_does_not_change_the_main_database(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 9]);
        $order = $this->order($user, $product, 1, 100000, 'completed', '2026-10-05');
        $before = $order->fresh()->updated_at;

        $this->artisan('eis:sync')->assertSuccessful();

        $this->assertSame(9, $product->fresh()->stock);
        $this->assertEquals($before, $order->fresh()->updated_at);
        $this->assertFalse(Schema::hasTable('eis_monthly_summaries'));
    }

    public function test_failed_sync_is_logged_and_keeps_old_data(): void
    {
        $this->order(User::factory()->create(), $this->product(), 1, 100000, 'completed', '2026-10-05');
        $this->artisan('eis:sync')->assertSuccessful();

        Schema::connection('eis')->drop('eis_product_monthly');

        $this->artisan('eis:sync')->assertFailed();

        $this->assertSame('failed', SyncLog::query()->latest('id')->value('status'));
        $this->assertSame(100000.0, (float) MonthlySummary::query()->whereDate('period', '2026-10-01')->value('revenue'));
    }

    public function test_dashboard_compares_full_months_and_ignores_running_month(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['name' => 'Produk Terlaris Uji', 'stock' => 2]);

        $this->order($user, $product, 1, 100000, 'completed', '2026-09-10');
        $this->order($user, $product, 1, 150000, 'completed', '2026-10-10');
        $this->order($user, $product, 9, 900000, 'completed', '2026-11-10');
        $this->artisan('eis:sync')->assertSuccessful();

        $this->actingAs($this->admin())->get('/admin/eis?months=1')
            ->assertOk()
            ->assertSee('Oktober 2026')
            ->assertSee('Rp150 rb')
            ->assertSee('Naik 50,0%')
            ->assertSee('Stok produk terlaris menipis: Produk Terlaris Uji')
            ->assertSee('Total kedua database sesuai');
    }

    public function test_dashboard_shows_no_comparison_when_previous_period_is_empty(): void
    {
        $this->order(User::factory()->create(), $this->product(), 1, 100000, 'completed', '2026-10-10');
        $this->artisan('eis:sync')->assertSuccessful();

        $this->actingAs($this->admin())->get('/admin/eis?months=1')
            ->assertOk()
            ->assertSee('Belum ada pembanding')
            ->assertDontSee('Naik ');
    }

    public function test_dashboard_warns_when_transactions_changed_after_sync(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->order($user, $product, 1, 100000, 'completed', '2026-10-10');
        $this->artisan('eis:sync')->assertSuccessful();

        $this->order($user, $product, 1, 100000, 'completed', '2026-10-11');

        $this->actingAs($this->admin())->get('/admin/eis')
            ->assertSee('Ada perubahan transaksi sejak sinkronisasi terakhir');
    }

    public function test_admin_can_sync_from_page_and_action_is_audited(): void
    {
        $this->order(User::factory()->create(), $this->product(), 1, 100000, 'completed', '2026-10-10');

        $this->actingAs($this->admin())->post('/admin/eis/sync')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, SyncLog::query()->where('status', 'success')->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'eis.synced']);
    }

    public function test_sync_refreshes_cached_page(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $admin = $this->admin();
        $this->order($user, $product, 1, 100000, 'completed', '2026-10-10');
        $this->artisan('eis:sync')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/eis')->assertSee('Rp100 rb');

        $this->order($user, $product, 1, 400000, 'completed', '2026-10-11');

        // Masih memakai laporan yang tersimpan sampai ada sinkronisasi baru.
        $this->actingAs($admin)->get('/admin/eis')->assertSee('Rp100 rb');

        $this->artisan('eis:sync')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/eis')->assertSee('Rp500 rb');
    }

    public function test_period_detail_combines_both_databases(): void
    {
        $order = $this->order(User::factory()->create(), $this->product(['name' => 'Produk Rincian']), 1, 100000, 'completed', '2026-10-10');
        $this->artisan('eis:sync')->assertSuccessful();

        $this->actingAs($this->admin())->get('/admin/eis/2026-10')
            ->assertOk()
            ->assertSee('Produk Rincian')
            ->assertSee($order->order_number);

        $this->actingAs($this->admin())->get('/admin/eis/2026-13')->assertNotFound();
    }

    public function test_page_stays_usable_when_second_database_is_not_ready(): void
    {
        Schema::connection('eis')->drop('eis_sync_logs');

        $this->actingAs($this->admin())->get('/admin/eis')
            ->assertOk()
            ->assertSee('Database EIS belum siap');
    }

    public function test_customer_and_guest_cannot_access_eis(): void
    {
        $this->get('/admin/eis')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/eis')->assertForbidden();
        $this->actingAs(User::factory()->create())->post('/admin/eis/sync')->assertForbidden();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function product(array $attributes = []): Product
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'uji-kategori'],
            ['name' => 'Uji Kategori', 'is_active' => true]
        );
        $n = Product::query()->count() + 1;

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => "Produk Uji {$n}",
            'slug' => "produk-uji-{$n}",
            'sku' => "UJI-{$n}",
            'description' => 'Deskripsi produk untuk pengujian.',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
            'is_featured' => false,
        ], $attributes));
    }

    private function order(User $user, Product $product, int $quantity, int $total, string $status, string $date): Order
    {
        $this->orderNumber++;

        $order = Order::create([
            'order_number' => 'EIS-TEST-'.$this->orderNumber,
            'user_id' => $user->id,
            'customer_name' => 'Pelanggan Uji',
            'phone' => '08123456789',
            'address' => 'Alamat pelanggan untuk pengujian',
            'payment_method' => 'cod',
            'subtotal' => $total,
            'shipping_cost' => 0,
            'total' => $total,
            'status' => $status,
            'ordered_at' => $date.' 10:00:00',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'price' => $total / $quantity,
            'quantity' => $quantity,
            'subtotal' => $total,
        ]);

        return $order;
    }
}
