<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'coupon_id',
        'client_id',
        'subscription_id',
        'redeemed_at',
        'meta_json',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
        'meta_json' => 'array',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
