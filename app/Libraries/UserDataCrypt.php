<?php

namespace App\Libraries;

use RuntimeException;

class UserDataCrypt
{
    private const PREFIX = 'enc:';

    public function encrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if ($this->isEncrypted($value)) {
            return $value;
        }

        $encrypter = $this->encrypter();

        return self::PREFIX . base64_encode($encrypter->encrypt($value));
    }

    public function decrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (! $this->isEncrypted($value)) {
            return $value;
        }

        $payload = base64_decode(substr($value, strlen(self::PREFIX)), true);

        if ($payload === false) {
            return $value;
        }

        try {
            return (string) $this->encrypter()->decrypt($payload);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function encryptFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->encrypt(is_string($data[$field]) ? trim($data[$field]) : $data[$field]);
            }
        }

        return $data;
    }

    public function decryptFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->decrypt(is_string($data[$field]) ? $data[$field] : null);
            }
        }

        return $data;
    }

    public function isEncrypted(string $value): bool
    {
        return str_starts_with($value, self::PREFIX);
    }

    private function encrypter()
    {
        $encrypter = service('encrypter');

        if ($encrypter === null) {
            throw new RuntimeException('Encryption service is not available.');
        }

        return $encrypter;
    }
}
