<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAccessRequestsForPermissions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Add permission_code column to store which specific permission is being requested
        if (! $db->fieldExists('permission_code', 'access_requests')) {
            $this->forge->addColumn('access_requests', [
                'permission_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'resource',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('access_requests', ['permission_code']);
    }
}
