<?php

namespace App\Services\WhatsApp;

use Carbon\Carbon;

class InterpretationValidator
{
    public function validate(array $interpretation, string $timezone): array
    {
        $action = $interpretation['action'] ?? 'unknown';
        $fields = $interpretation['fields'] ?? [];
        $question = $interpretation['question'] ?? null;

        if (! in_array($action, [
            'create_transaction',
            'create_debt',
            'list_pending_debts',
            'monthly_summary',
            'undo_last',
            'edit_last',
            'clarify',
            'unknown',
        ], true)) {
            return [
                'action' => 'unknown',
                'fields' => [],
                'question' => 'Quer registrar gasto/receita, ver pendências ou resumo do mês?',
            ];
        }

        if ($action === 'create_transaction') {
            if (! isset($fields['amount']) || $fields['amount'] <= 0) {
                return [
                    'action' => 'clarify',
                    'fields' => $fields,
                    'question' => 'Qual o valor?',
                ];
            }

            if (! in_array($fields['type'] ?? '', ['expense', 'income'], true)) {
                return [
                    'action' => 'clarify',
                    'fields' => $fields,
                    'question' => 'Isso é gasto ou receita?',
                ];
            }

            if (! isset($fields['description']) || trim((string) $fields['description']) === '') {
                return [
                    'action' => 'clarify',
                    'fields' => $fields,
                    'question' => 'Qual a descrição?',
                ];
            }

            if (! empty($fields['occurred_at'])) {
                try {
                    $fields['occurred_at'] = Carbon::parse($fields['occurred_at'], $timezone)->toIso8601String();
                } catch (\Throwable) {
                    $fields['occurred_at'] = null;
                }
            }

            return [
                'action' => $action,
                'fields' => $fields,
            ];
        }

        if ($action === 'create_debt') {
            if (! isset($fields['amount']) || $fields['amount'] <= 0) {
                return [
                    'action' => 'clarify',
                    'fields' => $fields,
                    'question' => 'Qual o valor?',
                ];
            }

            if (! empty($fields['due_date'])) {
                try {
                    $fields['due_date'] = Carbon::parse($fields['due_date'], $timezone)->toDateString();
                } catch (\Throwable) {
                    $fields['due_date'] = null;
                }
            }

            return [
                'action' => $action,
                'fields' => $fields,
            ];
        }

        if ($action === 'monthly_summary') {
            $fields['month'] = isset($fields['month']) ? (int) $fields['month'] : null;
            $fields['year'] = isset($fields['year']) ? (int) $fields['year'] : null;

            return [
                'action' => $action,
                'fields' => $fields,
            ];
        }

        if ($action === 'clarify') {
            return [
                'action' => 'clarify',
                'fields' => $fields,
                'question' => $question ?? 'Pode detalhar?',
            ];
        }

        if ($action === 'undo_last' || $action === 'edit_last') {
            return [
                'action' => $action,
                'fields' => $fields,
            ];
        }

        return [
            'action' => 'unknown',
            'fields' => [],
            'question' => 'Quer registrar gasto/receita, ver pendências ou resumo do mês?',
        ];
    }
}
