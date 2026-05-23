<?php
/**
 * Admin sidebar partial.
 */
use App\Libraries\PermissionManager;

$role   = session('user_role') ?? 'guest';
$name   = session('user_name') ?? 'User';
$active = $sidebarActive ?? '';
$isAdmin = $role === 'admin';

if (! function_exists('adm_nav_active')) {
    function adm_nav_active(string $key, string $active): string {
        return $key === $active ? ' active' : '';
    }
}

// Build a helper: returns nav item HTML, disabled if no permission
function navItem(string $url, string $icon, string $label, string $key, string $active, bool $hasPermission = true): string {
    $activeClass   = adm_nav_active($key, $active);
    $disabledClass = ! $hasPermission ? ' nav-disabled' : '';
    $disabledAttr  = ! $hasPermission ? ' tabindex="-1" aria-disabled="true" title="You don\'t have permission to access this page"' : '';
    $href          = $hasPermission ? $url : '#';

    return '<a href="' . $href . '" class="adm-nav-item' . $activeClass . $disabledClass . '"' . $disabledAttr . '>'
         . '<i class="bi ' . $icon . '"></i> ' . $label
         . (! $hasPermission ? ' <i class="bi bi-lock-fill ms-auto" style="font-size:0.7rem;opacity:0.5;"></i>' : '')
         . '</a>';
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

    <?= navItem(site_url('/admin/patients/list'), 'bi-people-fill', 'Manage Users',     'users',        $active, $isAdmin || PermissionManager::can('manage_users')) ?>
    <?= navItem(site_url('/admin/patients'),      'bi-folder2-open','Patient Records',  'patients',     $active, $isAdmin || PermissionManager::can('view_patients')) ?>

    <?php if ($isAdmin): ?>
    <a href="<?= site_url('/admin/permissions') ?>" class="adm-nav-item<?= adm_nav_active('permissions', $active) ?>">
        <i class="bi bi-shield-lock"></i> Manage Permissions
    </a>
    <?php endif; ?>

    <?= navItem(site_url('/admin/appointments'),    'bi-calendar-event',        'Appointments',    'appointments', $active, $isAdmin || PermissionManager::can('view_appointments')) ?>
    <?= navItem(site_url('/admin/doctor-schedules'),'bi-calendar2-check',       'Doctor Schedules','schedules',    $active, $isAdmin || PermissionManager::can('view_doctors')) ?>
    <?= navItem(site_url('/admin/access-requests'), 'bi-check-circle',          'Access Requests', 'access',       $active, $isAdmin || PermissionManager::can('access_requests')) ?>
    <?= navItem(site_url('/admin/announcements'),   'bi-megaphone',             'Announcements',   'announcements',$active, $isAdmin || PermissionManager::can('announcements')) ?>
    <?= navItem(site_url('/admin/audit-log'),       'bi-clock-history',         'System Audit Log','audit_log',    $active, $isAdmin || PermissionManager::can('view_audit_log')) ?>
    <?= navItem(site_url('/admin/audit-reports'),   'bi-file-earmark-bar-graph','Audit Reports',   'audit_reports',$active, $isAdmin || PermissionManager::can('view_reports')) ?>
</div>

<style>
    .adm-nav-item.nav-disabled {
        color: #94a3b8 !important;
        cursor: not-allowed;
        pointer-events: none;
        opacity: 0.55;
    }
    .adm-nav-item.nav-disabled:hover {
        background: none !important;
    }
</style>
