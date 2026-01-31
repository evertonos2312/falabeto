<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionItem extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'subscription_id',
        'item_type',
        'item_code',
        'description',
        'quantity',
        'unit_price_cents',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
