<?php

namespace App\Models;

use App\Libraries\UserDataCrypt;
use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'name', 'email', 'password_hash', 'role_password', 'role',
        'profile_photo', 'specialization', 'experience', 'degree', 'bio',
        'phone', 'city', 'address',
        'deleted_at',
    ];
    
    protected $useTimestamps = true;

    protected $beforeInsert = ['encryptSensitiveFields'];
    protected $beforeUpdate = ['encryptSensitiveFields'];
    protected $afterFind    = ['decryptSensitiveFields'];

    private array $sensitiveFields = [
        'phone',
        'city',
        'address',
        'specialization',
        'experience',
        'degree',
        'bio',
    ];

    protected function encryptSensitiveFields(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        $crypt = new UserDataCrypt();
        $data['data'] = $crypt->encryptFields($data['data'], $this->sensitiveFields);

        return $data;
    }

    protected function decryptSensitiveFields(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        $crypt = new UserDataCrypt();

        if ($data['data'] === null) {
            return $data;
        }

        if (isset($data['data'][0]) && is_array($data['data'][0])) {
            foreach ($data['data'] as $index => $row) {
                $data['data'][$index] = $crypt->decryptFields($row, $this->sensitiveFields);
            }

            return $data;
        }

        if (is_array($data['data'])) {
            $data['data'] = $crypt->decryptFields($data['data'], $this->sensitiveFields);
        }

        return $data;
    }
}

