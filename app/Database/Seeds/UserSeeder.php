<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        $users = [
            [
                'name'          => 'Admin',
                'email'         => 'admin@example.com',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role'          => 'admin',
            ],
            [
                'name'          => 'Assistant Admin',
                'email'         => 'assistant@example.com',
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
                'name'          => 'Client',
                'email'         => 'client@example.com',
                'password_hash' => password_hash('client123', PASSWORD_DEFAULT),
                'role'          => 'client',
            ],
        ];

        foreach ($users as $user) {
            // Avoid duplicate users if seeder is run multiple times
            if (! $userModel->where('email', $user['email'])->first()) {
                $userModel->insert($user);
            }
        }
    }
}

