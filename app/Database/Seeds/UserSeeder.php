<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Libraries\UserDataCrypt;

class UserSeeder extends Seeder
{
    public function run()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $crypt = null;
        try {
            $crypt = new UserDataCrypt();
        } catch (\Throwable) {
            $crypt = null;
        }

        $users = [
            [
                'name'          => 'Admin',
                'email'         => 'admin@example.com',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role'          => 'admin',
            ],
            [
                'name'          => 'Assistant Admin',
                'email'         => 'assistant.admin@example.com',
                'password_hash' => password_hash('assistant123', PASSWORD_DEFAULT),
                'role'          => 'assistant_admin',
            ],
            [
                'name'          => 'Secretary',
                'email'         => 'secretary@example.com',
                'password_hash' => password_hash('secretary123', PASSWORD_DEFAULT),
                'role'          => 'secretary',
            ],
            [
                'name'          => 'Doctor',
                'email'         => 'doctor@example.com',
                'password_hash' => password_hash('doctor123', PASSWORD_DEFAULT),
                'role'          => 'doctor',
            ],
            [
                'name'           => 'Maria Santos',
                'email'          => 'dr.santos@example.com',
                'password_hash'  => password_hash('doctor123', PASSWORD_DEFAULT),
                'role'           => 'doctor',
                'specialization' => 'General Practitioner',
                'experience'     => '8 years',
                'degree'         => 'MD, University of Santo Tomas',
                'bio'            => 'Experienced GP focused on preventive care and family medicine.',
                'phone'          => '09171234567',
                'city'           => 'General Santos City',
            ],
            [
                'name'           => 'Jose Reyes',
                'email'          => 'dr.reyes@example.com',
                'password_hash'  => password_hash('doctor123', PASSWORD_DEFAULT),
                'role'           => 'doctor',
                'specialization' => 'Cardiologist',
                'experience'     => '12 years',
                'degree'         => 'MD, University of the Philippines',
                'bio'            => 'Specialist in cardiovascular diseases and heart health management.',
                'phone'          => '09189876543',
                'city'           => 'General Santos City',
            ],
            [
                'name'           => 'Ana Cruz',
                'email'          => 'dr.cruz@example.com',
                'password_hash'  => password_hash('doctor123', PASSWORD_DEFAULT),
                'role'           => 'doctor',
                'specialization' => 'Pediatrician',
                'experience'     => '6 years',
                'degree'         => 'MD, Ateneo School of Medicine',
                'bio'            => 'Dedicated to child health and development from infancy to adolescence.',
                'phone'          => '09201122334',
                'city'           => 'General Santos City',
            ],
            [
                'name'           => 'Ramon Garcia',
                'email'          => 'dr.garcia@example.com',
                'password_hash'  => password_hash('doctor123', PASSWORD_DEFAULT),
                'role'           => 'doctor',
                'specialization' => 'Dermatologist',
                'experience'     => '10 years',
                'degree'         => 'MD, Far Eastern University',
                'bio'            => 'Expert in skin conditions, cosmetic dermatology, and skin cancer screening.',
                'phone'          => '09215566778',
                'city'           => 'General Santos City',
            ],
            [
                'name'          => 'Client',
                'email'         => 'client@example.com',
                'password_hash' => password_hash('client123', PASSWORD_DEFAULT),
                'role'          => 'client',
            ],
        ];

        foreach ($users as $user) {
            if (! $db->tableExists('users')) {
                return;
            }

            // Avoid duplicates by email.
            $existing = null;
            if ($db->fieldExists('email', 'users')) {
                $existing = $db->table('users')->where('email', $user['email'])->get()->getRowArray();
            }

            $userId = $existing ? (int) $existing['id'] : null;

            if (! $existing) {
                $userRow = [];

                if ($db->fieldExists('email', 'users')) {
                    $userRow['email'] = $user['email'];
                }
                if ($db->fieldExists('role', 'users')) {
                    $userRow['role'] = $user['role'];
                }
                if ($db->fieldExists('username', 'users')) {
                    $userRow['username'] = $user['email'];
                }
                if ($db->fieldExists('status', 'users')) {
                    $userRow['status'] = 'active';
                }
                if ($db->fieldExists('created_at', 'users')) {
                    $userRow['created_at'] = $now;
                }
                if ($db->fieldExists('updated_at', 'users')) {
                    $userRow['updated_at'] = $now;
                }
                // Legacy column support (some schemas still keep password_hash on users)
                if ($db->fieldExists('password_hash', 'users')) {
                    $userRow['password_hash'] = $user['password_hash'];
                }
                // If schema still has name on users, set it too.
                if ($db->fieldExists('name', 'users')) {
                    $userRow['name'] = $user['name'];
                }

                $db->table('users')->insert($userRow);
                $userId = (int) $db->insertID();
            }

            if (! $userId) {
                continue;
            }

            // Seed user_profiles (normalized name + profile fields)
            if ($db->tableExists('user_profiles')) {
                $profileRow = ['user_id' => $userId];

                if ($db->fieldExists('name', 'user_profiles')) {
                    $profileRow['name'] = $user['name'];
                }
                foreach (['bio', 'phone', 'city', 'address', 'profile_photo'] as $field) {
                    if (array_key_exists($field, $user) && $db->fieldExists($field, 'user_profiles')) {
                        $profileRow[$field] = $user[$field];
                    }
                }

                // Optionally encrypt sensitive profile fields when encryption service is available.
                if ($crypt) {
                    $profileRow = $crypt->encryptFields($profileRow, ['bio', 'phone', 'city', 'address']);
                }

                $existingProfile = $db->table('user_profiles')->where('user_id', $userId)->get()->getRowArray();
                if ($existingProfile) {
                    unset($profileRow['user_id']);
                    if (! empty($profileRow)) {
                        $db->table('user_profiles')->where('user_id', $userId)->update($profileRow);
                    }
                } else {
                    $db->table('user_profiles')->insert($profileRow);
                }
            }

            // Seed user_auth if present (normalized credentials)
            if ($db->tableExists('user_auth') && $db->fieldExists('password_hash', 'user_auth')) {
                $authRow = [
                    'user_id'       => $userId,
                    'password_hash' => $user['password_hash'],
                ];

                if ($db->fieldExists('hash_algo', 'user_auth')) {
                    $authRow['hash_algo'] = 'bcrypt';
                }
                if ($db->fieldExists('hash_params', 'user_auth')) {
                    $authRow['hash_params'] = '{}';
                }
                if ($db->fieldExists('pepper_version', 'user_auth')) {
                    $authRow['pepper_version'] = 1;
                }
                if ($db->fieldExists('must_rotate', 'user_auth')) {
                    $authRow['must_rotate'] = 0;
                }
                if ($db->fieldExists('created_at', 'user_auth')) {
                    $authRow['created_at'] = $now;
                }
                if ($db->fieldExists('updated_at', 'user_auth')) {
                    $authRow['updated_at'] = $now;
                }

                $existingAuth = $db->table('user_auth')->where('user_id', $userId)->get()->getRowArray();
                if ($existingAuth) {
                    unset($authRow['user_id']);
                    if (! empty($authRow)) {
                        $db->table('user_auth')->where('user_id', $userId)->update($authRow);
                    }
                } else {
                    $db->table('user_auth')->insert($authRow);
                }
            }

            // Seed doctor_profiles for doctors
            if (($user['role'] ?? null) === 'doctor' && $db->tableExists('doctor_profiles')) {
                $docRow = ['user_id' => $userId];
                foreach (['specialization', 'experience', 'degree'] as $field) {
                    if (array_key_exists($field, $user) && $db->fieldExists($field, 'doctor_profiles')) {
                        $docRow[$field] = $user[$field];
                    }
                }

                $existingDoc = $db->table('doctor_profiles')->where('user_id', $userId)->get()->getRowArray();
                if ($existingDoc) {
                    unset($docRow['user_id']);
                    if (! empty($docRow)) {
                        $db->table('doctor_profiles')->where('user_id', $userId)->update($docRow);
                    }
                } else {
                    $db->table('doctor_profiles')->insert($docRow);
                }
            }
        }
    }
}

