<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';

// View variables passed from controller
/** @var array $summary */
/** @var int $failed24 */
/** @var int $suspicious */
/** @var array $sessions */
/** @var array $events */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Audit Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <!-- Sidebar -->
        <div class="adm-sidebar">
            <div class="adm-sidebar-user">
                <div class="adm-sidebar-avatar"><i class="bi bi-person-circle"></i></div>
                <div>
                    <div class="adm-sidebar-name"><?= esc($name) ?></div>
                    <div class="adm-sidebar-role"><?= $role === 'assistant_admin' ? 'Assistant Admin' : 'Admin' ?></div>
                </div>
            </div>
            <hr class="adm-sidebar-divider">
            <a href="<?= site_url('/dashboard') ?>" class="adm-nav-item">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <?php if ($role === 'admin' || $role === 'assistant_admin'): ?>
            <a href="<?= site_url('/admin/patients/list') ?>" class="adm-nav-item">
                <i class="bi bi-people-fill"></i> Manage Users
            </a>
            <a href="<?= site_url('/admin/patients') ?>" class="adm-nav-item">
                <i class="bi bi-folder2-open"></i> Patient Records
            </a>
            <?php if ($role === 'admin') : ?>
            <a href="<?= site_url('/admin/permissions') ?>" class="adm-nav-item">
                <i class="bi bi-shield-lock"></i> Manage Permissions
            </a>
            <?php endif; ?>
            <a href="<?= site_url('/admin/appointments') ?>" class="adm-nav-item">
                <i class="bi bi-calendar-event"></i> Appointments
            </a>
            <a href="<?= site_url('/admin/doctor-schedules') ?>" class="adm-nav-item">
                <i class="bi bi-calendar2-check"></i> Doctor Schedules
            </a>
            <a href="<?= site_url('/admin/access-requests') ?>" class="adm-nav-item">
                <i class="bi bi-check-circle"></i> Access Requests
            </a>
            <a href="<?= site_url('/admin/announcements') ?>" class="adm-nav-item">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
            <a href="<?= site_url('/admin/audit-log') ?>" class="adm-nav-item active">
                <i class="bi bi-clock-history"></i> System Audit Log
            </a>
            <a href="<?= site_url('/admin/audit-reports') ?>" class="adm-nav-item">
                <i class="bi bi-file-earmark-bar-graph"></i> Audit Reports
            </a>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="adm-main-content">
            <div class="adm-wrapper">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Security Audit Log</h5>
    <span class="text-muted small">Last 7 days summary · <?= esc(date('F j, Y')) ?></span>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="audit-stat-card">
            <div class="audit-stat-icon" style="background:#dbeafe;color:#1e40af;"><i class="bi bi-box-arrow-in-right"></i></div>
            <div class="audit-stat-val"><?= esc((string) array_sum(array_column(array_filter($summary, fn($s) => $s['event_type'] === 'login_success'), 'count'))) ?></div>
            <div class="audit-stat-lbl">Successful Logins (7d)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="audit-stat-card">
            <div class="audit-stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-circle"></i></div>
            <div class="audit-stat-val"><?= esc((string) $failed24) ?></div>
            <div class="audit-stat-lbl">Failed Logins (24h)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="audit-stat-card">
            <div class="audit-stat-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="audit-stat-val"><?= esc((string) $suspicious) ?></div>
            <div class="audit-stat-lbl">Suspicious (24h)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="audit-stat-card">
            <div class="audit-stat-icon" style="background:#d1fae5;color:#065f46;"><i class="bi bi-people"></i></div>
            <div class="audit-stat-val"><?= esc((string) count($sessions)) ?></div>
            <div class="audit-stat-lbl">Active Sessions (8h)</div>
        </div>
    </div>
</div>

<!-- Active Sessions -->
<div class="audit-panel mb-4">
    <div class="audit-panel-header"><i class="bi bi-person-check me-2"></i>Active Sessions (last 8 hours)</div>
    <?php if (empty($sessions)): ?>
        <div class="text-muted small p-3">No active sessions.</div>
    <?php else: ?>
    <table class="audit-table">
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Last Login</th></tr></thead>
        <tbody>
            <?php foreach ($sessions as $i => $s): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= esc($s['name']) ?></td>
                <td><?= esc($s['email']) ?></td>
                <td><span class="role-badge"><?= esc($s['role']) ?></span></td>
                <td><?= esc($s['last_login_at'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Event Log -->
<div class="audit-panel">
    <div class="audit-panel-header"><i class="bi bi-journal-text me-2"></i>Recent Activity Log (last 200 events)</div>
    <?php if (empty($events)): ?>
        <div class="text-muted small p-3">No events recorded yet.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="audit-table">
        <thead>
            <tr><th>#</th><th>Time</th><th>Event</th><th>User ID</th><th>Email Attempted</th><th>Reason</th></tr>
        </thead>
        <tbody>
            <?php foreach ($events as $i => $e): ?>
            <?php
                $badgeClass = match($e['event_type']) {
                    'login_success', 'mfa_success', 'logout' => 'badge-success',
                    'login_failed', 'mfa_failed'             => 'badge-danger',
                    'suspicious_activity', 'login_locked'    => 'badge-warning',
                    default                                  => 'badge-info',
                };
            ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td style="white-space:nowrap;"><?= esc($e['created_at'] ?? '—') ?></td>
                <td><span class="event-badge <?= $badgeClass ?>"><?= esc($e['event_type']) ?></span></td>
                <td><?= esc((string) ($e['user_id'] ?? '—')) ?></td>
                <td><?= esc($e['email_attempted'] ?? '—') ?></td>
                <td><?= esc($e['reason_code'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<style>
.adm-page {
    display: flex;
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    margin-top: 0;
    min-height: calc(100vh - 60px);
    background: #edf2f7;
    overflow-x: hidden;
}
.adm-sidebar {
    width: 260px;
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-right: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 4px 0 24px rgba(42,106,126,0.08);
    padding: 28px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.adm-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
.adm-sidebar-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: #e0f0ff; color: #2a6a7e; font-size: 1.25rem;
    border: 2px solid rgba(42,106,126,0.08);
}
.adm-sidebar-name { font-size: 0.9rem; font-weight: 700; color: #0f172a; margin: 0; }
.adm-sidebar-role { font-size: 0.72rem; color: #2a6a7e; text-transform: uppercase; letter-spacing: 0.8px; }
.adm-sidebar-divider { border-color: #cce4ed; margin: 10px 0; }
.adm-nav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px; border-radius: 12px;
    font-size: 0.92rem; font-weight: 500;
    color: #2a6a7e; text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.adm-nav-item i { font-size: 1.15rem; }
.adm-nav-item:hover { background: rgba(204,228,237,0.6); color: #164a5c; }
.adm-nav-item.active {
    background: #2a6a7e; color: #ffffff;
    font-weight: 600; box-shadow: 0 4px 14px rgba(42,106,126,0.25);
}
.adm-main-content { flex: 1; padding: 32px 28px; min-width: 0; }
.adm-wrapper { width: 100%; }
.audit-stat-card {
    background: #fff; border-radius: 14px; padding: 18px;
    border: 1px solid #e2e8f0; box-shadow: 0 1px 4px rgba(15,23,42,0.05);
    display: flex; flex-direction: column; gap: 8px;
}
.audit-stat-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
.audit-stat-val  { font-size:2rem; font-weight:700; color:#0f172a; line-height:1; }
.audit-stat-lbl  { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.8px; color:#64748b; font-weight:600; }
.audit-panel { background:#fff; border-radius:14px; border:1px solid #e2e8f0; box-shadow:0 1px 4px rgba(15,23,42,0.05); overflow:hidden; }
.audit-panel-header { padding:14px 18px; font-size:0.85rem; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; background:#f8fafc; }
.audit-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
.audit-table th { padding:10px 14px; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:#64748b; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
.audit-table td { padding:10px 14px; color:#334155; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.audit-table tbody tr:last-child td { border-bottom:none; }
.audit-table tbody tr:hover { background:#f8fafc; }
.event-badge { padding:3px 8px; border-radius:999px; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; }
.badge-success { background:#d1fae5; color:#065f46; }
.badge-danger  { background:#fee2e2; color:#dc2626; }
.badge-warning { background:#fef3c7; color:#d97706; }
.badge-info    { background:#dbeafe; color:#1e40af; }
.role-badge { padding:2px 8px; border-radius:999px; font-size:0.7rem; font-weight:600; background:#e0f2fe; color:#0369a1; }
</style>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
