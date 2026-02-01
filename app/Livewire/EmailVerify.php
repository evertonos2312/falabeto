<?php

namespace App\Livewire;

use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmailVerify extends Component
{
    public string $email = '';
    public string $email_code = '';
    public ?int $cooldownEndsAt = null;
    public int $cooldownRemaining = 0;

    public function mount(): void
    {
        $client = Auth::guard('client')->user();
        $this->email = $client?->email ?? '';
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
        return view('livewire.email-verify')
            ->layout('layouts.app', ['title' => 'Confirmar email']);
    }
}
