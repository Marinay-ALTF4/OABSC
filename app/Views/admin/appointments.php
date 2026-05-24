<?php
$role = session('user_role') ?? 'guest';
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

                <?php if (session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
                        <?= esc(session('success')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
                        <?= esc(session('error')) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- ── SECTION TABS: Pending / Confirmed / Archive ── -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="pl-title mb-1"><i class="bi bi-calendar-event me-2"></i>All Appointments</h4>
                        <p class="pl-sub mb-0"><?= count($pending) + count($confirmed) ?> active appointments</p>
                    </div>
                </div>
                <div class="section-tabs-wrap mb-3">
                    <ul class="nav section-tabs" id="apptTabs">
                        <li class="nav-item">
                            <button class="section-tab active" data-target="tab-pending">
                                <i class="bi bi-hourglass-split me-1"></i>Pending
                                <span class="tab-badge"><?= count($pending) ?></span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="section-tab" data-target="tab-confirmed">
                                <i class="bi bi-check-circle me-1"></i>Confirmed
                                <span class="tab-badge tab-badge-green"><?= count($confirmed) ?></span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="section-tab" data-target="tab-archive">
                                <i class="bi bi-archive me-1"></i>Archive
                                <span class="tab-badge tab-badge-gray"><?= count($archived) ?></span>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Pending Tab -->
                <div id="tab-pending" class="tab-panel">
                    <div class="pl-card mb-4">
                        <div class="table-responsive">
                            <table class="table pl-table align-middle mb-0">
                                <thead>
                                    <tr><th>#</th><th>Patient</th><th>Doctor</th><th>Reason</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pending)): ?>
                                        <tr><td colspan="8" class="text-center text-muted py-4">No pending appointments.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($pending as $i => $a): ?>
                                        <tr>
                                            <td class="pl-id"><?= $i + 1 ?></td>
                                            <td class="pl-name"><?= esc($a['patient_name'] ?? '—') ?></td>
                                            <td class="pl-email"><?= esc($a['doctor_name'] ?? '—') ?></td>
                                            <td class="pl-email"><?= esc($a['reason'] ?? '—') ?></td>
                                            <td class="pl-date"><?= esc($a['appointment_date'] ?? '—') ?></td>
                                            <td class="pl-date"><?= esc(substr((string)($a['appointment_time'] ?? ''), 0, 5)) ?></td>
                                            <td><?= statusBadge('pending') ?></td>
                                            <td>
                                                <form method="post" action="<?= site_url('/admin/appointments/update-status') ?>" class="d-flex gap-1 align-items-center">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm appt-select">
                                                        <option value="pending" selected>Pending</option>
                                                        <option value="confirmed">Confirmed</option>
                                                        <option value="cancelled">Cancelled</option>
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
                </div>

                <!-- Confirmed Tab -->
                <div id="tab-confirmed" class="tab-panel d-none">
                    <div class="pl-card mb-4">
                        <div class="table-responsive">
                            <table class="table pl-table align-middle mb-0">
                                <thead>
                                    <tr><th>#</th><th>Patient</th><th>Doctor</th><th>Reason</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($confirmed)): ?>
                                        <tr><td colspan="8" class="text-center text-muted py-4">No confirmed appointments.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($confirmed as $i => $a): ?>
                                        <tr>
                                            <td class="pl-id"><?= $i + 1 ?></td>
                                            <td class="pl-name"><?= esc($a['patient_name'] ?? '—') ?></td>
                                            <td class="pl-email"><?= esc($a['doctor_name'] ?? '—') ?></td>
                                            <td class="pl-email"><?= esc($a['reason'] ?? '—') ?></td>
                                            <td class="pl-date"><?= esc($a['appointment_date'] ?? '—') ?></td>
                                            <td class="pl-date"><?= esc(substr((string)($a['appointment_time'] ?? ''), 0, 5)) ?></td>
                                            <td><?= statusBadge('confirmed') ?></td>
                                            <td>
                                                <form method="post" action="<?= site_url('/admin/appointments/update-status') ?>" class="d-flex gap-1 align-items-center">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm appt-select">
                                                        <option value="pending">Pending</option>
                                                        <option value="confirmed" selected>Confirmed</option>
                                                        <option value="cancelled">Cancelled</option>
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
                </div>

                <!-- Archive Tab -->
                <div id="tab-archive" class="tab-panel d-none">
                    <div class="pl-card mb-4">
                        <div class="table-responsive">
                            <table class="table pl-table align-middle mb-0">
                                <thead>
                                    <tr><th>#</th><th>Patient</th><th>Doctor</th><th>Reason</th><th>Date</th><th>Time</th><th>Status</th><th>Archived</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($archived)): ?>
                                        <tr><td colspan="9" class="text-center text-muted py-4">No archived appointments.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($archived as $i => $a): ?>
                                        <?php $isPast = ! empty($a['appointment_date']) && $a['appointment_date'] < date('Y-m-d'); ?>
                                        <tr>
                                            <td class="pl-id"><?= $i + 1 ?></td>
                                            <td class="pl-name"><?= esc($a['patient_name'] ?? '—') ?></td>
                                            <td class="pl-email"><?= esc($a['doctor_name'] ?? '—') ?></td>
                                            <td class="pl-email"><?= esc($a['reason'] ?? '—') ?></td>
                                            <td class="pl-date"><?= esc($a['appointment_date'] ?? '—') ?></td>
                                            <td class="pl-date"><?= esc(substr((string)($a['appointment_time'] ?? ''), 0, 5)) ?></td>
                                            <td><?= statusBadge(strtolower($a['status'] ?? '')) ?></td>
                                            <td class="pl-date" style="font-size:0.75rem;color:#94a3b8;"><?= esc(date('M j, Y', strtotime($a['archived_at']))) ?></td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <?php if ($isPast): ?>
                                                        <!-- Past date: only delete -->
                                                        <form method="post" action="<?= site_url('/admin/appointments/delete/' . $a['id']) ?>">
                                                            <?= csrf_field() ?>
                                                            <button class="pl-action-btn" style="background:#fee2e2;color:#991b1b;" onclick="return confirm('Permanently delete this appointment?')">
                                                                <i class="bi bi-trash me-1"></i>Delete
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <!-- Future date: restore or delete -->
                                                        <form method="post" action="<?= site_url('/admin/appointments/restore/' . $a['id']) ?>">
                                                            <?= csrf_field() ?>
                                                            <button class="pl-action-btn" style="background:#dbeafe;color:#1e40af;">
                                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                                            </button>
                                                        </form>
                                                        <form method="post" action="<?= site_url('/admin/appointments/delete/' . $a['id']) ?>">
                                                            <?= csrf_field() ?>
                                                            <button class="pl-action-btn" style="background:#fee2e2;color:#991b1b;" onclick="return confirm('Permanently delete this appointment?')">
                                                                <i class="bi bi-trash me-1"></i>Delete
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div>

<?php
function statusBadge(string $s): string {
    $map = [
        'confirmed' => 'background:#d1fae5;color:#065f46;',
        'completed' => 'background:#dbeafe;color:#1e40af;',
        'cancelled' => 'background:#fee2e2;color:#991b1b;',
        'serving'   => 'background:#fef3c7;color:#92400e;',
        'pending'   => 'background:#f1f5f9;color:#475569;',
    ];
    $style = $map[$s] ?? 'background:#f1f5f9;color:#475569;';
    return '<span class="pl-status-badge" style="' . $style . '">' . ucfirst($s) . '</span>';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.section-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.section-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('d-none'));
        this.classList.add('active');
        document.getElementById(this.dataset.target).classList.remove('d-none');
    });
});
</script>

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
        font-size: 0.75rem; font-weight: 600; padding: 5px 10px;
        border-radius: 8px; border: none; cursor: pointer;
        display: inline-flex; align-items: center;
        transition: all 0.15s; white-space: nowrap;
    }
    .pl-action-edit { background: #dbeafe; color: #1e40af; }
    .pl-action-edit:hover { background: #bfdbfe; }
    .appt-select { width: 115px; font-size: 0.78rem; border-radius: 8px; border-color: #e2e8f0; }

    /* Section Tabs */
    .section-tabs-wrap { border-bottom: 2px solid #e2e8f0; }
    .section-tabs { gap: 4px; list-style: none; padding: 0; margin: 0; display: flex; }
    .section-tab {
        background: none; border: none; padding: 10px 20px;
        font-size: 0.88rem; font-weight: 600; color: #64748b;
        border-bottom: 3px solid transparent; margin-bottom: -2px;
        cursor: pointer; display: flex; align-items: center; gap: 6px;
        transition: color 0.15s, border-color 0.15s;
        border-radius: 8px 8px 0 0;
    }
    .section-tab:hover { color: #1e3a8a; background: #f1f5f9; }
    .section-tab.active { color: #1e3a8a; border-bottom-color: #1e3a8a; background: #eff6ff; }
    .tab-badge {
        background: #fef3c7; color: #92400e;
        font-size: 0.68rem; font-weight: 700;
        padding: 1px 7px; border-radius: 999px;
    }
    .tab-badge-green { background: #d1fae5; color: #065f46; }
    .tab-badge-gray  { background: #f1f5f9; color: #475569; }
</style>
<?php echo view('layouts/_chat_widget'); ?>
</body>
</html>
