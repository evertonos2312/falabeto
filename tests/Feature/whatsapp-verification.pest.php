<?php

use App\Livewire\WhatsappVerify;
use App\Models\Client;
use Livewire\Livewire;

it('whatsapp verification flow', function () {
    $client = Client::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($client, 'client');

    $component = Livewire::test(WhatsappVerify::class)
        ->call('sendCode');

    $code = $component->get('generatedCode');

    $component
        ->set('code', $code)
        ->call('verifyCode')
        ->assertRedirect(route('plans'));

    $this->assertNotNull($client->fresh()->whatsapp_verified_at);
});
