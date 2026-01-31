<?php

use App\Livewire\Auth\Register;
use Livewire\Livewire;

it('register creates client and redirects', function () {
    Livewire::test(Register::class)
        ->set('name', 'Maria Silva')
        ->set('email', 'maria@example.com')
        ->set('phone_e164', '+5511999999999')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('termsAccepted', true)
        ->call('register')
        ->assertRedirect(route('whatsapp.verify'));

    $this->assertDatabaseHas('clients', [
        'email' => 'maria@example.com',
        'phone_e164' => '+5511999999999',
    ]);

    $client = \App\Models\Client::query()->where('email', 'maria@example.com')->first();
    expect($client->terms_accepted_at)->not->toBeNull();
    expect($client->privacy_accepted_at)->not->toBeNull();
});

it('register fails without terms acceptance', function () {
    Livewire::test(Register::class)
        ->set('email', 'fail@example.com')
        ->set('phone_e164', '+5511999999998')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('termsAccepted', false)
        ->call('register')
        ->assertHasErrors(['termsAccepted']);
});
