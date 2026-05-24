<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnnouncementsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Create announcements table if not exists
        if (! $db->tableExists('announcements')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'body' => [
                    'type' => 'TEXT',
                ],
                'content' => [
                    'type' => 'TEXT',
                ],
                'type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'info',
                ],
                'target_dashboard' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'all',
                ],
                'created_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('announcements');
        } else {
            // Check if column target_dashboard exists, if not add it
            if (! $db->fieldExists('target_dashboard', 'announcements')) {
                $this->forge->addColumn('announcements', [
                    'target_dashboard' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 50,
                        'default'    => 'all',
                    ],
                ]);
            }
            // Check if column body exists, if not add it
            if (! $db->fieldExists('body', 'announcements')) {
                $this->forge->addColumn('announcements', [
                    'body' => [
                        'type' => 'TEXT',
                    ],
                ]);
            }
        }

        // 2. Add announcement_id column to notifications table if it doesn't exist
        if (! $db->fieldExists('announcement_id', 'notifications')) {
            $this->forge->addColumn('notifications', [
                'announcement_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                ],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        
        if ($db->fieldExists('announcement_id', 'notifications')) {
            $this->forge->dropColumn('notifications', 'announcement_id');
        }

        $this->forge->dropTable('announcements', true);
    }
}
