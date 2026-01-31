<?php

use App\Services\WhatsApp\FastPathInterpreter;
use Carbon\Carbon;

it('parses yesterday date', function () {
    Carbon::setTestNow(Carbon::create(2026, 1, 30, 10, 0, 0, 'America/Sao_Paulo'));
    $fastPath = new FastPathInterpreter();
    $result = $fastPath->interpret('gastei 10 ontem', 'America/Sao_Paulo');

    expect($result['action'])->toBe('create_transaction');
    expect($result['fields']['occurred_at'])->not->toBeNull();
    expect(Carbon::parse($result['fields']['occurred_at'])->format('Y-m-d'))->toBe('2026-01-29');
});
