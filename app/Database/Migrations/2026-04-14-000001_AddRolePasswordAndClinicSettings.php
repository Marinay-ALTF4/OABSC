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

        // Create clinic_settings table
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'key'        => ['type' => 'VARCHAR', 'constraint' => 100],
            'value'      => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('clinic_settings', true);

        // Insert default clinic access code
        $db->table('clinic_settings')->insert([
            'key'   => 'clinic_access_code',
            'value' => password_hash('CLINIC2026', PASSWORD_DEFAULT),
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['role_password']);
        $this->forge->dropTable('clinic_settings', true);
    }
}
