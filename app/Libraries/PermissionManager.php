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

    // Define all system permissions with their labels and routes
    public static array $definitions = [
        'view_patients'      => ['label' => 'View Patients',       'routes' => ['/admin/patients', '/admin/patients/list', '/admin/patients/clients']],
        'manage_users'       => ['label' => 'Manage Users',        'routes' => ['/admin/patients/add', '/admin/patients/edit', '/admin/patients/delete', '/admin/patients/restore']],
        'view_appointments'  => ['label' => 'View Appointments',   'routes' => ['/admin/appointments']],
        'view_doctors'       => ['label' => 'View Doctors',        'routes' => ['/admin/doctors', '/admin/doctor-schedules', '/admin/doctors/specialization', '/admin/doctors/schedule']],
        'view_reports'       => ['label' => 'View Reports',        'routes' => ['/admin/reports', '/admin/audit-reports']],
        'view_audit_log'     => ['label' => 'View Audit Log',      'routes' => ['/admin/audit-log']],
        'manage_permissions' => ['label' => 'Manage Permissions',  'routes' => ['/admin/permissions']],
        'access_requests'    => ['label' => 'Access Requests',     'routes' => ['/admin/access-requests']],
        'announcements'      => ['label' => 'Announcements',       'routes' => ['/admin/announcements']],
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
        return in_array($permission, self::$cache ?? [], true);
    }

    public static function all(): array
    {
        self::load();
        return self::$cache ?? [];
    }

    public static function reset(): void
    {
        self::$cache = null;
    }
}
