<?php

namespace App\Services\OpenAI;

use Illuminate\Support\Facades\Http;

class BetoNluService
{
    public function interpret(string $text, array $context): array
    {
        $model = config('services.openai.model');
        $baseUrl = rtrim(config('services.openai.base_url'), '/');

        $systemPrompt = <<<'PROMPT'
Você é um assistente de NLU que extrai intenções financeiras.
Responda APENAS JSON válido (sem markdown).
Não invente valores.
Se faltarem dados, use action="clarify" com question curta.
PROMPT;

        $actionsSchema = <<<'PROMPT'
AÇÕES SUPORTADAS:
- create_transaction
- create_debt
- list_pending_debts
- monthly_summary
- undo_last
- edit_last
- clarify
- unknown

SCHEMAS:
create_transaction.fields:
- type: "expense"|"income"
- amount: number (ex: 12.50)
- description: string (curto, ex: "mercado")
- category: string|null
- occurred_at: string|null (ISO8601). Se usuário disser "ontem", converter para data.
create_debt.fields:
- amount: number
- creditor_name: string|null
- due_date: string|null (YYYY-MM-DD) (interpretar "dia 5", "março", etc)
- notes: string|null
list_pending_debts.fields: {}
monthly_summary.fields:
- month: int 1..12 | null
- year: int | null
undo_last.fields: {}
edit_last.fields:
- amount: number|null
- description: string|null
- creditor_name: string|null
- due_date: string|null

OUTPUT JSON obrigatório:
{
  "action": "...",
  "confidence": 0.0,
  "fields": {...},
  "question": "..." | null
}
PROMPT;

        $userPrompt = "Texto do usuário: {$text}\n" .
            "Timezone: {$context['timezone']}\n" .
            "Data atual: {$context['now']}\n\n" .
            $actionsSchema;

        $response = Http::baseUrl($baseUrl)
            ->timeout((int) config('services.openai.timeout'))
            ->withToken(config('services.openai.key'))
            ->post('/chat/completions', [
                'model' => $model,
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if (! $response->ok()) {
            return [
                'action' => 'unknown',
                'confidence' => 0.0,
                'fields' => [],
                'question' => 'Não entendi. Quer registrar gasto/receita, ver pendências ou resumo do mês?',
                'raw' => [
                    'error' => $response->json(),
                ],
            ];
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        $decoded = json_decode((string) $content, true);

        if (! is_array($decoded)) {
            return [
                'action' => 'unknown',
                'confidence' => 0.0,
                'fields' => [],
                'question' => 'Não entendi. Quer registrar gasto/receita, ver pendências ou resumo do mês?',
                'raw' => [
                    'content' => $content,
                ],
            ];
        }

        $decoded['raw'] = $decoded['raw'] ?? [
            'model' => $model,
        ];

        return $decoded;
    }
}
