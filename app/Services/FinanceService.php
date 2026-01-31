<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Debt;
use App\Models\Transaction;
use Carbon\Carbon;

class FinanceService
{
    public function createTransaction(Client $client, array $fields): Transaction
    {
        $occurredAt = isset($fields['occurred_at'])
            ? Carbon::parse($fields['occurred_at'], $client->timezone ?? 'America/Sao_Paulo')
            : now($client->timezone ?? 'America/Sao_Paulo');

        return Transaction::create([
            'client_id' => $client->id,
            'type' => $fields['type'],
            'amount_cents' => (int) round($fields['amount'] * 100),
            'occurred_at' => $occurredAt,
            'category' => $fields['category'] ?? null,
            'description' => $fields['description'],
            'notes' => $fields['notes'] ?? null,
            'created_via' => $fields['created_via'] ?? 'web',
            'source_message_log_id' => $fields['source_message_log_id'] ?? null,
        ]);
    }

    public function createDebt(Client $client, array $fields): Debt
    {
        return Debt::create([
            'client_id' => $client->id,
            'creditor_name' => $fields['creditor_name'] ?? 'Credor',
            'amount_cents' => (int) round($fields['amount'] * 100),
            'due_date' => $fields['due_date'] ?? now($client->timezone ?? 'America/Sao_Paulo')->toDateString(),
            'status' => 'pending',
            'notes' => $fields['notes'] ?? null,
            'created_via' => $fields['created_via'] ?? 'web',
            'source_message_log_id' => $fields['source_message_log_id'] ?? null,
        ]);
    }

    public function listPendingDebts(Client $client): array
    {
        return Debt::query()
            ->where('client_id', $client->id)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(fn (Debt $debt) => [
                'creditor_name' => $debt->creditor_name,
                'amount_cents' => $debt->amount_cents,
                'due_date' => $debt->due_date->format('Y-m-d'),
            ])
            ->all();
    }

    public function monthlySummary(Client $client, ?int $month, ?int $year): array
    {
        $timezone = $client->timezone ?? 'America/Sao_Paulo';
        $now = now($timezone);
        $month = $month ?: (int) $now->format('m');
        $year = $year ?: (int) $now->format('Y');

        $start = Carbon::create($year, $month, 1, 0, 0, 0, $timezone);
        $end = $start->copy()->endOfMonth();

        $income = Transaction::query()
            ->where('client_id', $client->id)
            ->where('type', 'income')
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount_cents');

        $expense = Transaction::query()
            ->where('client_id', $client->id)
            ->where('type', 'expense')
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount_cents');

        return [
            'income_cents' => (int) $income,
            'expense_cents' => (int) $expense,
            'balance_cents' => (int) ($income - $expense),
            'month' => $month,
            'year' => $year,
        ];
    }
}
