<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WhatsappVerify extends Component
{
    public ?string $generatedCode = null;
    public string $code = '';

    public function mount(): void
    {
        $client = Auth::guard('client')->user();

        if ($client?->whatsapp_verified_at) {
            $this->redirectRoute('plans');
        }
    }

    public function sendCode(): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        session(['whatsapp_code' => $code]);
        $this->generatedCode = $code;
        $this->reset('code');
    }

    public function verifyCode(): void
    {
        $this->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $expected = session('whatsapp_code');

        if (! $expected || $this->code !== $expected) {
            $this->addError('code', 'Código inválido.');
            return;
        }

        $client = Auth::guard('client')->user();
        $client->forceFill(['whatsapp_verified_at' => now()])->save();

        session()->forget('whatsapp_code');

        $this->redirectRoute('plans');
    }

    public function render()
    {
        return view('livewire.whatsapp-verify')
            ->layout('layouts.app', ['title' => 'Confirmar WhatsApp']);
    }
}
