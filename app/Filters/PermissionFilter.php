<?php

namespace App\Filters;

use App\Libraries\PermissionManager;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    // Map URI prefixes to permission codes per role
    private array $roleRouteMap = [
        'doctor' => [
            '/doctor/appointments' => 'doctor_appointments',
            '/doctor/queue'        => 'doctor_queue',
            '/doctor/records'      => 'doctor_patient_records',
            '/doctor/notes'        => 'doctor_notes',
            '/doctor/prescriptions'=> 'doctor_prescriptions',
            '/doctor/schedule'     => 'doctor_schedule',
        ],
        'secretary' => [
            '/secretary/appointments' => 'secretary_appointments',
            '/secretary/queue'        => 'secretary_queue',
            '/secretary/records'      => 'secretary_records',
            '/secretary/register'     => 'secretary_register',
            '/secretary/schedules'    => 'secretary_schedules',
            '/secretary/approvals'    => 'secretary_approvals',
        ],
        'client' => [
            '/appointments/new' => 'client_book_appointment',
            '/appointments/my'  => 'client_my_appointments',
            '/appointments'     => 'client_book_appointment',
            '/profile'          => 'client_profile',
        ],
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Update last_active_at for the current active session
        $token = session('auth_session_token');
        if ($token) {
            $tokenHash = hex2bin(hash('sha256', $token));
            $db = \Config\Database::connect();
            $db->query(
                'UPDATE auth_sessions SET last_active_at = NOW() WHERE refresh_token_hash = ?',
                [$tokenHash]
            );
        }

        $role = session('user_role');

        // Normalize URI
        $uri      = '/' . ltrim($request->getUri()->getPath(), '/');
        $baseUrl  = rtrim(config('App')->baseURL, '/');
        $basePath = parse_url($baseUrl, PHP_URL_PATH) ?? '';
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . ltrim($uri, '/');

        // Log module access for GET requests
        if ($request->getMethod() === 'get') {
            $moduleLogs = [
                '/admin/permissions'    => 'Permissions Module',
                '/admin/audit-log'      => 'Security Audit Log',
                '/admin/audit-reports'  => 'Audit Reports',
                '/admin/reports'        => 'Audit Reports',
                '/admin/appointments'   => 'Appointments Management',
                '/admin/patients'       => 'Patients Management',
                '/admin/doctors'        => 'Doctors Management',
                '/admin/announcements'  => 'Announcements Module',
                '/admin/settings'       => 'Settings Module',
                '/doctor/appointments'  => 'Doctor Appointments',
                '/doctor/queue'         => 'Doctor Queue',
                '/doctor/records'       => 'Patient Records',
                '/doctor/notes'         => 'Doctor Notes',
                '/doctor/prescriptions' => 'Doctor Prescriptions',
                '/doctor/schedule'      => 'Doctor Schedule',
                '/secretary/appointments' => 'Secretary Appointments',
                '/secretary/queue'        => 'Secretary Queue',
                '/secretary/records'      => 'Patient Records',
                '/secretary/register'     => 'Patient Registration',
                '/secretary/schedules'    => 'Secretary Schedules',
                '/secretary/approvals'    => 'Secretary Approvals',
                '/appointments/new'       => 'Book Appointment',
                '/appointments/my'        => 'My Appointments',
                '/profile'                => 'Profile Module',
            ];

            foreach ($moduleLogs as $prefix => $moduleName) {
                if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
                    $userId = session('user_id');
                    if ($userId) {
                        $logModel = new \App\Models\LoginEventModel();
                        $db = \Config\Database::connect();
                        $recent = $db->query(
                            'SELECT id FROM login_events 
                             WHERE user_id = ? AND event_type = ? AND reason_code = ? AND created_at >= ?',
                            [$userId, 'module_access', 'Opened ' . $moduleName, date('Y-m-d H:i:s', time() - 5)]
                        )->getRow();
                        
                        if (!$recent) {
                            $logModel->log('module_access', (int)$userId, null, 'Opened ' . $moduleName);
                        }
                    }
                    break;
                }
            }
        }

        // Admin always passes — no restrictions
        if ($role === 'admin') {
            return;
        }

        // ── Admin panel routes ──
        if (str_starts_with($uri, '/admin/')) {
            // Only admin and assistant_admin can access /admin/*
            if ($role !== 'assistant_admin') {
                return $this->denyAccess();
            }

            // Check specific permission for assistant_admin
            foreach (PermissionManager::$definitions as $code => $def) {
                // Only check admin-panel permissions (not role-specific ones)
                if (! str_starts_with($code, 'doctor_') && ! str_starts_with($code, 'secretary_') && ! str_starts_with($code, 'client_')) {
                    foreach ($def['routes'] as $route) {
                        if (str_starts_with($uri, $route)) {
                            if (! PermissionManager::can($code)) {
                                return $this->denyAccess();
                            }
                            return;
                        }
                    }
                }
            }
            return; // URI not in definitions — allow for assistant_admin
        }

        // ── Role-specific routes (doctor, secretary, client) ──
        if (isset($this->roleRouteMap[$role])) {
            foreach ($this->roleRouteMap[$role] as $routePrefix => $permCode) {
                if (str_starts_with($uri, $routePrefix)) {
                    if (! PermissionManager::can($permCode)) {
                        return $this->denyAccess();
                    }
                    return; // Permission granted
                }
            }
        }

        // ── Wrong role trying to access another role's routes ──
        // e.g. client trying to access /doctor/* or /secretary/*
        $allRolePrefixes = ['/doctor/', '/secretary/'];
        foreach ($allRolePrefixes as $prefix) {
            if (str_starts_with($uri, $prefix) && ! isset($this->roleRouteMap[$role][$prefix])) {
                // Only block if this role has no business here
                if ($role === 'client' || ($role === 'doctor' && str_starts_with($uri, '/secretary/')) || ($role === 'secretary' && str_starts_with($uri, '/doctor/'))) {
                    return $this->denyAccess();
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function denyAccess()
    {
        // Redirect to a proper route so CSRF and session work correctly
        // Pass the original URI so the access denied page knows which permission was denied
        $uri = '/' . ltrim(service('request')->getUri()->getPath(), '/');
        $baseUrl  = rtrim(config('App')->baseURL, '/');
        $basePath = parse_url($baseUrl, PHP_URL_PATH) ?? '';
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . ltrim($uri, '/');

        return redirect()->to('/access-denied?from=' . urlencode($uri));
    }
}
