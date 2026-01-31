<?php

namespace App\Services;

class StripeService
{
    public function createCheckoutSession(array $payload): string
    {
        // TODO: Integrate Stripe checkout session creation.
        return 'mock_session_id';
    }

    public function handleWebhook(string $payload, string $signature): void
    {
        // TODO: Validate webhook signature and process events.
    }
}
