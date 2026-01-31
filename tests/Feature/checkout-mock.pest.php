<?php

use App\Livewire\CheckoutMock;
use App\Models\Client;
use App\Models\Plan;
use App\Models\PlanItem;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use Livewire\Livewire;

it('checkout mock creates payment order and marks paid', function () {
    $plan = Plan::create([
        'code' => 'start',
        'name' => 'Start',
        'price_cents' => 1000,
        'billing_period' => 'monthly',
        'is_active' => true,
    ]);

    PlanItem::create([
        'plan_id' => $plan->id,
        'item_code' => 'messages_per_day',
        'item_type' => 'int',
        'value_int' => 30,
    ]);

    $client = Client::factory()->create([
        'whatsapp_verified_at' => now(),
    ]);

    $this->actingAs($client, 'client');
    session(['selected_plan_code' => $plan->code]);

    Livewire::test(CheckoutMock::class)
        ->call('pay')
        ->assertRedirect(route('success'));

    $this->assertDatabaseHas('payment_orders', [
        'client_id' => $client->id,
        'plan_id' => $plan->id,
        'status' => 'paid',
    ]);

    $this->assertSame('paid', $client->fresh()->payment_status);

    $subscription = Subscription::query()->where('client_id', $client->id)->first();
    expect($subscription)->not->toBeNull();
    expect($subscription->status)->toBe('trialing');
    expect($subscription->trial_ends_at)->not->toBeNull();

    $this->assertDatabaseHas('subscription_items', [
        'subscription_id' => $subscription->id,
        'item_type' => 'plan',
        'item_code' => 'start',
        'unit_price_cents' => 1000,
    ]);

    $featureCount = SubscriptionItem::query()
        ->where('subscription_id', $subscription->id)
        ->where('item_type', 'feature')
        ->count();

    expect($featureCount)->toBe(1);
});
