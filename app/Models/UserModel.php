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
        'name', 'email', 'password_hash', 'role',
        'profile_photo', 'specialization', 'experience', 'degree', 'bio',
        'phone', 'city', 'address',
        'failed_login_count', 'lock_until', 'last_login_at', 'status',
        'cancel_attempts', 'cancel_reset_at',
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
        } elseif (is_array($data['data'])) {
            $data['data'] = $crypt->decryptFields($data['data'], $this->sensitiveFields);
        }

        $data = $this->hydrateProfileName($data);

        return $data;
    }

    private function hydrateProfileName(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        $db = \Config\Database::connect();

        if (isset($data['data'][0]) && is_array($data['data'][0])) {
            $ids = [];
            foreach ($data['data'] as $row) {
                if (isset($row['id'])) {
                    $ids[] = (int) $row['id'];
                }
            }

            if (empty($ids)) {
                return $data;
            }

            $profiles = $db->table('user_profiles')
                ->select('user_id, name')
                ->whereIn('user_id', $ids)
                ->get()
                ->getResultArray();

            $profileMap = [];
            foreach ($profiles as $profile) {
                $profileMap[(int) $profile['user_id']] = $profile['name'] ?? '';
            }

            foreach ($data['data'] as $index => $row) {
                if (! isset($row['name']) || $row['name'] === '') {
                    $data['data'][$index]['name'] = $profileMap[(int) ($row['id'] ?? 0)] ?? ($row['username'] ?? '');
                }
            }

            return $data;
        }

        if (is_array($data['data']) && (! isset($data['data']['name']) || $data['data']['name'] === '')) {
            $userId = (int) ($data['data']['id'] ?? 0);
            if ($userId > 0) {
                $profile = $db->table('user_profiles')
                    ->select('name')
                    ->where('user_id', $userId)
                    ->get()
                    ->getRowArray();

                $data['data']['name'] = $profile['name'] ?? ($data['data']['username'] ?? '');
            }
        }

        return $data;
    }
}

