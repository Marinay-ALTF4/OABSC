<?php
$appointments = $appointments ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="container py-4">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">My Appointments</h4>
            <p class="text-muted small mb-0">Review your requested appointments.</p>
        </div>
        <a href="<?= site_url('/appointments/new') ?>" class="btn btn-primary">Book New Appointment</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($appointments)): ?>
                <div class="p-4 text-center text-muted">
                    You do not have any appointments yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?= esc($appointment['doctor_name'] ?? '-') ?></td>
                                    <td><?= esc($appointment['appointment_date'] ?? '-') ?></td>
                                    <td><?= esc(substr((string) ($appointment['appointment_time'] ?? ''), 0, 5)) ?></td>
                                    <td><?= esc($appointment['reason'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge text-bg-secondary text-capitalize">
                                            <?= esc($appointment['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= site_url('/dashboard') ?>" class="btn btn-link text-decoration-none ps-0">Back to Dashboard</a>
    </div>
</div>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #f5f8fa;
        min-height: 100vh;
    }
    .card {
        border: 1px solid #e1e8ed;
        border-left: 4px solid #4a90e2;
        background: white;
        border-radius: 12px;
    }
    .table thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.03em;
    }
    .btn-primary {
        background: #4a90e2;
        border: none;
        font-weight: 500;
        color: white;
    }
    .btn-primary:hover {
        background: #357abd;
        color: white;
    }
</style>
</body>
</html>
