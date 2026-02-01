<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'group',
        'key',
        'type',
        'value_text',
        'value_int',
        'value_bool',
        'updated_by_admin_id',
    ];

    protected $casts = [
        'value_bool' => 'boolean',
    ];

    public function scopeKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    public function getValue(): mixed
    {
        return match ($this->type) {
            'string', 'file' => $this->value_text,
            'int' => $this->value_int,
            'bool' => (bool) $this->value_bool,
            'json' => $this->value_text ? json_decode($this->value_text, true) : null,
            default => $this->value_text,
        };
    }

    public function setValue(mixed $value): void
    {
        $this->value_text = null;
        $this->value_int = null;
        $this->value_bool = null;

        match ($this->type) {
            'string', 'file' => $this->value_text = $value !== null ? (string) $value : null,
            'int' => $this->value_int = $value !== null ? (int) $value : null,
            'bool' => $this->value_bool = $value !== null ? (bool) $value : null,
            'json' => $this->value_text = $value !== null ? json_encode($value) : null,
            default => $this->value_text = $value !== null ? (string) $value : null,
        };
    }
}
