<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRolePasswordAndClinicSettings extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Add role_password to users
        if (! $db->fieldExists('role_password', 'users')) {
            $this->forge->addColumn('users', [
                'role_password' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'password_hash',
                ],
            ]);
        }


    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->fieldExists('role_password', 'users')) {
            $this->forge->dropColumn('users', ['role_password']);
        }
    }
}
