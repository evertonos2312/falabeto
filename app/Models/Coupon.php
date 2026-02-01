<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value_int',
        'duration',
        'duration_months',
        'max_redemptions',
        'redeemed_count',
        'valid_from',
        'valid_until',
        'is_active',
        'allowed_plan_codes',
        'first_purchase_only',
        'created_by_admin_id',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'allowed_plan_codes' => 'array',
        'first_purchase_only' => 'boolean',
    ];

    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }
}
