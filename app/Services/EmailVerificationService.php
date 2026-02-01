<?php

namespace App\Services;

use App\Mail\VerifyEmailCodeMail;
use App\Models\Client;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EmailVerificationService
{
    public function start(string $email, ?Client $client = null): void
    {
        $email = strtolower(trim($email));

        $this->enforceRateLimits($email);

        $code = (string) random_int(100000, 999999);

        $verification = EmailVerification::query()
            ->where('email', $email)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (! $verification) {
            $verification = new EmailVerification([
                'email' => $email,
            ]);
        }

        $verification->client_id = $client?->id;
        $verification->code_hash = Hash::make($code);
        $verification->expires_at = now()->addMinutes(10);
        $verification->attempts = 0;
        $verification->send_count = $verification->send_count + 1;
        $verification->last_sent_at = now();
        $verification->meta_json = [
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ];
        $verification->save();

        Mail::to($email)->send(new VerifyEmailCodeMail($code));
    }

    public function verify(string $email, string $code): Client
    {
        $email = strtolower(trim($email));
        $verification = EmailVerification::query()
            ->where('email', $email)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'code' => 'Código expirado. Solicite um novo.',
            ]);
        }

        if ($verification->attempts >= 5) {
            throw ValidationException::withMessages([
                'code' => 'Muitas tentativas. Solicite um novo código.',
            ]);
        }

        if (! Hash::check($code, $verification->code_hash)) {
            $verification->increment('attempts');
            throw ValidationException::withMessages([
                'code' => 'O código informado é inválido.',
            ]);
        }

        $verification->forceFill(['verified_at' => now()])->save();

        $client = $verification->client_id
            ? Client::query()->where('id', $verification->client_id)->first()
            : Client::query()->where('email', $email)->first();

        if (! $client) {
            throw ValidationException::withMessages([
                'code' => 'Conta não encontrada. Tente novamente.',
            ]);
        }

        $client->forceFill([
            'email_verified_at' => now(),
            'onboarding_status' => 'pending_whatsapp',
        ])->save();

        return $client;
    }

    public function resend(string $email, ?Client $client = null): void
    {
        $this->start($email, $client);
    }

    private function enforceRateLimits(string $email): void
    {
        $latest = EmailVerification::query()
            ->where('email', $email)
            ->latest('created_at')
            ->first();

        if ($latest && $latest->last_sent_at && $latest->last_sent_at->gt(now()->subSeconds(45))) {
            throw ValidationException::withMessages([
                'code' => 'Aguarde alguns segundos para reenviar o código.',
            ]);
        }

        $lastHourCount = EmailVerification::query()
            ->where('email', $email)
            ->where('created_at', '>=', now()->subHour())
            ->sum('send_count');

        if ($lastHourCount >= 3) {
            throw ValidationException::withMessages([
                'code' => 'Limite de envios atingido. Tente novamente mais tarde.',
            ]);
        }
    }
}
