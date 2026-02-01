<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\MessageLog;
use App\Models\UsageDaily;
use App\Services\FinanceService;
use App\Services\OpenAI\BetoNluService;
use App\Services\WhatsApp\FastPathInterpreter;
use App\Services\WhatsApp\InterpretationValidator;
use App\Services\WhatsApp\LastBotActionService;
use App\Support\FeatureGate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly BetoNluService $nluService,
        private readonly FastPathInterpreter $fastPath,
        private readonly FinanceService $financeService,
        private readonly InterpretationValidator $validator,
        private readonly LastBotActionService $lastBotAction,
    ) {}

    public function handle(Request $request)
    {
        $requestId = (string) $request->attributes->get('request_id');

        Log::info('whatsapp_webhook_received', [
            'request_id' => $requestId,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            $secret = (string) $request->header('X-WEBHOOK-SECRET');
            if ($secret === '' || $secret !== env('WHATSAPP_WEBHOOK_SECRET')) {
                return response()->json(['reply_text' => 'Segredo inválido.'], 401);
            }

            $payload = $request->validate([
                'phone' => ['required', 'string', 'max:20'],
                'text' => ['required', 'string', 'max:2000'],
                'timestamp' => ['required'],
            ]);

            $phone = $this->normalizePhone($payload['phone']);
            $text = trim($payload['text']);

            $client = Client::query()->where('phone_e164', $phone)->first();

            if (! $client) {
                $this->logMessage(null, $phone, 'inbound', $text, [
                    'action' => 'client_not_found',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ], false);

                return response()->json(['reply_text' => 'Não encontramos sua conta.'], 404);
            }

            $subscription = $client->subscriptions()->latest('started_at')->first();
            if (! $subscription || ! in_array($subscription->status, ['trialing', 'active'], true)) {
                $this->logMessage($client->id, $phone, 'inbound', $text, [
                    'action' => 'no_subscription',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ], false);

                return response()->json(['reply_text' => 'Para continuar, ative sua assinatura.'], 402);
            }

            $limit = (int) FeatureGate::getFeatureValue($client, 'messages_per_day', 30);
            $usageDate = now($client->timezone ?? 'America/Sao_Paulo')->toDateString();
            $usage = UsageDaily::query()
                ->where('client_id', $client->id)
                ->whereDate('date', $usageDate)
                ->first();

            if (! $usage) {
                $usage = UsageDaily::create([
                    'client_id' => $client->id,
                    'date' => $usageDate,
                    'messages_in' => 0,
                ]);
            }

            $usage->increment('messages_in');

            $rateLimitEnabled = settings('security.rate_limit_enabled', true);
            if ($rateLimitEnabled && $usage->messages_in > $limit) {
                $this->logMessage($client->id, $phone, 'inbound', $text, [
                    'action' => 'rate_limited',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ], false);

                return response()->json([
                    'reply_text' => 'Você atingiu o limite diário de mensagens do plano.',
                    'action' => 'rate_limited',
                ], 429);
            }

            $inboundLog = $this->logMessage($client->id, $phone, 'inbound', $text, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], false);

            $context = [
                'timezone' => $client->timezone ?? 'America/Sao_Paulo',
                'now' => now($client->timezone ?? 'America/Sao_Paulo')->toIso8601String(),
            ];

            $interpretation = $this->fastPath->interpret($text, $context['timezone']);
            $llmUsed = false;

            if (! $interpretation) {
                $interpretation = $this->nluService->interpret($text, $context);
                $llmUsed = true;
            }

            $validated = $this->validator->validate($interpretation, $context['timezone']);

            if ($validated['action'] === 'clarify') {
                $reply = $validated['question'] ?? 'Pode detalhar?';
                $this->logMessage($client->id, $phone, 'outbound', $reply, [
                    'action' => 'clarify',
                    'fields' => $validated['fields'] ?? [],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ], $llmUsed, $interpretation);

                return response()->json([
                    'reply_text' => $reply,
                    'action' => 'clarify',
                    'data' => $validated['fields'] ?? [],
                ]);
            }

            $result = $this->executeAction($client, $validated, $inboundLog?->id);
            $reply = $result['reply_text'];

            if ($result['record'] ?? null) {
                $inboundLog?->update([
                    'meta_json' => array_merge($inboundLog->meta_json ?? [], [
                        'action' => $validated['action'],
                        'record' => $result['record'],
                        'validation' => $result['validation'] ?? [],
                    ]),
                ]);
            }

            Log::info('whatsapp_webhook_result', [
                'request_id' => $requestId,
                'client_id' => $client->id,
                'phone' => $phone,
                'action' => $validated['action'],
                'llm_used' => $llmUsed,
                'status' => 'ok',
                'validation_errors_count' => $result['validation_errors_count'] ?? 0,
            ]);

            $this->logMessage($client->id, $phone, 'outbound', $reply, [
                'action' => $validated['action'],
                'fields' => $validated['fields'] ?? [],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], $llmUsed, $interpretation);

            return response()->json([
                'reply_text' => $reply,
                'action' => $validated['action'],
                'data' => $result['data'] ?? [],
            ]);
        } catch (\Throwable $e) {
            Log::error('whatsapp_webhook_error', [
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['reply_text' => 'Erro ao processar sua mensagem.'], 500);
        }
    }

    private function executeAction(Client $client, array $validated, ?string $messageLogId): array
    {
        switch ($validated['action']) {
            case 'create_transaction':
                $transaction = $this->financeService->createTransaction($client, array_merge($validated['fields'], [
                    'created_via' => 'whatsapp',
                    'source_message_log_id' => $messageLogId,
                ]));
                $monthSummary = $this->financeService->monthlySummary($client, null, null);

                return [
                    'reply_text' => sprintf(
                        'Anotado! %s R$ %s. No mês você já gastou R$ %s.',
                        Str::title($transaction->description),
                        $this->formatCents($transaction->amount_cents),
                        $this->formatCents($monthSummary['expense_cents'])
                    ),
                    'data' => [
                        'transaction_id' => $transaction->id,
                    ],
                    'record' => [
                        'type' => 'transaction',
                        'id' => $transaction->id,
                    ],
                ];
            case 'create_debt':
                $debt = $this->financeService->createDebt($client, array_merge($validated['fields'], [
                    'created_via' => 'whatsapp',
                    'source_message_log_id' => $messageLogId,
                ]));
                return [
                    'reply_text' => sprintf(
                        'Dívida cadastrada: %s, R$ %s com vencimento %s.',
                        $debt->creditor_name ?? 'Credor',
                        $this->formatCents($debt->amount_cents),
                        $debt->due_date->format('d/m')
                    ),
                    'data' => [
                        'debt_id' => $debt->id,
                    ],
                    'record' => [
                        'type' => 'debt',
                        'id' => $debt->id,
                    ],
                ];
            case 'undo_last':
                return $this->handleUndo($client);
            case 'edit_last':
                return $this->handleEdit($client, $validated['fields'] ?? []);
            case 'list_pending_debts':
                $items = $this->financeService->listPendingDebts($client);
                if (empty($items)) {
                    return [
                        'reply_text' => 'Você não tem dívidas pendentes.',
                        'data' => [],
                    ];
                }

                $lines = array_map(function ($item) {
                    return sprintf(
                        '%s R$ %s vence %s',
                        $item['creditor_name'],
                        $this->formatCents($item['amount_cents']),
                        Carbon::parse($item['due_date'])->format('d/m')
                    );
                }, $items);

                return [
                    'reply_text' => "Pendências:\n" . implode("\n", $lines),
                    'data' => $items,
                ];
            case 'monthly_summary':
                $summary = $this->financeService->monthlySummary($client, $validated['fields']['month'] ?? null, $validated['fields']['year'] ?? null);
                return [
                    'reply_text' => sprintf(
                        'Resumo %02d/%d: entradas R$ %s, saídas R$ %s, saldo R$ %s.',
                        $summary['month'],
                        $summary['year'],
                        $this->formatCents($summary['income_cents']),
                        $this->formatCents($summary['expense_cents']),
                        $this->formatCents($summary['balance_cents'])
                    ),
                    'data' => $summary,
                ];
            default:
                return [
                    'reply_text' => 'Quer registrar gasto/receita, ver pendências ou resumo do mês?',
                    'data' => [],
                    'validation_errors_count' => 0,
                ];
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (! str_starts_with($digits, '55') && strlen($digits) >= 10) {
            $digits = '55' . $digits;
        }

        return '+' . $digits;
    }

    private function logMessage(?string $clientId, ?string $phone, string $direction, string $body, ?array $meta, bool $llmUsed, ?array $interpretation = null): ?MessageLog
    {
        $snippet = Str::of($body)->replaceMatches('/\s+/', ' ')->substr(0, 64)->toString();
        $hash = hash('sha256', $body);
        $storeBody = (bool) settings('security.log_message_body', false);

        return MessageLog::create([
            'client_id' => $clientId,
            'phone_e164' => $phone,
            'channel' => 'whatsapp',
            'direction' => $direction,
            'body' => $storeBody ? $body : null,
            'body_snippet' => $snippet,
            'body_hash' => $hash,
            'llm_used' => $llmUsed,
            'llm_model' => $llmUsed ? config('services.openai.model') : null,
            'llm_cost_cents' => null,
            'meta_json' => $meta,
        ]);
    }

    private function handleUndo(Client $client): array
    {
        $last = $this->lastBotAction->getLastActionForClient($client->id);
        if (! $last) {
            return [
                'reply_text' => 'Não encontrei nada recente pra desfazer.',
                'data' => [],
            ];
        }

        if ($last['record_type'] === 'transaction') {
            $record = \App\Models\Transaction::query()
                ->where('id', $last['record_id'])
                ->where('client_id', $client->id)
                ->where('created_via', 'whatsapp')
                ->first();

            if (! $record) {
                return [
                    'reply_text' => 'Não encontrei nada recente pra desfazer.',
                    'data' => [],
                ];
            }

            $record->delete();
            return [
                'reply_text' => 'Fechado. Desfiz a última anotação.',
                'data' => [],
            ];
        }

        if ($last['record_type'] === 'debt') {
            $record = \App\Models\Debt::query()
                ->where('id', $last['record_id'])
                ->where('client_id', $client->id)
                ->where('created_via', 'whatsapp')
                ->first();

            if (! $record) {
                return [
                    'reply_text' => 'Não encontrei nada recente pra desfazer.',
                    'data' => [],
                ];
            }

            $record->delete();
            return [
                'reply_text' => 'Fechado. Desfiz a última anotação.',
                'data' => [],
            ];
        }

        return [
            'reply_text' => 'Não encontrei nada recente pra desfazer.',
            'data' => [],
        ];
    }

    private function handleEdit(Client $client, array $fields): array
    {
        $last = $this->lastBotAction->getLastActionForClient($client->id);
        if (! $last) {
            return [
                'reply_text' => 'Não encontrei nada recente pra corrigir.',
                'data' => [],
            ];
        }

        $hasData = ! empty($fields['amount']) || ! empty($fields['description']) || ! empty($fields['creditor_name']) || ! empty($fields['due_date']);
        if (! $hasData) {
            return [
                'reply_text' => "O que você quer corrigir? Me diga o valor e a descrição. Ex: '12 mercado' ou '150 conta de luz'.",
                'data' => [],
            ];
        }

        if ($last['record_type'] === 'transaction') {
            $record = \App\Models\Transaction::query()
                ->where('id', $last['record_id'])
                ->where('client_id', $client->id)
                ->where('created_via', 'whatsapp')
                ->first();

            if (! $record) {
                return [
                    'reply_text' => 'Não encontrei nada recente pra corrigir.',
                    'data' => [],
                ];
            }

            if (! empty($fields['amount'])) {
                $record->amount_cents = (int) round($fields['amount'] * 100);
            }
            if (! empty($fields['description'])) {
                $record->description = $fields['description'];
            }
            if (! empty($fields['occurred_at'])) {
                $record->occurred_at = $fields['occurred_at'];
            }
            $record->save();

            return [
                'reply_text' => 'Atualizado. Já corrigi a última anotação.',
                'data' => [
                    'transaction_id' => $record->id,
                ],
            ];
        }

        if ($last['record_type'] === 'debt') {
            $record = \App\Models\Debt::query()
                ->where('id', $last['record_id'])
                ->where('client_id', $client->id)
                ->where('created_via', 'whatsapp')
                ->first();

            if (! $record) {
                return [
                    'reply_text' => 'Não encontrei nada recente pra corrigir.',
                    'data' => [],
                ];
            }

            if (! empty($fields['amount'])) {
                $record->amount_cents = (int) round($fields['amount'] * 100);
            }
            if (! empty($fields['creditor_name'])) {
                $record->creditor_name = $fields['creditor_name'];
            } elseif (! empty($fields['description'])) {
                $record->creditor_name = $fields['description'];
            }
            if (! empty($fields['due_date'])) {
                $record->due_date = $fields['due_date'];
            }
            $record->save();

            return [
                'reply_text' => 'Atualizado. Já corrigi a última anotação.',
                'data' => [
                    'debt_id' => $record->id,
                ],
            ];
        }

        return [
            'reply_text' => 'Não encontrei nada recente pra corrigir.',
            'data' => [],
        ];
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '.');
    }
}
