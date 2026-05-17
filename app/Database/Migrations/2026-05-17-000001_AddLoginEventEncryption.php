<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoginEventEncryption extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // Add comment to email_attempted and user_agent fields documenting they are now encrypted
        $db->query("ALTER TABLE login_events MODIFY COLUMN email_attempted VARCHAR(191) COMMENT 'Encrypted email address attempted for login'");
        $db->query("ALTER TABLE login_events MODIFY COLUMN user_agent VARCHAR(255) COMMENT 'Encrypted user agent string'");
    }

    public function down()
    {
        $db = \Config\Database::connect();

        // Remove encryption comments
        $db->query("ALTER TABLE login_events MODIFY COLUMN email_attempted VARCHAR(191) COMMENT ''");
        $db->query("ALTER TABLE login_events MODIFY COLUMN user_agent VARCHAR(255) COMMENT ''");
    }
}
