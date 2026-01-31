<?php

namespace App\Models;

use App\Models\Concerns\EncryptedAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use HasFactory, HasUlids, EncryptedAttributes, SoftDeletes;

    protected $fillable = [
        'client_id',
        'creditor_name',
        'amount_cents',
        'due_date',
        'status',
        'notes',
        'created_via',
        'source_message_log_id',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function setCreditorNameAttribute(?string $value): void
    {
        $this->attributes['creditor_name_encrypted'] = $this->encryptAttribute($value);
    }

    public function getCreditorNameAttribute(): ?string
    {
        return $this->decryptAttribute($this->attributes['creditor_name_encrypted'] ?? null);
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
