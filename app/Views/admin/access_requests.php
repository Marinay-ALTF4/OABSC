<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
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
        <!-- Sidebar -->
        <?= view('admin/_sidebar', ['sidebarActive' => 'access']) ?>

        <!-- Main Content -->
        <div class="adm-main-content">
            <div class="adm-wrapper">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="pl-title mb-1"><i class="bi bi-check-circle me-2"></i>Access Requests</h4>
        <p class="pl-sub mb-0">Review and manage pending access requests from users.</p>
    </div>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Pending Requests -->
<div class="adm-section-label mb-3">Pending Requests</div>
<?php if (empty($pending)): ?>
    <div class="alert alert-info">No pending access requests.</div>
<?php else: ?>
<div class="row g-3 mb-4">
    <?php foreach ($pending as $req):
        $userModel = new \App\Models\UserModel();
        $requester = $userModel->find($req['user_id']);
        $label = $req['resource'] === 'patient_records' ? 'Patient Records' : 'Clinic Reports';
    ?>
    <div class="col-12">
        <div class="adm-card d-flex align-items-center justify-content-between flex-wrap gap-2 p-3">
            <div>
                <span class="fw-semibold"><?= esc($requester['name'] ?? '—') ?></span>
                <span class="text-muted small ms-1">(<?= esc($requester['email'] ?? '—') ?>)</span>
                <span class="text-muted small ms-2">is requesting access to</span>
                <span class="badge bg-primary ms-1"><?= esc($label) ?></span>
            </div>
            <div class="d-flex gap-2">
                <form action="<?= site_url('/access-request/approve') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="pl-action-btn pl-action-approve"><i class="bi bi-check-lg me-1"></i>Approve</button>
                </form>
                <form action="<?= site_url('/access-request/approve') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <input type="hidden" name="action" value="deny">
                    <button type="submit" class="pl-action-btn pl-action-deny"><i class="bi bi-x-lg me-1"></i>Deny</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- All Requests History -->
<div class="adm-section-label mb-3">Request History</div>
<div class="pl-card">
    <table class="pl-table">
        <thead>
            <tr><th>#</th><th>User</th><th>Email</th><th>Resource</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if (empty($all)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No requests found.</td></tr>
            <?php else: ?>
                <?php foreach ($all as $i => $r): ?>
                <tr>
                    <td class="pl-id"><?= $i + 1 ?></td>
                    <td class="pl-name"><?= esc($r['user_name'] ?? '—') ?></td>
                    <td class="pl-email"><?= esc($r['user_email'] ?? '—') ?></td>
                    <td class="pl-email"><?= esc($r['resource'] === 'patient_records' ? 'Patient Records' : 'Clinic Reports') ?></td>
                    <td>
                        <?php
                        $statusStyle = match($r['status']) {
                            'approved' => 'background:#d1fae5;color:#065f46;',
                            'denied'   => 'background:#fee2e2;color:#991b1b;',
                            default    => 'background:#fef9c3;color:#854d0e;',
                        };
                        ?>
                        <span class="pl-status-badge" style="<?= $statusStyle ?>"><?= ucfirst(esc($r['status'])) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    .adm-section-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #5a7288; }
    .adm-card {
        background: rgba(255,255,255,0.96); border-radius: 18px; padding: 20px 18px;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,0.06);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .pl-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden;
    }
    .pl-action-btn {
        font-size: 0.75rem; font-weight: 600; padding: 5px 14px;
        border-radius: 8px; border: none; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: all 0.15s;
    }
    .pl-action-approve { background: #d1fae5; color: #065f46; }
    .pl-action-approve:hover { background: #a7f3d0; color: #064e3b; }
    .pl-action-deny { background: #fee2e2; color: #991b1b; }
    .pl-action-deny:hover { background: #fecaca; color: #7f1d1d; }
    .pl-table { font-size: 0.85rem; width: 100%; border-collapse: collapse; }
    .pl-table thead tr { background: #f8fafc; }
    .pl-table thead th {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.6px; color: #5a7288; padding: 0.85rem 1rem;
        border-bottom: 1px solid #e2e8f0; white-space: nowrap;
    }
    .pl-table tbody tr { transition: background 0.12s; }
    .pl-table tbody tr:hover { background: #f8fafc; }
    .pl-table tbody td { padding: 0.8rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .pl-table tbody tr:last-child td { border-bottom: none; }
    .pl-status-badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
</style>
</body>
</html>
