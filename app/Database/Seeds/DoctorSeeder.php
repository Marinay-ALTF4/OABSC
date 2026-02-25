<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'           => 'Dr. John Smith',
                'specialization' => 'General Practitioner',
                'email'          => 'john.smith@clinic.com',
                'phone'          => '555-0101',
                'available'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'Dr. Sarah Johnson',
                'specialization' => 'Cardiologist',
                'email'          => 'sarah.johnson@clinic.com',
                'phone'          => '555-0102',
                'available'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'Dr. Michael Chen',
                'specialization' => 'Pediatrician',
                'email'          => 'michael.chen@clinic.com',
                'phone'          => '555-0103',
                'available'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'Dr. Emily Davis',
                'specialization' => 'Dermatologist',
                'email'          => 'emily.davis@clinic.com',
                'phone'          => '555-0104',
                'available'      => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('doctors')->insertBatch($data);
    }
}
