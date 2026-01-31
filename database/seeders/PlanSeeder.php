<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'start',
                'name' => 'Start',
                'price_cents' => 1000,
                'billing_period' => 'monthly',
                'is_active' => true,
                'trial_enabled' => true,
                'trial_days' => 30,
                'sort_order' => 1,
            ],
            [
                'code' => 'intermediate',
                'name' => 'Intermediário',
                'price_cents' => 1500,
                'billing_period' => 'monthly',
                'is_active' => true,
                'trial_enabled' => true,
                'trial_days' => 30,
                'sort_order' => 2,
            ],
            [
                'code' => 'premium',
                'name' => 'Premium',
                'price_cents' => 2000,
                'billing_period' => 'monthly',
                'is_active' => true,
                'trial_enabled' => true,
                'trial_days' => 30,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['code' => $plan['code']], $plan);
        }
    }
}
