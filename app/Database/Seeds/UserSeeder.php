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
            // Avoid duplicate users if seeder is run multiple times
            if (! $userModel->where('email', $user['email'])->first()) {
                $userModel->insert($user);
            }
        }
    }
}

