<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\PaymentOrder;
use App\Models\Plan;
use App\Models\PlanItem;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CheckoutMock extends Component
{
    public ?Plan $plan = null;
    public ?Coupon $coupon = null;
    public ?string $couponCode = null;
    public int $finalPriceCents = 0;
    public int $discountCents = 0;

    public function mount(): void
    {
        $client = Auth::guard('client')->user();

        if (! $client?->whatsapp_verified_at) {
            $this->redirectRoute('whatsapp.verify');
            return;
        }

        $planCode = session('selected_plan_code');
        $this->couponCode = session('selected_coupon_code');

        if (! $planCode) {
            $this->redirectRoute('plans');
            return;
        }

        $this->plan = Plan::query()->where('code', $planCode)->first();

        if (! $this->plan) {
            $this->redirectRoute('plans');
            return;
        }

        $this->loadCoupon();
        $this->computeFinalPrice();
    }

    public function pay(): void
    {
        $client = Auth::guard('client')->user();

        if (! $client || ! $this->plan) {
            $this->redirectRoute('plans');
            return;
        }

        $this->loadCoupon();
        $this->computeFinalPrice();

        DB::transaction(function () use ($client) {
            PaymentOrder::create([
                'client_id' => $client->id,
                'plan_id' => $this->plan->id,
                'amount_cents' => $this->finalPriceCents,
                'status' => 'paid',
                'provider' => 'stripe_mock',
                'trial_ends_at' => $client->trial_used_at ? null : now()->addDays(30),
            ]);

            $eligibleForTrial = $client->trial_used_at === null;
            $trialEndsAt = $eligibleForTrial ? now()->addDays(30) : null;
            $status = $eligibleForTrial ? 'trialing' : 'active';

            $subscription = Subscription::create([
                'client_id' => $client->id,
                'plan_id' => $this->plan->id,
                'status' => $status,
                'started_at' => now(),
                'trial_ends_at' => $trialEndsAt,
                'next_renewal_at' => $trialEndsAt ?? now()->addMonth(),
                'current_period_start' => $eligibleForTrial ? null : now(),
                'current_period_end' => $eligibleForTrial ? null : now()->addMonth(),
                'coupon_id' => $this->coupon?->id,
                'gateway' => 'mock',
            ]);

            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'item_type' => 'plan',
                'item_code' => $this->plan->code,
                'description' => "Plano {$this->plan->name} mensal",
                'quantity' => 1,
                'unit_price_cents' => $this->finalPriceCents,
                'meta_json' => [
                    'catalog_price_cents' => $this->plan->price_cents,
                    'discount_cents' => $this->discountCents,
                ],
            ]);

            $planItems = PlanItem::query()->where('plan_id', $this->plan->id)->get();

            foreach ($planItems as $item) {
                SubscriptionItem::create([
                    'subscription_id' => $subscription->id,
                    'item_type' => 'feature',
                    'item_code' => $item->item_code,
                    'description' => "Feature {$item->item_code}",
                    'quantity' => 1,
                    'unit_price_cents' => 0,
                    'meta_json' => [
                        'type' => $item->item_type,
                        'value' => $item->item_type === 'int'
                            ? $item->value_int
                            : ($item->item_type === 'bool' ? $item->value_bool : $item->value_string),
                    ],
                ]);
            }

            if ($this->coupon) {
                CouponRedemption::create([
                    'coupon_id' => $this->coupon->id,
                    'client_id' => $client->id,
                    'subscription_id' => $subscription->id,
                    'redeemed_at' => now(),
                ]);

                $this->coupon->increment('redeemed_count');
            }

            if ($eligibleForTrial) {
                $client->forceFill(['trial_used_at' => now()])->save();
            }

            $client->forceFill(['payment_status' => 'paid'])->save();
        });

        session()->forget(['selected_plan_code', 'selected_coupon_code']);

        $this->redirectRoute('success');
    }

    protected function loadCoupon(): void
    {
        $this->coupon = null;
        $this->discountCents = 0;

        if (! $this->couponCode) {
            return;
        }

        $coupon = Coupon::query()
            ->where('code', strtoupper($this->couponCode))
            ->first();

        if (! $coupon || ! $coupon->is_active) {
            $this->addError('couponCode', 'Cupom inválido ou inativo.');
            return;
        }

        if ($coupon->valid_from && now()->lt($coupon->valid_from)) {
            $this->addError('couponCode', 'Cupom ainda não está válido.');
            return;
        }

        if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
            $this->addError('couponCode', 'Cupom expirado.');
            return;
        }

        if ($coupon->max_redemptions !== null && $coupon->redeemed_count >= $coupon->max_redemptions) {
            $this->addError('couponCode', 'Cupom esgotado.');
            return;
        }

        $clientId = Auth::guard('client')->id();

        if ($clientId && CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->where('client_id', $clientId)
            ->exists()
        ) {
            $this->addError('couponCode', 'Você já usou este cupom.');
            return;
        }

        $this->coupon = $coupon;
    }

    protected function computeFinalPrice(): void
    {
        if (! $this->plan) {
            $this->finalPriceCents = 0;
            return;
        }

        $base = $this->plan->price_cents;
        $discount = 0;

        if ($this->coupon) {
            if ($this->coupon->discount_type === 'percent') {
                $discount = (int) round($base * ($this->coupon->discount_value_int / 100));
            } else {
                $discount = $this->coupon->discount_value_int;
            }
        }

        $this->discountCents = min($base, max(0, $discount));
        $this->finalPriceCents = max(0, $base - $this->discountCents);
    }

    public function render()
    {
        return view('livewire.checkout-mock', [
            'finalPriceCents' => $this->finalPriceCents,
            'discountCents' => $this->discountCents,
        ])
            ->layout('layouts.app', ['title' => 'Checkout']);
    }
}
