<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoginEventEncryption extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('login_events')) {
            return;
        }

        // If a FK exists (or is about to be added), older data may contain orphan user_id values.
        // Make user_id nullable and null out any rows that reference missing users.
        if ($db->fieldExists('user_id', 'login_events') && $db->tableExists('users')) {
            try {
                $db->query('ALTER TABLE login_events MODIFY COLUMN user_id INT(11) UNSIGNED NULL');
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                $db->query(
                    'UPDATE login_events le\n'
                    . 'LEFT JOIN users u ON u.id = le.user_id\n'
                    . 'SET le.user_id = NULL\n'
                    . 'WHERE le.user_id IS NOT NULL AND u.id IS NULL'
                );
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Ensure the columns exist (older schemas may not have them yet)
        if (! $db->fieldExists('email_attempted', 'login_events')) {
            $this->forge->addColumn('login_events', [
                'email_attempted' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                    'null'       => true,
                ],
            ]);
        }

        if (! $db->fieldExists('user_agent', 'login_events')) {
            $this->forge->addColumn('login_events', [
                'user_agent' => [
                    'type'       => 'TEXT',
                    'null'       => true,
                ],
            ]);
        }

        // Comments are informational only; do not block migrations if MODIFY fails.
        if ($db->fieldExists('email_attempted', 'login_events')) {
            try {
                $db->query("ALTER TABLE login_events MODIFY COLUMN email_attempted VARCHAR(191) NULL COMMENT 'Encrypted email address attempted for login'");
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($db->fieldExists('user_agent', 'login_events')) {
            try {
                // Keep this as TEXT to match the original table definition in this codebase.
                $db->query("ALTER TABLE login_events MODIFY COLUMN user_agent TEXT NULL COMMENT 'Encrypted user agent string'");
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('login_events')) {
            return;
        }

        // Remove encryption comments (best-effort)
        if ($db->fieldExists('email_attempted', 'login_events')) {
            try {
                $db->query("ALTER TABLE login_events MODIFY COLUMN email_attempted VARCHAR(191) NULL COMMENT ''");
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($db->fieldExists('user_agent', 'login_events')) {
            try {
                $db->query("ALTER TABLE login_events MODIFY COLUMN user_agent TEXT NULL COMMENT ''");
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
}
