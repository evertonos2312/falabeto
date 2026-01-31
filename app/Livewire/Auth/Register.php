<?php

namespace App\Livewire\Auth;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Register extends Component
{
    public ?string $name = null;
    public string $email = '';
    public string $phone_e164 = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $termsAccepted = false;

    public function register(): void
    {
        $this->normalizePhone();

        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients,email'],
            'phone_e164' => ['required', 'regex:/^\+\d{10,15}$/', 'unique:clients,phone_e164'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'termsAccepted' => ['accepted'],
        ], [
            'phone_e164.regex' => 'Informe o telefone com DDD e número.',
            'termsAccepted.accepted' => 'Você precisa aceitar os termos e a política de privacidade.',
        ]);

        $client = Client::create([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'],
            'phone_e164' => $validated['phone_e164'],
            'password' => $validated['password'],
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'accepted_ip' => request()->ip(),
            'accepted_user_agent' => request()->userAgent(),
        ]);

        Auth::guard('client')->login($client);

        $this->redirectRoute('whatsapp.verify');
    }

    private function normalizePhone(): void
    {
        $raw = $this->phone_e164 ?? '';
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '') {
            return;
        }

        if (str_starts_with(trim($raw), '+')) {
            $this->phone_e164 = '+' . $digits;

            return;
        }

        $this->phone_e164 = '+55' . $digits;
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.app', ['title' => 'Cadastro']);
    }
}
