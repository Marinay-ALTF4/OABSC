<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupNormalizedSchema extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $this->cleanupUserProfiles($db);
        $this->cleanupAnnouncements($db);
        $this->cleanupNotificationLinks($db);
        $this->cleanupLegacyUserCredentials($db);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $this->restoreLegacyUserCredentials($db);
        $this->restoreNotificationAnnouncementColumn($db);
        $this->restoreAnnouncementsContentColumn($db);
        $this->restoreUserProfileAddressColumn($db);
    }

    private function cleanupUserProfiles($db): void
    {
        if (! $db->tableExists('user_profiles')) {
            return;
        }

        if (! $db->fieldExists('street', 'user_profiles')) {
            $this->forge->addColumn('user_profiles', [
                'street' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'phone',
                ],
            ]);
        }

        foreach (['province', 'zip', 'country'] as $column) {
            if (! $db->fieldExists($column, 'user_profiles')) {
                $this->forge->addColumn('user_profiles', [
                    $column => [
                        'type' => 'TEXT',
                        'null' => true,
                        'after' => 'street',
                    ],
                ]);
            }
        }

        if ($db->fieldExists('address', 'user_profiles')) {
            $db->query('UPDATE user_profiles SET street = COALESCE(NULLIF(street, ""), address)');
            $this->forge->dropColumn('user_profiles', ['address']);
        }
    }

    private function cleanupAnnouncements($db): void
    {
        if (! $db->tableExists('announcements')) {
            return;
        }

        if ($db->fieldExists('content', 'announcements') && $db->fieldExists('body', 'announcements')) {
            $db->query('UPDATE announcements SET body = COALESCE(NULLIF(body, ""), content)');
            $this->forge->dropColumn('announcements', ['content']);
        }
    }

    private function cleanupNotificationLinks($db): void
    {
        if (! $db->tableExists('notifications')) {
            return;
        }

        if (! $db->tableExists('notification_announcement_links')) {
            $this->forge->addField([
                'notification_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'announcement_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'linked_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('notification_id', true);
            $this->forge->addForeignKey('notification_id', 'notifications', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('announcement_id', 'announcements', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('notification_announcement_links', true);
        }

        if ($db->fieldExists('announcement_id', 'notifications')) {
            $db->query(
                "INSERT INTO notification_announcement_links (notification_id, announcement_id, linked_at)
SELECT id, announcement_id, created_at
FROM notifications
WHERE announcement_id IS NOT NULL
ON DUPLICATE KEY UPDATE announcement_id = VALUES(announcement_id)"
            );
            $this->forge->dropColumn('notifications', ['announcement_id']);
        }
    }

    private function cleanupLegacyUserCredentials($db): void
    {
        if ($db->tableExists('user_credentials')) {
            $this->forge->dropTable('user_credentials', true);
        }
    }

    private function restoreLegacyUserCredentials($db): void
    {
        if ($db->tableExists('user_credentials')) {
            return;
        }

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
            'hash_algo' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'argon2id',
            ],
            'hash_params' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'pepper_version' => [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'must_rotate' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('user_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_credentials', true);

        if ($db->tableExists('user_auth')) {
            $db->query(
                "INSERT INTO user_credentials (user_id, password_hash, hash_algo, hash_params, pepper_version, must_rotate, created_at, updated_at)
SELECT user_id, password_hash, 'argon2id', '{}', 1, 0, created_at, updated_at
FROM user_auth
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)"
            );
        }
    }

    private function restoreNotificationAnnouncementColumn($db): void
    {
        if (! $db->tableExists('notifications')) {
            return;
        }

        if (! $db->fieldExists('announcement_id', 'notifications')) {
            $this->forge->addColumn('notifications', [
                'announcement_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'type',
                ],
            ]);
        }

        if ($db->tableExists('notification_announcement_links')) {
            $db->query(
                "UPDATE notifications n
INNER JOIN notification_announcement_links l ON l.notification_id = n.id
SET n.announcement_id = l.announcement_id"
            );
            $this->forge->dropTable('notification_announcement_links', true);
        }
    }

    private function restoreAnnouncementsContentColumn($db): void
    {
        if (! $db->tableExists('announcements')) {
            return;
        }

        if (! $db->fieldExists('content', 'announcements')) {
            $this->forge->addColumn('announcements', [
                'content' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'body',
                ],
            ]);
        }

        if ($db->fieldExists('body', 'announcements')) {
            $db->query('UPDATE announcements SET content = body');
        }
    }

    private function restoreUserProfileAddressColumn($db): void
    {
        if (! $db->tableExists('user_profiles')) {
            return;
        }

        if (! $db->fieldExists('address', 'user_profiles')) {
            $this->forge->addColumn('user_profiles', [
                'address' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'city',
                ],
            ]);
        }

        if ($db->fieldExists('street', 'user_profiles')) {
            $db->query('UPDATE user_profiles SET address = street');
        }

        $dropColumns = [];
        foreach (['street', 'province', 'zip', 'country'] as $column) {
            if ($db->fieldExists($column, 'user_profiles')) {
                $dropColumns[] = $column;
            }
        }

        if (! empty($dropColumns)) {
            $this->forge->dropColumn('user_profiles', $dropColumns);
        }
    }
}
