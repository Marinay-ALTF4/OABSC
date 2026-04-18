<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDoctorSchedulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'doctor_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'day'        => ['type' => 'VARCHAR', 'constraint' => 20], // Monday, Tuesday, etc.
            'start_time' => ['type' => 'TIME'],
            'end_time'   => ['type' => 'TIME'],
            'is_available' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('doctor_schedules', true);
    }

    public function down()
    {
        $this->forge->dropTable('doctor_schedules', true);
    }
}
