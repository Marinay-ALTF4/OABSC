<?php $pageTitle = 'Security Audit Log'; ?>
<?= view('layouts/admin', ['pageTitle' => $pageTitle, 'active' => 'audit']) ?>

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
