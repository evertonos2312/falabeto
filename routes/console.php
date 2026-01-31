<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\PurgeMessageLogs;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('falabeto:purge-message-logs {--days=30}', function () {
    $this->call(PurgeMessageLogs::class, ['--days' => $this->option('days')]);
})->purpose('Remove message logs older than N days.');
