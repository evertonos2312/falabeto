<?php

namespace App\Livewire\Auth;

use App\Models\Client;
use App\Services\EmailVerificationService;
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
    public string $step = 'form';
    public string $email_code = '';
    public ?int $cooldownEndsAt = null;
    public int $cooldownRemaining = 0;

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
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome deve ter no máximo 255 caracteres.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Informe um email válido.',
            'email.max' => 'O email deve ter no máximo 255 caracteres.',
            'email.unique' => 'Este email já está em uso.',
            'phone_e164.required' => 'O campo celular é obrigatório.',
            'phone_e164.regex' => 'Informe um celular válido com DDD.',
            'phone_e164.unique' => 'Este celular já está em uso.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'termsAccepted.accepted' => 'Você precisa aceitar os termos e a política de privacidade.',
        ]);

        $client = Client::create([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'],
            'phone_e164' => $validated['phone_e164'],
            'password' => $validated['password'],
            'onboarding_status' => 'pending_email',
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
            'accepted_ip' => request()->ip(),
            'accepted_user_agent' => request()->userAgent(),
        ]);

        app(EmailVerificationService::class)->start($client->email, $client);
        $this->cooldownEndsAt = now()->addSeconds(45)->timestamp;
        $this->updateCooldown();

        Auth::guard('client')->login($client);

        $this->step = 'verify_email';
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

    public function verifyEmailCode(): void
    {
        $this->validate([
            'email_code' => ['required', 'digits:6'],
        ], [
            'email_code.required' => 'O código é obrigatório.',
            'email_code.digits' => 'O código deve ter 6 dígitos.',
        ]);

        try {
            app(EmailVerificationService::class)->verify($this->email, $this->email_code);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('email_code', $e->validator->errors()->first('code'));
            return;
        }

        $this->redirectRoute('whatsapp.verify');
    }

    public function resendEmailCode(): void
    {
        try {
            app(EmailVerificationService::class)->resend($this->email, Auth::guard('client')->user());
            $this->cooldownEndsAt = now()->addSeconds(45)->timestamp;
            $this->updateCooldown();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->addError('email_code', $e->validator->errors()->first('code'));
        }
    }

    public function updateCooldown(): void
    {
        if (! $this->cooldownEndsAt) {
            $this->cooldownRemaining = 0;
            return;
        }

        $remaining = $this->cooldownEndsAt - now()->timestamp;
        $this->cooldownRemaining = $remaining > 0 ? $remaining : 0;
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.app', ['title' => 'Cadastro']);
    }
}
