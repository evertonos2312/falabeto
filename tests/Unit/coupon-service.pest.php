<?php

use App\Models\Client;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CouponService;
use Illuminate\Validation\ValidationException;

it('rejects inactive or expired or maxed coupons', function () {
    $client = Client::factory()->create();
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

    $service = app(CouponService::class);

    $inactive = Coupon::create([
        'code' => 'OFF1',
        'type' => 'percent',
        'value_int' => 10,
        'duration' => 'once',
        'is_active' => false,
    ]);

    expect(fn () => $service->validateCoupon($inactive->code, $client, $plan))
        ->toThrow(ValidationException::class);

    $expired = Coupon::create([
        'code' => 'OFF2',
        'type' => 'fixed',
        'value_int' => 100,
        'duration' => 'once',
        'is_active' => true,
        'valid_until' => now()->subDay(),
    ]);

    expect(fn () => $service->validateCoupon($expired->code, $client, $plan))
        ->toThrow(ValidationException::class);

    $maxed = Coupon::create([
        'code' => 'OFF3',
        'type' => 'percent',
        'value_int' => 10,
        'duration' => 'once',
        'is_active' => true,
        'max_redemptions' => 1,
        'redeemed_count' => 1,
    ]);

    expect(fn () => $service->validateCoupon($maxed->code, $client, $plan))
        ->toThrow(ValidationException::class);
});

it('applies coupon to subscription and creates redemption', function () {
    $client = Client::factory()->create();

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
        'code' => 'WELCOME',
        'type' => 'percent',
        'value_int' => 10,
        'duration' => 'once',
        'is_active' => true,
    ]);

    $subscription = Subscription::create([
        'client_id' => $client->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'started_at' => now(),
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
        'next_renewal_at' => now()->addMonth(),
        'gateway' => 'mock',
    ]);

    app(CouponService::class)->applyToSubscription($coupon, $subscription, $client, 1500, ['source' => 'test']);

    $subscription->refresh();
    $coupon->refresh();

    expect($subscription->coupon_id)->toBe($coupon->id)
        ->and($subscription->coupon_snapshot_json['code'])->toBe('WELCOME')
        ->and($coupon->redeemed_count)->toBe(1);

    $this->assertDatabaseHas('coupon_redemptions', [
        'coupon_id' => $coupon->id,
        'client_id' => $client->id,
        'subscription_id' => $subscription->id,
    ]);
});

it('calculates final amount and detects free forever', function () {
    $service = app(CouponService::class);

    $coupon = Coupon::create([
        'code' => 'FREE100',
        'type' => 'percent',
        'value_int' => 100,
        'duration' => 'forever',
        'is_active' => true,
    ]);

    $final = $service->calculateFinalAmount(1000, $coupon);

    expect($final)->toBe(0)
        ->and($service->isFreeForever($coupon, $final))->toBeTrue();

    $once = Coupon::create([
        'code' => 'FREEONCE',
        'type' => 'percent',
        'value_int' => 100,
        'duration' => 'once',
        'is_active' => true,
    ]);

    $finalOnce = $service->calculateFinalAmount(1000, $once);

    expect($finalOnce)->toBe(0)
        ->and($service->isFreeForever($once, $finalOnce))->toBeFalse();
});
