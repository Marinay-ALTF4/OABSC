<?= view('secretary/_layout_top', ['pageTitle' => 'Patient History', 'active' => 'records']) ?>

<?php
$patient = $patient ?? null;
$patients = $patients ?? [];
$appointments = $appointments ?? [];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h5 class="sec-page-title">
            <i class="bi bi-clock-history me-2"></i>
            <?= $patient ? 'History: ' . esc($patient['name'] ?? 'Patient') : 'Patient History' ?>
        </h5>
        <div class="text-muted small mt-1">
            <?= $patient ? esc($patient['email'] ?? '') : 'Select a patient to view appointment history.' ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if ($patient): ?>
            <a href="<?= site_url('/secretary/records') ?>" class="sec-save-btn" style="text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;">
                <i class="bi bi-people"></i>All Patients
            </a>
        <?php endif; ?>
        <a href="<?= site_url('/secretary/records') ?>" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<?php if (session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= esc(session('error')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php
    $total = count($appointments);
    $pending = count(array_filter($appointments, fn($a) => strtolower($a['status'] ?? '') === 'pending'));
    $confirmed = count(array_filter($appointments, fn($a) => strtolower($a['status'] ?? '') === 'confirmed'));
    $completed = count(array_filter($appointments, fn($a) => strtolower($a['status'] ?? '') === 'completed'));
    $cancelled = count(array_filter($appointments, fn($a) => strtolower($a['status'] ?? '') === 'cancelled'));
?>

<?php if (! $patient): ?>
    <div class="sec-table-card">
        <div class="table-responsive">
            <table class="sec-table">
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($patients)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No patients found.</td></tr>
                    <?php else: ?>
                        <?php foreach (($patients ?? []) as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= esc($p['name'] ?? $p['username'] ?? '—') ?></td>
                                <td><?= esc($p['email']) ?></td>
                                <td><?= esc($p['phone'] ?? '—') ?></td>
                                <td><?= esc(date('M j, Y', strtotime($p['created_at']))) ?></td>
                                <td><a href="<?= site_url('/secretary/records/' . (int) $p['id']) ?>" class="btn btn-sm btn-outline-success">View History</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3"><div class="p-3 border rounded bg-white"><div class="small text-muted">Total</div><div class="fw-bold fs-5"><?= $total ?></div></div></div>
        <div class="col-6 col-md-3"><div class="p-3 border rounded bg-white"><div class="small text-muted">Pending</div><div class="fw-bold fs-5"><?= $pending ?></div></div></div>
        <div class="col-6 col-md-3"><div class="p-3 border rounded bg-white"><div class="small text-muted">Confirmed / Done</div><div class="fw-bold fs-5"><?= $confirmed + $completed ?></div></div></div>
        <div class="col-6 col-md-3"><div class="p-3 border rounded bg-white"><div class="small text-muted">Cancelled</div><div class="fw-bold fs-5"><?= $cancelled ?></div></div></div>
    </div>

    <div class="sec-table-card">
        <div class="table-responsive">
            <table class="sec-table">
                <thead>
                    <tr><th>#</th><th>Doctor</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No appointment records found for this patient.</td></tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appt):
                            $status = strtolower($appt['status'] ?? 'pending');
                            $statusStyle = match($status) {
                                'confirmed' => 'background:#d1fae5;color:#065f46;',
                                'completed' => 'background:#dbeafe;color:#1e40af;',
                                'cancelled' => 'background:#fee2e2;color:#991b1b;',
                                default => 'background:#fef9c3;color:#854d0e;',
                            };
                            $dateFormatted = !empty($appt['appointment_date']) ? date('M j, Y', strtotime($appt['appointment_date'])) : '—';
                            $timeFormatted = !empty($appt['appointment_time']) ? date('g:i A', strtotime($appt['appointment_time'])) : '—';
                        ?>
                            <tr>
                                <td><?= esc((string)($appt['id'] ?? '')) ?></td>
                                <td><?= esc($appt['doctor_name'] ?? '—') ?></td>
                                <td><?= esc($dateFormatted) ?></td>
                                <td><?= esc($timeFormatted) ?></td>
                                <td><?= esc($appt['reason'] ?? '—') ?></td>
                                <td><span class="badge" style="<?= $statusStyle ?>"><?= esc(ucfirst($status)) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?= view('secretary/_layout_bottom') ?>