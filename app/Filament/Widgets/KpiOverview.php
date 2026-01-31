<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\MessageLog;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\UsageDaily;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = now();
        $last7 = $now->copy()->subDays(7);
        $last30 = $now->copy()->subDays(30);

        $totalClients = Client::query()->count();
        $new7 = Client::query()->where('created_at', '>=', $last7)->count();
        $new30 = Client::query()->where('created_at', '>=', $last30)->count();

        $subscriptionsByStatus = Subscription::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $mrrCents = SubscriptionItem::query()
            ->where('item_type', 'plan')
            ->whereHas('subscription', function ($query) {
                $query->where('status', 'active');
            })
            ->sum('unit_price_cents');

        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $forecastCents = SubscriptionItem::query()
            ->where('item_type', 'plan')
            ->whereHas('subscription', function ($query) use ($monthStart, $monthEnd) {
                $query->whereIn('status', ['active', 'trialing'])
                    ->where(function ($trial) use ($monthStart, $monthEnd) {
                        $trial->whereNull('trial_ends_at')
                            ->orWhereBetween('trial_ends_at', [$monthStart, $monthEnd]);
                    });
            })
            ->sum('unit_price_cents');

        $messagesToday = UsageDaily::query()
            ->where('date', $now->toDateString())
            ->sum('messages_in');

        $logsLastDay = MessageLog::query()
            ->where('created_at', '>=', $now->copy()->subDay())
            ->count();

        $unknownActions = MessageLog::query()
            ->where('created_at', '>=', $now->copy()->subDay())
            ->whereNotNull('meta_json')
            ->whereRaw("json_extract(meta_json, '$.action') = 'unknown'")
            ->count();

        return [
            Stat::make('Total clientes', $totalClients),
            Stat::make('Novos 7 dias', $new7),
            Stat::make('Novos 30 dias', $new30),
            Stat::make('Assinaturas ativas', $subscriptionsByStatus['active'] ?? 0),
            Stat::make('Assinaturas trial', $subscriptionsByStatus['trialing'] ?? 0),
            Stat::make('MRR', $this->formatCents($mrrCents)),
            Stat::make('Receita prevista mês', $this->formatCents($forecastCents)),
            Stat::make('Mensagens hoje', $messagesToday),
            Stat::make('Webhook logs 24h', $logsLastDay),
            Stat::make('Ações unknown 24h', $unknownActions),
        ];
    }

    private function formatCents(int $cents): string
    {
        return 'R$ ' . number_format($cents / 100, 2, ',', '.');
    }
}
