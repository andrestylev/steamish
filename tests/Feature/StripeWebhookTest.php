<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Game;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['stripe.webhook_secret' => 'whsec_test_secret']);
        config(['stripe.secret' => 'sk_test_dummy']);
    }

    public function test_missing_signature_returns_400(): void
    {
        $response = $this->postJson('/stripe/webhook', ['test' => true]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Missing Stripe signature']);
    }

    public function test_invalid_signature_returns_400(): void
    {
        $response = $this->postJson('/stripe/webhook', ['test' => true], [
            'Stripe-Signature' => 't=123,v1=invalid_signature',
        ]);

        $response->assertStatus(400);
    }

    public function test_valid_checkout_session_completed_creates_purchase_records(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['price' => 29.99]);
        CartItem::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        $sessionId = 'cs_test_' . uniqid();
        $payload = json_encode([
            'id' => 'evt_test_' . uniqid(),
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $sessionId,
                    'object' => 'checkout.session',
                    'metadata' => ['user_id' => (string) $user->id],
                ],
            ],
            'created' => time(),
        ]);

        $time = time();
        $signedPayload = "{$time}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, 'whsec_test_secret');
        $header = "t={$time},v1={$signature}";

        // Mock the Stripe HTTP client to return fake line items
        $mockClient = new class implements ClientInterface {
            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
            {
                return [
                    json_encode([
                        'object' => 'list',
                        'data' => [['id' => 'li_1', 'price' => ['product' => 'prod_1']]],
                        'has_more' => false,
                    ]),
                    200,
                    [],
                ];
            }
        };
        \Stripe\ApiRequestor::setHttpClient($mockClient);

        $response = $this->postJson('/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $header,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'stripe_session_id' => $sessionId,
        ]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_valid_event_clears_cart_items(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['price' => 29.99]);
        CartItem::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        $sessionId = 'cs_test_' . uniqid();
        $payload = json_encode([
            'id' => 'evt_test_' . uniqid(),
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $sessionId,
                    'object' => 'checkout.session',
                    'metadata' => ['user_id' => (string) $user->id],
                ],
            ],
            'created' => time(),
        ]);

        $time = time();
        $signedPayload = "{$time}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, 'whsec_test_secret');
        $header = "t={$time},v1={$signature}";

        $mockClient = new class implements ClientInterface {
            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
            {
                return [
                    json_encode([
                        'object' => 'list',
                        'data' => [
                            ['id' => 'li_1', 'price' => ['product' => 'prod_1']],
                            ['id' => 'li_2', 'price' => ['product' => 'prod_2']],
                        ],
                        'has_more' => false,
                    ]),
                    200,
                    [],
                ];
            }
        };
        \Stripe\ApiRequestor::setHttpClient($mockClient);

        $response = $this->postJson('/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $header,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_idempotency_duplicate_session_id_does_not_duplicate_purchases(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create(['price' => 29.99]);
        CartItem::factory()->create(['user_id' => $user->id, 'game_id' => $game->id]);

        $sessionId = 'cs_test_dup_' . uniqid();

        Purchase::factory()->create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'stripe_session_id' => $sessionId,
            'amount_paid' => 29.99,
        ]);

        $payload = json_encode([
            'id' => 'evt_test_' . uniqid(),
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => $sessionId,
                    'object' => 'checkout.session',
                    'metadata' => ['user_id' => (string) $user->id],
                ],
            ],
            'created' => time(),
        ]);

        $time = time();
        $signedPayload = "{$time}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, 'whsec_test_secret');
        $header = "t={$time},v1={$signature}";

        $mockClient = new class implements ClientInterface {
            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
            {
                return [
                    json_encode([
                        'object' => 'list',
                        'data' => [['id' => 'li_1', 'price' => ['product' => 'prod_1']]],
                        'has_more' => false,
                    ]),
                    200,
                    [],
                ];
            }
        };
        \Stripe\ApiRequestor::setHttpClient($mockClient);

        $response = $this->postJson('/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $header,
        ]);

        $response->assertStatus(200);

        $purchases = Purchase::where('stripe_session_id', $sessionId)
            ->where('game_id', $game->id)
            ->get();
        $this->assertEquals(1, $purchases->count());
    }
}
