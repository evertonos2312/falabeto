<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\Subscription;

class SubscriptionPolicy
{
    public function view(Client $client, Subscription $subscription): bool
    {
        return $subscription->client_id === $client->id;
    }

    public function cancel(Client $client, Subscription $subscription): bool
    {
        return $subscription->client_id === $client->id;
    }
}
