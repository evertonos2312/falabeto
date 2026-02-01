<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'client_id',
        'email',
        'code_hash',
        'expires_at',
        'attempts',
        'send_count',
        'last_sent_at',
        'verified_at',
        'meta_json',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'verified_at' => 'datetime',
        'meta_json' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
