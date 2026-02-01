<?php

use App\Livewire\Auth\Login;
use App\Models\Client;
use Livewire\Livewire;

it('login with email', function () {
    $client = Client::factory()->create([
        'email' => 'ana@example.com',
        'password' => 'password123',
    ]);

    Livewire::test(Login::class)
        ->set('login', 'ana@example.com')
        ->set('password', 'password123')
        ->call('authenticate')
        ->assertRedirect(route('email.verify'));

    $this->assertAuthenticatedAs($client, 'client');
});

it('login with phone', function () {
    $client = Client::factory()->create([
        'phone_e164' => '+5511988887777',
        'password' => 'password123',
    ]);

    Livewire::test(Login::class)
        ->set('login', '+5511988887777')
        ->set('password', 'password123')
        ->call('authenticate')
        ->assertRedirect(route('email.verify'));

    $this->assertAuthenticatedAs($client, 'client');
});
