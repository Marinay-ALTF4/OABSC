<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureSecretaryRolePermissions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->tableExists('roles')) {
            return;
        }

        // Ensure the secretary role exists
        $secRole = $db->table('roles')->where('name', 'secretary')->get()->getRowArray();
        if (! $secRole) {
            $db->table('roles')->insert([
                'name'        => 'secretary',
                'description' => 'Secretary',
            ]);
            $secRole = $db->table('roles')->where('name', 'secretary')->get()->getRowArray();
        }

        if (! $secRole) {
            return;
        }

        // Ensure secretary permissions exist
        if ($db->tableExists('permissions')) {
            $permissions = [
                ['code' => 'secretary_appointments', 'description' => 'Manage Appointments'],
                ['code' => 'secretary_queue',        'description' => 'Patient Queue'],
                ['code' => 'secretary_records',      'description' => 'Patient Records'],
                ['code' => 'secretary_register',     'description' => 'Register New Patient'],
                ['code' => 'secretary_schedules',    'description' => 'Doctor Schedules'],
                ['code' => 'secretary_approvals',    'description' => 'Pending Approvals'],
            ];

            foreach ($permissions as $perm) {
                $exists = $db->table('permissions')->where('code', $perm['code'])->get()->getRowArray();
                if (! $exists) {
                    $db->table('permissions')->insert($perm);
                }
            }
        }

        // Ensure role_permissions mappings exist
        if ($db->tableExists('role_permissions') && $db->tableExists('permissions')) {
            $roleId = (int) $secRole['id'];

            $codes = [
                'secretary_appointments',
                'secretary_queue',
                'secretary_records',
                'secretary_register',
                'secretary_schedules',
                'secretary_approvals',
            ];

            foreach ($codes as $code) {
                $perm = $db->table('permissions')->where('code', $code)->get()->getRowArray();
                if (! $perm) {
                    continue;
                }

                $exists = $db->table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', (int) $perm['id'])
                    ->get()
                    ->getRowArray();

                if (! $exists) {
                    $db->table('role_permissions')->insert([
                        'role_id'       => $roleId,
                        'permission_id' => (int) $perm['id'],
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Intentionally no-op: do not delete roles/permissions automatically.
    }
}
