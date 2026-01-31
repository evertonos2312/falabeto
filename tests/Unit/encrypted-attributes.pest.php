<?php

use App\Models\Debt;
use App\Models\Transaction;

it('transaction roundtrip encryption', function () {
    $transaction = new Transaction();
    $transaction->description = 'Aluguel';
    $transaction->notes = 'Pago no boleto';

    expect($transaction->getAttributes()['description_encrypted'])->not->toBe('Aluguel');
    expect($transaction->description)->toBe('Aluguel');
    expect($transaction->notes)->toBe('Pago no boleto');
});

it('debt roundtrip encryption', function () {
    $debt = new Debt();
    $debt->creditor_name = 'Banco Central';
    $debt->notes = 'Negociar parcelas';

    expect($debt->getAttributes()['creditor_name_encrypted'])->not->toBe('Banco Central');
    expect($debt->creditor_name)->toBe('Banco Central');
    expect($debt->notes)->toBe('Negociar parcelas');
});
