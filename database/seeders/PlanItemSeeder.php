<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanItem;
use Illuminate\Database\Seeder;

class PlanItemSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'start' => [
                ['item_code' => 'messages_per_day', 'item_type' => 'int', 'value_int' => 30],
                ['item_code' => 'export_csv', 'item_type' => 'bool', 'value_bool' => false],
                ['item_code' => 'reports_level', 'item_type' => 'int', 'value_int' => 0],
                ['item_code' => 'scheduled_transactions_limit', 'item_type' => 'int', 'value_int' => 10],
            ],
            'intermediate' => [
                ['item_code' => 'messages_per_day', 'item_type' => 'int', 'value_int' => 80],
                ['item_code' => 'export_csv', 'item_type' => 'bool', 'value_bool' => true],
                ['item_code' => 'reports_level', 'item_type' => 'int', 'value_int' => 1],
                ['item_code' => 'scheduled_transactions_limit', 'item_type' => 'int', 'value_int' => 999999],
            ],
            'premium' => [
                ['item_code' => 'messages_per_day', 'item_type' => 'int', 'value_int' => 300],
                ['item_code' => 'export_csv', 'item_type' => 'bool', 'value_bool' => true],
                ['item_code' => 'reports_level', 'item_type' => 'int', 'value_int' => 2],
                ['item_code' => 'scheduled_transactions_limit', 'item_type' => 'int', 'value_int' => 999999],
            ],
        ];

        foreach ($definitions as $planCode => $items) {
            $plan = Plan::query()->where('code', $planCode)->first();

            if (! $plan) {
                continue;
            }

            foreach ($items as $item) {
                PlanItem::updateOrCreate(
                    ['plan_id' => $plan->id, 'item_code' => $item['item_code']],
                    array_merge($item, ['plan_id' => $plan->id])
                );
            }
        }
    }
}
