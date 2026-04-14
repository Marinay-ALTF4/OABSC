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

<div class="pl-page">
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="pl-title mb-1">
                <?= $patient ? 'History: ' . esc($patient['name']) : 'Patient History' ?>
            </h4>
            <p class="pl-sub mb-0">
                <?= $patient ? esc($patient['email']) : 'Select a patient to view their appointment history.' ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($patient): ?>
                <a href="<?= site_url('/admin/patients/history') ?>" class="pl-btn pl-btn-ghost">
                    <i class="bi bi-people me-1"></i>All Patients
                </a>
            <?php endif; ?>
            <a href="<?= site_url('/dashboard') ?>" class="pl-btn pl-btn-ghost">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (! $patient): ?>
    <!-- Patient picker -->
    <div class="pl-card mb-4" style="max-width:480px;">
        <p class="au-label mb-3">Select a patient to view their history:</p>
        <form action="" method="get" id="patientPickForm">
            <div class="au-field">
                <div class="au-input-wrap">
                    <i class="bi bi-search au-icon"></i>
                    <select name="_redirect" class="au-input" onchange="redirectToPatient(this)">
                        <option value="">— Choose a patient —</option>
                        <?php foreach (($patients ?? []) as $p): ?>
                            <option value="<?= site_url('/admin/patients/history/' . (int)$p['id']) ?>">
                                <?= esc($p['name']) ?> — <?= esc($p['email']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <?php else: ?>
    <!-- Patient info card -->
    <div class="ph-info-card mb-4">
        <div class="ph-info-avatar"><?= strtoupper(substr($patient['name'], 0, 2)) ?></div>
        <div>
            <div class="ph-info-name"><?= esc($patient['name']) ?></div>
            <div class="ph-info-meta"><i class="bi bi-envelope me-1"></i><?= esc($patient['email']) ?></div>
            <?php if (!empty($patient['phone'])): ?>
                <div class="ph-info-meta"><i class="bi bi-telephone me-1"></i><?= esc($patient['phone']) ?></div>
            <?php endif; ?>
        </div>
        <div class="ms-auto">
            <a href="<?= site_url('/admin/patients/edit/' . (int)$patient['id']) ?>" class="pl-btn pl-btn-ghost" style="font-size:0.75rem;">
                <i class="bi bi-pencil me-1"></i>Edit Patient
            </a>
        </div>
    </div>

    <!-- Appointment history table -->
    <div class="pl-card">
        <div class="ph-table-header">
            <span><i class="bi bi-calendar2-check me-2"></i>Appointment History</span>
            <span class="ph-count"><?= count($appointments) ?> record<?= count($appointments) !== 1 ? 's' : '' ?></span>
        </div>
        <?php if (empty($appointments)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-calendar-x" style="font-size:2rem;opacity:0.3;"></i>
                <p class="mt-2 mb-0" style="font-size:0.875rem;">No appointment records found for this patient.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table pl-table align-middle mb-0">
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
                    <tbody>
                        <?php foreach ($appointments as $appt): ?>
                            <?php
                                $status = strtolower($appt['status'] ?? 'pending');
                                $statusStyle = match($status) {
                                    'confirmed' => 'background:#d1fae5;color:#065f46;',
                                    'completed' => 'background:#dbeafe;color:#1e40af;',
                                    'cancelled' => 'background:#fee2e2;color:#991b1b;',
                                    default     => 'background:#fef9c3;color:#854d0e;',
                                };
                            ?>
                            <tr>
                                <td class="pl-id"><?= esc((string)($appt['id'] ?? '')) ?></td>
                                <td class="pl-name"><?= esc($appt['doctor_name'] ?? '—') ?></td>
                                <td class="pl-date"><?= esc($appt['appointment_date'] ?? '—') ?></td>
                                <td class="pl-date"><?= esc($appt['appointment_time'] ?? '—') ?></td>
                                <td class="pl-email"><?= esc($appt['reason'] ?? '—') ?></td>
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
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function redirectToPatient(sel) {
    if (sel.value) window.location.href = sel.value;
}
</script>
<style>
    body { background: #edf2f7; }
    .pl-page { min-height: calc(100vh - 60px); }
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
    .pl-card.mb-4 { padding: 1.5rem; }
    .au-field { margin-bottom: 0; }
    .au-label { font-size: 0.8rem; font-weight: 600; color: #334155; display: block; }
    .au-input-wrap { position: relative; display: flex; align-items: center; }
    .au-icon { position: absolute; left: 12px; color: #94a3b8; font-size: 0.95rem; pointer-events: none; }
    .au-input {
        width: 100%; padding: 0.6rem 0.9rem 0.6rem 2.2rem;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: 0.875rem; color: #0f172a; background: #fafafa; outline: none;
        transition: border-color 0.15s; appearance: auto;
    }
    .au-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); background: white; }

    .ph-info-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06);
        padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem;
    }
    .ph-info-avatar {
        width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg,#3b556e,#2e445a);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; font-weight: 700; color: white;
    }
    .ph-info-name { font-size: 1rem; font-weight: 700; color: #0f172a; }
    .ph-info-meta { font-size: 0.8rem; color: #64748b; margin-top: 2px; }

    .ph-table-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.85rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem; font-weight: 700; color: #0f172a;
        background: #f8fafc;
    }
    .ph-count { font-size: 0.75rem; font-weight: 600; color: #64748b; }

    .pl-table { font-size: 0.85rem; }
    .pl-table thead th {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.6px; color: #5a7288; padding: 0.85rem 1rem;
        border-bottom: 1px solid #e2e8f0; white-space: nowrap; background: #f8fafc;
    }
    .pl-table tbody tr:hover { background: #f8fafc; }
    .pl-table tbody td { padding: 0.8rem 1rem; border-bottom: 1px solid #f1f5f9; }
    .pl-table tbody tr:last-child td { border-bottom: none; }
    .pl-id    { color: #94a3b8; font-size: 0.78rem; font-weight: 600; }
    .pl-name  { font-weight: 600; color: #0f172a; }
    .pl-email { color: #475569; font-size: 0.82rem; }
    .pl-date  { color: #64748b; font-size: 0.82rem; }
    .pl-status-badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
</style>
</body>
</html>
