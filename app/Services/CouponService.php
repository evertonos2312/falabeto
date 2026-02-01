<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function validateCoupon(string $code, Client $client, ?Plan $plan = null): Coupon
    {
        $normalized = strtoupper(trim($code));

        $coupon = Coupon::query()->where('code', $normalized)->first();

        if (! $coupon || ! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon' => 'O cupom é inválido ou está inativo.']);
        }

        if ($coupon->valid_from && now()->lt($coupon->valid_from)) {
            throw ValidationException::withMessages(['coupon' => 'O cupom ainda não está válido.']);
        }

        if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
            throw ValidationException::withMessages(['coupon' => 'O cupom expirou.']);
        }

        if ($coupon->max_redemptions !== null && $coupon->redeemed_count >= $coupon->max_redemptions) {
            throw ValidationException::withMessages(['coupon' => 'O cupom esgotou.']);
        }

        if (CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->where('client_id', $client->id)
            ->exists()
        ) {
            throw ValidationException::withMessages(['coupon' => 'Você já utilizou este cupom.']);
        }

        if ($coupon->first_purchase_only) {
            $hasPaid = Subscription::query()
                ->where('client_id', $client->id)
                ->whereIn('status', ['active', 'past_due', 'canceled'])
                ->exists();

            if ($hasPaid) {
                throw ValidationException::withMessages(['coupon' => 'O cupom é válido apenas para a primeira compra.']);
            }
        }

        $allowedPlans = $coupon->allowed_plan_codes ?? [];
        if ($plan && ! empty($allowedPlans) && ! in_array($plan->code, $allowedPlans, true)) {
            throw ValidationException::withMessages(['coupon' => 'O cupom não é válido para este plano.']);
        }

        return $coupon;
    }

    public function applyToSubscription(Coupon $coupon, Subscription $subscription, Client $client, int $appliedAmountCents = 0, array $meta = []): void
    {
        DB::transaction(function () use ($coupon, $subscription, $client, $meta, $appliedAmountCents) {
            $locked = Coupon::query()->where('id', $coupon->id)->lockForUpdate()->firstOrFail();

            if ($locked->max_redemptions !== null && $locked->redeemed_count >= $locked->max_redemptions) {
                throw ValidationException::withMessages(['coupon' => 'O cupom esgotou.']);
            }

            $subscription->forceFill([
                'coupon_id' => $locked->id,
                'coupon_snapshot_json' => [
                    'code' => $locked->code,
                    'name' => $locked->name,
                    'type' => $locked->type,
                    'value_int' => $locked->value_int,
                    'duration' => $locked->duration,
                    'duration_months' => $locked->duration_months,
                    'allowed_plan_codes' => $locked->allowed_plan_codes,
                    'first_purchase_only' => $locked->first_purchase_only,
                    'applied_amount_cents' => $appliedAmountCents,
                ],
            ])->save();

            CouponRedemption::create([
                'coupon_id' => $locked->id,
                'client_id' => $client->id,
                'subscription_id' => $subscription->id,
                'redeemed_at' => now(),
                'meta_json' => $meta,
            ]);

            $locked->increment('redeemed_count');
        });
    }

    public function calculateDiscountCents(Coupon $coupon, int $basePriceCents): int
    {
        if ($coupon->type === 'percent') {
            $discount = (int) round($basePriceCents * ($coupon->value_int / 100));
        } else {
            $discount = (int) $coupon->value_int;
        }

        return min($basePriceCents, max(0, $discount));
    }

    public function calculateFinalAmount(int $basePriceCents, ?Coupon $coupon): int
    {
        if (! $coupon) {
            return $basePriceCents;
        }

        $discount = $this->calculateDiscountCents($coupon, $basePriceCents);

        return max(0, $basePriceCents - $discount);
    }

    public function isFreeForever(Coupon $coupon, int $finalAmountCents): bool
    {
        return $coupon->duration === 'forever' && $finalAmountCents === 0;
    }
}
