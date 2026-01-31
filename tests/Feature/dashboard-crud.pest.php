<?php

use App\Livewire\Dashboard\Debts;
use App\Livewire\Dashboard\Transactions;
use App\Models\Client;
use Livewire\Livewire;

it('create transaction via dashboard component', function () {
    $client = Client::factory()->create(['whatsapp_verified_at' => now()]);

    $this->actingAs($client, 'client');

    Livewire::test(Transactions::class)
        ->set('type', 'income')
        ->set('amount_cents', 12000)
        ->set('occurred_at', now()->format('Y-m-d\TH:i'))
        ->set('category', 'vendas')
        ->set('description', 'Matrícula')
        ->set('notes', 'Pago via Pix')
        ->call('save');

    $this->assertDatabaseHas('transactions', [
        'client_id' => $client->id,
        'type' => 'income',
        'amount_cents' => 12000,
        'category' => 'vendas',
    ]);
});

it('create debt via dashboard component', function () {
    $client = Client::factory()->create(['whatsapp_verified_at' => now()]);

    $this->actingAs($client, 'client');

    Livewire::test(Debts::class)
        ->set('creditor_name', 'Fornecedor XYZ')
        ->set('amount_cents', 4500)
        ->set('due_date', now()->addDays(10)->format('Y-m-d'))
        ->set('status', 'pending')
        ->set('notes', 'Pagar no vencimento')
        ->call('save');

    $this->assertDatabaseHas('debts', [
        'client_id' => $client->id,
        'amount_cents' => 4500,
        'status' => 'pending',
    ]);
});
