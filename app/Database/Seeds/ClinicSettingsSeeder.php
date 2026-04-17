<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClinicSettingsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Set default clinic access code: CLINIC2026
        $existing = $db->table('clinic_settings')->where('key', 'clinic_access_code')->get()->getRowArray();
        if ($existing) {
            $db->table('clinic_settings')->where('key', 'clinic_access_code')->update([
                'value' => password_hash('CLINIC2026', PASSWORD_DEFAULT),
            ]);
        } else {
            $db->table('clinic_settings')->insert([
                'key'   => 'clinic_access_code',
                'value' => password_hash('CLINIC2026', PASSWORD_DEFAULT),
            ]);
        }

        // Set admin role password - same as admin login password (Admin123)
        $admin = $db->table('users')->where('role', 'admin')->get()->getRowArray();
        if ($admin) {
            $db->table('users')->where('id', $admin['id'])->update([
                'role_password' => password_hash('Admin123', PASSWORD_DEFAULT),
            ]);
        }

        // Set default assistant admin role password if exists
        $assistantAdmins = $db->table('users')->where('role', 'assistant_admin')->get()->getResultArray();
        foreach ($assistantAdmins as $asst) {
            if (empty($asst['role_password'])) {
                $db->table('users')->where('id', $asst['id'])->update([
                    'role_password' => password_hash('Assistant123', PASSWORD_DEFAULT),
                ]);
            }
        }

        echo "Clinic settings seeded successfully.\n";
        echo "Clinic Access Code: CLINIC2026\n";
        echo "Admin Role Password: Admin123\n";
        echo "Assistant Admin default Role Password: Assistant123 (only for existing ones without password)\n";
    }
}
