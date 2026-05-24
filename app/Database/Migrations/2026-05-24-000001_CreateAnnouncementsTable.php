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
            if ($db->fieldExists('content', 'announcements') && ! $db->fieldExists('body', 'announcements')) {
                $this->forge->addColumn('announcements', [
                    'body' => [
                        'type' => 'TEXT',
                        'null' => true,
                        'after' => 'title',
                    ],
                ]);
                $db->query('UPDATE announcements SET body = content WHERE body IS NULL OR body = ""');
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $this->forge->dropTable('announcements', true);
    }
}
