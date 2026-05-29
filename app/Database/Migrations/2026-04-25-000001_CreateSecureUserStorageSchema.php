<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSecureUserStorageSchema extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $this->addUsersSecurityColumns($db);
        $this->createUserAuthTable($db);
        $this->createRbacTables($db);
        $this->createAuthSessionsTable($db);
        $this->createLoginEventsTable($db);

        $this->seedDefaultRoles($db);
        $this->backfillUsersPublicId($db);
        $this->backfillUserAuth($db);
        $this->backfillUserRoles($db);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $this->forge->dropTable('login_events', true);
        $this->forge->dropTable('auth_sessions', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropTable('user_auth', true);

        $columns = [];
        foreach (['public_id', 'username', 'status', 'is_email_verified', 'failed_login_count', 'lock_until', 'last_login_at', 'password_changed_at'] as $column) {
            if ($db->fieldExists($column, 'users')) {
                $columns[] = $column;
            }
        }

        if (! empty($columns)) {
            $this->forge->dropColumn('users', $columns);
        }
    }

    private function addUsersSecurityColumns($db): void
    {
        $fields = [];

        if (! $db->fieldExists('public_id', 'users')) {
            $fields['public_id'] = [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
            ];
        }

        if (! $db->fieldExists('username', 'users')) {
            $fields['username'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ];
        }

        if (! $db->fieldExists('status', 'users')) {
            $fields['status'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
            ];
        }

        if (! $db->fieldExists('is_email_verified', 'users')) {
            $fields['is_email_verified'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ];
        }

        if (! $db->fieldExists('failed_login_count', 'users')) {
            $fields['failed_login_count'] = [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'constraint' => 5,
                'default'    => 0,
            ];
        }

        if (! $db->fieldExists('lock_until', 'users')) {
            $fields['lock_until'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $db->fieldExists('last_login_at', 'users')) {
            $fields['last_login_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $db->fieldExists('password_changed_at', 'users')) {
            $fields['password_changed_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('users', $fields);
        }

        // Only create indexes if the columns exist
        if ($db->fieldExists('public_id', 'users')) {
            $this->createIndexIfMissing($db, 'users', 'uq_users_public_id', 'UNIQUE', '(public_id)');
        }
        if ($db->fieldExists('username', 'users')) {
            $this->createIndexIfMissing($db, 'users', 'uq_users_username', 'UNIQUE', '(username)');
        }
        if ($db->fieldExists('status', 'users') && $db->fieldExists('created_at', 'users')) {
            $this->createIndexIfMissing($db, 'users', 'idx_users_status_created', 'INDEX', '(status, created_at)');
        }
        if ($db->fieldExists('last_login_at', 'users')) {
            $this->createIndexIfMissing($db, 'users', 'idx_users_last_login', 'INDEX', '(last_login_at)');
        }
    }

    private function createUserAuthTable($db): void
    {
        // If the table already exists (e.g. created by an earlier migration), make sure
        // it has the newer columns expected by backfills and seeders.
        if ($db->tableExists('user_auth')) {
            $missing = [];

            if (! $db->fieldExists('hash_algo', 'user_auth')) {
                $missing['hash_algo'] = ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true];
            }
            if (! $db->fieldExists('hash_params', 'user_auth')) {
                $missing['hash_params'] = ['type' => 'TEXT', 'null' => true];
            }
            if (! $db->fieldExists('pepper_version', 'user_auth')) {
                $missing['pepper_version'] = ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 1];
            }
            if (! $db->fieldExists('must_rotate', 'user_auth')) {
                $missing['must_rotate'] = ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0];
            }
            if (! $db->fieldExists('created_at', 'user_auth')) {
                $missing['created_at'] = ['type' => 'DATETIME', 'null' => true];
            }
            if (! $db->fieldExists('updated_at', 'user_auth')) {
                $missing['updated_at'] = ['type' => 'DATETIME', 'null' => true];
            }

            if (! empty($missing)) {
                $this->forge->addColumn('user_auth', $missing);
            }

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
                'constraint' => 50,
                'null'       => true,
            ],
            'hash_params' => [
                'type' => 'TEXT',
                'null' => true,
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
                'unsigned'   => true,
                'default'    => 0,
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
        $this->forge->createTable('user_auth', true);
    }

    private function createRbacTables($db): void
    {
        if (! $db->tableExists('roles')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                ],
                'description' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name', 'uq_roles_name');
            $this->forge->createTable('roles', true);
        }

        if (! $db->tableExists('permissions')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'description' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('code', 'uq_permissions_code');
            $this->forge->createTable('permissions', true);
        }

        if (! $db->tableExists('user_roles')) {
            $this->forge->addField([
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'assigned_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey(['user_id', 'role_id'], true);
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('user_roles', true);
        }

        if (! $db->tableExists('role_permissions')) {
            $this->forge->addField([
                'role_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'permission_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
            ]);
            $this->forge->addKey(['role_id', 'permission_id'], true);
            $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('role_permissions', true);
        }
    }

    private function createAuthSessionsTable($db): void
    {
        if ($db->tableExists('auth_sessions')) {
            return;
        }

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
            'refresh_token_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'device_fingerprint_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'ip_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'issued_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_active_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'revoke_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_sessions', true);
    }

    private function createLoginEventsTable($db): void
    {
        if ($db->tableExists('login_events')) {
            $this->normalizeLoginEventsUserId($db);
            $this->ensureLoginEventsForeignKey($db);
            return;
        }

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
                'null'       => true,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'reason_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'ip_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('login_events', true);
    }

    private function normalizeLoginEventsUserId($db): void
    {
        if (! $db->tableExists('users') || ! $db->fieldExists('user_id', 'login_events')) {
            return;
        }

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

    private function ensureLoginEventsForeignKey($db): void
    {
        if (! $db->tableExists('users') || ! $db->fieldExists('user_id', 'login_events')) {
            return;
        }

        if ($this->foreignKeyExists($db, 'login_events', 'login_events_user_id_foreign')) {
            return;
        }

        $orphans = $db->query(
            'SELECT COUNT(*) AS cnt FROM login_events le\n'
            . 'LEFT JOIN users u ON u.id = le.user_id\n'
            . 'WHERE le.user_id IS NOT NULL AND u.id IS NULL'
        )->getRowArray();

        if (! empty($orphans) && (int) $orphans['cnt'] > 0) {
            return;
        }

        try {
            $db->query(
                'ALTER TABLE login_events\n'
                . 'ADD CONSTRAINT `login_events_user_id_foreign`\n'
                . 'FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)\n'
                . 'ON DELETE CASCADE ON UPDATE CASCADE'
            );
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function foreignKeyExists($db, string $table, string $constraint): bool
    {
        $rows = $db->query(
            'SELECT CONSTRAINT_NAME\n'
            . 'FROM information_schema.KEY_COLUMN_USAGE\n'
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$table, $constraint]
        )->getResultArray();

        return ! empty($rows);
    }

    private function seedDefaultRoles($db): void
    {
        if (! $db->tableExists('roles')) {
            return;
        }

        $defaults = [
            ['name' => 'admin', 'description' => 'Administrator'],
            ['name' => 'assistant_admin', 'description' => 'Assistant administrator'],
            ['name' => 'secretary', 'description' => 'Secretary'],
            ['name' => 'doctor', 'description' => 'Doctor'],
            ['name' => 'client', 'description' => 'Client'],
        ];

        foreach ($defaults as $role) {
            $exists = $db->table('roles')->where('name', $role['name'])->countAllResults() > 0;
            if (! $exists) {
                $db->table('roles')->insert($role);
            }
        }
    }

    private function backfillUsersPublicId($db): void
    {
        if (! $db->fieldExists('public_id', 'users')) {
            return;
        }

        $rows = $db->table('users')->select('id')->where('public_id IS NULL', null, false)->get()->getResultArray();
        foreach ($rows as $row) {
            $db->table('users')->where('id', (int) $row['id'])->update(['public_id' => $this->uuidV4()]);
        }
    }

    private function backfillUserAuth($db): void
    {
        if (! $db->tableExists('user_auth') || ! $db->fieldExists('password_hash', 'users')) {
            return;
        }

        $insertCols = ['user_id', 'password_hash'];
        $selectExpr = ['u.id', 'u.password_hash'];

        if ($db->fieldExists('hash_algo', 'user_auth')) {
            $insertCols[] = 'hash_algo';
            $selectExpr[] = "'argon2id'";
        }
        if ($db->fieldExists('hash_params', 'user_auth')) {
            $insertCols[] = 'hash_params';
            $selectExpr[] = "'{}'";
        }
        if ($db->fieldExists('pepper_version', 'user_auth')) {
            $insertCols[] = 'pepper_version';
            $selectExpr[] = '1';
        }
        if ($db->fieldExists('must_rotate', 'user_auth')) {
            $insertCols[] = 'must_rotate';
            $selectExpr[] = '0';
        }
        if ($db->fieldExists('created_at', 'user_auth') && $db->fieldExists('created_at', 'users')) {
            $insertCols[] = 'created_at';
            $selectExpr[] = 'u.created_at';
        }
        if ($db->fieldExists('updated_at', 'user_auth') && $db->fieldExists('updated_at', 'users')) {
            $insertCols[] = 'updated_at';
            $selectExpr[] = 'u.updated_at';
        }

        $db->query(
            "INSERT INTO user_auth (" . implode(', ', $insertCols) . ")\n" .
            "SELECT " . implode(', ', $selectExpr) . "\n" .
            "FROM users u\n" .
            "LEFT JOIN user_auth ua ON ua.user_id = u.id\n" .
            "WHERE ua.user_id IS NULL AND u.password_hash IS NOT NULL AND u.password_hash <> ''"
        );
    }

    private function backfillUserRoles($db): void
    {
        if (! $db->tableExists('user_roles') || ! $db->tableExists('roles')) {
            return;
        }

        $roles = $db->table('roles')->select('id, name')->get()->getResultArray();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role['name']] = (int) $role['id'];
        }

        if (empty($roleMap)) {
            return;
        }

        $users = $db->table('users')->select('id, role')->get()->getResultArray();
        foreach ($users as $user) {
            $roleName = (string) ($user['role'] ?? '');
            if (! isset($roleMap[$roleName])) {
                continue;
            }

            $exists = $db->table('user_roles')
                ->where('user_id', (int) $user['id'])
                ->where('role_id', $roleMap[$roleName])
                ->countAllResults() > 0;

            if (! $exists) {
                $db->table('user_roles')->insert([
                    'user_id' => (int) $user['id'],
                    'role_id' => $roleMap[$roleName],
                    'assigned_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function createIndexIfMissing($db, string $table, string $indexName, string $indexType, string $columns): void
    {
        $exists = $db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName])->getResultArray();
        if (! empty($exists)) {
            return;
        }

        $columnList = trim($columns, '()');
        $sql = strtoupper($indexType) === 'UNIQUE'
            ? "ALTER TABLE `{$table}` ADD UNIQUE INDEX `{$indexName}` ({$columnList})"
            : "ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columnList})";

        $db->query($sql);
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
