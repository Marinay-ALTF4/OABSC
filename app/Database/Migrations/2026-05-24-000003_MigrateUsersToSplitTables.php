<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateUsersToSplitTables extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('user_auth') || ! $db->tableExists('user_profiles') || ! $db->tableExists('doctor_profiles')) {
            return;
        }

        if (! $db->fieldExists('password_hash', 'users') || ! $db->fieldExists('name', 'users')) {
            return;
        }

        $db->query(
            "INSERT INTO user_auth (user_id, password_hash, role_password, mfa_code_hash, mfa_expires_at, failed_login_count, cancel_attempts, cancel_reset_at, lock_until, last_login_at, password_changed_at, is_email_verified)\n" .
            "SELECT id, password_hash, role_password, mfa_code_hash, mfa_expires_at, failed_login_count, cancel_attempts, cancel_reset_at, lock_until, last_login_at, password_changed_at, is_email_verified\n" .
            "FROM users\n" .
            "ON DUPLICATE KEY UPDATE\n" .
            "password_hash = VALUES(password_hash),\n" .
            "role_password = VALUES(role_password),\n" .
            "mfa_code_hash = VALUES(mfa_code_hash),\n" .
            "mfa_expires_at = VALUES(mfa_expires_at),\n" .
            "failed_login_count = VALUES(failed_login_count),\n" .
            "cancel_attempts = VALUES(cancel_attempts),\n" .
            "cancel_reset_at = VALUES(cancel_reset_at),\n" .
            "lock_until = VALUES(lock_until),\n" .
            "last_login_at = VALUES(last_login_at),\n" .
            "password_changed_at = VALUES(password_changed_at),\n" .
            "is_email_verified = VALUES(is_email_verified)"
        );

        $db->query(
            "INSERT INTO user_profiles (user_id, name, bio, phone, city, address, profile_photo)\n" .
            "SELECT id, name, bio, phone, city, address, profile_photo\n" .
            "FROM users\n" .
            "ON DUPLICATE KEY UPDATE\n" .
            "name = VALUES(name),\n" .
            "bio = VALUES(bio),\n" .
            "phone = VALUES(phone),\n" .
            "city = VALUES(city),\n" .
            "address = VALUES(address),\n" .
            "profile_photo = VALUES(profile_photo)"
        );

        $db->query(
            "INSERT INTO doctor_profiles (user_id, specialization, experience, degree)\n" .
            "SELECT id, specialization, experience, degree\n" .
            "FROM users\n" .
            "WHERE role = 'doctor'\n" .
            "   OR specialization IS NOT NULL\n" .
            "   OR experience IS NOT NULL\n" .
            "   OR degree IS NOT NULL\n" .
            "ON DUPLICATE KEY UPDATE\n" .
            "specialization = VALUES(specialization),\n" .
            "experience = VALUES(experience),\n" .
            "degree = VALUES(degree)"
        );
    }

    public function down()
    {
        // Intentionally left blank to avoid data loss.
    }
}
