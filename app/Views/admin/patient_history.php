<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient History</title>
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

                <!-- Page header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h4 class="pl-title mb-1">
                            <?php if ($patient): ?>
                                <i class="bi bi-clock-history me-2" style="color:#2a6a7e;"></i>History: <?= esc($patient['name']) ?>
                            <?php else: ?>
                                <i class="bi bi-clock-history me-2" style="color:#2a6a7e;"></i>Patient History
                            <?php endif; ?>
                        </h4>
                        <p class="pl-sub mb-0">
                            <?= $patient ? esc($patient['email']) : 'Search and select a patient to view their appointment history.' ?>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($patient): ?>
                            <a href="<?= site_url('/admin/patients/history') ?>" class="pl-btn pl-btn-ghost">
                                <i class="bi bi-people me-1"></i>All Patients
                            </a>
                        <?php endif; ?>
                        <a href="<?= site_url('/admin/patients') ?>" class="pl-btn pl-btn-ghost">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger py-2 mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <?php if (! $patient): ?>
                <!-- ── Patient Picker ── -->
                <div class="picker-card mb-4">
                    <div class="picker-header">
                        <i class="bi bi-person-lines-fill me-2"></i>Select a Patient
                    </div>
                    <div class="picker-body">
                        <!-- Search input -->
                        <div class="position-relative mb-3">
                            <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:0.88rem;pointer-events:none;"></i>
                            <input type="text" id="patientSearch"
                                placeholder="Search by name or email…"
                                class="picker-search"
                                autocomplete="off">
                        </div>

                        <!-- Patient list -->
                        <div id="patientList" class="patient-list">
                            <?php $patients = $patients ?? []; ?>
                            <?php if (empty($patients)): ?>
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <p>No patients registered yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($patients as $p):
                                    $initials = strtoupper(substr($p['name'] ?? 'P', 0, 2));
                                ?>
                                <a href="<?= site_url('/admin/patients/history/' . (int)$p['id']) ?>"
                                   class="patient-item"
                                   data-name="<?= strtolower(esc($p['name'])) ?>"
                                   data-email="<?= strtolower(esc($p['email'] ?? '')) ?>">
                                    <div class="patient-avatar"><?= $initials ?></div>
                                    <div class="patient-info">
                                        <div class="patient-name"><?= esc($p['name']) ?></div>
                                        <div class="patient-email"><?= esc($p['email'] ?? '—') ?></div>
                                    </div>
                                    <i class="bi bi-chevron-right patient-arrow"></i>
                                </a>
                                <?php endforeach; ?>
                                <div id="noPatientResult" class="empty-state d-none">
                                    <i class="bi bi-person-x"></i>
                                    <p>No patients match your search.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="picker-footer">
                            <span id="patientCount"><?= count($patients ?? []) ?> patient<?= count($patients ?? []) !== 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- ── Patient Info Card ── -->
                <?php
                    $appointments = $appointments ?? [];
                    $total     = count($appointments);
                    $pending   = count(array_filter($appointments, fn($a) => strtolower($a['status'] ?? '') === 'pending'));
                    $confirmed = count(array_filter($appointments, fn($a) => strtolower($a['status'] ?? '') === 'confirmed'));
                    $completed = count(array_filter($appointments, fn($a) => strtolower($a['status'] ?? '') === 'completed'));
                    $cancelled = count(array_filter($appointments, fn($a) => strtolower($a['status'] ?? '') === 'cancelled'));
                ?>
                <div class="ph-info-card mb-3">
                    <div class="ph-info-avatar"><?= strtoupper(substr($patient['name'], 0, 2)) ?></div>
                    <div class="flex-grow-1">
                        <div class="ph-info-name"><?= esc($patient['name']) ?></div>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            <span class="ph-info-meta"><i class="bi bi-envelope me-1"></i><?= esc($patient['email']) ?></span>
                            <?php if (!empty($patient['phone'])): ?>
                                <span class="ph-info-meta"><i class="bi bi-telephone me-1"></i><?= esc($patient['phone']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($patient['city'])): ?>
                                <span class="ph-info-meta"><i class="bi bi-geo-alt me-1"></i><?= esc($patient['city']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?= site_url('/admin/patients/edit/' . (int)$patient['id']) ?>" class="pl-btn pl-btn-ghost" style="font-size:0.75rem;flex-shrink:0;">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                </div>

                <!-- ── Stats row ── -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-pill" style="border-left:3px solid #94a3b8;">
                            <div class="stat-val"><?= $total ?></div>
                            <div class="stat-lbl">Total</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-pill" style="border-left:3px solid #f59e0b;">
                            <div class="stat-val"><?= $pending ?></div>
                            <div class="stat-lbl">Pending</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-pill" style="border-left:3px solid #10b981;">
                            <div class="stat-val"><?= $confirmed + $completed ?></div>
                            <div class="stat-lbl">Confirmed / Done</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-pill" style="border-left:3px solid #ef4444;">
                            <div class="stat-val"><?= $cancelled ?></div>
                            <div class="stat-lbl">Cancelled</div>
                        </div>
                    </div>
                </div>

                <!-- ── Appointment History Table ── -->
                <div class="pl-card">
                    <div class="ph-table-header">
                        <span><i class="bi bi-calendar2-check me-2"></i>Appointment History</span>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($appointments)): ?>
                            <!-- Filter tabs -->
                            <div class="filter-tabs" id="filterTabs">
                                <button class="filter-tab active" data-filter="all">All <span class="filter-count"><?= $total ?></span></button>
                                <button class="filter-tab" data-filter="pending">Pending <span class="filter-count"><?= $pending ?></span></button>
                                <button class="filter-tab" data-filter="confirmed">Confirmed <span class="filter-count"><?= $confirmed ?></span></button>
                                <button class="filter-tab" data-filter="completed">Completed <span class="filter-count"><?= $completed ?></span></button>
                                <button class="filter-tab" data-filter="cancelled">Cancelled <span class="filter-count"><?= $cancelled ?></span></button>
                            </div>
                            <?php endif; ?>
                            <span class="ph-count" id="apptCount"><?= $total ?> record<?= $total !== 1 ? 's' : '' ?></span>
                        </div>
                    </div>

                    <?php if (empty($appointments)): ?>
                        <div class="empty-state py-5">
                            <i class="bi bi-calendar-x" style="font-size:2rem;opacity:0.3;"></i>
                            <p class="mt-2 mb-0" style="font-size:0.875rem;">No appointment records found for this patient.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table pl-table align-middle mb-0" id="apptTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="apptTableBody">
                                    <?php foreach ($appointments as $appt):
                                        $status = strtolower($appt['status'] ?? 'pending');
                                        $statusStyle = match($status) {
                                            'confirmed' => 'background:#d1fae5;color:#065f46;',
                                            'completed' => 'background:#dbeafe;color:#1e40af;',
                                            'cancelled' => 'background:#fee2e2;color:#991b1b;',
                                            default     => 'background:#fef9c3;color:#854d0e;',
                                        };
                                        // Format date nicely
                                        $dateFormatted = !empty($appt['appointment_date'])
                                            ? date('M j, Y', strtotime($appt['appointment_date']))
                                            : '—';
                                        // Format time nicely
                                        $timeFormatted = !empty($appt['appointment_time'])
                                            ? date('g:i A', strtotime($appt['appointment_time']))
                                            : '—';
                                    ?>
                                    <tr data-status="<?= esc($status) ?>">
                                        <td class="pl-id"><?= esc((string)($appt['id'] ?? '')) ?></td>
                                        <td class="pl-name"><?= esc($appt['doctor_name'] ?? '—') ?></td>
                                        <td class="pl-date"><?= esc($dateFormatted) ?></td>
                                        <td class="pl-date"><?= esc($timeFormatted) ?></td>
                                        <td class="pl-reason"><?= esc($appt['reason'] ?? '—') ?></td>
                                        <td>
                                            <span class="pl-status-badge" style="<?= $statusStyle ?>">
                                                <?= esc(ucfirst($status)) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="noApptResult" class="empty-state py-4 d-none">
                            <i class="bi bi-funnel" style="font-size:1.5rem;opacity:0.3;"></i>
                            <p class="mt-2 mb-0" style="font-size:0.875rem;">No appointments match this filter.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Patient search filter ──
(function() {
    const input = document.getElementById('patientSearch');
    if (!input) return;

    const items    = document.querySelectorAll('.patient-item');
    const noResult = document.getElementById('noPatientResult');
    const countEl  = document.getElementById('patientCount');
    const total    = items.length;

    input.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        let visible = 0;

        items.forEach(item => {
            const match = !q
                || item.dataset.name.includes(q)
                || item.dataset.email.includes(q);
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        if (noResult) noResult.classList.toggle('d-none', visible > 0 || !q);
        if (countEl) {
            countEl.textContent = q
                ? `${visible} result${visible !== 1 ? 's' : ''}`
                : `${total} patient${total !== 1 ? 's' : ''}`;
        }
    });
})();

// ── Appointment status filter tabs ──
(function() {
    const tabs    = document.querySelectorAll('.filter-tab');
    const tbody   = document.getElementById('apptTableBody');
    const noRes   = document.getElementById('noApptResult');
    const countEl = document.getElementById('apptCount');
    if (!tabs.length || !tbody) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            const rows   = tbody.querySelectorAll('tr');
            let visible  = 0;

            rows.forEach(row => {
                const show = filter === 'all' || row.dataset.status === filter;
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (noRes) noRes.classList.toggle('d-none', visible > 0);
            if (countEl) countEl.textContent = `${visible} record${visible !== 1 ? 's' : ''}`;
        });
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
        font-size: 0.92rem; font-weight: 500; color: #2a6a7e; text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }
    .adm-nav-item i { font-size: 1.15rem; }
    .adm-nav-item:hover { background: rgba(204,228,237,0.6); color: #164a5c; }
    .adm-nav-item.active { background: #2a6a7e; color: #fff; font-weight: 600; box-shadow: 0 4px 14px rgba(42,106,126,0.25); }
    .adm-main-content { flex: 1; padding: 32px 28px; min-width: 0; }
    .adm-wrapper { width: 100%; }

    /* Buttons */
    .pl-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
    .pl-sub   { font-size: 0.85rem; color: #64748b; }
    .pl-btn {
        font-size: 0.8rem; font-weight: 600; padding: 7px 16px; border-radius: 10px;
        border: none; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; transition: all 0.15s;
    }
    .pl-btn-ghost { background: white; color: #475569; border: 1px solid #dbe4ef; }
    .pl-btn-ghost:hover { background: #f1f5f9; color: #1e40af; }

    /* Patient picker card */
    .picker-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden; max-width: 560px;
    }
    .picker-header {
        padding: 0.9rem 1.25rem; background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.85rem; font-weight: 700; color: #0f172a;
    }
    .picker-body { padding: 1.1rem 1.25rem 0; }
    .picker-search {
        width: 100%; padding: 0.6rem 0.9rem 0.6rem 2.4rem;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: 0.85rem; color: #0f172a; outline: none;
        transition: border-color 0.15s;
    }
    .picker-search:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    .patient-list { max-height: 380px; overflow-y: auto; margin: 0 -1.25rem; }
    .patient-item {
        display: flex; align-items: center; gap: 0.85rem;
        padding: 0.75rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        text-decoration: none; color: inherit; transition: background 0.12s;
    }
    .patient-item:last-of-type { border-bottom: none; }
    .patient-item:hover { background: #f0f9ff; }
    .patient-avatar {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg,#3b556e,#2e445a);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; color: white;
    }
    .patient-info { flex: 1; min-width: 0; }
    .patient-name  { font-size: 0.875rem; font-weight: 600; color: #0f172a; }
    .patient-email { font-size: 0.78rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .patient-arrow { color: #cbd5e1; font-size: 0.8rem; flex-shrink: 0; }
    .picker-footer {
        padding: 0.6rem 1.25rem; border-top: 1px solid #f1f5f9;
        font-size: 0.72rem; color: #94a3b8; text-align: right;
    }

    /* Patient info card */
    .ph-info-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06);
        padding: 1.1rem 1.4rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
    }
    .ph-info-avatar {
        width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg,#3b556e,#2e445a);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; font-weight: 700; color: white;
    }
    .ph-info-name { font-size: 1rem; font-weight: 700; color: #0f172a; }
    .ph-info-meta { font-size: 0.8rem; color: #64748b; }

    /* Stats */
    .stat-pill {
        background: white; border-radius: 12px; border: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(15,23,42,0.05);
        padding: 0.7rem 1rem;
    }
    .stat-val { font-size: 1.4rem; font-weight: 700; color: #0f172a; line-height: 1; }
    .stat-lbl { font-size: 0.72rem; color: #64748b; margin-top: 2px; }

    /* Table card */
    .pl-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden;
    }
    .ph-table-header {
        display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;
        padding: 0.85rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem; font-weight: 700; color: #0f172a; background: #f8fafc;
    }
    .ph-count { font-size: 0.75rem; font-weight: 600; color: #64748b; white-space: nowrap; }

    /* Filter tabs */
    .filter-tabs { display: flex; gap: 4px; flex-wrap: wrap; }
    .filter-tab {
        font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 8px;
        border: 1px solid #e2e8f0; background: white; color: #64748b; cursor: pointer;
        transition: all 0.15s; display: inline-flex; align-items: center; gap: 4px;
    }
    .filter-tab:hover { background: #f1f5f9; color: #1e40af; border-color: #bfdbfe; }
    .filter-tab.active { background: #1e3a8a; color: white; border-color: #1e3a8a; }
    .filter-count {
        background: rgba(255,255,255,0.25); border-radius: 999px;
        padding: 0 5px; font-size: 0.65rem; font-weight: 700;
    }
    .filter-tab:not(.active) .filter-count { background: #f1f5f9; color: #475569; }

    /* Table */
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
    .pl-id     { color: #94a3b8; font-size: 0.78rem; font-weight: 600; }
    .pl-name   { font-weight: 600; color: #0f172a; }
    .pl-date   { color: #64748b; font-size: 0.82rem; white-space: nowrap; }
    .pl-reason { color: #475569; font-size: 0.82rem; max-width: 220px; }
    .pl-status-badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }

    /* Empty state */
    .empty-state { text-align: center; color: #94a3b8; padding: 2rem 1rem; }
    .empty-state i { font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.4; }
    .empty-state p { font-size: 0.85rem; margin: 0; }
</style>
</body>
</html>
