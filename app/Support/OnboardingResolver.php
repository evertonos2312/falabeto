<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Subscription;

class OnboardingResolver
{
    public static function nextRouteFor(Client $client): string
    {
        if (! $client->email_verified_at) {
            return route('email.verify');
        }

        if (! $client->whatsapp_verified_at) {
            return route('whatsapp.verify');
        }

        if (! self::hasActiveSubscription($client)) {
            return route('plans');
        }

        return route('dashboard');
    }

    public static function hasActiveSubscription(Client $client): bool
    {
        $subscription = Subscription::query()
            ->where('client_id', $client->id)
            ->latest('started_at')
            ->first();

        if (! $subscription) {
            return false;
        }

        if (! in_array($subscription->status, ['trialing', 'active'], true)) {
            return false;
        }

        if ($subscription->current_period_end && $subscription->current_period_end->lt(now())) {
            return false;
        }

        return true;
    }
}
