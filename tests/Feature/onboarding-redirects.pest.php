<?php

use App\Models\Client;
use App\Models\Plan;
use App\Models\Subscription;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects to email verify when email is missing', function () {
    $client = Client::factory()->create([
        'email_verified_at' => null,
        'whatsapp_verified_at' => null,
        'onboarding_status' => 'pending_email',
    ]);

    actingAs($client, 'client');

    get('/dashboard')->assertRedirect(route('email.verify'));
});

it('redirects to whatsapp verify when email ok and whatsapp missing', function () {
    $client = Client::factory()->create([
        'email_verified_at' => now(),
        'whatsapp_verified_at' => null,
        'onboarding_status' => 'pending_whatsapp',
    ]);

    actingAs($client, 'client');

    get('/dashboard')->assertRedirect(route('whatsapp.verify'));
});

it('redirects to checkout when email and whatsapp ok but no subscription', function () {
    $client = Client::factory()->create([
        'email_verified_at' => now(),
        'whatsapp_verified_at' => now(),
        'onboarding_status' => 'pending_checkout',
    ]);

    actingAs($client, 'client');

    get('/dashboard')->assertRedirect(route('plans'));
});

it('allows dashboard when onboarding is active and subscription is active', function () {
    $client = Client::factory()->create([
        'email_verified_at' => now(),
        'whatsapp_verified_at' => now(),
        'onboarding_status' => 'active',
    ]);

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

    Subscription::create([
        'client_id' => $client->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'started_at' => now(),
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
        'next_renewal_at' => now()->addMonth(),
        'gateway' => 'mock',
    ]);

    actingAs($client, 'client');

    get('/dashboard')->assertOk();
});
