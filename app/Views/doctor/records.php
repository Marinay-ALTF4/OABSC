<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body style="background:#f0f4f8;">
<?= view('header') ?>

<?php
$stats = $stats ?? ['patients' => 0, 'appointments' => 0, 'today' => 0];
$patients = $patients ?? [];
$appointments = $appointments ?? [];
$search = $search ?? '';
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Patient Records</h4>
            <p class="text-muted small mb-0">View patients and the appointment history linked to your account.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($patient)): ?>
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="record-stat">
                    <div class="record-icon text-primary bg-primary-subtle"><i class="bi bi-people"></i></div>
                    <div class="text-muted small text-uppercase fw-semibold">Patients</div>
                    <div class="h4 fw-bold mb-0"><?= esc((string) $stats['patients']) ?></div>
                    <div class="text-muted small">Unique patients in your records</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="record-stat">
                    <div class="record-icon text-info bg-info-subtle"><i class="bi bi-journal-medical"></i></div>
                    <div class="text-muted small text-uppercase fw-semibold">Appointments</div>
                    <div class="h4 fw-bold mb-0"><?= esc((string) $stats['appointments']) ?></div>
                    <div class="text-muted small">Total linked appointments</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="record-stat">
                    <div class="record-icon text-success bg-success-subtle"><i class="bi bi-calendar-day"></i></div>
                    <div class="text-muted small text-uppercase fw-semibold">Today</div>
                    <div class="h4 fw-bold mb-0"><?= esc((string) $stats['today']) ?></div>
                    <div class="text-muted small">Records scheduled for today</div>
                </div>
            </div>
        </div>

        <div class="record-panel mb-4">
            <div class="record-panel-head d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-semibold text-dark small"><i class="bi bi-folder2-open me-2 text-primary"></i>Patient List</div>
                    <div class="record-panel-sub">Select a patient to open their appointment history.</div>
                </div>
                <form method="get" class="d-flex gap-2 flex-wrap">
                    <input type="text" name="search" class="form-control form-control-sm record-search" placeholder="Search name, email, or phone..." value="<?= esc($search) ?>">
                    <button class="btn btn-sm btn-primary px-3">Search</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table record-table table-hover align-middle mb-0 table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Appointments</th>
                            <th>Latest Visit</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">No patient records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $i => $p): ?>
                                <?php
                                    $status = strtolower((string) ($p['latest_status'] ?? ''));
                                    $statusClass = match ($status) {
                                        'approved'  => 'bg-success-subtle text-success',
                                        'pending'   => 'bg-warning-subtle text-warning',
                                        'completed' => 'bg-primary-subtle text-primary',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                        default     => 'bg-secondary-subtle text-secondary',
                                    };
                                ?>
                                <tr>
                                    <td class="text-muted fw-semibold"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($p['name'] ?? 'Unknown') ?></div>
                                    </td>
                                    <td><?= esc($p['email'] ?? '—') ?></td>
                                    <td><?= esc($p['phone'] ?? '—') ?></td>
                                    <td><span class="badge bg-light text-dark border rounded-pill px-3 py-2"><?= esc((string) ($p['appointment_count'] ?? 0)) ?></span></td>
                                    <td>
                                        <div class="small fw-semibold"><?= esc(($p['latest_date'] ?? '-') !== '' ? date('M j, Y', strtotime((string) $p['latest_date'])) : '-') ?></div>
                                        <div class="small text-muted"><?= esc(substr((string) ($p['latest_time'] ?? ''), 0, 5) ?: '-') ?> <?php if ($status !== ''): ?><span class="badge <?= $statusClass ?> ms-1"><?= esc(ucfirst($status)) ?></span><?php endif; ?></div>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= site_url('/doctor/records/' . (int) ($p['id'] ?? 0)) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <?php
            $statusText = 'patient';
        ?>
        <div class="record-profile mb-4">
            <div class="record-avatar"><?= strtoupper(substr((string) ($patient['name'] ?? 'PT'), 0, 2)) ?></div>
            <div>
                <div class="record-name"><?= esc($patient['name'] ?? 'Unknown') ?></div>
                <div class="record-meta"><i class="bi bi-envelope me-1"></i><?= esc($patient['email'] ?? '—') ?></div>
                <?php if (! empty($patient['phone'])): ?>
                    <div class="record-meta"><i class="bi bi-telephone me-1"></i><?= esc($patient['phone']) ?></div>
                <?php endif; ?>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="<?= site_url('/doctor/records') ?>" class="btn btn-sm btn-outline-secondary">All Records</a>
            </div>
        </div>

        <div class="record-panel">
            <div class="record-panel-head d-flex justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-semibold text-dark small"><i class="bi bi-calendar2-check me-2 text-primary"></i>Appointment History</div>
                    <div class="record-panel-sub">Appointments connected to this patient.</div>
                </div>
                <span class="badge bg-primary rounded-pill px-3 py-2"><?= count($appointments) ?> record<?= count($appointments) === 1 ? '' : 's' ?></span>
            </div>
            <?php if (empty($appointments)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x d-block mb-2" style="font-size:1.5rem;"></i>
                    No appointment records found for this patient.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table record-table table-hover align-middle mb-0 table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appt): ?>
                                <?php
                                    $status = strtolower((string) ($appt['status'] ?? 'pending'));
                                    $statusClass = match ($status) {
                                        'approved'  => 'bg-success-subtle text-success',
                                        'pending'   => 'bg-warning-subtle text-warning',
                                        'completed' => 'bg-primary-subtle text-primary',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                        default     => 'bg-secondary-subtle text-secondary',
                                    };
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc((string) ($appt['appointment_date'] ?? '-')) ?></td>
                                    <td><?= esc(substr((string) ($appt['appointment_time'] ?? ''), 0, 5) ?: '-') ?></td>
                                    <td style="max-width:320px;">
                                        <span class="d-block text-truncate" title="<?= esc((string) ($appt['reason'] ?? '')) ?>"><?= esc((string) ($appt['reason'] ?? '-')) ?></span>
                                    </td>
                                    <td><span class="badge <?= $statusClass ?> rounded-pill px-3 py-2"><?= esc(ucfirst($status)) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<style>
    .record-stat {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
        padding: 12px 14px;
        height: 100%;
    }

    .record-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        margin-bottom: 8px;
    }

    .record-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .record-panel-head {
        padding: 12px 14px 8px;
    }

    .record-panel-sub {
        color: #64748b;
        font-size: 0.8rem;
    }

    .record-search {
        min-width: 240px;
    }

    .record-table thead th {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
    }

    .record-table {
        font-size: 0.86rem;
    }

    .record-table td,
    .record-table th {
        padding-top: 0.55rem;
        padding-bottom: 0.55rem;
        vertical-align: middle;
    }

    .record-profile {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .record-avatar {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #1d4ed8, #60a5fa);
        color: #fff;
        font-weight: 700;
    }

    .record-name {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .record-meta {
        font-size: 0.8rem;
        color: #64748b;
    }
</style>
