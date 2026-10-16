<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypt a bank detail at rest while tolerating rows written before encryption.
 *
 * A value is encrypted on write. On read it is decrypted, but a value that is
 * not valid ciphertext (a row written before this cast existed) is returned
 * as-is, so old plaintext rows keep working and are re-encrypted the next time
 * they are saved.
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class EncryptedString implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (\Throwable) {
            // Legacy plaintext row: keep it readable.
            return (string) $value;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Crypt::encryptString((string) $value);
    }
}
