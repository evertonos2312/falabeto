<?php

use App\Services\WhatsApp\InterpretationValidator;

it('missing amount triggers clarify', function () {
    $validator = new InterpretationValidator();

    $result = $validator->validate([
        'action' => 'create_transaction',
        'fields' => [
            'type' => 'expense',
            'description' => 'mercado',
        ],
    ], 'America/Sao_Paulo');

    expect($result['action'])->toBe('clarify');
    expect($result['question'])->toBe('Qual o valor?');
});
