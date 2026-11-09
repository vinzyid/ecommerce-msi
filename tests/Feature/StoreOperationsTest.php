<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Voucher;
use App\Models\CartItem;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderReceipt;
use App\Notifications\OrderStatusChanged;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoReviewSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StoreOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cancel_restores_stock_and_voucher_once(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 3]);
        $voucher = Voucher::create(['code' => 'HEMAT', 'type' => 'fixed', 'value' => 1000, 'used_count' => 1, 'is_active' => true]);
        $order = $this->order($user, $product, ['voucher_id' => $voucher->id, 'voucher_code' => 'HEMAT']);

        $this->actingAs($user)->get(route('orders.show', $order))->assertSee('Batalkan Pesanan');
        $this->actingAs($user)->post(route('orders.cancel', $order))->assertSessionHas('success');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame(0, $voucher->fresh()->used_count);
        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertSee('Pesanan dibatalkan')
            ->assertDontSee('Batalkan Pesanan');

        // Membatalkan ulang tidak boleh menambah stok lagi.
        $this->actingAs($user)->post(route('orders.cancel', $order))->assertSessionHasErrors('status');
        $this->assertSame(5, $product->fresh()->stock);
    }

    #[DataProvider('finishedStatuses')]
    public function test_customer_cannot_cancel_after_shipping(string $status): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 3]);
        $order = $this->order($user, $product, ['status' => $status]);

        $this->actingAs($user)->post(route('orders.cancel', $order))->assertSessionHasErrors('status');

        $this->assertSame($status, $order->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public static function finishedStatuses(): array
    {
        return [['shipped'], ['completed']];
    }

    public function test_other_customer_cannot_cancel_order(): void
    {
        $order = $this->order(User::factory()->create(), $this->product());

        $this->actingAs(User::factory()->create())->post(route('orders.cancel', $order))->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancel_survives_deleted_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->order($user, $product);
        $product->delete();

        $this->actingAs($user)->post(route('orders.cancel', $order))->assertSessionHas('success');
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_admin_cancel_restores_stock(): void
    {
        $product = $this->product(['stock' => 3]);
        $order = $this->order(User::factory()->create(), $product, ['status' => 'shipped']);

        $this->actingAs($this->admin())->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_admin_cannot_move_order_backwards(): void
    {
        $order = $this->order(User::factory()->create(), $this->product(), ['status' => 'shipped']);

        $this->actingAs($this->admin())->patch(route('admin.orders.status', $order), ['status' => 'pending'])
            ->assertSessionHasErrors('status');
        $this->assertSame('shipped', $order->fresh()->status);
    }

    public function test_admin_shipping_issues_tracking_number_and_completion_is_timestamped(): void
    {
        $order = $this->order(User::factory()->create(), $this->product(), ['status' => 'processing']);

        $this->actingAs($this->admin())->patch(route('admin.orders.status', $order), ['status' => 'shipped']);
        $this->assertNotNull($order->fresh()->tracking_number);
        $this->assertNotNull($order->fresh()->shipped_at);

        $this->actingAs($this->admin())->patch(route('admin.orders.status', $order), ['status' => 'completed']);
        $this->assertNotNull($order->fresh()->completed_at);
    }

    public function test_cod_order_can_be_confirmed_in_demo_mode(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, $this->product());

        $this->actingAs($user)->get(route('orders.show', $order))->assertSee('Konfirmasi Pesanan COD (Simulasi)');
        $this->actingAs($user)->post(route('orders.simulatePayment', $order))
            ->assertSessionHas('success', 'Pesanan COD dikonfirmasi dan sedang diproses penjual.');

        $this->assertSame('processing', $order->fresh()->status);
    }

    public function test_checkout_sends_order_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        CartItem::create(['user_id' => $user->id, 'product_id' => $this->product()->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/checkout', [
            'customer_name' => 'Budi Santoso', 'phone' => '081234567890', 'address' => 'Jalan Merdeka nomor 10',
            'province' => 'DIY', 'city' => 'Yogyakarta', 'district' => 'Danurejan', 'postal_code' => '55212',
            'shipping_method' => 'regular', 'payment_method' => 'cod',
        ])->assertRedirect();

        Notification::assertSentTo($user, OrderPlaced::class);
    }

    public function test_user_can_reset_password_with_emailed_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'budi@example.test']);

        $this->get('/login')->assertSee('Lupa password?');
        $this->post('/lupa-password', ['email' => 'budi@example.test'])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))->assertOk();

            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ])->assertRedirect('/login');

            return true;
        });

        $this->assertTrue(Hash::check('PasswordBaru123', $user->fresh()->password));
    }

    public function test_forgot_password_response_does_not_reveal_registered_emails(): void
    {
        Notification::fake();

        $this->post('/lupa-password', ['email' => 'tidak-ada@example.test'])
            ->assertSessionHas('status', 'Jika email terdaftar, link reset password sudah kami kirim.');

        Notification::assertNothingSent();
    }

    public function test_reset_rejects_invalid_token_and_weak_password(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.test', 'password' => 'lama1234']);
        $old = $user->password;
        $payload = ['token' => 'salah', 'email' => $user->email, 'password' => 'PasswordBaru123', 'password_confirmation' => 'PasswordBaru123'];

        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
        $this->post('/reset-password', ['password' => '12345678', 'password_confirmation' => '12345678'] + $payload)->assertSessionHasErrors('password');

        $this->assertSame($old, $user->fresh()->password);
    }

    public function test_catalog_filters_by_price_range_and_keeps_filter_on_sort_links(): void
    {
        $category = $this->category();
        $this->product(['name' => 'Barang Murah', 'slug' => 'murah', 'sku' => 'A', 'price' => 50000], $category);
        $this->product(['name' => 'Barang Sedang', 'slug' => 'sedang', 'sku' => 'B', 'price' => 500000], $category);
        $this->product(['name' => 'Barang Mahal', 'slug' => 'mahal', 'sku' => 'C', 'price' => 5000000], $category);

        $this->get('/?q=Barang&min_price=100000&max_price=1000000')
            ->assertOk()
            ->assertSee('Barang Sedang')
            ->assertDontSee('Barang Murah')
            ->assertDontSee('Barang Mahal')
            ->assertSee('min_price=100000', false);

        $this->get('/?q=Barang&max_price=1')->assertOk()->assertSee('Produk tidak ditemukan')->assertSee('name="max_price"', false);
        $this->get('/?min_price=abc')->assertRedirect();
    }

    public function test_admin_can_upload_product_image_and_old_upload_is_removed(): void
    {
        $product = $this->product();
        $payload = fn (array $extra) => $extra + [
            'category_id' => $product->category_id, 'name' => $product->name, 'sku' => $product->sku,
            'description' => 'Deskripsi', 'price' => 1000, 'stock' => 1,
        ];

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), $payload(['image' => UploadedFile::fake()->image('a.jpg')]))->assertRedirect();
        $first = $product->fresh()->image_url;
        $this->assertStringStartsWith('/images/products/uploads/', $first);
        $this->assertFileExists(public_path(ltrim($first, '/')));

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), $payload(['image' => UploadedFile::fake()->image('b.png')]))->assertRedirect();
        $second = $product->fresh()->image_url;
        $this->assertFileDoesNotExist(public_path(ltrim($first, '/')));
        File::delete(public_path(ltrim($second, '/')));

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), $payload(['image' => UploadedFile::fake()->create('jahat.svg', 10, 'image/svg+xml')]))
            ->assertSessionHasErrors('image');
    }

    public function test_admin_can_export_orders_as_csv_with_safe_cells(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->order($user, $product, ['customer_name' => '=HYPERLINK("x")', 'order_number' => 'ETL-A']);
        $this->order($user, $product, ['status' => 'completed', 'order_number' => 'ETL-B']);

        $all = $this->actingAs($this->admin())->get(route('admin.orders.export'))->assertOk()->streamedContent();
        $this->assertStringContainsString('ETL-A', $all);
        $this->assertStringContainsString('ETL-B', $all);
        $this->assertStringContainsString("'=HYPERLINK", $all);

        $filtered = $this->actingAs($this->admin())->get(route('admin.orders.export', ['status' => 'completed']))->streamedContent();
        $this->assertStringNotContainsString('ETL-A', $filtered);
        $this->assertStringContainsString('ETL-B', $filtered);

        $this->actingAs($user)->get(route('admin.orders.export'))->assertForbidden();
    }

    public function test_policy_pages_open_and_footer_links_to_them(): void
    {
        foreach (['/syarat', '/privasi', '/retur'] as $path) {
            $this->get($path)->assertOk()->assertSee('proyek demo');
        }

        $this->get('/tentang')->assertSee(route('pages.terms'))->assertSee(route('pages.privacy'))->assertSee(route('pages.returns'));
    }

    public function test_responses_include_security_headers(): void
    {
        $this->get('/tentang')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_customer_gets_email_when_order_status_changes(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $order = $this->order($user, $this->product());

        $this->actingAs($user)->post(route('orders.simulatePayment', $order));
        Notification::assertSentTo($user, OrderStatusChanged::class, fn ($n) => str_contains($n->toMail($user)->subject, 'diproses'));

        Notification::fake();
        $this->actingAs($user)->post(route('orders.simulateShip', $order));
        Notification::assertSentTo($user, OrderStatusChanged::class, function ($n) use ($user, $order) {
            return str_contains(implode(' ', $n->toMail($user)->introLines), $order->fresh()->tracking_number);
        });

        Notification::fake();
        $order->update(['notes' => 'tanpa ubah status']);
        Notification::assertNothingSent();
    }

    public function test_customer_gets_email_when_order_is_cancelled(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('orders.cancel', $this->order($user, $this->product())));

        Notification::assertSentTo($user, OrderStatusChanged::class, fn ($n) => str_contains($n->toMail($user)->subject, 'dibatalkan'));
    }

    public function test_receipt_is_emailed_when_online_payment_succeeds(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $order = $this->order($user, $this->product(), ['payment_method' => 'qris']);

        $this->actingAs($user)->post(route('orders.simulatePayment', $order));

        Notification::assertSentTo($user, OrderReceipt::class, fn ($n) => str_contains($n->toMail($user)->subject, $order->order_number));
        Notification::assertNotSentTo($user, OrderStatusChanged::class);
    }

    public function test_cod_receipt_is_emailed_only_when_order_is_completed(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $order = $this->order($user, $this->product());

        $this->actingAs($user)->post(route('orders.simulatePayment', $order));
        $this->actingAs($user)->post(route('orders.simulateShip', $order));
        Notification::assertNotSentTo($user, OrderReceipt::class);

        $this->actingAs($user)->post(route('orders.complete', $order));
        Notification::assertSentTo($user, OrderReceipt::class);
    }

    public function test_customer_and_admin_can_email_invoice_but_others_cannot(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $order = $this->order($user, $this->product());

        $this->actingAs($user)->post(route('orders.invoice.email', $order))->assertSessionHas('success');
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post(route('orders.invoice.email', $order))->assertSessionHas('success');
        Notification::assertSentToTimes($user, OrderReceipt::class, 2);

        $this->actingAs(User::factory()->create())->post(route('orders.invoice.email', $order))->assertForbidden();
    }

    public function test_receipt_email_renders_order_details_and_logo(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $order = $this->order($user, $this->product(), ['payment_method' => 'qris', 'status' => 'processing']);

        $mail = (new OrderReceipt($order))->toMail($user);
        $html = (string) $mail->render();

        $this->assertCount(1, $mail->rawAttachments);
        $this->assertSame("Struk-{$order->order_number}.pdf", $mail->rawAttachments[0]['name']);
        $this->assertStringStartsWith('%PDF', $mail->rawAttachments[0]['data']);

        $this->assertStringContainsString($order->order_number, $html);
        $this->assertStringContainsString('Produk Uji', $html);
        $this->assertStringContainsString('Rp115.000', $html);
        $this->assertStringContainsString('Lunas', $html);
        $this->assertFileExists(public_path('images/logo-email.png'));
    }

    public function test_invoice_page_shows_logo_and_email_button(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, $this->product());

        $this->actingAs($user)->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertSee('VinzyPlay')
            ->assertSee(route('orders.invoice.email', $order));
    }

    public function test_voucher_per_user_limit_blocks_reuse_until_order_is_cancelled(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['price' => 100000, 'stock' => 10]);
        $voucher = Voucher::create(['code' => 'SEKALI', 'type' => 'fixed', 'value' => 5000, 'per_user_limit' => 1, 'is_active' => true]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/voucher', ['code' => 'SEKALI'])->assertSessionHasNoErrors();
        $order = $this->order($user, $product, ['voucher_id' => $voucher->id]);

        $this->actingAs($user)->post('/voucher', ['code' => 'SEKALI'])->assertSessionHasErrors('code');

        $other = User::factory()->create();
        CartItem::create(['user_id' => $other->id, 'product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($other)->post('/voucher', ['code' => 'SEKALI'])->assertSessionHasNoErrors();

        $order->cancel();
        $this->actingAs($user)->post('/voucher', ['code' => 'SEKALI'])->assertSessionHasNoErrors();
    }

    public function test_checkout_rejects_voucher_over_per_user_limit_and_charges_full_price(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['price' => 100000, 'stock' => 10]);
        $voucher = Voucher::create(['code' => 'SEKALI', 'type' => 'fixed', 'value' => 5000, 'per_user_limit' => 1, 'is_active' => true]);
        $this->order($user, $product, ['voucher_id' => $voucher->id, 'order_number' => 'ETL-LAMA']);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->withSession(['voucher_code' => 'SEKALI'])->post('/checkout', $this->checkoutData())->assertRedirect();

        $new = Order::query()->where('order_number', '!=', 'ETL-LAMA')->firstOrFail();
        $this->assertSame(0, $new->discount);
        $this->assertNull($new->voucher_id);
        $this->assertSame(0, $voucher->fresh()->used_count);
    }

    public function test_voucher_below_minimum_spend_is_not_consumed_at_checkout(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['price' => 50000, 'stock' => 10]);
        $voucher = Voucher::create(['code' => 'MIN', 'type' => 'fixed', 'value' => 5000, 'min_spend' => 1000000, 'is_active' => true]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->withSession(['voucher_code' => 'MIN'])->post('/checkout', $this->checkoutData())->assertRedirect();

        $this->assertNull(Order::firstOrFail()->voucher_code);
        $this->assertSame(0, $voucher->fresh()->used_count);
    }

    public function test_expired_vouchers_are_hidden_from_home_and_promo(): void
    {
        $this->product(['price' => 80000, 'compare_at_price' => 100000]);
        Voucher::create(['code' => 'MASIHBERLAKU', 'type' => 'fixed', 'value' => 1000, 'is_active' => true]);
        Voucher::create(['code' => 'SUDAHLEWAT', 'type' => 'fixed', 'value' => 1000, 'is_active' => true, 'expires_at' => now()->subDay()]);

        foreach (['/', '/promo'] as $path) {
            $this->get($path)->assertSee('MASIHBERLAKU')->assertDontSee('SUDAHLEWAT');
        }
    }

    public function test_admin_can_search_orders_and_export_the_same_result(): void
    {
        $budi = User::factory()->create(['username' => 'budi', 'email' => 'budi@example.test']);
        $sari = User::factory()->create(['username' => 'sari', 'email' => 'sari@example.test']);
        $product = $this->product();
        $this->order($budi, $product, ['order_number' => 'ETL-AAA', 'customer_name' => 'Budi Santoso']);
        $this->order($sari, $product, ['order_number' => 'ETL-BBB', 'customer_name' => 'Sari Dewi']);
        $admin = $this->admin();

        foreach (['q=ETL-AAA', 'q=budi+santoso', 'q=budi@example', 'q=budi'] as $query) {
            $this->actingAs($admin)->get("/admin/orders?{$query}")->assertSee('ETL-AAA')->assertDontSee('ETL-BBB');
        }
        $this->actingAs($admin)->get('/admin/orders?q=%25')->assertDontSee('ETL-AAA');
        $this->actingAs($admin)->get('/admin/orders?q=ETL&status=completed')->assertDontSee('ETL-AAA');

        $csv = $this->actingAs($admin)->get('/admin/orders/export?q=ETL-BBB')->streamedContent();
        $this->assertStringContainsString('ETL-BBB', $csv);
        $this->assertStringNotContainsString('ETL-AAA', $csv);
    }

    public function test_catalog_cache_reduces_queries_and_refreshes_on_changes(): void
    {
        $product = $this->product(['name' => 'Konsol Lama', 'is_featured' => true]);
        $this->get('/')->assertOk()->assertSee('Konsol Lama');

        DB::enableQueryLog();
        $this->get('/')->assertOk();
        $this->assertLessThanOrEqual(3, count(DB::getQueryLog()));
        DB::disableQueryLog();

        // Kartu unggulan harus ikut data terbaru, bukan cache lama.
        $product->update(['name' => 'Konsol Baru']);
        $this->get('/')->assertSee('Konsol Baru')->assertDontSee('Konsol Lama');

        $product->decrement('stock', 3);
        $category = Category::firstOrFail();
        $category->update(['name' => 'Kategori Baru']);
        $this->get('/')->assertSee('Kategori Baru');

        $review = $product->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 5, 'comment' => 'Mantap']);
        $this->assertSame(1, $this->viewFeatured()->reviews_count);
        $review->delete();
        $this->assertSame(0, $this->viewFeatured()->reviews_count);
    }

    public function test_product_page_does_not_requery_average_rating(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $product->reviews()->create(['user_id' => $user->id, 'rating' => 4, 'comment' => 'Bagus']);

        DB::enableQueryLog();
        $this->get('/products/'.$product->slug)->assertOk()->assertSee('4,0');
        $sql = collect(DB::getQueryLog())->pluck('query')->implode(' ');
        DB::disableQueryLog();

        $this->assertStringNotContainsString('avg("rating") as aggregate', $sql);
    }

    public function test_order_page_keeps_shipping_info_beside_order_details(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, $this->product());

        $dom = new \DOMDocument;
        @$dom->loadHTML($this->actingAs($user)->get(route('orders.show', $order))->getContent());
        $grid = (new \DOMXPath($dom))->query("//div[contains(@class, 'lg:grid-cols-[1fr_320px]')]")->item(0);
        $children = array_values(array_filter(iterator_to_array($grid->childNodes), fn ($node) => $node instanceof \DOMElement));

        $this->assertSame(['section', 'aside'], array_map(fn ($node) => $node->nodeName, $children));
        $this->assertStringContainsString('Informasi Pengiriman', $children[1]->textContent);
        $this->assertStringContainsString('Kunjungi Toko', $children[0]->textContent);
    }

    public function test_order_history_tabs_filter_and_count_only_own_orders(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->order($user, $product, ['order_number' => 'ETL-MENUNGGU']);
        $this->order($user, $product, ['order_number' => 'ETL-SELESAI', 'status' => 'completed']);
        $this->order(User::factory()->create(), $product, ['order_number' => 'ETL-ORANGLAIN']);

        $this->actingAs($user)->get('/orders')
            ->assertSee('ETL-MENUNGGU')->assertSee('ETL-SELESAI')->assertDontSee('ETL-ORANGLAIN')
            ->assertViewHas('counts', fn ($counts) => (string) $counts['pending'] === '1' && (string) $counts['completed'] === '1');

        $this->actingAs($user)->get('/orders?status=completed')->assertSee('ETL-SELESAI')->assertDontSee('ETL-MENUNGGU');
        $this->actingAs($user)->get('/orders?status=shipped')->assertSee('Pesanan tidak ditemukan');
        $this->actingAs($user)->get('/orders?status=salah')->assertRedirect();
    }

    public function test_order_history_search_finds_number_and_product_name_within_own_orders(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $keyboard = $this->product(['name' => 'Keyboard Neo', 'slug' => 'neo', 'sku' => 'K'], $category);
        $mouse = $this->product(['name' => 'Mouse Atk', 'slug' => 'atk', 'sku' => 'M'], $category);
        $this->order($user, $keyboard, ['order_number' => 'ETL-AAA']);
        $this->order($user, $mouse, ['order_number' => 'ETL-BBB']);
        $this->order(User::factory()->create(), $keyboard, ['order_number' => 'ETL-CCC']);

        $this->actingAs($user)->get('/orders?q=keyboard')->assertSee('ETL-AAA')->assertDontSee('ETL-BBB')->assertDontSee('ETL-CCC');
        $this->actingAs($user)->get('/orders?q=etl-bbb')->assertSee('ETL-BBB')->assertDontSee('ETL-AAA');
        $this->actingAs($user)->get('/orders?q=%25')->assertDontSee('ETL-AAA');
    }

    public function test_order_history_shows_actions_matching_status(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $get = fn (array $attributes) => tap($this->order($user, $product, $attributes), fn () => null);

        $order = $get(['payment_method' => 'qris']);
        $this->actingAs($user)->get('/orders')->assertSee('Bayar Sekarang')->assertDontSee('Pesanan Diterima')->assertDontSee('Beli Lagi');

        $order->update(['payment_method' => 'cod']);
        $this->actingAs($user)->get('/orders')->assertDontSee('Bayar Sekarang');

        $order->update(['status' => 'shipped']);
        $this->actingAs($user)->get('/orders')->assertSee('Pesanan Diterima');

        $order->update(['status' => 'completed']);
        $this->actingAs($user)->get('/orders')->assertSee('Beli Lagi')->assertSee('Beri Ulasan');

        $product->reviews()->create(['user_id' => $user->id, 'rating' => 5, 'comment' => 'Bagus']);
        $this->actingAs($user)->get('/orders')->assertSee('Beli Lagi')->assertSee('Ubah Ulasan')->assertDontSee('Beri Ulasan');
    }

    public function test_customer_reviews_several_items_at_once_from_order(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $first = $this->product(['name' => 'Barang Satu', 'slug' => 'satu', 'sku' => 'S1'], $category);
        $second = $this->product(['name' => 'Barang Dua', 'slug' => 'dua', 'sku' => 'S2'], $category);
        $third = $this->product(['name' => 'Barang Tiga', 'slug' => 'tiga', 'sku' => 'S3'], $category);
        $order = $this->order($user, $first, ['status' => 'completed']);
        foreach ([$second, $third] as $product) {
            $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku, 'price' => 1000, 'quantity' => 1, 'subtotal' => 1000]);
        }

        $this->actingAs($user)->get('/orders')->assertSee('Nilai Barang')->assertSee('name="items['.$first->id.'][rating]"', false);

        $this->actingAs($user)->from('/orders')->post(route('orders.reviews.store', $order), ['items' => [
            $first->id => ['rating' => 5, 'comment' => 'Mantap'],
            $second->id => ['rating' => 3],
            $third->id => ['comment' => 'Tanpa bintang'],
        ]])->assertRedirect('/orders')->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', ['product_id' => $first->id, 'user_id' => $user->id, 'rating' => 5, 'comment' => 'Mantap']);
        $this->assertDatabaseHas('reviews', ['product_id' => $second->id, 'user_id' => $user->id, 'rating' => 3]);
        $this->assertDatabaseMissing('reviews', ['product_id' => $third->id]);

        // Barang yang belum dinilai tetap memunculkan "Beri Ulasan"; rating lama bisa diubah.
        $this->actingAs($user)->get('/orders')->assertSee('Beri Ulasan');
        $this->actingAs($user)->post(route('orders.reviews.store', $order), ['items' => [$first->id => ['rating' => 2]]]);
        $this->assertDatabaseCount('reviews', 2);
        $this->assertDatabaseHas('reviews', ['product_id' => $first->id, 'rating' => 2]);
    }

    public function test_order_review_rejects_unfinished_orders_foreign_products_and_other_users(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $bought = $this->product(['slug' => 'dibeli', 'sku' => 'B'], $category);
        $foreign = $this->product(['slug' => 'lain', 'sku' => 'L'], $category);
        $pending = $this->order($user, $bought);
        $done = $this->order($user, $bought, ['status' => 'completed']);

        $this->actingAs($user)->post(route('orders.reviews.store', $pending), ['items' => [$bought->id => ['rating' => 5]]])->assertSessionHasErrors('review');
        $this->actingAs($user)->post(route('orders.reviews.store', $done), ['items' => [$foreign->id => ['rating' => 5]]])->assertSessionHasErrors('review');
        $this->actingAs($user)->post(route('orders.reviews.store', $done), ['items' => [$bought->id => ['rating' => 9]]])->assertSessionHasErrors('items.'.$bought->id.'.rating');
        $this->actingAs(User::factory()->create())->post(route('orders.reviews.store', $done), ['items' => [$bought->id => ['rating' => 5]]])->assertForbidden();
        $this->actingAs($this->admin())->post(route('orders.reviews.store', $done), ['items' => [$bought->id => ['rating' => 5]]])->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_order_detail_shows_store_logo_item_image_and_review_dialog(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['image_url' => '/images/products/contoh.jpg']);
        $order = $this->order($user, $product, ['status' => 'completed']);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertSee('Kunjungi Toko')
            ->assertSee('src="/images/products/contoh.jpg"', false)
            ->assertSee('review-order-'.$order->id)
            ->assertSee('Beri Ulasan')
            ->assertSee('Beli Lagi');

        $product->reviews()->create(['user_id' => $user->id, 'rating' => 4]);
        $this->actingAs($user)->get(route('orders.show', $order))->assertSee('Sudah dinilai')->assertSee('Ubah Ulasan');

        // Admin melihat pesanan, tapi tidak mendapat tombol ulasan atau beli lagi milik pembeli.
        $this->actingAs($this->admin())->get(route('orders.show', $order))
            ->assertOk()->assertDontSee('Ubah Ulasan')->assertDontSee('Beri Ulasan')->assertDontSee('Beli Lagi');
    }

    public function test_order_history_collapses_extra_items_and_handles_deleted_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->order($user, $product);
        foreach (['Dua', 'Tiga'] as $name) {
            $order->items()->create(['product_name' => "Barang {$name}", 'sku' => $name, 'price' => 1000, 'quantity' => 1, 'subtotal' => 1000]);
        }

        $this->actingAs($user)->get('/orders')->assertOk()->assertSee('+1 barang lainnya')->assertSee('Total 4 barang');

        $product->delete();
        $this->actingAs($user)->get('/orders')->assertOk()->assertSee('Produk Uji');
    }

    public function test_order_history_query_count_does_not_grow_with_orders(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->order($user, $product);

        $count = function () use ($user) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($user)->get('/orders')->assertOk();
            $total = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $total;
        };

        // Permintaan pertama mengisi cache footer, jadi tidak ikut dibandingkan.
        $count();
        $few = $count();
        foreach (range(1, 8) as $i) {
            $this->order($user, $product);
        }

        $this->assertSame($few, $count());
    }

    public function test_reorder_adds_available_items_within_stock(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $limited = $this->product(['name' => 'Stok Tipis', 'slug' => 'tipis', 'sku' => 'T', 'stock' => 3], $category);
        $empty = $this->product(['name' => 'Stok Habis', 'slug' => 'habis', 'sku' => 'H', 'stock' => 0], $category);
        $order = $this->order($user, $limited, ['status' => 'completed']);
        $order->items()->create(['product_id' => $empty->id, 'product_name' => $empty->name, 'sku' => 'H', 'price' => 1000, 'quantity' => 1, 'subtotal' => 1000]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $limited->id, 'quantity' => 2]);

        $this->actingAs($user)->post(route('orders.reorder', $order))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', '1 barang ditambahkan ke keranjang. 1 barang tidak tersedia.');

        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'product_id' => $limited->id, 'quantity' => 3]);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $empty->id]);
    }

    public function test_reorder_reports_when_nothing_is_available_and_blocks_other_users(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['stock' => 0]);
        $order = $this->order($user, $product, ['status' => 'cancelled']);

        $this->actingAs($user)->from('/orders')->post(route('orders.reorder', $order))->assertRedirect('/orders')->assertSessionHasErrors('reorder');
        $this->assertDatabaseCount('cart_items', 0);

        $this->actingAs(User::factory()->create())->post(route('orders.reorder', $order))->assertForbidden();
    }

    public function test_online_payment_goes_to_success_page(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, $this->product(), ['payment_method' => 'qris']);

        $this->actingAs($user)->get(route('orders.paid', $order))->assertRedirect(route('orders.show', $order));

        $this->actingAs($user)->post(route('orders.simulatePayment', $order))->assertRedirect(route('orders.paid', $order));

        $this->actingAs($user)->get(route('orders.paid', $order))
            ->assertOk()->assertSee('Pembayaran Berhasil')->assertSee($order->order_number)
            ->assertSee('Lihat Pesanan')->assertSee('Belanja Lagi');

        $this->actingAs(User::factory()->create())->get(route('orders.paid', $order))->assertForbidden();
    }

    public function test_cod_never_shows_payment_success_page(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, $this->product(), ['status' => 'processing']);

        $this->actingAs($user)->get(route('orders.paid', $order))->assertRedirect(route('orders.show', $order));
    }

    public function test_account_shows_order_status_shortcuts_with_counts(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->order($user, $product);
        $this->order($user, $product);
        $this->order(User::factory()->create(), $product);

        $this->actingAs($user)->get('/account')
            ->assertSee('Pesanan Saya')
            ->assertSee(route('orders.index', ['status' => 'shipped']), false)
            ->assertViewHas('orderCounts', fn ($counts) => (string) $counts['pending'] === '2');
    }

    public function test_product_page_shows_store_card_and_products_from_other_categories(): void
    {
        $category = $this->category();
        $other = Category::create(['name' => 'Lain', 'slug' => 'lain', 'is_active' => true]);
        $product = $this->product([], $category);
        $this->product(['name' => 'Barang Kategori Lain', 'slug' => 'lain-1', 'sku' => 'L1'], $other);
        $this->product(['name' => 'Barang Nonaktif', 'slug' => 'lain-2', 'sku' => 'L2', 'is_active' => false], $other);

        $this->get('/products/'.$product->slug)
            ->assertSee('Chat Toko')
            ->assertSee('Produk Lainnya dari Toko Ini')
            ->assertSee('Barang Kategori Lain')
            ->assertDontSee('Barang Nonaktif');
    }

    public function test_search_suggestions_return_only_active_matches(): void
    {
        $category = $this->category();
        $this->product(['name' => 'Keyboard Neo', 'slug' => 'neo', 'sku' => 'A', 'image_url' => '/images/products/a.jpg'], $category);
        $this->product(['name' => 'Keyboard Rahasia', 'slug' => 'rahasia', 'sku' => 'B', 'is_active' => false], $category);
        $this->product(['name' => 'Mouse Atk', 'slug' => 'atk', 'sku' => 'C', 'description' => 'Tetikus'], $category);

        $this->getJson('/search/suggest?q=KEYBOARD')
            ->assertOk()
            ->assertJsonCount(1, 'suggestions')
            ->assertJsonPath('suggestions.0.name', 'Keyboard Neo')
            ->assertJsonPath('suggestions.0.url', route('products.show', 'neo'))
            ->assertJsonPath('suggestions.0.image_url', '/images/products/a.jpg');

        $this->getJson('/search/suggest?q=k')->assertOk()->assertJsonCount(0, 'suggestions');
        $this->getJson('/search/suggest')->assertOk()->assertJsonCount(0, 'suggestions');
    }

    public function test_review_photo_is_saved_replaced_and_removed_with_review(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->order($user, $product, ['status' => 'completed']);
        $post = fn (string $file) => $this->actingAs($user)->post(route('orders.reviews.store', $order), ['items' => [
            $product->id => ['rating' => 5, 'photo' => UploadedFile::fake()->image($file)],
        ]]);

        $post('a.jpg')->assertSessionHas('success');
        $first = public_path(ltrim(Review::firstOrFail()->photo_path, '/'));
        $this->assertFileExists($first);
        $this->get('/products/'.$product->slug)->assertSee('Foto ulasan dari');

        $post('b.png')->assertSessionHas('success');
        $review = Review::firstOrFail();
        $second = public_path(ltrim($review->photo_path, '/'));
        $this->assertFileDoesNotExist($first);
        $this->assertFileExists($second);

        // Mengubah rating tanpa file baru tidak menghapus foto.
        $this->actingAs($user)->post(route('orders.reviews.store', $order), ['items' => [$product->id => ['rating' => 3]]]);
        $this->assertSame($review->photo_path, $review->fresh()->photo_path);

        $this->actingAs($user)->delete(route('reviews.destroy', $review));
        $this->assertFileDoesNotExist($second);
    }

    public function test_review_photo_rejects_non_images_on_both_forms(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->order($user, $product, ['status' => 'completed']);
        $bad = UploadedFile::fake()->create('jahat.svg', 10, 'image/svg+xml');

        $this->actingAs($user)->post(route('orders.reviews.store', $order), ['items' => [$product->id => ['rating' => 5, 'photo' => $bad]]])
            ->assertSessionHasErrors('items.'.$product->id.'.photo');
        $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 5, 'photo' => $bad])->assertSessionHasErrors('photo');
        $this->actingAs($user)->post(route('reviews.store', $product), ['rating' => 5, 'photo' => UploadedFile::fake()->image('besar.jpg')->size(3000)])
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_main_seeder_refuses_to_wipe_existing_orders(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, $this->product());

        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Seeder seharusnya menolak jalan.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Seeder dibatalkan', $e->getMessage());
        }

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_demo_review_seeder_only_touches_demo_accounts_and_keeps_orders(): void
    {
        $this->seed(DatabaseSeeder::class);
        $real = User::factory()->create(['username' => 'pembeli_asli']);
        $product = Product::query()->where('name', 'PlayStation 5 Console')->firstOrFail();
        $mine = $product->reviews()->create(['user_id' => $real->id, 'rating' => 2, 'comment' => 'Ulasan asli']);
        $orders = Order::count();

        $this->seed(DemoReviewSeeder::class);
        $this->seed(DemoReviewSeeder::class);

        $demo = Review::query()->where('comment', 'like', '%DualSense-nya juga enak%')->get();
        $this->assertCount(1, $demo);
        $this->assertSame(5, $demo->first()->rating);
        $this->assertSame('Ulasan asli', $mine->fresh()->comment);
        $this->assertSame($orders, Order::count());
    }

    private function viewFeatured(): Product
    {
        return $this->get('/')->viewData('featuredProducts')->first();
    }

    private function checkoutData(): array
    {
        return [
            'customer_name' => 'Budi Santoso', 'phone' => '081234567890', 'address' => 'Jalan Merdeka nomor 10',
            'province' => 'DIY', 'city' => 'Yogyakarta', 'district' => 'Danurejan', 'postal_code' => '55212',
            'shipping_method' => 'regular', 'payment_method' => 'cod',
        ];
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function category(): Category
    {
        return Category::firstOrCreate(['slug' => 'uji'], ['name' => 'Uji', 'is_active' => true]);
    }

    private function product(array $attributes = [], ?Category $category = null): Product
    {
        return Product::create($attributes + [
            'category_id' => ($category ?? $this->category())->id,
            'name' => 'Produk Uji', 'slug' => 'produk-uji', 'sku' => 'UJI-1', 'description' => 'Deskripsi.',
            'price' => 50000, 'stock' => 10, 'is_active' => true, 'is_featured' => false,
        ]);
    }

    private function order(User $user, Product $product, array $attributes = []): Order
    {
        $order = Order::create($attributes + [
            'order_number' => 'ETL-'.uniqid(), 'user_id' => $user->id, 'customer_name' => 'Pelanggan Uji',
            'phone' => '08123456789', 'address' => 'Alamat uji', 'payment_method' => 'cod',
            'subtotal' => 100000, 'shipping_cost' => 15000, 'total' => 115000, 'status' => 'pending', 'ordered_at' => now(),
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku,
            'price' => 50000, 'quantity' => 2, 'subtotal' => 100000,
        ]);

        return $order;
    }
}
