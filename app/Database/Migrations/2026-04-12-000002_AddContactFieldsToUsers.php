<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContactFieldsToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = [];

        if (! $db->fieldExists('phone', 'users')) {
            $fields['phone'] = ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'bio'];
        }
        if (! $db->fieldExists('city', 'users')) {
            $fields['city'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'phone'];
        }
        if (! $db->fieldExists('address', 'users')) {
            $fields['address'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'city'];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['phone', 'city', 'address']);
    }
}
