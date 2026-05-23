<?php
use App\Libraries\PermissionManager;
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';

// Role color map
$roleColors = [
    'client'              => ['bg' => '#fef3c7', 'color' => '#92400e'],
    'doctor'              => ['bg' => '#ede9fe', 'color' => '#7c3aed'],
    'secretary'           => ['bg' => '#d1fae5', 'color' => '#065f46'],
    'assistant_admin'     => ['bg' => '#dbeafe', 'color' => '#1e40af'],
    'assistant_secretary' => ['bg' => '#cffafe', 'color' => '#0e7490'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <?= view('admin/_sidebar', ['sidebarActive' => 'access']) ?>

        <div class="adm-main-content">
            <div class="adm-wrapper">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="pl-title mb-1"><i class="bi bi-check-circle me-2"></i>Access Requests</h4>
                        <p class="pl-sub mb-0">Review and manage feature access requests from users.</p>
                    </div>
                </div>

                <?php if (session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
                        <?= esc(session('success')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- ── Pending Requests ── -->
                <div class="adm-section-label mb-3">
                    Pending Requests
                    <?php if (! empty($pending)): ?>
                        <span class="pending-count-badge"><?= count($pending) ?></span>
                    <?php endif; ?>
                </div>

                <?php if (empty($pending)): ?>
                    <div class="empty-pending mb-4">
                        <i class="bi bi-inbox"></i>
                        <span>No pending access requests.</span>
                    </div>
                <?php else: ?>
                    <div class="row g-3 mb-5">
                        <?php foreach ($pending as $req):
                            $userModel  = new \App\Models\UserModel();
                            $requester  = $userModel->find($req['user_id']);
                            $permCode   = $req['permission_code'] ?? $req['resource'];
                            $permLabel  = PermissionManager::$definitions[$permCode]['label'] ?? $permCode;
                            $reqRole    = $req['requested_role'] ?? 'user';
                            $roleStyle  = $roleColors[$reqRole] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
                            $initials   = strtoupper(substr($requester['name'] ?? 'U', 0, 2));
                        ?>
                        <div class="col-12 col-md-6">
                            <div class="req-card">
                                <div class="req-card-top">
                                    <div class="req-avatar"><?= $initials ?></div>
                                    <div class="req-user-info">
                                        <div class="req-user-name"><?= esc($requester['name'] ?? '—') ?></div>
                                        <div class="req-user-email"><?= esc($requester['email'] ?? '—') ?></div>
                                    </div>
                                    <span class="req-role-badge" style="background:<?= $roleStyle['bg'] ?>;color:<?= $roleStyle['color'] ?>;">
                                        <?= esc(str_replace('_', ' ', ucfirst($reqRole))) ?>
                                    </span>
                                </div>

                                <div class="req-perm-row">
                                    <i class="bi bi-lock-fill req-perm-icon"></i>
                                    <div>
                                        <div class="req-perm-label">Requesting access to:</div>
                                        <div class="req-perm-name"><?= esc($permLabel) ?></div>
                                        <div class="req-perm-code"><?= esc($permCode) ?></div>
                                    </div>
                                </div>

                                <div class="req-time">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= esc(date('M j, Y g:i A', strtotime($req['created_at'] ?? 'now'))) ?>
                                </div>

                                <div class="req-actions">
                                    <form action="<?= site_url('/access-request/approve') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="req-btn req-btn-approve">
                                            <i class="bi bi-check-lg me-1"></i>Approve
                                        </button>
                                    </form>
                                    <form action="<?= site_url('/access-request/approve') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="action" value="deny">
                                        <button type="submit" class="req-btn req-btn-deny">
                                            <i class="bi bi-x-lg me-1"></i>Deny
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- ── Request History ── -->
                <div class="adm-section-label mb-3">Request History</div>
                <div class="pl-card">
                    <div class="table-responsive">
                        <table class="table pl-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Feature Requested</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($all)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No requests found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($all as $i => $r):
                                        $permCode  = $r['permission_code'] ?? $r['resource'];
                                        $permLabel = PermissionManager::$definitions[$permCode]['label'] ?? $permCode;
                                        $reqRole   = $r['requested_role'] ?? '—';
                                        $roleStyle = $roleColors[$reqRole] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
                                        $statusStyle = match($r['status']) {
                                            'approved' => 'background:#d1fae5;color:#065f46;',
                                            'denied'   => 'background:#fee2e2;color:#991b1b;',
                                            default    => 'background:#fef9c3;color:#854d0e;',
                                        };
                                    ?>
                                    <tr>
                                        <td class="pl-id"><?= $i + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="hist-avatar"><?= strtoupper(substr($r['user_name'] ?? 'U', 0, 2)) ?></div>
                                                <div>
                                                    <div class="pl-name"><?= esc($r['user_name'] ?? '—') ?></div>
                                                    <div class="pl-email"><?= esc($r['user_email'] ?? '—') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="req-role-badge" style="background:<?= $roleStyle['bg'] ?>;color:<?= $roleStyle['color'] ?>;">
                                                <?= esc(str_replace('_', ' ', ucfirst($reqRole))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="pl-name" style="font-size:0.82rem;"><?= esc($permLabel) ?></div>
                                            <div class="pl-email" style="font-size:0.72rem;"><?= esc($permCode) ?></div>
                                        </td>
                                        <td class="pl-date"><?= esc(date('M j, Y', strtotime($r['created_at'] ?? 'now'))) ?></td>
                                        <td>
                                            <span class="pl-status-badge" style="<?= $statusStyle ?>">
                                                <?= ucfirst(esc($r['status'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { background: #edf2f7; font-family: 'Inter', sans-serif; }
    .dashboard-wrapper { width: 100%; }
    .adm-page {
        display: flex; width: 100vw; position: relative;
        left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw;
        min-height: calc(100vh - 60px); background: #edf2f7; overflow-x: hidden;
    }
    .adm-sidebar {
        width: 260px; flex-shrink: 0;
        background: rgba(255,255,255,0.55); backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255,255,255,0.6);
        box-shadow: 4px 0 24px rgba(42,106,126,0.08);
        padding: 28px 16px; display: flex; flex-direction: column; gap: 6px;
    }
    .adm-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
    .adm-sidebar-avatar { width:44px;height:44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#e0f0ff;color:#2a6a7e;font-size:1.25rem; }
    .adm-sidebar-name { font-size:0.9rem;font-weight:700;color:#0f172a;margin:0; }
    .adm-sidebar-role { font-size:0.72rem;color:#2a6a7e;text-transform:uppercase;letter-spacing:0.8px; }
    .adm-sidebar-divider { border-color:#cce4ed;margin:10px 0; }
    .adm-nav-item { display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;font-size:0.92rem;font-weight:500;color:#2a6a7e;text-decoration:none;transition:background 0.15s,color 0.15s; }
    .adm-nav-item i { font-size:1.15rem; }
    .adm-nav-item:hover { background:rgba(204,228,237,0.6);color:#164a5c; }
    .adm-nav-item.active { background:#2a6a7e;color:#fff;font-weight:600;box-shadow:0 4px 14px rgba(42,106,126,0.25); }
    .adm-nav-item.nav-disabled { color:#94a3b8!important;cursor:not-allowed;pointer-events:none;opacity:0.55; }
    .adm-main-content { flex:1;padding:32px 28px;min-width:0; }
    .adm-wrapper { width:100%; }
    .pl-title { font-size:1.3rem;font-weight:700;color:#0f172a; }
    .pl-sub   { font-size:0.85rem;color:#64748b; }
    .adm-section-label { font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#5a7288;display:flex;align-items:center;gap:8px; }
    .pending-count-badge { background:#ef4444;color:white;font-size:0.65rem;font-weight:700;padding:1px 7px;border-radius:999px; }

    /* Empty state */
    .empty-pending {
        background:white;border-radius:14px;border:1px solid #e2e8f0;
        padding:1.5rem;display:flex;align-items:center;gap:10px;
        color:#94a3b8;font-size:0.85rem;
    }
    .empty-pending i { font-size:1.4rem; }

    /* Request Card */
    .req-card {
        background:white;border-radius:18px;border:1px solid #e2e8f0;
        box-shadow:0 2px 8px rgba(15,23,42,0.06);
        padding:1.25rem;transition:box-shadow 0.15s;
    }
    .req-card:hover { box-shadow:0 6px 20px rgba(15,23,42,0.1); }
    .req-card-top { display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem; }
    .req-avatar {
        width:42px;height:42px;border-radius:50%;flex-shrink:0;
        background:linear-gradient(135deg,#3b556e,#2e445a);
        display:flex;align-items:center;justify-content:center;
        font-size:0.8rem;font-weight:700;color:white;
    }
    .req-user-info { flex:1;min-width:0; }
    .req-user-name  { font-size:0.88rem;font-weight:700;color:#0f172a; }
    .req-user-email { font-size:0.75rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .req-role-badge { font-size:0.68rem;font-weight:700;padding:3px 10px;border-radius:999px;white-space:nowrap;flex-shrink:0; }

    .req-perm-row {
        display:flex;align-items:flex-start;gap:0.75rem;
        background:#f8fafc;border-radius:12px;padding:0.75rem 1rem;
        margin-bottom:0.75rem;
    }
    .req-perm-icon { font-size:1.1rem;color:#f59e0b;margin-top:2px;flex-shrink:0; }
    .req-perm-label { font-size:0.72rem;color:#94a3b8;font-weight:500;margin-bottom:2px; }
    .req-perm-name  { font-size:0.88rem;font-weight:700;color:#0f172a; }
    .req-perm-code  { font-size:0.72rem;color:#64748b;font-family:monospace; }

    .req-time { font-size:0.75rem;color:#94a3b8;margin-bottom:1rem; }

    .req-actions { display:flex;gap:8px; }
    .req-btn {
        font-size:0.78rem;font-weight:600;padding:6px 16px;
        border-radius:9px;border:none;cursor:pointer;
        display:inline-flex;align-items:center;transition:all 0.15s;
    }
    .req-btn-approve { background:#d1fae5;color:#065f46; }
    .req-btn-approve:hover { background:#a7f3d0; }
    .req-btn-deny { background:#fee2e2;color:#991b1b; }
    .req-btn-deny:hover { background:#fecaca; }

    /* History table */
    .pl-card { background:white;border-radius:18px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(15,23,42,0.06);overflow:hidden; }
    .pl-table { font-size:0.85rem; }
    .pl-table thead tr { background:#f8fafc; }
    .pl-table thead th { font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#5a7288;padding:0.85rem 1rem;border-bottom:1px solid #e2e8f0;white-space:nowrap; }
    .pl-table tbody tr { transition:background 0.12s; }
    .pl-table tbody tr:hover { background:#f8fafc; }
    .pl-table tbody td { padding:0.8rem 1rem;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
    .pl-table tbody tr:last-child td { border-bottom:none; }
    .pl-id    { color:#94a3b8;font-size:0.78rem;font-weight:600; }
    .pl-name  { font-weight:600;color:#0f172a; }
    .pl-email { color:#475569;font-size:0.78rem; }
    .pl-date  { color:#64748b;font-size:0.82rem;white-space:nowrap; }
    .pl-status-badge { font-size:0.72rem;font-weight:700;padding:3px 10px;border-radius:999px;white-space:nowrap; }
    .hist-avatar {
        width:30px;height:30px;border-radius:50%;flex-shrink:0;
        background:linear-gradient(135deg,#3b556e,#2e445a);
        display:flex;align-items:center;justify-content:center;
        font-size:0.65rem;font-weight:700;color:white;
    }
</style>
<?php echo view('layouts/_chat_widget'); ?>
</body>
</html>
