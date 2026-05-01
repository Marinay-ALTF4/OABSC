<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSecureUserStorageSchema extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $this->addUsersSecurityColumns($db);
        $this->createUserCredentialsTable($db);
        $this->createPasswordHistoryTable($db);
        $this->createRbacTables($db);
        $this->createMfaFactorsTable($db);
        $this->createAuthSessionsTable($db);
        $this->createLoginEventsTable($db);

        $this->seedDefaultRoles($db);
        $this->backfillUsersPublicId($db);
        $this->backfillUserCredentials($db);
        $this->backfillUserRoles($db);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $this->forge->dropTable('login_events', true);
        $this->forge->dropTable('auth_sessions', true);
        $this->forge->dropTable('mfa_factors', true);
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropTable('password_history', true);
        $this->forge->dropTable('user_credentials', true);

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

        $this->createIndexIfMissing($db, 'users', 'uq_users_public_id', 'UNIQUE', '(public_id)');
        $this->createIndexIfMissing($db, 'users', 'uq_users_username', 'UNIQUE', '(username)');
        $this->createIndexIfMissing($db, 'users', 'idx_users_status_created', 'INDEX', '(status, created_at)');
        $this->createIndexIfMissing($db, 'users', 'idx_users_last_login', 'INDEX', '(last_login_at)');
    }

    private function createUserCredentialsTable($db): void
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
    }

    private function createPasswordHistoryTable($db): void
    {
        if ($db->tableExists('password_history')) {
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
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'created_at'], false, false, 'idx_ph_user_created');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('password_history', true);
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
            $this->forge->addKey(['role_id', 'user_id'], false, false, 'idx_ur_role_user');
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

    private function createMfaFactorsTable($db): void
    {
        if ($db->tableExists('mfa_factors')) {
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
            'factor_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'secret_enc' => [
                'type' => 'BLOB',
                'null' => true,
            ],
            'public_key' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'counter' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_verified' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_primary' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'is_verified'], false, false, 'idx_mfa_user_verified');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('mfa_factors', true);

        $this->createIndexIfMissing($db, 'mfa_factors', 'uq_mfa_user_type_label', 'UNIQUE', '(user_id, factor_type, label)');
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
                'type'       => 'BINARY',
                'constraint' => 32,
            ],
            'device_fingerprint_hash' => [
                'type'       => 'BINARY',
                'constraint' => 32,
                'null'       => true,
            ],
            'ip_hash' => [
                'type'       => 'BINARY',
                'constraint' => 32,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'issued_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
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
        $this->forge->addKey(['user_id', 'revoked_at', 'expires_at'], false, false, 'idx_as_user_active');
        $this->forge->addKey('expires_at', false, false, 'idx_as_expires');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_sessions', true);

        $this->createIndexIfMissing($db, 'auth_sessions', 'uq_as_refresh_hash', 'UNIQUE', '(refresh_token_hash)');
    }

    private function createLoginEventsTable($db): void
    {
        if ($db->tableExists('login_events')) {
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
            'email_attempted' => [
                'type'       => 'VARCHAR',
                'constraint' => 191,
                'null'       => true,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'reason_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'ip_hash' => [
                'type'       => 'BINARY',
                'constraint' => 32,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'created_at'], false, false, 'idx_le_user_created');
        $this->forge->addKey(['ip_hash', 'created_at'], false, false, 'idx_le_ip_created');
        $this->forge->addKey(['event_type', 'created_at'], false, false, 'idx_le_event_created');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('login_events', true);
    }

    private function seedDefaultRoles($db): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Primary administrator'],
            ['name' => 'assistant_admin', 'description' => 'Assistant administrator'],
            ['name' => 'secretary', 'description' => 'Clinic secretary'],
            ['name' => 'doctor', 'description' => 'Medical practitioner'],
            ['name' => 'client', 'description' => 'Patient/client user'],
            ['name' => 'assistant_secretary', 'description' => 'Assistant secretary'],
        ];

        foreach ($roles as $role) {
            $exists = $db->table('roles')->where('name', $role['name'])->get()->getRowArray();
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

        $users = $db->table('users')->select('id, public_id')->get()->getResultArray();
        foreach ($users as $user) {
            if (empty($user['public_id'])) {
                $db->table('users')->where('id', (int) $user['id'])->update([
                    'public_id' => $this->uuidV4(),
                ]);
            }
        }
    }

    private function backfillUserCredentials($db): void
    {
        if (! $db->tableExists('user_credentials') || ! $db->fieldExists('password_hash', 'users')) {
            return;
        }

        $db->query(
            "INSERT INTO user_credentials (user_id, password_hash, hash_algo, hash_params, pepper_version, must_rotate, created_at, updated_at)
             SELECT u.id, u.password_hash, 'legacy', '{}', 1, 0, u.created_at, u.updated_at
             FROM users u
             LEFT JOIN user_credentials uc ON uc.user_id = u.id
             WHERE uc.user_id IS NULL AND u.password_hash IS NOT NULL AND u.password_hash <> ''"
        );
    }

    private function backfillUserRoles($db): void
    {
        if (! $db->tableExists('user_roles') || ! $db->tableExists('roles') || ! $db->fieldExists('role', 'users')) {
            return;
        }

        $db->query(
            "INSERT INTO user_roles (user_id, role_id, assigned_at)
             SELECT u.id, r.id, NOW()
             FROM users u
             INNER JOIN roles r ON r.name = u.role
             LEFT JOIN user_roles ur ON ur.user_id = u.id AND ur.role_id = r.id
             WHERE ur.user_id IS NULL AND u.role IS NOT NULL AND u.role <> ''"
        );
    }

    private function createIndexIfMissing($db, string $table, string $indexName, string $indexType, string $columns): void
    {
        if (! $db->tableExists($table)) {
            return;
        }

        $exists = $db->query("SHOW INDEX FROM {$table} WHERE Key_name = " . $db->escape($indexName))->getRowArray();
        if ($exists) {
            return;
        }

        $prefix = strtoupper($indexType) === 'UNIQUE' ? 'CREATE UNIQUE INDEX' : 'CREATE INDEX';
        $db->query("{$prefix} {$indexName} ON {$table} {$columns}");
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
