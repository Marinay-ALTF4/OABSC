<?php
$appointments = $appointments ?? [];

$upcoming  = [];
$completed = [];
$cancelled = [];
$today     = date('Y-m-d');

foreach ($appointments as $appt) {
    $status = strtolower($appt['status'] ?? 'pending');
    if ($status === 'cancelled') {
        $cancelled[] = $appt;
    } elseif ($status === 'completed') {
        $completed[] = $appt;
    } else {
        $upcoming[] = $appt;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="appt-page">
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="mb-1 fw-bold">My Appointments</h4>
            <p class="text-muted small mb-0">Review and manage all your clinic appointments.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('/dashboard') ?>" class="btn btn-sm btn-back">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
            <a href="<?= site_url('/appointments/new') ?>" class="btn btn-sm btn-book">
                <i class="bi bi-plus-lg me-1"></i> Book New
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2 mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <!-- Summary Badges -->
    <div class="d-flex gap-2 flex-wrap mb-4">
        <span class="summary-badge badge-upcoming">
            <i class="bi bi-calendar-event me-1"></i><?= count($upcoming) ?> Upcoming
        </span>
        <span class="summary-badge badge-completed">
            <i class="bi bi-check-circle me-1"></i><?= count($completed) ?> Completed
        </span>
        <span class="summary-badge badge-cancelled">
            <i class="bi bi-x-circle me-1"></i><?= count($cancelled) ?> Cancelled
        </span>
    </div>

    <!-- Tabs -->
    <ul class="nav appt-tabs mb-4" role="tablist">
        <li class="nav-item">
            <button class="appt-tab active" data-bs-toggle="tab" data-bs-target="#tab-upcoming" type="button">
                <i class="bi bi-calendar-event me-1"></i> Upcoming
                <span class="tab-count"><?= count($upcoming) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="appt-tab" data-bs-toggle="tab" data-bs-target="#tab-completed" type="button">
                <i class="bi bi-check-circle me-1"></i> Completed
                <span class="tab-count"><?= count($completed) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="appt-tab" data-bs-toggle="tab" data-bs-target="#tab-cancelled" type="button">
                <i class="bi bi-x-circle me-1"></i> Cancelled
                <span class="tab-count"><?= count($cancelled) ?></span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">

        <!-- UPCOMING -->
        <div class="tab-pane fade show active" id="tab-upcoming">
            <?php if (empty($upcoming)): ?>
                <?= emptyState('calendar-x', 'No upcoming appointments', 'You have no scheduled appointments. Book one now!') ?>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($upcoming as $appt): ?>
                        <div class="col-12">
                            <?= appointmentCard($appt, 'upcoming') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- COMPLETED -->
        <div class="tab-pane fade" id="tab-completed">
            <?php if (empty($completed)): ?>
                <?= emptyState('clipboard-check', 'No completed appointments', 'Your completed appointments will appear here.') ?>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($completed as $appt): ?>
                        <div class="col-12">
                            <?= appointmentCard($appt, 'completed') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- CANCELLED -->
        <div class="tab-pane fade" id="tab-cancelled">
            <?php if (empty($cancelled)): ?>
                <?= emptyState('slash-circle', 'No cancelled appointments', 'You have no cancelled appointments.') ?>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($cancelled as $appt): ?>
                        <div class="col-12">
                            <?= appointmentCard($appt, 'cancelled') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="detail-row"><span class="detail-label"><i class="bi bi-person-fill me-2 text-primary"></i>Doctor</span><span id="d-doctor" class="detail-value"></span></div>
                <div class="detail-row"><span class="detail-label"><i class="bi bi-calendar3 me-2 text-primary"></i>Date</span><span id="d-date" class="detail-value"></span></div>
                <div class="detail-row"><span class="detail-label"><i class="bi bi-clock me-2 text-primary"></i>Time</span><span id="d-time" class="detail-value"></span></div>
                <div class="detail-row"><span class="detail-label"><i class="bi bi-chat-left-text me-2 text-primary"></i>Reason</span><span id="d-reason" class="detail-value"></span></div>
                <div class="detail-row border-0"><span class="detail-label"><i class="bi bi-info-circle me-2 text-primary"></i>Status</span><span id="d-status" class="detail-value"></span></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Reschedule Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Rescheduling is currently handled by our clinic staff.<br>
                    Please call or message us to reschedule your appointment.
                </p>
                <div class="reschedule-info">
                    <div class="mb-2"><i class="bi bi-telephone-fill me-2 text-primary"></i><strong>Phone:</strong> (02) 8123-4567</div>
                    <div class="mb-2"><i class="bi bi-envelope-fill me-2 text-primary"></i><strong>Email:</strong> clinic@oabsc.com</div>
                    <div><i class="bi bi-clock-fill me-2 text-primary"></i><strong>Hours:</strong> Mon–Sat, 8:00 AM – 5:00 PM</div>
                </div>
                <div class="mt-3 p-3 rounded" style="background:#f0f7ff;border:1px solid #bfdbfe;">
                    <div class="small text-muted mb-1">Appointment to reschedule:</div>
                    <strong id="r-doctor" class="d-block"></strong>
                    <span id="r-datetime" class="small text-muted"></span>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Cancel Appointment?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">You are about to cancel your appointment with:</p>
                <strong id="c-doctor" class="d-block mb-1"></strong>
                <span id="c-datetime" class="small text-muted d-block mb-3"></span>
                <p class="small text-danger mb-0"><i class="bi bi-exclamation-triangle me-1"></i>This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Keep It</button>
                <form id="cancelForm" method="post" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">Yes, Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewDetails(doctor, date, time, reason, status) {
    document.getElementById('d-doctor').textContent = doctor;
    document.getElementById('d-date').textContent   = date;
    document.getElementById('d-time').textContent   = time;
    document.getElementById('d-reason').textContent = reason;
    const badge = document.getElementById('d-status');
    badge.innerHTML = `<span class="status-pill status-${status}">${status}</span>`;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

function reschedule(doctor, date, time) {
    document.getElementById('r-doctor').textContent   = doctor;
    document.getElementById('r-datetime').textContent = date + ' at ' + time;
    new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
}

function cancelAppt(id, doctor, date, time) {
    document.getElementById('c-doctor').textContent   = doctor;
    document.getElementById('c-datetime').textContent = date + ' at ' + time;
    document.getElementById('cancelForm').action = '<?= site_url('/appointments/cancel/') ?>' + id;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
</script>

<?php
function appointmentCard(array $appt, string $type): string {
    $id     = $appt['id'] ?? 0;
    $doctor = esc($appt['doctor_name'] ?? '-');
    $date   = esc($appt['appointment_date'] ?? '-');
    $time   = esc(substr((string)($appt['appointment_time'] ?? ''), 0, 5));
    $reason = esc($appt['reason'] ?? '-');
    $status = strtolower($appt['status'] ?? 'pending');
    $created = isset($appt['created_at']) ? date('M d, Y', strtotime($appt['created_at'])) : '-';

    $formattedDate = '-';
    if ($date !== '-') {
        $formattedDate = date('F j, Y', strtotime($date));
    }
    $formattedTime = '-';
    if ($time !== '-') {
        $formattedTime = date('g:i A', strtotime($time));
    }

    $actions = '';
    if ($type === 'upcoming') {
        $actions = '
        <div class="d-flex gap-2 flex-wrap mt-3">
            <button class="appt-action-btn btn-view"
                onclick="viewDetails(\'' . addslashes($doctor) . '\',\'' . $formattedDate . '\',\'' . $formattedTime . '\',\'' . addslashes($reason) . '\',\'' . $status . '\')">
                <i class="bi bi-eye me-1"></i>View Details
            </button>
            <button class="appt-action-btn btn-reschedule"
                onclick="reschedule(\'' . addslashes($doctor) . '\',\'' . $formattedDate . '\',\'' . $formattedTime . '\')">
                <i class="bi bi-arrow-repeat me-1"></i>Reschedule
            </button>
            <button class="appt-action-btn btn-cancel"
                onclick="cancelAppt(' . $id . ',\'' . addslashes($doctor) . '\',\'' . $formattedDate . '\',\'' . $formattedTime . '\')">
                <i class="bi bi-x-lg me-1"></i>Cancel
            </button>
        </div>';
    } else {
        $actions = '
        <div class="d-flex gap-2 flex-wrap mt-3">
            <button class="appt-action-btn btn-view"
                onclick="viewDetails(\'' . addslashes($doctor) . '\',\'' . $formattedDate . '\',\'' . $formattedTime . '\',\'' . addslashes($reason) . '\',\'' . $status . '\')">
                <i class="bi bi-eye me-1"></i>View Details
            </button>
        </div>';
    }

    return '
    <div class="appt-card appt-card-' . $type . '">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="appt-avatar appt-avatar-' . $type . '">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <div class="appt-doctor">' . $doctor . '</div>
                    <div class="appt-meta">
                        <span><i class="bi bi-calendar3 me-1"></i>' . $formattedDate . '</span>
                        <span class="mx-2">·</span>
                        <span><i class="bi bi-clock me-1"></i>' . $formattedTime . '</span>
                    </div>
                    <div class="appt-reason">' . $reason . '</div>
                </div>
            </div>
            <span class="status-pill status-' . $status . '">' . ucfirst($status) . '</span>
        </div>
        ' . $actions . '
        <div class="appt-booked-on">Booked on ' . $created . '</div>
    </div>';
}

function emptyState(string $icon, string $title, string $sub): string {
    return '
    <div class="empty-state">
        <i class="bi bi-' . $icon . '"></i>
        <h6>' . $title . '</h6>
        <p>' . $sub . '</p>
    </div>';
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { background: #edf2f7; min-height: 100vh; font-family: 'Inter', sans-serif; }

    .appt-page { min-height: calc(100vh - 60px); }

    /* Buttons */
    .btn-back {
        background: white; border: 1px solid #dbe4ef; color: #475569;
        font-weight: 500; border-radius: 10px;
    }
    .btn-back:hover { background: #f1f5f9; }
    .btn-book {
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        color: white; border: none; font-weight: 600; border-radius: 10px;
        box-shadow: 0 2px 8px rgba(30,64,175,0.25);
    }
    .btn-book:hover { background: linear-gradient(135deg, #1e40af, #1e3a8a); color: white; }

    /* Summary Badges */
    .summary-badge {
        padding: 0.4rem 1rem; border-radius: 999px;
        font-size: 0.8rem; font-weight: 600;
    }
    .badge-upcoming  { background: #dbeafe; color: #1e40af; }
    .badge-completed { background: #d1fae5; color: #065f46; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }

    /* Tabs */
    .appt-tabs { border-bottom: 2px solid #e2e8f0; gap: 0; }
    .appt-tab {
        background: none; border: none; padding: 0.6rem 1.25rem;
        font-size: 0.85rem; font-weight: 600; color: #64748b;
        border-bottom: 3px solid transparent; margin-bottom: -2px;
        transition: all 0.15s; cursor: pointer; border-radius: 0;
    }
    .appt-tab:hover { color: #1e40af; }
    .appt-tab.active { color: #1e40af; border-bottom-color: #1e40af; }
    .tab-count {
        display: inline-flex; align-items: center; justify-content: center;
        background: #e2e8f0; color: #475569;
        width: 20px; height: 20px; border-radius: 50%;
        font-size: 0.7rem; font-weight: 700; margin-left: 6px;
    }
    .appt-tab.active .tab-count { background: #dbeafe; color: #1e40af; }

    /* Appointment Card */
    .appt-card {
        background: white; border-radius: 16px; padding: 1.25rem 1.5rem;
        border: 1px solid #e2e8f0; box-shadow: 0 1px 4px rgba(15,23,42,0.05);
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .appt-card:hover { box-shadow: 0 6px 20px rgba(15,23,42,0.1); transform: translateY(-1px); }
    .appt-card-upcoming  { border-left: 4px solid #3b82f6; }
    .appt-card-completed { border-left: 4px solid #10b981; }
    .appt-card-cancelled { border-left: 4px solid #ef4444; opacity: 0.85; }

    /* Avatar */
    .appt-avatar {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; flex-shrink: 0;
    }
    .appt-avatar-upcoming  { background: #dbeafe; color: #1e40af; }
    .appt-avatar-completed { background: #d1fae5; color: #065f46; }
    .appt-avatar-cancelled { background: #fee2e2; color: #991b1b; }

    .appt-doctor { font-weight: 700; font-size: 0.95rem; color: #0f172a; margin-bottom: 3px; }
    .appt-meta   { font-size: 0.8rem; color: #64748b; margin-bottom: 4px; }
    .appt-reason { font-size: 0.8rem; color: #475569; max-width: 420px;
                   white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .appt-booked-on { font-size: 0.72rem; color: #94a3b8; margin-top: 0.75rem; }

    /* Status Pills */
    .status-pill {
        padding: 0.3rem 0.75rem; border-radius: 999px;
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .status-pending   { background: #fef3c7; color: #92400e; }
    .status-confirmed { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* Action Buttons */
    .appt-action-btn {
        font-size: 0.78rem; font-weight: 600; padding: 0.4rem 0.9rem;
        border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s;
    }
    .btn-view       { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .btn-view:hover { background: #dbeafe; }
    .btn-reschedule       { background: #f0fdf4; color: #065f46; border: 1px solid #a7f3d0; }
    .btn-reschedule:hover { background: #d1fae5; }
    .btn-cancel       { background: #fff1f2; color: #991b1b; border: 1px solid #fecaca; }
    .btn-cancel:hover { background: #fee2e2; }

    /* Empty State */
    .empty-state { text-align: center; padding: 3.5rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.4; }
    .empty-state h6 { font-weight: 700; color: #475569; margin-bottom: 0.4rem; }
    .empty-state p  { font-size: 0.85rem; }

    /* Detail Modal */
    .detail-row {
        display: flex; justify-content: space-between; align-items: flex-start;
        padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9; gap: 1rem;
    }
    .detail-label { font-size: 0.82rem; color: #64748b; font-weight: 500; min-width: 90px; }
    .detail-value { font-size: 0.85rem; color: #0f172a; font-weight: 600; text-align: right; }

    /* Reschedule Info */
    .reschedule-info {
        background: #f8fafc; border-radius: 10px; padding: 1rem;
        border: 1px solid #e2e8f0; font-size: 0.85rem; color: #334155;
    }
</style>
</body>
</html>
