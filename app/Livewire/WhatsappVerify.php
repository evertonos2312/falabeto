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

        if ($client && ! $client->email_verified_at) {
            session()->flash('warning', 'Confirme seu email antes de validar o WhatsApp.');
            $this->redirectRoute('email.verify');
        }

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
        ], [
            'code.required' => 'O campo código é obrigatório.',
            'code.digits' => 'O código deve ter 6 dígitos.',
        ]);

        $expected = session('whatsapp_code');

        if (! $expected || $this->code !== $expected) {
            $this->addError('code', 'O código informado é inválido.');
            return;
        }

        $client = Auth::guard('client')->user();
        $client->forceFill([
            'whatsapp_verified_at' => now(),
            'onboarding_status' => 'pending_checkout',
        ])->save();

        session()->forget('whatsapp_code');

        $this->redirectRoute('plans');
    }

    public function render()
    {
        return view('livewire.whatsapp-verify')
            ->layout('layouts.app', ['title' => 'Confirmar WhatsApp']);
    }
}
