<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Crypt;

trait EncryptedAttributes
{
    protected function encryptAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        return Crypt::encryptString($trimmed);
    }

    protected function decryptAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::decryptString($value);
    }
}
