<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDoctorNameFromAppointments extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->fieldExists('doctor_name', 'appointments')) {
            $this->forge->dropColumn('appointments', ['doctor_name']);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (! $db->fieldExists('doctor_name', 'appointments')) {
            $this->forge->addColumn('appointments', [
                'doctor_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                    'after'      => 'user_id',
                ],
            ]);
        }

        if ($db->tableExists('user_profiles')) {
            $db->query(
                "UPDATE appointments a\n" .
                "LEFT JOIN user_profiles up ON up.user_id = a.doctor_id\n" .
                "SET a.doctor_name = up.name"
            );
            return;
        }

        if ($db->fieldExists('name', 'users')) {
            $db->query(
                "UPDATE appointments a\n" .
                "LEFT JOIN users u ON u.id = a.doctor_id\n" .
                "SET a.doctor_name = u.name"
            );
        }
    }
}
