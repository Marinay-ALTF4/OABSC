<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <?= view('admin/_sidebar', ['sidebarActive' => 'appointments']) ?>

        <div class="adm-main-content">
            <div class="adm-wrapper">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="pl-title mb-1"><i class="bi bi-calendar-event me-2"></i>All Appointments</h4>
                        <p class="pl-sub mb-0"><?= esc((string) count($appointments)) ?> total appointments</p>
                    </div>
                </div>

                <?php if (session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show py-2">
                        <?= esc(session('success')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="pl-card">
                    <div class="table-responsive">
                        <table class="table pl-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">No appointments found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $i => $a): ?>
                                    <tr>
                                        <td class="pl-id"><?= $i + 1 ?></td>
                                        <td class="pl-name"><?= esc($a['patient_name'] ?? '—') ?></td>
                                        <td class="pl-email"><?= esc($a['doctor_name'] ?? '—') ?></td>
                                        <td class="pl-email"><?= esc($a['reason'] ?? '—') ?></td>
                                        <td class="pl-date"><?= esc($a['appointment_date'] ?? '—') ?></td>
                                        <td class="pl-date"><?= esc(substr((string) ($a['appointment_time'] ?? ''), 0, 5)) ?></td>
                                        <td>
                                            <?php
                                            $s   = strtolower($a['status'] ?? '');
                                            $statusStyle = match($s) {
                                                'confirmed' => 'background:#d1fae5;color:#065f46;',
                                                'completed' => 'background:#dbeafe;color:#1e40af;',
                                                'cancelled' => 'background:#fee2e2;color:#991b1b;',
                                                'serving'   => 'background:#fef3c7;color:#92400e;',
                                                default     => 'background:#f1f5f9;color:#475569;',
                                            };
                                            ?>
                                            <span class="pl-status-badge" style="<?= $statusStyle ?>"><?= ucfirst($s) ?></span>
                                        </td>
                                        <td>
                                            <form method="post" action="<?= site_url('/admin/appointments/update-status') ?>" class="d-flex gap-1">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                                <select name="status" class="form-select form-select-sm" style="width:120px;font-size:0.78rem;border-radius:8px;border-color:#e2e8f0;">
                                                    <option value="pending"   <?= $a['status']==='pending'   ? 'selected' : '' ?>>Pending</option>
                                                    <option value="confirmed" <?= $a['status']==='confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                    <option value="cancelled" <?= $a['status']==='cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                                <button class="pl-action-btn pl-action-edit">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
    .pl-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden;
    }
    .pl-table { font-size: 0.85rem; }
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
    .pl-id    { color: #94a3b8; font-size: 0.78rem; font-weight: 600; }
    .pl-name  { font-weight: 600; color: #0f172a; }
    .pl-email { color: #475569; font-size: 0.82rem; }
    .pl-date  { color: #64748b; font-size: 0.82rem; }
    .pl-status-badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .pl-action-btn {
        font-size: 0.75rem; font-weight: 600; padding: 5px 12px;
        border-radius: 8px; border: none; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: all 0.15s; margin-left: 4px;
    }
    .pl-action-edit { background: #dbeafe; color: #1e40af; }
    .pl-action-edit:hover { background: #bfdbfe; color: #1e3a8a; }
</style>
</body>
</html>
