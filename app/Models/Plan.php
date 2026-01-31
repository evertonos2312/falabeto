<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'code',
        'name',
        'price_cents',
        'billing_period',
        'is_active',
        'trial_enabled',
        'trial_days',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_enabled' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(PlanItem::class);
    }
}
