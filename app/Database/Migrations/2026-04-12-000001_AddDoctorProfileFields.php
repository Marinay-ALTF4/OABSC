<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDoctorProfileFields extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = [];

        if (! $db->fieldExists('profile_photo', 'users')) {
            $fields['profile_photo'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'role'];
        }
        if (! $db->fieldExists('specialization', 'users')) {
            $fields['specialization'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'profile_photo'];
        }
        if (! $db->fieldExists('experience', 'users')) {
            $fields['experience'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'specialization'];
        }
        if (! $db->fieldExists('degree', 'users')) {
            $fields['degree'] = ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'experience'];
        }
        if (! $db->fieldExists('bio', 'users')) {
            $fields['bio'] = ['type' => 'TEXT', 'null' => true, 'after' => 'degree'];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $columns = array_values(array_filter([
            $db->fieldExists('profile_photo', 'users') ? 'profile_photo' : null,
            $db->fieldExists('specialization', 'users') ? 'specialization' : null,
            $db->fieldExists('experience', 'users') ? 'experience' : null,
            $db->fieldExists('degree', 'users') ? 'degree' : null,
            $db->fieldExists('bio', 'users') ? 'bio' : null,
        ]));

        if (! empty($columns)) {
            $this->forge->dropColumn('users', $columns);
        }
    }
}
