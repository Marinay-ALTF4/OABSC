<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClinicSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $db   = \Config\Database::connect();
        $hash = password_hash('CLINIC2026', PASSWORD_DEFAULT);

        $existing = $db->query("SELECT id FROM clinic_settings WHERE `key` = 'clinic_access_code'")->getRowArray();

        if ($existing) {
            $db->query("UPDATE clinic_settings SET `value` = ? WHERE `key` = 'clinic_access_code'", [$hash]);
        } else {
            $db->query("INSERT INTO clinic_settings (`key`, `value`) VALUES ('clinic_access_code', ?)", [$hash]);
        }

        echo "Clinic settings seeded successfully.\n";
        echo "Clinic Access Code: CLINIC2026\n";
        echo "Admin Role Password: admin123\n";
        echo "Assistant Admin Role Password: assistant123\n";
    }
}
