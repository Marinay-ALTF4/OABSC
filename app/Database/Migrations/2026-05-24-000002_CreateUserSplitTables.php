<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSplitTables extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('user_auth')) {
            $this->forge->addField([
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'password_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'role_password' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'mfa_code_hash' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'mfa_expires_at' => [
                    'type' => 'INT',
                    'null' => true,
                ],
                'failed_login_count' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'cancel_attempts' => [
                    'type'       => 'TINYINT',
                    'constraint' => 3,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'cancel_reset_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
                'lock_until' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'last_login_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'password_changed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'is_email_verified' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
            ]);

            $this->forge->addKey('user_id', true);
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('user_auth', true);
        }

        if (! $db->tableExists('user_profiles')) {
            $this->forge->addField([
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'bio' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'phone' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'city' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'address' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'profile_photo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);

            $this->forge->addKey('user_id', true);
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('user_profiles', true);
        }

        if (! $db->tableExists('doctor_profiles')) {
            $this->forge->addField([
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'specialization' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'experience' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'degree' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('user_id', true);
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('doctor_profiles', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('doctor_profiles', true);
        $this->forge->dropTable('user_profiles', true);
        $this->forge->dropTable('user_auth', true);
    }
}
