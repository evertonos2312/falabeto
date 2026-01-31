<?php

namespace App\Console\Commands;

use App\Models\MessageLog;
use Illuminate\Console\Command;

class PurgeMessageLogs extends Command
{
    protected $signature = 'falabeto:purge-message-logs {--days=30}';

    protected $description = 'Remove message logs older than N days.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        MessageLog::query()->where('created_at', '<', $cutoff)->delete();

        $this->info('Message logs purged.');

        return self::SUCCESS;
    }
}
