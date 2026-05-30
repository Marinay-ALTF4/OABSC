<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';

// View variables passed from controller
/** @var array $summary */
/** @var int $failed24 */
/** @var int $suspicious */
/** @var array $sessions */
/** @var array $events */
/** @var int $activeCount */

if (!function_exists('parseUserAgent')) {
    function parseUserAgent(string $ua): array {
        $browser = 'Unknown';
        $os = 'Unknown';
        $icon = 'bi-laptop';

        // Parse OS
        if (stripos($ua, 'windows') !== false) {
            $os = 'Windows';
            $icon = 'bi-windows';
        } elseif (stripos($ua, 'macintosh') !== false || stripos($ua, 'mac os x') !== false) {
            $os = 'macOS';
            $icon = 'bi-apple';
        } elseif (stripos($ua, 'iphone') !== false) {
            $os = 'iPhone';
            $icon = 'bi-phone';
        } elseif (stripos($ua, 'android') !== false) {
            $os = 'Android';
            $icon = 'bi-android';
        } elseif (stripos($ua, 'linux') !== false) {
            $os = 'Linux';
            $icon = 'bi-ubuntu';
        }

        // Parse Browser
        if (stripos($ua, 'firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'chrome') !== false && stripos($ua, 'edg') === false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'safari') !== false && stripos($ua, 'chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($ua, 'edg') !== false) {
            $browser = 'Edge';
        }

        return ['browser' => $browser, 'os' => $os, 'icon' => $icon];
    }
}

if (! function_exists('prettyAuditCode')) {
    function prettyAuditCode($value): string
    {
        $s = trim((string) $value);
        if ($s === '' || $s === '—') {
            return '—';
        }

        $s = str_replace(['_', '-'], ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;

        return ucwords($s);
    }
}
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
        <?= view('admin/_sidebar', ['sidebarActive' => 'audit_log']) ?>

        <!-- Main Content -->
        <div class="adm-main-content">
            <div class="adm-wrapper">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="pl-title mb-1"><i class="bi bi-shield-lock me-2"></i>Security Audit Log</h4>
        <p class="pl-sub mb-0">Last 7 days summary · <?= esc(date('F j, Y')) ?></p>
    </div>
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
            <div class="audit-stat-val"><?= esc((string) $activeCount) ?></div>
            <div class="audit-stat-lbl">Active Sessions (8h)</div>
        </div>
    </div>
</div>

<!-- Active Sessions -->
<div class="audit-panel mb-4">
    <div class="audit-panel-header"><i class="bi bi-person-check me-2"></i>User Sessions & Activity Logs</div>
    <?php if (empty($sessions)): ?>
        <div class="text-muted small p-3">No sessions recorded.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="audit-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Location / IP</th>
                <th>Device & Browser</th>
                <th>Login Time</th>
                <th>Status</th>
                <th>Time Active</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sessions as $i => $s): ?>
            <?php
                $uaInfo = parseUserAgent($s['user_agent'] ?? '');
                $loginTime = strtotime($s['issued_at']);
                $isActive = is_null($s['revoked_at']) && strtotime((string)$s['expires_at']) > time();
                
                if ($isActive) {
                    $lastActive = $s['last_active_at'] ? strtotime($s['last_active_at']) : time();
                    $diffSecs = max(0, $lastActive - $loginTime);
                    $duration = round($diffSecs / 60);
                    $statusHtml = '<span class="status-badge badge-active"><span class="pulse-dot"></span>Active now</span>';
                    $durationText = $duration . ' min' . ($duration == 1 ? '' : 's');
                } else {
                    $lastActive = $s['revoked_at'] ? strtotime($s['revoked_at']) : ($s['last_active_at'] ? strtotime($s['last_active_at']) : strtotime($s['expires_at']));
                    $diffSecs = max(0, $lastActive - $loginTime);
                    $duration = round($diffSecs / 60);
                    
                    if (!is_null($s['revoked_at'])) {
                        $statusHtml = '<span class="status-badge badge-revoked">Logged out</span>';
                    } else {
                        $statusHtml = '<span class="status-badge badge-expired">Expired</span>';
                    }
                    $durationText = $duration . ' min' . ($duration == 1 ? '' : 's');
                }
                
                $ipAddress = $s['ip_address'] ?? 'Unknown';
                if ($ipAddress === '::1') {
                    $ipAddress = 'Localhost (::1)';
                }
            ?>
            <tr>
                <td>
                    <div style="font-weight:600;color:#0f172a;"><?= esc($s['name']) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;"><?= esc($s['email']) ?></div>
                </td>
                <td><span class="role-badge"><?= esc(prettyAuditCode($s['role'] ?? '')) ?></span></td>
                <td>
                    <div style="font-weight:500;color:#334155;"><i class="bi bi-geo-alt me-1 text-muted"></i><?= esc($ipAddress) ?></div>
                </td>
                <td>
                    <div class="device-info text-nowrap">
                        <i class="bi <?= esc($uaInfo['icon']) ?> me-1"></i>
                        <span><?= esc($uaInfo['os']) ?> · <?= esc($uaInfo['browser']) ?></span>
                    </div>
                </td>
                <td>
                    <div style="color:#475569;" class="text-nowrap"><?= esc($s['issued_at']) ?></div>
                </td>
                <td><?= $statusHtml ?></td>
                <td>
                    <div
                        class="session-duration"
                        style="font-weight:600;color:#0f172a;"
                        data-issued-at="<?= esc($s['issued_at'] ?? '') ?>"
                        data-revoked-at="<?= esc($s['revoked_at'] ?? '') ?>"
                        data-expires-at="<?= esc($s['expires_at'] ?? '') ?>"
                    ><?= esc($durationText) ?></div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
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
            <tr><th>#</th><th>Time</th><th>Event</th><th>User ID</th><th>Role</th><th>Email</th><th>Time Active</th><th>Reason</th></tr>
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
                $timeActiveId = 'time-active-' . $i;
            ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td style="white-space:nowrap;"><?= esc($e['created_at'] ?? '—') ?></td>
                <td><span class="event-badge <?= $badgeClass ?>"><?= esc(prettyAuditCode($e['event_type'] ?? '')) ?></span></td>
                <td><?= esc((string) ($e['user_id'] ?? '—')) ?></td>
                <td><?= esc($e['display_role'] ?? '—') ?></td>
                <td><?= esc($e['display_email'] ?? $e['email_attempted'] ?? '—') ?></td>
                <td>
                    <?php if (($e['event_type'] ?? '') === 'logout' && ! empty($e['time_active_issued_at']) && ! empty($e['time_active_revoked_at'])): ?>
                        <button
                            class="btn btn-sm btn-outline-primary"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#<?= esc($timeActiveId) ?>"
                            aria-expanded="false"
                            aria-controls="<?= esc($timeActiveId) ?>"
                        >
                            View
                        </button>
                        <div class="collapse mt-2" id="<?= esc($timeActiveId) ?>">
                            <div
                                class="session-duration small text-muted"
                                style="display:inline-block;font-weight:600;color:#0f172a;"
                                data-issued-at="<?= esc($e['time_active_issued_at']) ?>"
                                data-revoked-at="<?= esc($e['time_active_revoked_at']) ?>"
                            ><?= esc($e['time_active'] ?? '—') ?></div>
                        </div>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= esc($e['reason_display'] ?? prettyAuditCode($e['reason_code'] ?? '—')) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<script>
(function () {
    function formatDuration(seconds) {
        seconds = Math.max(0, Math.floor(seconds));
        var minutes = Math.floor(seconds / 60);
        var hours = Math.floor(minutes / 60);
        var days = Math.floor(hours / 24);

        if (days > 0) {
            return days + ' day' + (days === 1 ? '' : 's') + ' ' + (hours % 24) + ' hr' + ((hours % 24) === 1 ? '' : 's');
        }
        if (hours > 0) {
            return hours + ' hr' + (hours === 1 ? '' : 's') + ' ' + (minutes % 60) + ' min' + ((minutes % 60) === 1 ? '' : 's');
        }
        return minutes + ' min' + (minutes === 1 ? '' : 's');
    }

    function parseTime(value) {
        var ts = Date.parse(value);
        return isNaN(ts) ? null : ts;
    }

    function updateDurations() {
        document.querySelectorAll('.session-duration').forEach(function (el) {
            var issuedAt = parseTime(el.dataset.issuedAt || '');
            if (issuedAt === null) {
                return;
            }

            var revokedAt = parseTime(el.dataset.revokedAt || '');
            var expiresAt = parseTime(el.dataset.expiresAt || '');
            var now = Date.now();
            var endAt = revokedAt !== null ? revokedAt : (expiresAt !== null ? Math.min(now, expiresAt) : now);

            el.textContent = formatDuration((endAt - issuedAt) / 1000);
        });
    }

    updateDurations();
    setInterval(updateDurations, 1000);
    setInterval(function () {
        window.location.reload();
    }, 30000);
})();
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { background: #edf2f7; font-family: 'Inter', sans-serif; }
    .dashboard-wrapper { width: 100%; }
    .adm-page {
        display: flex; width: 100vw; position: relative;
        left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw;
        margin-top: 0; min-height: calc(100vh - 60px);
        background: #edf2f7; overflow-x: hidden;
    }
    .adm-sidebar {
        width: 260px; flex-shrink: 0;
        background: rgba(255,255,255,0.55); backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255,255,255,0.6);
        box-shadow: 4px 0 24px rgba(42,106,126,0.08);
        padding: 28px 16px; display: flex; flex-direction: column; gap: 6px;
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
    .pl-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
    .pl-sub   { font-size: 0.85rem; color: #64748b; }
    .audit-stat-card {
        background: #fff; border-radius: 18px; padding: 20px 18px;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,0.06);
        display: flex; flex-direction: column; gap: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .audit-stat-card:hover { transform: translateY(-2px); box-shadow: 0 7px 18px rgba(15,23,42,0.12); }
    .audit-stat-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
    .audit-stat-val  { font-size:2rem; font-weight:700; color:#0f172a; line-height:1; }
    .audit-stat-lbl  { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.8px; color:#64748b; font-weight:600; }
    .audit-panel { background:#fff; border-radius:18px; border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(15,23,42,0.06); overflow:hidden; }
    .audit-panel-header { padding:14px 18px; font-size:0.85rem; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; background:#f8fafc; }
    .audit-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
    .audit-table th { padding:10px 14px; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.7px; color:#64748b; border-bottom:1px solid #e2e8f0; background:#f8fafc; }
    .audit-table td { padding:10px 14px; color:#334155; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .audit-table tbody tr:last-child td { border-bottom:none; }
    .audit-table tbody tr:hover { background:#f8fafc; }
    .event-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }
    .badge-success { background:#d1fae5; color:#065f46; }
    .badge-danger  { background:#fee2e2; color:#dc2626; }
    .badge-warning { background:#fef3c7; color:#d97706; }
    .badge-info    { background:#dbeafe; color:#1e40af; }
    .role-badge { padding:2px 8px; border-radius:999px; font-size:0.7rem; font-weight:600; background:#e0f2fe; color:#0369a1; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .badge-active {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .badge-revoked {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #cbd5e1;
    }
    .badge-expired {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .pulse-dot {
        width: 8px;
        height: 8px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
        animation: pulse-animation 1.5s infinite;
    }
    @keyframes pulse-animation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    .device-info {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #334155;
    }
    .device-info i {
        font-size: 1.1rem;
        color: #64748b;
    }
</style>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php echo view('layouts/_chat_widget'); ?>
</body>
</html>
