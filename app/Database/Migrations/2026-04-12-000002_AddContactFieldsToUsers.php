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
        $db = \Config\Database::connect();
        $columns = array_values(array_filter([
            $db->fieldExists('phone', 'users') ? 'phone' : null,
            $db->fieldExists('city', 'users') ? 'city' : null,
            $db->fieldExists('address', 'users') ? 'address' : null,
        ]));

        if (! empty($columns)) {
            $this->forge->dropColumn('users', $columns);
        }
    }
}
