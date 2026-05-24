<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupUsersAfterSplit extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $columns = [];
        foreach ([
            'name',
            'password_hash',
            'mfa_code_hash',
            'mfa_expires_at',
            'role_password',
            'profile_photo',
            'specialization',
            'experience',
            'degree',
            'bio',
            'phone',
            'city',
            'address',
            'failed_login_count',
            'lock_until',
            'last_login_at',
            'password_changed_at',
            'cancel_attempts',
            'cancel_reset_at',
            'is_email_verified',
        ] as $column) {
            if ($db->fieldExists($column, 'users')) {
                $columns[] = $column;
            }
        }

        if (! empty($columns)) {
            $this->forge->dropColumn('users', $columns);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $fields = [];
        foreach ([
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mfa_code_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'mfa_expires_at' => ['type' => 'INT', 'null' => true],
            'role_password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'profile_photo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'specialization' => ['type' => 'TEXT', 'null' => true],
            'experience' => ['type' => 'TEXT', 'null' => true],
            'degree' => ['type' => 'TEXT', 'null' => true],
            'bio' => ['type' => 'TEXT', 'null' => true],
            'phone' => ['type' => 'TEXT', 'null' => true],
            'city' => ['type' => 'TEXT', 'null' => true],
            'address' => ['type' => 'TEXT', 'null' => true],
            'failed_login_count' => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
            'lock_until' => ['type' => 'DATETIME', 'null' => true],
            'last_login_at' => ['type' => 'DATETIME', 'null' => true],
            'password_changed_at' => ['type' => 'DATETIME', 'null' => true],
            'cancel_attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'cancel_reset_at' => ['type' => 'DATETIME', 'null' => true],
            'is_email_verified' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ] as $field => $definition) {
            if (! $db->fieldExists($field, 'users')) {
                $fields[$field] = $definition;
            }
        }

        if (! empty($fields)) {
            $this->forge->addColumn('users', $fields);
        }

        if (! $db->tableExists('user_auth') || ! $db->tableExists('user_profiles') || ! $db->tableExists('doctor_profiles')) {
            return;
        }

        $db->query(
            "UPDATE users u\n" .
            "LEFT JOIN user_profiles up ON up.user_id = u.id\n" .
            "LEFT JOIN doctor_profiles dp ON dp.user_id = u.id\n" .
            "LEFT JOIN user_auth ua ON ua.user_id = u.id\n" .
            "SET\n" .
            "u.name = COALESCE(up.name, u.name),\n" .
            "u.bio = up.bio,\n" .
            "u.phone = up.phone,\n" .
            "u.city = up.city,\n" .
            "u.address = up.address,\n" .
            "u.profile_photo = up.profile_photo,\n" .
            "u.specialization = dp.specialization,\n" .
            "u.experience = dp.experience,\n" .
            "u.degree = dp.degree,\n" .
            "u.password_hash = ua.password_hash,\n" .
            "u.role_password = ua.role_password,\n" .
            "u.mfa_code_hash = ua.mfa_code_hash,\n" .
            "u.mfa_expires_at = ua.mfa_expires_at,\n" .
            "u.failed_login_count = ua.failed_login_count,\n" .
            "u.cancel_attempts = ua.cancel_attempts,\n" .
            "u.cancel_reset_at = ua.cancel_reset_at,\n" .
            "u.lock_until = ua.lock_until,\n" .
            "u.last_login_at = ua.last_login_at,\n" .
            "u.password_changed_at = ua.password_changed_at,\n" .
            "u.is_email_verified = ua.is_email_verified"
        );
    }
}
