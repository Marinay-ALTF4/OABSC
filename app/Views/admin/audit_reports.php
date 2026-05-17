<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';

// View variables passed from AuditReport controller
/** @var string $filter */
/** @var int $total_success */
/** @var int $total_failed */
/** @var int $total_locked */
/** @var int $total_suspicious */
/** @var int $total_mfa_success */
/** @var int $total_mfa_failed */
/** @var int $total_logout */
/** @var int $active_sessions */
/** @var int $alert_count */
/** @var array $events */
/** @var array $chart_labels */
/** @var array $chart_success */
/** @var array $chart_failed */
/** @var string $since */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <!-- Sidebar -->
        <?= view('admin/_sidebar', ['sidebarActive' => 'audit_reports']) ?>

        <!-- Main Content -->
        <div class="adm-main-content">
            <div class="adm-wrapper">

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Security Audit Reports</h5>
        <p class="text-muted small mb-0">Generated: <?= esc(date('F j, Y g:i A')) ?> · Period: <?= esc(ucfirst($filter)) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <!-- Date Filter -->
        <div class="btn-group" role="group">
            <a href="?filter=daily"   class="btn btn-sm <?= $filter === 'daily'   ? 'btn-primary' : 'btn-outline-secondary' ?>">Daily</a>
            <a href="?filter=weekly"  class="btn btn-sm <?= $filter === 'weekly'  ? 'btn-primary' : 'btn-outline-secondary' ?>">Weekly</a>
            <a href="?filter=monthly" class="btn btn-sm <?= $filter === 'monthly' ? 'btn-primary' : 'btn-outline-secondary' ?>">Monthly</a>
        </div>
        <!-- Export CSV -->
        <a href="<?= site_url('/admin/audit-reports/export?filter=' . esc($filter)) ?>" class="btn btn-sm btn-success">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="ar-card ar-card-success">
            <div class="ar-card-icon"><i class="bi bi-box-arrow-in-right"></i></div>
            <div class="ar-card-val"><?= esc((string) $total_success) ?></div>
            <div class="ar-card-lbl">Successful Logins</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="ar-card ar-card-danger">
            <div class="ar-card-icon"><i class="bi bi-x-circle"></i></div>
            <div class="ar-card-val"><?= esc((string) $total_failed) ?></div>
            <div class="ar-card-lbl">Failed Logins</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="ar-card ar-card-warning">
            <div class="ar-card-icon"><i class="bi bi-lock"></i></div>
            <div class="ar-card-val"><?= esc((string) $total_locked) ?></div>
            <div class="ar-card-lbl">Locked Accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="ar-card ar-card-orange">
            <div class="ar-card-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="ar-card-val"><?= esc((string) $total_suspicious) ?></div>
            <div class="ar-card-lbl">Suspicious Activity</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="ar-card ar-card-blue">
            <div class="ar-card-icon"><i class="bi bi-shield-check"></i></div>
            <div class="ar-card-val"><?= esc((string) $total_mfa_success) ?></div>
            <div class="ar-card-lbl">MFA Successes</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="ar-card ar-card-purple">
            <div class="ar-card-icon"><i class="bi bi-people"></i></div>
            <div class="ar-card-val"><?= esc((string) $active_sessions) ?></div>
            <div class="ar-card-lbl">Active Sessions</div>
        </div>
    </div>
</div>

<!-- Chart -->
<div class="ar-panel mb-4">
    <div class="ar-panel-header"><i class="bi bi-bar-chart-line me-2"></i>Login Activity — <?= esc(ucfirst($filter)) ?> Breakdown</div>
    <div class="p-3" style="height:260px;">
        <canvas id="loginChart"></canvas>
    </div>
</div>

<!-- MFA Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="ar-panel h-100">
            <div class="ar-panel-header"><i class="bi bi-shield-lock me-2"></i>MFA Statistics</div>
            <div class="p-3">
                <table class="ar-table">
                    <tbody>
                        <tr><td>MFA Successes</td><td class="fw-bold text-success"><?= esc((string) $total_mfa_success) ?></td></tr>
                        <tr><td>MFA Failures</td><td class="fw-bold text-danger"><?= esc((string) $total_mfa_failed) ?></td></tr>
                        <tr><td>Logouts</td><td class="fw-bold"><?= esc((string) $total_logout) ?></td></tr>
                        <tr><td>Security Alerts Sent</td><td class="fw-bold text-warning"><?= esc((string) $alert_count) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ar-panel h-100">
            <div class="ar-panel-header"><i class="bi bi-pie-chart me-2"></i>Event Distribution</div>
            <div class="p-3" style="height:180px;">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Event Log Table -->
<div class="ar-panel">
    <div class="ar-panel-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-text me-2"></i>Event Log (last 100 events)</span>
        <span class="text-muted" style="font-size:0.75rem;">Since <?= esc(date('M j, Y g:i A', strtotime($since))) ?></span>
    </div>
    <?php if (empty($events)): ?>
        <div class="text-muted small p-4 text-center">No events recorded for this period.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="ar-table ar-table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Timestamp</th>
                    <th>Event</th>
                    <th>User ID</th>
                    <th>Email Attempted</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $i => $e): ?>
                <?php
                    $badge = match($e['event_type']) {
                        'login_success', 'mfa_success' => 'badge-success',
                        'login_failed',  'mfa_failed'  => 'badge-danger',
                        'suspicious_activity', 'login_locked' => 'badge-warning',
                        'logout'                       => 'badge-secondary',
                        default                        => 'badge-info',
                    };
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td style="white-space:nowrap;font-size:0.78rem;"><?= esc($e['created_at'] ?? '—') ?></td>
                    <td><span class="ar-badge <?= $badge ?>"><?= esc($e['event_type']) ?></span></td>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels  = <?= json_encode($chart_labels) ?>;
const success = <?= json_encode($chart_success) ?>;
const failed  = <?= json_encode($chart_failed) ?>;

// Bar chart
new Chart(document.getElementById('loginChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Successful Logins', data: success, backgroundColor: 'rgba(16,185,129,0.7)', borderRadius: 6 },
            { label: 'Failed Logins',     data: failed,  backgroundColor: 'rgba(239,68,68,0.7)',  borderRadius: 6 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// Pie chart
new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Success', 'Failed', 'Locked', 'Suspicious', 'MFA OK', 'MFA Fail'],
        datasets: [{
            data: [
                <?= (int) $total_success ?>,
                <?= (int) $total_failed ?>,
                <?= (int) $total_locked ?>,
                <?= (int) $total_suspicious ?>,
                <?= (int) $total_mfa_success ?>,
                <?= (int) $total_mfa_failed ?>
            ],
            backgroundColor: [
                '#10b981','#ef4444','#f59e0b','#f97316','#3b82f6','#8b5cf6'
            ],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'right', labels: { font: { size: 11 } } } }
    }
});
</script>

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
.ar-card {
    background: #fff; border-radius: 14px; padding: 16px;
    border: 1px solid #e2e8f0; box-shadow: 0 1px 4px rgba(15,23,42,0.05);
    display: flex; flex-direction: column; gap: 6px; height: 100%;
}
.ar-card-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
.ar-card-val  { font-size:1.9rem; font-weight:700; color:#0f172a; line-height:1; }
.ar-card-lbl  { font-size:0.68rem; text-transform:uppercase; letter-spacing:0.8px; color:#64748b; font-weight:600; }
.ar-card-success .ar-card-icon { background:#d1fae5; color:#065f46; }
.ar-card-danger  .ar-card-icon { background:#fee2e2; color:#dc2626; }
.ar-card-warning .ar-card-icon { background:#fef3c7; color:#d97706; }
.ar-card-orange  .ar-card-icon { background:#ffedd5; color:#ea580c; }
.ar-card-blue    .ar-card-icon { background:#dbeafe; color:#1e40af; }
.ar-card-purple  .ar-card-icon { background:#ede9fe; color:#6d28d9; }
.ar-panel { background:#fff; border-radius:14px; border:1px solid #e2e8f0; box-shadow:0 1px 4px rgba(15,23,42,0.05); overflow:hidden; }
.ar-panel-header { padding:12px 18px; font-size:0.85rem; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; background:#f8fafc; }
.ar-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
.ar-table th { padding:10px 14px; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:#64748b; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
.ar-table td { padding:10px 14px; color:#334155; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.ar-table tbody tr:last-child td { border-bottom:none; }
.ar-table-striped tbody tr:hover { background:#f8fafc; }
.ar-badge { padding:3px 8px; border-radius:999px; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap; }
.badge-success   { background:#d1fae5; color:#065f46; }
.badge-danger    { background:#fee2e2; color:#dc2626; }
.badge-warning   { background:#fef3c7; color:#d97706; }
.badge-secondary { background:#f1f5f9; color:#475569; }
.badge-info      { background:#dbeafe; color:#1e40af; }
</style>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
