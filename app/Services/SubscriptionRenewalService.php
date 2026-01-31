<?php

namespace App\Services;

use App\Models\PlanItem;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use Illuminate\Support\Facades\DB;

class SubscriptionRenewalService
{
    public function renewDue(): int
    {
        $dueSubscriptions = Subscription::query()
            ->whereIn('status', ['trialing', 'active'])
            ->whereNotNull('next_renewal_at')
            ->where('next_renewal_at', '<=', now())
            ->get();

        $processed = 0;

        foreach ($dueSubscriptions as $subscription) {
            DB::transaction(function () use ($subscription, &$processed) {
                $plan = $subscription->plan;
                $now = now();

                $subscription->forceFill([
                    'status' => 'active',
                    'current_period_start' => $now,
                    'current_period_end' => $now->copy()->addMonth(),
                    'next_renewal_at' => $now->copy()->addMonth(),
                ])->save();

                $planItem = SubscriptionItem::query()
                    ->where('subscription_id', $subscription->id)
                    ->where('item_type', 'plan')
                    ->first();

                if ($planItem) {
                    $planItem->forceFill([
                        'item_code' => $plan->code,
                        'description' => "Plano {$plan->name} mensal",
                        'unit_price_cents' => $plan->price_cents,
                        'meta_json' => [
                            'catalog_price_cents' => $plan->price_cents,
                            'discount_cents' => 0,
                        ],
                    ])->save();
                } else {
                    SubscriptionItem::create([
                        'subscription_id' => $subscription->id,
                        'item_type' => 'plan',
                        'item_code' => $plan->code,
                        'description' => "Plano {$plan->name} mensal",
                        'quantity' => 1,
                        'unit_price_cents' => $plan->price_cents,
                        'meta_json' => [
                            'catalog_price_cents' => $plan->price_cents,
                            'discount_cents' => 0,
                        ],
                    ]);
                }

                SubscriptionItem::query()
                    ->where('subscription_id', $subscription->id)
                    ->where('item_type', 'feature')
                    ->delete();

                $features = PlanItem::query()->where('plan_id', $plan->id)->get();

                foreach ($features as $item) {
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

                $processed++;
            });
        }

        return $processed;
    }

    public function renewSubscription(Subscription $subscription): void
    {
        $subscription->forceFill([
            'next_renewal_at' => now(),
        ])->save();

        $this->regenerateSnapshot($subscription, true);
    }

    public function regenerateSnapshot(Subscription $subscription, bool $useCurrentCatalogPrice): void
    {
        $plan = $subscription->plan;

        $planItem = SubscriptionItem::query()
            ->where('subscription_id', $subscription->id)
            ->where('item_type', 'plan')
            ->first();

        $price = $useCurrentCatalogPrice ? $plan->price_cents : ($planItem?->unit_price_cents ?? $plan->price_cents);

        if ($planItem) {
            $planItem->forceFill([
                'item_code' => $plan->code,
                'description' => "Plano {$plan->name} mensal",
                'unit_price_cents' => $price,
                'meta_json' => [
                    'catalog_price_cents' => $plan->price_cents,
                    'discount_cents' => 0,
                ],
            ])->save();
        } else {
            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'item_type' => 'plan',
                'item_code' => $plan->code,
                'description' => "Plano {$plan->name} mensal",
                'quantity' => 1,
                'unit_price_cents' => $price,
                'meta_json' => [
                    'catalog_price_cents' => $plan->price_cents,
                    'discount_cents' => 0,
                ],
            ]);
        }

        SubscriptionItem::query()
            ->where('subscription_id', $subscription->id)
            ->where('item_type', 'feature')
            ->whereNot('item_code', 'messages_per_day_override')
            ->delete();

        $features = PlanItem::query()->where('plan_id', $plan->id)->get();

        foreach ($features as $item) {
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
    }
}
