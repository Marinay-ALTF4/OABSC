<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleSpecificPermissions extends Migration
{
    // New permissions for doctor, secretary, client features
    private array $newPermissions = [
        // Doctor
        ['code' => 'doctor_appointments',  'description' => 'My Appointments'],
        ['code' => 'doctor_queue',         'description' => "Today's Queue"],
        ['code' => 'doctor_patient_records','description' => 'Patient Records'],
        ['code' => 'doctor_notes',         'description' => 'Write Notes'],
        ['code' => 'doctor_prescriptions', 'description' => 'Prescriptions'],
        ['code' => 'doctor_schedule',      'description' => 'Schedule Settings'],
        // Secretary
        ['code' => 'secretary_appointments','description' => 'Manage Appointments'],
        ['code' => 'secretary_queue',       'description' => 'Patient Queue'],
        ['code' => 'secretary_records',     'description' => 'Patient Records'],
        ['code' => 'secretary_register',    'description' => 'Register New Patient'],
        ['code' => 'secretary_schedules',   'description' => 'Doctor Schedules'],
        ['code' => 'secretary_approvals',   'description' => 'Pending Approvals'],
        // Client
        ['code' => 'client_book_appointment', 'description' => 'Book Appointment'],
        ['code' => 'client_my_appointments',  'description' => 'My Appointments'],
        ['code' => 'client_profile',          'description' => 'Profile Settings'],
    ];

    public function up()
    {
        $db = \Config\Database::connect();

        foreach ($this->newPermissions as $perm) {
            $exists = $db->table('permissions')->where('code', $perm['code'])->get()->getRowArray();
            if (! $exists) {
                $db->table('permissions')->insert($perm);
            }
        }

        // Assign all doctor permissions to doctor role by default
        $doctorRole = $db->table('roles')->where('name', 'doctor')->get()->getRowArray();
        if ($doctorRole) {
            $doctorPerms = ['doctor_appointments','doctor_queue','doctor_patient_records','doctor_notes','doctor_prescriptions','doctor_schedule'];
            foreach ($doctorPerms as $code) {
                $perm = $db->table('permissions')->where('code', $code)->get()->getRowArray();
                if ($perm) {
                    $exists = $db->table('role_permissions')
                        ->where('role_id', $doctorRole['id'])
                        ->where('permission_id', $perm['id'])
                        ->get()->getRowArray();
                    if (! $exists) {
                        $db->table('role_permissions')->insert(['role_id' => $doctorRole['id'], 'permission_id' => $perm['id']]);
                    }
                }
            }
        }

        // Assign all secretary permissions to secretary role by default
        $secRole = $db->table('roles')->where('name', 'secretary')->get()->getRowArray();
        if ($secRole) {
            $secPerms = ['secretary_appointments','secretary_queue','secretary_records','secretary_register','secretary_schedules','secretary_approvals'];
            foreach ($secPerms as $code) {
                $perm = $db->table('permissions')->where('code', $code)->get()->getRowArray();
                if ($perm) {
                    $exists = $db->table('role_permissions')
                        ->where('role_id', $secRole['id'])
                        ->where('permission_id', $perm['id'])
                        ->get()->getRowArray();
                    if (! $exists) {
                        $db->table('role_permissions')->insert(['role_id' => $secRole['id'], 'permission_id' => $perm['id']]);
                    }
                }
            }
        }

        // Assign all client permissions to client role by default
        $clientRole = $db->table('roles')->where('name', 'client')->get()->getRowArray();
        if ($clientRole) {
            $clientPerms = ['client_book_appointment','client_my_appointments','client_profile'];
            foreach ($clientPerms as $code) {
                $perm = $db->table('permissions')->where('code', $code)->get()->getRowArray();
                if ($perm) {
                    $exists = $db->table('role_permissions')
                        ->where('role_id', $clientRole['id'])
                        ->where('permission_id', $perm['id'])
                        ->get()->getRowArray();
                    if (! $exists) {
                        $db->table('role_permissions')->insert(['role_id' => $clientRole['id'], 'permission_id' => $perm['id']]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        foreach ($this->newPermissions as $perm) {
            $db->table('permissions')->where('code', $perm['code'])->delete();
        }
    }
}
