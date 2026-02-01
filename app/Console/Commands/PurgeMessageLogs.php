<?php

namespace App\Console\Commands;

use App\Models\MessageLog;
use Illuminate\Console\Command;

class PurgeMessageLogs extends Command
{
    protected $signature = 'falabeto:purge-message-logs {--days=}';

    protected $description = 'Remove message logs older than N days.';

    public function handle(): int
    {
        $days = $this->option('days');
        $days = $days !== null ? (int) $days : (int) settings('security.message_logs_retention_days', 30);
        $cutoff = now()->subDays($days);

        MessageLog::query()->where('created_at', '<', $cutoff)->delete();

        $this->info('Message logs purged.');

        return self::SUCCESS;
    }
}
