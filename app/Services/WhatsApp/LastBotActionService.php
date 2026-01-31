<?php

namespace App\Services\WhatsApp;

use App\Models\MessageLog;

class LastBotActionService
{
    public function getLastActionForClient(string $clientId): ?array
    {
        $logs = MessageLog::query()
            ->where('client_id', $clientId)
            ->where('direction', 'inbound')
            ->whereNotNull('meta_json')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        foreach ($logs as $log) {
            $record = $log->meta_json['record'] ?? null;
            if (! $record || empty($record['type']) || empty($record['id'])) {
                continue;
            }

            return [
                'record_type' => $record['type'],
                'record_id' => $record['id'],
                'action' => $log->meta_json['action'] ?? null,
                'created_at' => $log->created_at,
                'message_log_id' => $log->id,
            ];
        }

        return null;
    }
}
