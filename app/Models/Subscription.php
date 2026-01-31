<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'client_id',
        'plan_id',
        'status',
        'started_at',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'next_renewal_at',
        'cancel_at_period_end',
        'coupon_id',
        'gateway',
        'gateway_customer_id',
        'gateway_subscription_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'next_renewal_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function items()
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
