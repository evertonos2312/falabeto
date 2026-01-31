<?php

namespace App\Models;

use App\Models\Concerns\EncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, HasUlids, EncryptedAttributes, SoftDeletes;

    protected $fillable = [
        'client_id',
        'type',
        'amount_cents',
        'occurred_at',
        'category',
        'description',
        'notes',
        'created_via',
        'source_message_log_id',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description_encrypted'] = $this->encryptAttribute($value);
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->decryptAttribute($this->attributes['description_encrypted'] ?? null);
    }

    public function setNotesAttribute(?string $value): void
    {
        $this->attributes['notes_encrypted'] = $this->encryptAttribute($value);
    }

    public function getNotesAttribute(): ?string
    {
        return $this->decryptAttribute($this->attributes['notes_encrypted'] ?? null);
    }
}
