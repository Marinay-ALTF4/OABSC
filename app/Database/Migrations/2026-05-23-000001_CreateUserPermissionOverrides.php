<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserPermissionOverrides extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('user_permission_overrides')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'permission_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                // 'deny' = explicitly blocked for this user
                // 'grant' = explicitly allowed for this user (overrides role block)
                'type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['deny', 'grant'],
                    'default'    => 'deny',
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['user_id', 'permission_code'], false, false, 'idx_upo_user_perm');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('user_permission_overrides');
        }
    }

    public function down()
    {
        $this->forge->dropTable('user_permission_overrides', true);
    }
}
