<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccessRequestsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'resource'     => ['type' => 'VARCHAR', 'constraint' => 100], // e.g. 'patient_records', 'clinic_reports'
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'denied'], 'default' => 'pending'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('access_requests', true);
    }

    public function down()
    {
        $this->forge->dropTable('access_requests', true);
    }
}
