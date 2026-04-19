<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMfaFieldsToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'mfa_code_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'password_hash',
            ],
            'mfa_expires_at' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
                'after'   => 'mfa_code_hash',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['mfa_code_hash', 'mfa_expires_at']);
    }
}
