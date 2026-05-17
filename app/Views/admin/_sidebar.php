<?php
/**
 * Admin sidebar partial.
 * Pass $sidebarActive = 'dashboard' | 'users' | 'patients' | 'permissions' |
 *                       'appointments' | 'schedules' | 'access' | 'announcements' |
 *                       'audit_log' | 'audit_reports'
 * from the including view.
 */
$role  = session('user_role') ?? 'guest';
$name  = session('user_name') ?? 'User';
$active = $sidebarActive ?? '';

if (!function_exists('adm_nav_active')) {
    function adm_nav_active(string $key, string $active): string {
        return $key === $active ? ' active' : '';
    }
}
?>
<div class="adm-sidebar">
    <div class="adm-sidebar-user">
        <div class="adm-sidebar-avatar"><i class="bi bi-person-circle"></i></div>
        <div>
            <div class="adm-sidebar-name"><?= esc($name) ?></div>
            <div class="adm-sidebar-role"><?= $role === 'assistant_admin' ? 'Assistant Admin' : 'Admin' ?></div>
        </div>
    </div>
    <hr class="adm-sidebar-divider">

    <a href="<?= site_url('/dashboard') ?>" class="adm-nav-item<?= adm_nav_active('dashboard', $active) ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?= site_url('/admin/patients/list') ?>" class="adm-nav-item<?= adm_nav_active('users', $active) ?>">
        <i class="bi bi-people-fill"></i> Manage Users
    </a>
    <a href="<?= site_url('/admin/patients') ?>" class="adm-nav-item<?= adm_nav_active('patients', $active) ?>">
        <i class="bi bi-folder2-open"></i> Patient Records
    </a>
    <?php if ($role === 'admin') : ?>
    <a href="<?= site_url('/admin/permissions') ?>" class="adm-nav-item<?= adm_nav_active('permissions', $active) ?>">
        <i class="bi bi-shield-lock"></i> Manage Permissions
    </a>
    <?php endif; ?>
    <a href="<?= site_url('/admin/appointments') ?>" class="adm-nav-item<?= adm_nav_active('appointments', $active) ?>">
        <i class="bi bi-calendar-event"></i> Appointments
    </a>
    <a href="<?= site_url('/admin/doctor-schedules') ?>" class="adm-nav-item<?= adm_nav_active('schedules', $active) ?>">
        <i class="bi bi-calendar2-check"></i> Doctor Schedules
    </a>
    <a href="<?= site_url('/admin/access-requests') ?>" class="adm-nav-item<?= adm_nav_active('access', $active) ?>">
        <i class="bi bi-check-circle"></i> Access Requests
    </a>
    <a href="<?= site_url('/admin/announcements') ?>" class="adm-nav-item<?= adm_nav_active('announcements', $active) ?>">
        <i class="bi bi-megaphone"></i> Announcements
    </a>
    <a href="<?= site_url('/admin/audit-log') ?>" class="adm-nav-item<?= adm_nav_active('audit_log', $active) ?>">
        <i class="bi bi-clock-history"></i> System Audit Log
    </a>
    <a href="<?= site_url('/admin/audit-reports') ?>" class="adm-nav-item<?= adm_nav_active('audit_reports', $active) ?>">
        <i class="bi bi-file-earmark-bar-graph"></i> Audit Reports
    </a>
</div>
