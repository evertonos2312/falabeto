<?php

namespace App\Support;

use App\Models\Client;

class FeatureGate
{
    public static function getFeatureValue(Client $client, string $code, mixed $default = null): mixed
    {
        $subscription = $client->subscriptions()->latest('started_at')->first();

        if (! $subscription) {
            return $default;
        }

        $item = $subscription->items()
            ->where('item_type', 'feature')
            ->where('item_code', $code)
            ->first();

        if (! $item) {
            return $default;
        }

        return $item->meta_json['value'] ?? $default;
    }

    public static function hasFeature(Client $client, string $code): bool
    {
        return (bool) self::getFeatureValue($client, $code, false);
    }
}
