<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddArchivedAtToAppointments extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (! $db->fieldExists('archived_at', 'appointments')) {
            $this->forge->addColumn('appointments', [
                'archived_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'default' => null,
                    'after' => 'status',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('appointments', ['archived_at']);
    }
}
