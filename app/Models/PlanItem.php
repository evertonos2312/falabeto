<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanItem extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'plan_id',
        'item_code',
        'item_type',
        'value_int',
        'value_bool',
        'value_string',
    ];

    protected $casts = [
        'value_bool' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
