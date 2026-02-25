<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f8fa;
            min-height: 100vh;
        }
        .card {
            border: 1px solid #e1e8ed;
            border-left: 4px solid #4a90e2;
            background: white;
            border-radius: 12px;
        }
        .appointment-card {
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
            transition: all 0.2s;
        }
        .appointment-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-primary {
            background: #4a90e2;
            border: none;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: #357abd;
        }
        .btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
            font-weight: 500;
        }
        .btn-outline-danger:hover {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
        }
        .nav-tabs .nav-link.active {
            color: #4a90e2;
            border-bottom: 3px solid #4a90e2;
            background: transparent;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #999;
        }
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        .timeline {
            position: relative;
            padding-left: 2rem;
            margin-top: 1rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0.5rem;
            bottom: 0.5rem;
            width: 2px;
            background: #e1e8ed;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1rem;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-dot {
            position: absolute;
            left: -1.5rem;
            top: 0.25rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4a90e2;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #e1e8ed;
        }
        .timeline-dot.created {
            background: #28a745;
        }
        .timeline-dot.updated {
            background: #ffc107;
        }
        .timeline-dot.cancelled {
            background: #dc3545;
        }
        .timeline-content {
            font-size: 0.85rem;
        }
        .timeline-time {
            color: #999;
            font-size: 0.75rem;
        }
        .btn-link-history {
            color: #4a90e2;
            text-decoration: none;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .btn-link-history:hover {
            text-decoration: underline;
        }
        .history-section {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e1e8ed;
        }
    </style>
</head>
<body>
<?= view('header') ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">My Appointments</h4>
                            <p class="text-muted small mb-0">View and manage your appointments</p>
                        </div>
                        <a href="<?= site_url('/dashboard') ?>" class="btn btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left me-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success">
                            <?= esc(session()->getFlashdata('success')) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= esc(session()->getFlashdata('error')) ?>
                        </div>
                    <?php endif; ?>

                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                                Upcoming (<?= count($upcoming) ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab">
                                Past (<?= count($past) ?>)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Upcoming Appointments -->
                        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
                            <?php if (empty($upcoming)): ?>
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <h5>No upcoming appointments</h5>
                                    <p class="text-muted">You don't have any upcoming appointments</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($upcoming as $appointment): ?>
                                    <div class="appointment-card">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-start mb-2">
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-1"><?= esc($appointment['doctor_name']) ?></h5>
                                                        <p class="text-muted small mb-2"><?= esc($appointment['specialization']) ?></p>
                                                    </div>
                                                    <span class="status-badge status-<?= esc($appointment['status']) ?>">
                                                        <?= esc($appointment['status']) ?>
                                                    </span>
                                                </div>
                                                <div class="row g-3 small">
                                                    <div class="col-auto">
                                                        <strong>Date:</strong> <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?>
                                                    </div>
                                                    <div class="col-auto">
                                                        <strong>Time:</strong> <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <strong class="small">Reason:</strong>
                                                    <p class="text-muted small mb-0"><?= esc($appointment['reason']) ?></p>
                                                </div>
                                                <?php if (!empty($appointment['notes'])): ?>
                                                    <div class="mt-2">
                                                        <strong class="small">Notes:</strong>
                                                        <p class="text-muted small mb-0"><?= esc($appointment['notes']) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-3 pt-2 border-top">
                                                    <small class="text-muted">
                                                        <strong>Booked:</strong> <?= date('M d, Y \a\t h:i A', strtotime($appointment['created_at'])) ?>
                                                        <?php if ($appointment['created_at'] !== $appointment['updated_at']): ?>
                                                            | <strong>Updated:</strong> <?= date('M d, Y \a\t h:i A', strtotime($appointment['updated_at'])) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                                <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed'): ?>
                                                    <button 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmCancel(<?= $appointment['id'] ?>)"
                                                    >
                                                        Cancel Appointment
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Past Appointments -->
                        <div class="tab-pane fade" id="past" role="tabpanel">
                            <?php if (empty($past)): ?>
                                <div class="empty-state">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h5>No past appointments</h5>
                                    <p class="text-muted">Your appointment history will appear here</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($past as $appointment): ?>
                                    <div class="appointment-card">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-start mb-2">
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-1"><?= esc($appointment['doctor_name']) ?></h5>
                                                        <p class="text-muted small mb-2"><?= esc($appointment['specialization']) ?></p>
                                                    </div>
                                                    <span class="status-badge status-<?= esc($appointment['status']) ?>">
                                                        <?= esc($appointment['status']) ?>
                                                    </span>
                                                </div>
                                                <div class="row g-3 small">
                                                    <div class="col-auto">
                                                        <strong>Date:</strong> <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?>
                                                    </div>
                                                    <div class="col-auto">
                                                        <strong>Time:</strong> <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <strong class="small">Reason:</strong>
                                                    <p class="text-muted small mb-0"><?= esc($appointment['reason']) ?></p>
                                                </div>
                                                <?php if (!empty($appointment['notes'])): ?>
                                                    <div class="mt-2">
                                                        <strong class="small">Notes:</strong>
                                                        <p class="text-muted small mb-0"><?= esc($appointment['notes']) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-3 pt-2 border-top">
                                                    <small class="text-muted">
                                                        <strong>Booked:</strong> <?= date('M d, Y \a\t h:i A', strtotime($appointment['created_at'])) ?>
                                                        <?php if ($appointment['created_at'] !== $appointment['updated_at']): ?>
                                                            | <strong>Updated:</strong> <?= date('M d, Y \a\t h:i A', strtotime($appointment['updated_at'])) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel this appointment?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
                <form id="cancelForm" method="post" style="display: inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmCancel(appointmentId) {
        const form = document.getElementById('cancelForm');
        form.action = '<?= site_url('/appointments/cancel/') ?>' + appointmentId;
        const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
        modal.show();
    }
</script>
</body>
</html>
