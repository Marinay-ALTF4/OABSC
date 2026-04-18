<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDoctorIdToAppointments extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (! $db->fieldExists('doctor_id', 'appointments')) {
            $this->forge->addColumn('appointments', [
                'doctor_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'doctor_name',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('appointments', ['doctor_id']);
    }
}
