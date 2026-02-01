<?php

namespace App\Livewire;

use App\Models\Coupon;
use App\Models\PaymentOrder;
use App\Models\Plan;
use App\Models\PlanItem;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Services\CouponService;
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
            $couponService = app(CouponService::class);
            $finalAmount = $couponService->calculateFinalAmount($this->plan->price_cents, $this->coupon);
            $freeForever = $this->coupon && $couponService->isFreeForever($this->coupon, $finalAmount);

            if (! $freeForever) {
                PaymentOrder::create([
                    'client_id' => $client->id,
                    'plan_id' => $this->plan->id,
                    'amount_cents' => $this->finalPriceCents,
                    'status' => 'paid',
                    'provider' => 'stripe_mock',
                    'trial_ends_at' => $client->trial_used_at ? null : now()->addDays(30),
                ]);
            }

            $eligibleForTrial = $client->trial_used_at === null;
            $trialDays = (int) settings('commercial.trial_days_default', 30);
            $trialEnabled = (bool) settings('commercial.trial_enabled_default', true);
            $trialEndsAt = $eligibleForTrial && $trialEnabled ? now()->addDays($trialDays) : null;
            $status = $eligibleForTrial ? 'trialing' : 'active';
            $currentStart = $eligibleForTrial ? null : now();
            $currentEnd = $eligibleForTrial ? null : now()->addMonth();
            $nextRenewal = $trialEndsAt ?? now()->addMonth();
            $gateway = 'mock';

            if ($freeForever) {
                $status = 'active';
                $trialEndsAt = null;
                $currentStart = now();
                $currentEnd = now()->addMonth();
                $nextRenewal = now()->addMonth();
                $gateway = 'complimentary';
            }

            $subscription = Subscription::create([
                'client_id' => $client->id,
                'plan_id' => $this->plan->id,
                'status' => $status,
                'started_at' => now(),
                'trial_ends_at' => $trialEndsAt,
                'next_renewal_at' => $nextRenewal,
                'current_period_start' => $currentStart,
                'current_period_end' => $currentEnd,
                'gateway' => $gateway,
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
                app(CouponService::class)->applyToSubscription(
                    $this->coupon,
                    $subscription,
                    $client,
                    $this->plan->price_cents,
                    ['source' => $freeForever ? 'checkout_free_forever' : 'checkout']
                );
            }

            if ($eligibleForTrial && ! $freeForever) {
                $client->forceFill(['trial_used_at' => now()])->save();
            }

            if ($freeForever && $client->trial_used_at === null) {
                $client->forceFill(['trial_used_at' => now()])->save();
            }

            $client->forceFill([
                'payment_status' => 'paid',
                'onboarding_status' => 'active',
            ])->save();
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

        $clientId = Auth::guard('client')->id();
        $client = $clientId ? Auth::guard('client')->user() : null;

        try {
            if (! $client) {
                return;
            }
            $this->coupon = app(CouponService::class)->validateCoupon($this->couponCode, $client, $this->plan);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('couponCode', $e->validator->errors()->first('coupon') ?? 'Cupom inválido.');
        }
    }

    protected function computeFinalPrice(): void
    {
        if (! $this->plan) {
            $this->finalPriceCents = 0;
            return;
        }

        $base = $this->plan->price_cents;
        $discount = $this->coupon ? app(CouponService::class)->calculateDiscountCents($this->coupon, $base) : 0;

        $this->discountCents = $discount;
        $this->finalPriceCents = max(0, $base - $discount);
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
