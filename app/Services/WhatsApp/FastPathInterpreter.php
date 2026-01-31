<?php

namespace App\Services\WhatsApp;

use Carbon\Carbon;

class FastPathInterpreter
{
    public function interpret(string $text, string $timezone): ?array
    {
        $normalized = mb_strtolower(trim($text));

        if ($normalized === '') {
            return null;
        }

        if (preg_match('/\b(desfaz|desfazer|volta)\b/u', $normalized)) {
            return [
                'action' => 'undo_last',
                'confidence' => 0.9,
                'fields' => [],
                'question' => null,
            ];
        }

        if (preg_match('/\b(corrigir|ajustar|editar ultimo|editar última)\b/u', $normalized)) {
            return [
                'action' => 'edit_last',
                'confidence' => 0.8,
                'fields' => $this->parseCorrectionFields($normalized, $timezone),
                'question' => null,
            ];
        }

        if (str_contains($normalized, 'pendente')) {
            return [
                'action' => 'list_pending_debts',
                'confidence' => 0.9,
                'fields' => [],
                'question' => null,
            ];
        }

        if (str_contains($normalized, 'resumo')) {
            return [
                'action' => 'monthly_summary',
                'confidence' => 0.8,
                'fields' => [
                    'month' => null,
                    'year' => null,
                ],
                'question' => null,
            ];
        }

        if (preg_match('/\b(gastei|paguei|coloca|lanca|lança)\b/u', $normalized)) {
            return $this->parseTransaction($normalized, 'expense', $timezone);
        }

        if (preg_match('/\b(recebi|entrada|ganhei|salario|salário)\b/u', $normalized)) {
            return $this->parseTransaction($normalized, 'income', $timezone);
        }

        return null;
    }

    private function parseTransaction(string $text, string $type, string $timezone): ?array
    {
        if (! preg_match('/(\d+[.,]?\d*)/u', $text, $matches)) {
            return null;
        }

        $amount = (float) str_replace(',', '.', $matches[1]);

        $description = trim(preg_replace('/\b(gastei|paguei|coloca|lanca|lança|recebi|entrada|ganhei|salario|salário)\b/u', '', $text));
        $description = preg_replace('/\s+/', ' ', $description);

        $occurredAt = $this->parseRelativeDate($text, $timezone);

        return [
            'action' => 'create_transaction',
            'confidence' => 0.85,
            'fields' => [
                'type' => $type,
                'amount' => $amount,
                'description' => $description !== '' ? $description : null,
                'category' => null,
                'occurred_at' => $occurredAt?->toIso8601String(),
            ],
            'question' => null,
        ];
    }

    public function parseRelativeDate(string $text, string $timezone): ?Carbon
    {
        $now = Carbon::now($timezone);

        if (str_contains($text, 'ontem')) {
            return $now->copy()->subDay();
        }

        if (str_contains($text, 'hoje')) {
            return $now->copy();
        }

        return null;
    }

    private function parseCorrectionFields(string $text, string $timezone): array
    {
        $fields = [
            'amount' => null,
            'description' => null,
            'creditor_name' => null,
            'due_date' => null,
            'occurred_at' => null,
        ];

        if (preg_match('/(\d+[.,]?\d*)/u', $text, $matches)) {
            $fields['amount'] = (float) str_replace(',', '.', $matches[1]);
        }

        if (preg_match('/dia\s+(\d{1,2})/u', $text, $matches)) {
            $day = (int) $matches[1];
            $now = Carbon::now($timezone);
            $fields['due_date'] = $now->copy()->day($day)->toDateString();
        }

        $relative = $this->parseRelativeDate($text, $timezone);
        if ($relative) {
            $fields['occurred_at'] = $relative->toIso8601String();
        }

        $clean = preg_replace('/\b(corrigir|ajustar|editar ultimo|editar última|pra|para|dia|ontem|hoje)\b/u', '', $text);
        $clean = preg_replace('/(\d+[.,]?\d*)/u', '', $clean);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        if ($clean !== '') {
            $fields['description'] = $clean;
            $fields['creditor_name'] = $clean;
        }

        return $fields;
    }
}
