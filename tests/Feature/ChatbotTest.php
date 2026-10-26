<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatbotFeedback;
use App\Models\ChatbotMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.chatbot.api_key' => 'test-key',
            'services.chatbot.base_url' => 'https://ai.test/v1',
        ]);
    }

    public function test_answer_includes_cards_for_mentioned_products(): void
    {
        $category = $this->category();
        $headset = $this->product($category, ['name' => 'HyperX Cloud Alpha Headset', 'slug' => 'hyperx-cloud-alpha', 'sku' => 'HX-1', 'price' => 1299000, 'compare_at_price' => 1499000]);
        $this->product($category, ['name' => 'Logitech G Pro Mouse', 'slug' => 'logitech-g-pro', 'sku' => 'LG-1']);

        Http::fake(['ai.test/*' => Http::response($this->completion('Coba **HyperX Cloud Alpha Headset**, harganya Rp1.299.000.'))]);

        $response = $this->postJson('/chatbot', ['question' => 'Rekomendasi headset gaming dong'])
            ->assertOk()
            ->assertJsonPath('cached', false)
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', $headset->name)
            ->assertJsonPath('products.0.price', 'Rp1.299.000')
            ->assertJsonPath('products.0.discount_percent', 13)
            ->assertJsonPath('products.0.in_stock', true);

        $this->assertSame(ChatbotMessage::first()->id, $response->json('message_id'));
    }

    public function test_answer_streams_as_ndjson_when_provider_streams(): void
    {
        $this->product($this->category(), ['name' => 'Mini GT Porsche 911', 'slug' => 'mini-gt-porsche', 'sku' => 'MGT-1']);

        $sse = 'data: '.json_encode(['choices' => [['delta' => ['content' => 'Ada **Mini GT ']]]])."\n\n"
            .'data: '.json_encode(['choices' => [['delta' => ['content' => 'Porsche 911**.']]]])."\n\n"
            ."data: [DONE]\n\n";
        Http::fake(['ai.test/*' => Http::response($sse, 200, ['Content-Type' => 'text/event-stream'])]);

        $events = $this->streamedEvents(['question' => 'Ada diecast porsche yang ready?']);

        $this->assertSame(['delta', 'delta', 'done'], array_column($events, 'type'));
        $this->assertSame('Ada **Mini GT Porsche 911**.', $events[2]['answer']);
        $this->assertSame('Mini GT Porsche 911', $events[2]['products'][0]['name']);

        Http::assertSent(fn (Request $request) => $request['stream'] === true);
    }

    public function test_stream_falls_back_when_provider_returns_plain_json(): void
    {
        Http::fake(['ai.test/*' => Http::response($this->completion('Stok aman, silakan checkout.'))]);

        $events = $this->streamedEvents(['question' => 'Apakah stok keyboard masih tersedia?']);

        $this->assertSame(['delta', 'done'], array_column($events, 'type'));
        $this->assertSame('Stok aman, silakan checkout.', $events[0]['text']);
    }

    public function test_page_product_is_sent_as_context_and_contextual_answer_is_not_cached(): void
    {
        $product = $this->product($this->category(), ['name' => 'PlayStation 5 Slim', 'slug' => 'ps5-slim', 'sku' => 'PS5-1', 'stock' => 3]);

        Http::fake(['ai.test/*' => Http::response($this->completion('Ya, PlayStation 5 Slim masih ada 3 unit.'))]);

        $this->postJson('/chatbot', ['question' => 'Produk ini masih ada?', 'product' => $product->slug])
            ->assertOk()
            ->assertJsonPath('message_id', null);

        Http::assertSent(fn (Request $request) => collect($request['messages'])
            ->contains(fn ($m) => $m['role'] === 'system' && str_contains($m['content'], 'KONTEKS HALAMAN')
                && str_contains($m['content'], 'PlayStation 5 Slim')));

        $this->assertDatabaseCount('chatbot_messages', 0);
    }

    public function test_off_topic_question_is_blocked_without_calling_ai(): void
    {
        Http::fake();

        $this->postJson('/chatbot', ['question' => 'Berapa hasil dari 12 x 8?'])
            ->assertOk()
            ->assertJsonPath('blocked', true)
            ->assertJsonPath('products', []);

        Http::assertNothingSent();
    }

    public function test_provider_error_is_reported_as_stream_error_event(): void
    {
        Http::fake(['ai.test/*' => Http::response(['error' => 'down'], 503)]);

        $events = $this->streamedEvents(['question' => 'Rekomendasi mouse gaming ringan']);

        $this->assertSame('error', $events[0]['type']);
        $this->assertSame('Layanan chatbot sedang sibuk. Silakan coba lagi nanti.', $events[0]['error']);
    }

    public function test_visitor_can_rate_an_answer(): void
    {
        $this->postJson('/chatbot/feedback', [
            'rating' => -1,
            'question' => 'Harga PS5?',
            'answer' => 'Maaf, saya kurang tahu.',
        ])->assertCreated();

        $this->assertDatabaseHas('chatbot_feedback', ['rating' => -1, 'question' => 'Harga PS5?', 'user_id' => null]);

        $this->postJson('/chatbot/feedback', ['rating' => 5, 'question' => 'x', 'answer' => 'y'])
            ->assertJsonValidationErrors('rating');
    }

    public function test_admin_sees_feedback_summary(): void
    {
        ChatbotFeedback::create(['question' => 'Harga PS5?', 'answer' => 'Rp7.999.000', 'rating' => 1]);
        ChatbotFeedback::create(['question' => 'Ada voucher?', 'answer' => 'Tidak tahu.', 'rating' => -1]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/admin/chatbot')
            ->assertOk()
            ->assertSee('Kualitas Jawaban Chatbot')
            ->assertSee('50% puas')
            ->assertSee('Ada voucher?');
    }

    public function test_product_page_widget_uses_contextual_suggestions(): void
    {
        $product = $this->product($this->category(), ['name' => 'Fantech Gamepad', 'slug' => 'fantech-gamepad', 'sku' => 'FT-1']);

        $this->get('/products/'.$product->slug)
            ->assertOk()
            ->assertSee('data-page-product="fantech-gamepad"', false)
            ->assertSee('Apakah stok Fantech Gamepad masih tersedia?');
    }

    private function streamedEvents(array $payload): array
    {
        $content = $this->post('/chatbot', $payload, ['Accept' => 'application/x-ndjson'])
            ->assertOk()
            ->streamedContent();

        return array_map(
            fn (string $line) => json_decode($line, true),
            array_values(array_filter(explode("\n", $content)))
        );
    }

    private function completion(string $content): array
    {
        return ['choices' => [['message' => ['role' => 'assistant', 'content' => $content]]]];
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
}
