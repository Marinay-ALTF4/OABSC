<?php

namespace App\Libraries;

/**
 * PermissionManager
 * Checks if the current user's role has a given permission.
 * Permissions are cached per request to avoid repeated DB queries.
 */
class PermissionManager
{
    private static ?array $cache = null;

    // Per-user overrides cache: ['deny' => [...codes], 'grant' => [...codes]]
    private static ?array $userOverrides = null;

    // Define all system permissions with their labels and routes
    public static array $definitions = [
        // Admin panel permissions (for assistant_admin)
        'view_patients'      => ['label' => 'View Patients',       'routes' => ['/admin/patients', '/admin/patients/list', '/admin/patients/clients', '/admin/patients/history']],
        'manage_users'       => ['label' => 'Manage Users',        'routes' => ['/admin/patients/add', '/admin/patients/edit', '/admin/patients/delete', '/admin/patients/restore']],
        'view_appointments'  => ['label' => 'View Appointments',   'routes' => ['/admin/appointments']],
        'view_doctors'       => ['label' => 'View Doctors',        'routes' => ['/admin/doctors', '/admin/doctor-schedules', '/admin/doctors/specialization', '/admin/doctors/schedule']],
        'view_reports'       => ['label' => 'View Reports',        'routes' => ['/admin/reports', '/admin/audit-reports']],
        'view_audit_log'     => ['label' => 'View Audit Log',      'routes' => ['/admin/audit-log']],
        'manage_permissions' => ['label' => 'Manage Permissions',  'routes' => ['/admin/permissions']],
        'access_requests'    => ['label' => 'Access Requests',     'routes' => ['/admin/access-requests']],
        'announcements'      => ['label' => 'Announcements',       'routes' => ['/admin/announcements']],
        // Doctor permissions
        'doctor_appointments'   => ['label' => 'My Appointments',    'routes' => ['/doctor/appointments']],
        'doctor_queue'          => ['label' => "Today's Queue",      'routes' => ['/doctor/queue']],
        'doctor_patient_records'=> ['label' => 'Patient Records',    'routes' => ['/doctor/records']],
        'doctor_notes'          => ['label' => 'Write Notes',        'routes' => ['/doctor/notes']],
        'doctor_prescriptions'  => ['label' => 'Prescriptions',      'routes' => ['/doctor/prescriptions']],
        'doctor_schedule'       => ['label' => 'Schedule Settings',  'routes' => ['/doctor/schedule']],
        // Secretary permissions
        'secretary_appointments'=> ['label' => 'Manage Appointments','routes' => ['/secretary/appointments', '/secretary/update-status']],
        'secretary_queue'       => ['label' => 'Patient Queue',      'routes' => ['/secretary/queue']],
        'secretary_records'     => ['label' => 'Patient Records',    'routes' => ['/secretary/records']],
        'secretary_register'    => ['label' => 'Register New Patient','routes' => ['/secretary/register']],
        'secretary_schedules'   => ['label' => 'Doctor Schedules',   'routes' => ['/secretary/schedules']],
        'secretary_approvals'   => ['label' => 'Pending Approvals',  'routes' => ['/secretary/approvals']],
        // Client permissions
        'client_book_appointment'=> ['label' => 'Book Appointment',  'routes' => ['/appointments/new', '/appointments']],
        'client_my_appointments' => ['label' => 'My Appointments',   'routes' => ['/appointments/my']],
        'client_profile'         => ['label' => 'Profile Settings',  'routes' => ['/profile']],
    ];

    /**
     * Load permissions for the current role from DB.
     */
    public static function load(): void
    {
        if (self::$cache !== null) return;

        $role = session('user_role');
        if (! $role) {
            self::$cache = [];
            return;
        }

        // Admin always has all permissions
        if ($role === 'admin') {
            self::$cache = array_keys(self::$definitions);
            return;
        }

        $db = \Config\Database::connect();
        $rows = $db->query(
            'SELECT p.code
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN roles r ON r.id = rp.role_id
             WHERE r.name = ?',
            [$role]
        )->getResultArray();

        self::$cache = array_column($rows, 'code');
    }

    public static function can(string $permission): bool
    {
        self::load();
        self::loadUserOverrides();

        // User-level DENY overrides role permission
        if (in_array($permission, self::$userOverrides['deny'] ?? [], true)) {
            return false;
        }

        // User-level GRANT overrides role block
        if (in_array($permission, self::$userOverrides['grant'] ?? [], true)) {
            return true;
        }

        return in_array($permission, self::$cache ?? [], true);
    }

    private static function loadUserOverrides(): void
    {
        if (self::$userOverrides !== null) return;

        $userId = (int) session('user_id');
        if (! $userId) {
            self::$userOverrides = ['deny' => [], 'grant' => []];
            return;
        }

        $db   = \Config\Database::connect();
        $rows = $db->query(
            'SELECT permission_code, type FROM user_permission_overrides WHERE user_id = ?',
            [$userId]
        )->getResultArray();

        self::$userOverrides = ['deny' => [], 'grant' => []];
        foreach ($rows as $row) {
            self::$userOverrides[$row['type']][] = $row['permission_code'];
        }
    }

    public static function all(): array
    {
        self::load();
        return self::$cache ?? [];
    }

    public static function reset(): void
    {
        self::$cache        = null;
        self::$userOverrides = null;
    }
}
