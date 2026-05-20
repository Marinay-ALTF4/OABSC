<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Patient List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <?= view('admin/_sidebar', ['sidebarActive' => 'patients']) ?>

        <div class="adm-main-content">
            <div class="adm-wrapper">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h4 class="pl-title mb-1">Patient List</h4>
                        <p class="pl-sub mb-0">All registered patients (clients) in the clinic.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:0.82rem;pointer-events:none;"></i>
                            <input type="text" id="clientSearchInput" placeholder="Search name, email, phone…"
                                style="padding:7px 12px 7px 30px;border-radius:10px;border:1.5px solid #e2e8f0;font-size:0.82rem;width:240px;outline:none;transition:border-color 0.15s;"
                                onfocus="this.style.borderColor='#93c5fd'" onblur="this.style.borderColor='#e2e8f0'"
                                autocomplete="off">
                        </div>
                        <a href="<?= site_url('/admin/patients') ?>" class="pl-btn pl-btn-ghost">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success py-2 mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger py-2 mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <div class="pl-card">
                    <?php $users = $users ?? []; ?>
                    <?php if (empty($users)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-people" style="font-size:2rem;opacity:0.3;"></i>
                            <p class="mt-2 mb-0" style="font-size:0.875rem;">No patients found.</p>
                        </div>                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-1">
                            <span id="searchCount" style="font-size:0.75rem;color:#94a3b8;">
                                <?= count($users) ?> patient<?= count($users) !== 1 ? 's' : '' ?>
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table pl-table align-middle mb-0" id="clientTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="clientTableBody">
                                    <?php foreach ($users as $user): ?>
                                        <?php $isDeleted = !empty($user['deleted_at']); ?>
                                        <tr>
                                            <td class="pl-id"><?= esc((string)($user['id'] ?? '')) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="doc-avatar"><?= strtoupper(substr($user['name'] ?? 'P', 0, 2)) ?></div>
                                                    <span class="pl-name"><?= esc($user['name'] ?? '') ?></span>
                                                </div>
                                            </td>
                                            <td class="pl-email"><?= esc($user['email'] ?? '—') ?></td>
                                            <td class="pl-date"><?= esc($user['phone'] ?? '—') ?></td>
                                            <td>
                                                <?php if ($isDeleted): ?>
                                                    <span class="pl-status-badge" style="background:#f1f5f9;color:#64748b;">Deleted</span>
                                                <?php else: ?>
                                                    <span class="pl-status-badge" style="background:#d1fae5;color:#065f46;">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="pl-date"><?= esc($user['created_at'] ?? '—') ?></td>
                                            <td>
                                                <a href="<?= site_url('/admin/patients/history/' . (int)$user['id']) ?>"
                                                   class="pl-action-btn pl-action-view">
                                                    <i class="bi bi-clock-history me-1"></i>View Appointments
                                                    <?php if (($user['appointment_count'] ?? 0) > 0): ?>
                                                        <span class="appt-count-badge"><?= (int)$user['appointment_count'] ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const input     = document.getElementById('clientSearchInput');
    const tbody     = document.getElementById('clientTableBody');
    const countEl   = document.getElementById('searchCount');
    if (!input || !tbody) return;

    const totalRows = tbody.querySelectorAll('tr').length;

    input.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        let visible = 0;

        // Remove any existing no-results row
        const noRes = document.getElementById('no-results-row');
        if (noRes) noRes.remove();

        tbody.querySelectorAll('tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            const show = !q || text.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Show no-results row if nothing matches
        if (q && visible === 0) {
            const tr = document.createElement('tr');
            tr.id = 'no-results-row';
            tr.innerHTML = `<td colspan="7" class="text-center py-4" style="color:#94a3b8;font-size:0.85rem;">
                <i class="bi bi-person-x" style="font-size:1.5rem;display:block;margin-bottom:0.4rem;"></i>
                No patients match "<strong>${q}</strong>"
            </td>`;
            tbody.appendChild(tr);
        }

        // Update count label
        if (countEl) {
            countEl.textContent = q
                ? `${visible} result${visible !== 1 ? 's' : ''} for "${this.value}"`
                : `${totalRows} patient${totalRows !== 1 ? 's' : ''}`;
        }
    });
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
    .pl-btn {
        font-size: 0.8rem; font-weight: 600; padding: 7px 16px; border-radius: 10px;
        border: none; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; transition: all 0.15s;
    }
    .pl-btn-ghost { background: white; color: #475569; border: 1px solid #dbe4ef; }
    .pl-btn-ghost:hover { background: #f1f5f9; color: #1e40af; }
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
    .doc-avatar {
        width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg,#3b556e,#2e445a);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.7rem; font-weight: 700; color: white;
    }
    .pl-action-btn {
        font-size: 0.75rem; font-weight: 600; padding: 5px 12px;
        border-radius: 8px; border: none; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: all 0.15s; white-space: nowrap;
    }
    .pl-action-view { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .pl-action-view:hover { background: #dbeafe; color: #1e3a8a; }
    .appt-count-badge {
        background: #1e40af; color: white;
        font-size: 0.65rem; font-weight: 700;
        padding: 1px 6px; border-radius: 999px; margin-left: 5px;
    }
</style>
</body>
</html>
