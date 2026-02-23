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
                'password_hash' => password_hash('Admin123', PASSWORD_DEFAULT),
                'role'          => 'admin',
            ],
            [
                'name'          => 'Client',
                'email'         => 'client@example.com',
                'password_hash' => password_hash('Client123!', PASSWORD_DEFAULT),
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

