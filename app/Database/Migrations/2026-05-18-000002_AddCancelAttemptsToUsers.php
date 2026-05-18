<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCancelAttemptsToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $fields = [];

        if (! $db->fieldExists('cancel_attempts', 'users')) {
            $fields['cancel_attempts'] = [
                'type'       => 'TINYINT',
                'constraint' => 3,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'status',
            ];
        }

        if (! $db->fieldExists('cancel_reset_at', 'users')) {
            $fields['cancel_reset_at'] = [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'cancel_attempts',
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['cancel_attempts', 'cancel_reset_at']);
    }
}
