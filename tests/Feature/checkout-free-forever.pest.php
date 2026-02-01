<?php

use App\Livewire\CheckoutMock;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('creates active subscription without stripe when free forever coupon applies', function () {
    $client = Client::factory()->create(['whatsapp_verified_at' => now()]);

    $plan = Plan::create([
        'code' => 'start',
        'name' => 'Start',
        'price_cents' => 1000,
        'billing_period' => 'monthly',
        'is_active' => true,
        'trial_enabled' => true,
        'trial_days' => 14,
        'sort_order' => 1,
    ]);

    $coupon = Coupon::create([
        'code' => 'FREE100',
        'type' => 'percent',
        'value_int' => 100,
        'duration' => 'forever',
        'is_active' => true,
    ]);

    actingAs($client, 'client');

    Session::put('selected_plan_code', $plan->code);
    Session::put('selected_coupon_code', $coupon->code);

    Livewire::test(CheckoutMock::class)
        ->call('pay');

    $subscription = Subscription::query()->where('client_id', $client->id)->latest('started_at')->first();

    expect($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe('active')
        ->and($subscription->gateway)->toBe('complimentary')
        ->and($subscription->coupon_id)->toBe($coupon->id);
});

it('does not skip flow for 100% once coupon', function () {
    $client = Client::factory()->create(['whatsapp_verified_at' => now()]);

    $plan = Plan::create([
        'code' => 'intermediate',
        'name' => 'Intermediário',
        'price_cents' => 1500,
        'billing_period' => 'monthly',
        'is_active' => true,
        'trial_enabled' => true,
        'trial_days' => 14,
        'sort_order' => 2,
    ]);

    $coupon = Coupon::create([
        'code' => 'FREEONCE',
        'type' => 'percent',
        'value_int' => 100,
        'duration' => 'once',
        'is_active' => true,
    ]);

    actingAs($client, 'client');

    Session::put('selected_plan_code', $plan->code);
    Session::put('selected_coupon_code', $coupon->code);

    Livewire::test(CheckoutMock::class)
        ->call('pay');

    $subscription = Subscription::query()->where('client_id', $client->id)->latest('started_at')->first();

    expect($subscription)->not->toBeNull()
        ->and($subscription->gateway)->not->toBe('complimentary');
});
