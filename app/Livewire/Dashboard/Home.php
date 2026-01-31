<?php

namespace App\Livewire\Dashboard;

use App\Models\Transaction;
use App\Support\FeatureGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Home extends Component
{
    public array $summary = [];
    public array $previousSummary = [];
    public array $topCategories = [];
    public int $reportsLevel = 0;

    public function mount(): void
    {
        $client = Auth::guard('client')->user();
        $this->reportsLevel = (int) FeatureGate::getFeatureValue($client, 'reports_level', 0);

        $now = now();
        $currentStart = $now->copy()->startOfMonth();
        $currentEnd = $now->copy()->endOfMonth();

        $this->summary = $this->buildSummary($client->id, $currentStart, $currentEnd);

        if ($this->reportsLevel >= 1) {
            $prevStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $prevEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();
            $this->previousSummary = $this->buildSummary($client->id, $prevStart, $prevEnd);
        }

        if ($this->reportsLevel >= 2) {
            $this->topCategories = Transaction::query()
                ->where('client_id', $client->id)
                ->whereBetween('occurred_at', [$currentStart, $currentEnd])
                ->whereNotNull('category')
                ->selectRaw('category, SUM(amount_cents) as total_cents')
                ->groupBy('category')
                ->orderByDesc('total_cents')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'category' => $row->category,
                    'total_cents' => (int) $row->total_cents,
                ])
                ->all();
        }
    }

    private function buildSummary(string $clientId, $start, $end): array
    {
        $income = Transaction::query()
            ->where('client_id', $clientId)
            ->where('type', 'income')
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount_cents');

        $expense = Transaction::query()
            ->where('client_id', $clientId)
            ->where('type', 'expense')
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount_cents');

        return [
            'income' => (int) $income,
            'expense' => (int) $expense,
            'balance' => (int) ($income - $expense),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.home')
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
