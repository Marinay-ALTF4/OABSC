<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Libraries\PermissionManager;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $db = \Config\Database::connect();

        // Insert all defined permissions
        foreach (PermissionManager::$definitions as $code => $def) {
            $exists = $db->query('SELECT id FROM permissions WHERE code = ?', [$code])->getRowArray();
            if (! $exists) {
                $db->query(
                    'INSERT INTO permissions (code, description) VALUES (?, ?)',
                    [$code, $def['label']]
                );
            }
        }

        // Assign ALL permissions to admin role
        $adminRole = $db->query("SELECT id FROM roles WHERE name = 'admin'")->getRowArray();
        if ($adminRole) {
            $allPerms = $db->query('SELECT id FROM permissions')->getResultArray();
            foreach ($allPerms as $perm) {
                $exists = $db->query(
                    'SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                    [$adminRole['id'], $perm['id']]
                )->getRowArray();
                if (! $exists) {
                    $db->query(
                        'INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                        [$adminRole['id'], $perm['id']]
                    );
                }
            }
        }

        // Assign basic permissions to assistant_admin
        $assistantRole = $db->query("SELECT id FROM roles WHERE name = 'assistant_admin'")->getRowArray();
        if ($assistantRole) {
            $basicPerms = ['view_patients', 'view_appointments', 'view_doctors', 'access_requests'];
            foreach ($basicPerms as $code) {
                $perm = $db->query('SELECT id FROM permissions WHERE code = ?', [$code])->getRowArray();
                if ($perm) {
                    $exists = $db->query(
                        'SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                        [$assistantRole['id'], $perm['id']]
                    )->getRowArray();
                    if (! $exists) {
                        $db->query(
                            'INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                            [$assistantRole['id'], $perm['id']]
                        );
                    }
                }
            }
        }

        echo "Permissions seeded successfully.\n";
    }
}
