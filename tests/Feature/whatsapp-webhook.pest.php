<?php

use App\Models\Client;
use App\Models\MessageLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\Transaction;
use App\Models\UsageDaily;
use App\Services\OpenAI\BetoNluService;
use App\Services\WhatsApp\FastPathInterpreter;

function seedSubscription(Client $client, int $messagesPerDay = 30): void
{
    $plan = Plan::create([
        'code' => 'start',
        'name' => 'Start',
        'price_cents' => 1000,
        'billing_period' => 'monthly',
        'is_active' => true,
    ]);

    $subscription = Subscription::create([
        'client_id' => $client->id,
        'plan_id' => $plan->id,
        'status' => 'trialing',
        'started_at' => now(),
        'trial_ends_at' => now()->addDays(30),
        'next_renewal_at' => now()->addDays(30),
        'gateway' => 'mock',
    ]);

    SubscriptionItem::create([
        'subscription_id' => $subscription->id,
        'item_type' => 'feature',
        'item_code' => 'messages_per_day',
        'description' => 'Mensagens por dia',
        'quantity' => 1,
        'unit_price_cents' => 0,
        'meta_json' => [
            'type' => 'int',
            'value' => $messagesPerDay,
        ],
    ]);
}

it('webhook creates transaction and logs', function () {
    config(['app.timezone' => 'America/Sao_Paulo']);
    putenv('WHATSAPP_WEBHOOK_SECRET=secret');
    putenv('LOG_MESSAGE_BODY=false');

    $client = Client::factory()->create([
        'phone_e164' => '+5511999999999',
        'whatsapp_verified_at' => now(),
    ]);

    seedSubscription($client, 30);

    $fakeFastPath = new class extends FastPathInterpreter {
        public function interpret(string $text, string $timezone): ?array
        {
            return null;
        }
    };

    $fakeNlu = new class extends BetoNluService {
        public function interpret(string $text, array $context): array
        {
            return [
                'action' => 'create_transaction',
                'confidence' => 0.9,
                'fields' => [
                    'type' => 'expense',
                    'amount' => 12,
                    'description' => 'mercado',
                    'category' => null,
                    'occurred_at' => now()->toIso8601String(),
                ],
                'question' => null,
            ];
        }
    };

    $this->app->instance(FastPathInterpreter::class, $fakeFastPath);
    $this->app->instance(BetoNluService::class, $fakeNlu);

    $response = $this->postJson('/api/webhooks/whatsapp', [
        'phone' => '+55 (11) 99999-9999',
        'text' => 'coloca 12 no mercado',
        'timestamp' => now()->timestamp,
    ], [
        'X-WEBHOOK-SECRET' => 'secret',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('transactions', [
        'client_id' => $client->id,
        'type' => 'expense',
        'amount_cents' => 1200,
    ]);

    expect(MessageLog::query()->count())->toBe(2);
    $this->assertDatabaseHas('message_logs', [
        'direction' => 'inbound',
        'body' => null,
    ]);
    $this->assertDatabaseHas('message_logs', [
        'direction' => 'outbound',
    ]);
});

it('rate limit blocks after limit', function () {
    putenv('WHATSAPP_WEBHOOK_SECRET=secret');

    $client = Client::factory()->create([
        'phone_e164' => '+5511988887777',
        'whatsapp_verified_at' => now(),
    ]);

    seedSubscription($client, 30);

    for ($i = 0; $i < 30; $i++) {
        $response = $this->postJson('/api/webhooks/whatsapp', [
            'phone' => '+5511988887777',
            'text' => 'gastei 1 cafe',
            'timestamp' => now()->timestamp,
        ], [
            'X-WEBHOOK-SECRET' => 'secret',
        ]);

        $response->assertOk();
    }

    $blocked = $this->postJson('/api/webhooks/whatsapp', [
        'phone' => '+5511988887777',
        'text' => 'gastei 1 cafe',
        'timestamp' => now()->timestamp,
    ], [
        'X-WEBHOOK-SECRET' => 'secret',
    ]);

    $blocked->assertStatus(429);

    $usage = UsageDaily::query()->where('client_id', $client->id)->first();
    expect($usage->messages_in)->toBe(31);
});

it('undo last action soft deletes transaction', function () {
    putenv('WHATSAPP_WEBHOOK_SECRET=secret');

    $client = Client::factory()->create([
        'phone_e164' => '+5511977776666',
        'whatsapp_verified_at' => now(),
    ]);

    seedSubscription($client, 30);

    $this->postJson('/api/webhooks/whatsapp', [
        'phone' => '+5511977776666',
        'text' => 'gastei 12 mercado',
        'timestamp' => now()->timestamp,
    ], [
        'X-WEBHOOK-SECRET' => 'secret',
    ])->assertOk();

    $transaction = Transaction::query()->where('client_id', $client->id)->first();
    expect($transaction)->not->toBeNull();

    $this->postJson('/api/webhooks/whatsapp', [
        'phone' => '+5511977776666',
        'text' => 'desfaz',
        'timestamp' => now()->timestamp,
    ], [
        'X-WEBHOOK-SECRET' => 'secret',
    ])->assertOk();

    expect(Transaction::withTrashed()->find($transaction->id)->deleted_at)->not->toBeNull();
});

it('correct last debt updates record', function () {
    putenv('WHATSAPP_WEBHOOK_SECRET=secret');

    $client = Client::factory()->create([
        'phone_e164' => '+5511966665555',
        'whatsapp_verified_at' => now(),
    ]);

    seedSubscription($client, 30);

    $fakeNlu = new class extends BetoNluService {
        public function interpret(string $text, array $context): array
        {
            return [
                'action' => 'create_debt',
                'confidence' => 0.9,
                'fields' => [
                    'amount' => 150,
                    'creditor_name' => 'Lucas',
                    'due_date' => now()->addDays(5)->toDateString(),
                    'notes' => null,
                ],
                'question' => null,
            ];
        }
    };

    $this->app->instance(BetoNluService::class, $fakeNlu);

    $this->postJson('/api/webhooks/whatsapp', [
        'phone' => '+5511966665555',
        'text' => 'cadastra 150 pro Lucas',
        'timestamp' => now()->timestamp,
    ], [
        'X-WEBHOOK-SECRET' => 'secret',
    ])->assertOk();

    $debt = \App\Models\Debt::query()->where('client_id', $client->id)->first();
    expect($debt)->not->toBeNull();

    $this->postJson('/api/webhooks/whatsapp', [
        'phone' => '+5511966665555',
        'text' => 'corrigir pra 200 dia 5 lucas',
        'timestamp' => now()->timestamp,
    ], [
        'X-WEBHOOK-SECRET' => 'secret',
    ])->assertOk();

    $debt->refresh();
    expect($debt->amount_cents)->toBe(20000);
    expect($debt->creditor_name)->toBe('lucas');
    expect($debt->due_date->format('d'))->toBe('05');
});

it('undo does not affect web-created records', function () {
    putenv('WHATSAPP_WEBHOOK_SECRET=secret');

    $client = Client::factory()->create([
        'phone_e164' => '+5511955554444',
        'whatsapp_verified_at' => now(),
    ]);

    seedSubscription($client, 30);

    $transaction = Transaction::create([
        'client_id' => $client->id,
        'type' => 'expense',
        'amount_cents' => 1000,
        'occurred_at' => now(),
        'category' => 'teste',
        'description' => 'manual',
        'created_via' => 'web',
    ]);

    $this->postJson('/api/webhooks/whatsapp', [
        'phone' => '+5511955554444',
        'text' => 'desfaz',
        'timestamp' => now()->timestamp,
    ], [
        'X-WEBHOOK-SECRET' => 'secret',
    ])->assertOk();

    expect(Transaction::withTrashed()->find($transaction->id)->deleted_at)->toBeNull();
});
