<?php

namespace App\Database\Migrations;

use App\Libraries\UserDataCrypt;
use CodeIgniter\Database\Migration;

class EncryptSensitiveUserData extends Migration
{
    private array $fields = [
        'phone' => ['type' => 'TEXT', 'null' => true],
        'city' => ['type' => 'TEXT', 'null' => true],
        'address' => ['type' => 'TEXT', 'null' => true],
        'specialization' => ['type' => 'TEXT', 'null' => true],
        'experience' => ['type' => 'TEXT', 'null' => true],
        'degree' => ['type' => 'TEXT', 'null' => true],
        'bio' => ['type' => 'TEXT', 'null' => true],
    ];

    public function up()
    {
        $db = \Config\Database::connect();
        $crypt = new UserDataCrypt();

        $this->forge->modifyColumn('users', $this->fields);

        $users = $db->table('users')
            ->select('id, phone, city, address, specialization, experience, degree, bio')
            ->get()
            ->getResultArray();

        foreach ($users as $user) {
            $updates = [];

            foreach (array_keys($this->fields) as $field) {
                $value = $user[$field] ?? null;
                if ($value !== null && $value !== '') {
                    $updates[$field] = $crypt->encrypt((string) $value);
                }
            }

            if (! empty($updates)) {
                $db->table('users')->where('id', (int) $user['id'])->update($updates);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $crypt = new UserDataCrypt();

        $users = $db->table('users')
            ->select('id, phone, city, address, specialization, experience, degree, bio')
            ->get()
            ->getResultArray();

        foreach ($users as $user) {
            $updates = [];

            foreach (array_keys($this->fields) as $field) {
                $value = $user[$field] ?? null;
                if ($value !== null && $value !== '') {
                    $updates[$field] = $crypt->decrypt((string) $value);
                }
            }

            if (! empty($updates)) {
                $db->table('users')->where('id', (int) $user['id'])->update($updates);
            }
        }

        $this->forge->modifyColumn('users', [
            'phone' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'city' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'address' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'specialization' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'experience' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'degree' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'bio' => ['type' => 'TEXT', 'null' => true],
        ]);
    }
}
