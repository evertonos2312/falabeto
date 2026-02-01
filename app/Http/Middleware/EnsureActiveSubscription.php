<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = Auth::guard('client')->user();

        if (! $client) {
            return redirect()->route('login');
        }

        $subscription = $client->subscriptions()->latest('started_at')->first();

        if (! $subscription || ! in_array($subscription->status, ['trialing', 'active'], true)) {
            return redirect()->route('plans');
        }

        return $next($request);
    }
}
