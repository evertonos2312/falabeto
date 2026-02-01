<?php

use App\Livewire\CheckoutMock;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\PlanItem;
use App\Models\Subscription;
use Livewire\Livewire;

function seedPlan(): Plan
{
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

    return $plan;
}

it('coupon percent applies discount', function () {
    $plan = seedPlan();

    Coupon::create([
        'code' => 'OFF20',
        'type' => 'percent',
        'value_int' => 20,
        'duration' => 'once',
        'is_active' => true,
    ]);

    $client = Client::factory()->create(['whatsapp_verified_at' => now()]);
    $this->actingAs($client, 'client');
    session(['selected_plan_code' => $plan->code, 'selected_coupon_code' => 'OFF20']);

    Livewire::test(CheckoutMock::class)
        ->call('pay')
        ->assertRedirect(route('success'));

    $subscription = Subscription::query()->where('client_id', $client->id)->first();

    $this->assertDatabaseHas('subscription_items', [
        'subscription_id' => $subscription->id,
        'item_type' => 'plan',
        'unit_price_cents' => 800,
    ]);
});

it('coupon amount applies discount', function () {
    $plan = seedPlan();

    Coupon::create([
        'code' => 'OFF300',
        'type' => 'fixed',
        'value_int' => 300,
        'duration' => 'once',
        'is_active' => true,
    ]);

    $client = Client::factory()->create(['whatsapp_verified_at' => now()]);
    $this->actingAs($client, 'client');
    session(['selected_plan_code' => $plan->code, 'selected_coupon_code' => 'OFF300']);

    Livewire::test(CheckoutMock::class)
        ->call('pay')
        ->assertRedirect(route('success'));

    $subscription = Subscription::query()->where('client_id', $client->id)->first();

    $this->assertDatabaseHas('subscription_items', [
        'subscription_id' => $subscription->id,
        'item_type' => 'plan',
        'unit_price_cents' => 700,
    ]);
});

it('second trial is blocked', function () {
    $plan = seedPlan();

    $client = Client::factory()->create([
        'whatsapp_verified_at' => now(),
        'trial_used_at' => now()->subDays(1),
    ]);

    $this->actingAs($client, 'client');
    session(['selected_plan_code' => $plan->code]);

    Livewire::test(CheckoutMock::class)
        ->call('pay')
        ->assertRedirect(route('success'));

    $subscription = Subscription::query()->where('client_id', $client->id)->first();

    expect($subscription->status)->toBe('active');
    expect($subscription->trial_ends_at)->toBeNull();
});
