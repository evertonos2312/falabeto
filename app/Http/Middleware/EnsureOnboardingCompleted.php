<?php

namespace App\Http\Middleware;

use App\Support\OnboardingResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = Auth::guard('client')->user();

        if (! $client) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $allowed = [
            'email.verify',
            'whatsapp.verify',
            'whatsapp.verify.post',
            'plans',
            'checkout.mock',
            'checkout.mock.post',
            'success',
            'logout',
        ];

        if ($routeName && in_array($routeName, $allowed, true)) {
            return $next($request);
        }

        $target = OnboardingResolver::nextRouteFor($client);

        if ($request->fullUrl() !== $target) {
            return redirect()->to($target);
        }

        return $next($request);
    }
}
