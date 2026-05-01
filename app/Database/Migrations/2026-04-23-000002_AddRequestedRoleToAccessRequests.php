<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRequestedRoleToAccessRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('access_requests', [
            'requested_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'user_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('access_requests', 'requested_role');
    }
}
