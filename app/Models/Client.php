<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, HasUlids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone_e164',
        'password',
        'timezone',
        'whatsapp_verified_at',
        'email_verified_at',
        'onboarding_status',
        'payment_status',
        'trial_used_at',
        'terms_accepted_at',
        'privacy_accepted_at',
        'accepted_ip',
        'accepted_user_agent',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'trial_used_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function paymentOrders()
    {
        return $this->hasMany(PaymentOrder::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function messageLogs()
    {
        return $this->hasMany(MessageLog::class);
    }
}
