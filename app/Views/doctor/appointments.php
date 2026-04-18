<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body style="background:#f0f4f8;">
<?= view('header') ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">My Appointments</h4>
            <p class="text-muted small mb-0">View and manage your patient appointments.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <div class="d-flex gap-2 mb-4">
        <a href="?filter=upcoming" class="btn btn-sm <?= $filter === 'upcoming' ? 'btn-primary' : 'btn-outline-secondary' ?>">Upcoming</a>
        <a href="?filter=today" class="btn btn-sm <?= $filter === 'today' ? 'btn-primary' : 'btn-outline-secondary' ?>">Today</a>
        <a href="?filter=past" class="btn btn-sm <?= $filter === 'past' ? 'btn-primary' : 'btn-outline-secondary' ?>">Past</a>
        <a href="?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($appointments)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0">No appointments found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appt): ?>
                                <?php
                                $statusClass = match($appt['status']) {
                                    'approved'  => 'bg-success-subtle text-success',
                                    'pending'   => 'bg-warning-subtle text-warning',
                                    'completed' => 'bg-primary-subtle text-primary',
                                    'cancelled' => 'bg-danger-subtle text-danger',
                                    default     => 'bg-secondary-subtle text-secondary',
                                };
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($appt['patient_name']) ?></div>
                                        <?php if ($appt['patient_phone']): ?>
                                            <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= esc($appt['patient_phone']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($appt['appointment_date']) ?></td>
                                    <td><?= esc(substr($appt['appointment_time'], 0, 5)) ?></td>
                                    <td style="max-width:200px;">
                                        <span class="text-truncate d-block" style="max-width:180px;" title="<?= esc($appt['reason']) ?>">
                                            <?= esc($appt['reason']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusClass ?>"><?= esc(ucfirst($appt['status'])) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($appt['status'] === 'pending'): ?>
                                            <form action="<?= site_url('/doctor/appointments/status') ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form action="<?= site_url('/doctor/appointments/status') ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this appointment?')">Cancel</button>
                                            </form>
                                        <?php elseif ($appt['status'] === 'approved'): ?>
                                            <form action="<?= site_url('/doctor/appointments/status') ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-primary">Mark Done</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
