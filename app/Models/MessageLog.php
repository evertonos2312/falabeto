<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'client_id',
        'phone_e164',
        'channel',
        'direction',
        'body',
        'body_snippet',
        'body_hash',
        'llm_used',
        'llm_model',
        'llm_cost_cents',
        'meta_json',
    ];

    protected $casts = [
        'llm_used' => 'boolean',
        'meta_json' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
