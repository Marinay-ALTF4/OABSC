<?php
$appointments = $appointments ?? [];

$upcoming  = [];
$completed = [];
$cancelled = [];
$approved  = [];
$pending   = [];
$today     = date('Y-m-d');

foreach ($appointments as $appt) {
    $status = strtolower($appt['status'] ?? 'pending');
    if ($status === 'cancelled') {
        $cancelled[] = $appt;
    } elseif ($status === 'completed') {
        $completed[] = $appt;
    } else {
        $upcoming[] = $appt;
        // Further separate approved and pending
        if ($status === 'approved') {
            $approved[] = $appt;
        } else {
            $pending[] = $appt;
        }
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
<div style="max-width:1400px; margin:0 auto; padding: 3rem 3rem 5rem;">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="section-label-appt mb-1">My Visits</div>
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

    <!-- Summary Card -->
    <div class="summary-card mb-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="summary-stat">
                <div class="summary-stat-icon" style="background:#eef2ff;color:#6366f1;"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <div class="summary-stat-val"><?= count($upcoming) ?></div>
                    <div class="summary-stat-lbl">Upcoming</div>
                </div>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-stat">
                <div class="summary-stat-icon" style="background:#d1fae5;color:#065f46;"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="summary-stat-val"><?= count($completed) ?></div>
                    <div class="summary-stat-lbl">Completed</div>
                </div>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-stat">
                <div class="summary-stat-icon" style="background:#fee2e2;color:#991b1b;"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="summary-stat-val"><?= count($cancelled) ?></div>
                    <div class="summary-stat-lbl">Cancelled</div>
                </div>
            </div>
            <div class="summary-divider ms-auto d-none d-md-block"></div>
            <!-- Cancel Attempts inline -->
            <div class="ms-auto">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <span class="ca-label">Weekly Cancellation Attempts</span>
                    <span class="ca-count <?= ($cancel_remaining ?? 3) === 0 ? 'ca-count-empty' : (($cancel_remaining ?? 3) === 1 ? 'ca-count-low' : '') ?>">
                        <?= $cancel_remaining ?? 3 ?> / 3 remaining
                    </span>
                </div>
                <div class="ca-dots mt-2">
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <span class="ca-dot <?= $i <= (3 - ($cancel_remaining ?? 3)) ? 'ca-dot-used' : 'ca-dot-free' ?>"></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
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
                    <!-- APPROVED SECTION -->
                    <?php if (!empty($approved)): ?>
                        <div class="col-12">
                            <div class="appt-section-header">
                                <i class="bi bi-check-circle-fill me-2" style="color:#10b981;"></i>
                                <strong>Confirmed</strong>
                                <span class="appt-section-count"><?= count($approved) ?></span>
                            </div>
                        </div>
                        <?php foreach ($approved as $appt): ?>
                            <div class="col-12">
                                <?= appointmentCard($appt, 'upcoming') ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- PENDING SECTION -->
                    <?php if (!empty($pending)): ?>
                        <div class="col-12">
                            <div class="appt-section-header">
                                <i class="bi bi-hourglass-split me-2" style="color:#f59e0b;"></i>
                                <strong>Pending Confirmation</strong>
                                <span class="appt-section-count"><?= count($pending) ?></span>
                            </div>
                        </div>
                        <?php foreach ($pending as $appt): ?>
                            <div class="col-12">
                                <?= appointmentCard($appt, 'upcoming') ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    body { background: #eef0f8; min-height: 100vh; font-family: 'Inter', sans-serif; }
    .appt-page { min-height: calc(100vh - 60px); }

    /* Header */
    .section-label-appt { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #6366f1; margin-bottom: 4px; display: block; }
    h4.fw-bold { font-size: 1.8rem; font-weight: 800; color: #0f172a; letter-spacing: -0.3px; }

    /* Buttons */
    .btn-back { background: white; border: 1.5px solid #e0e7ff; color: #6366f1; font-weight: 600; border-radius: 12px; padding: 0.5rem 1.1rem; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
    .btn-back:hover { background: #eef2ff; color: #4f46e5; }
    .btn-book { background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; font-weight: 700; border-radius: 12px; padding: 0.5rem 1.25rem; font-size: 0.85rem; box-shadow: 0 4px 14px rgba(99,102,241,0.35); display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
    .btn-book:hover { opacity: 0.9; color: white; transform: translateY(-1px); }

    /* Summary card */
    .summary-card { background: white; border-radius: 18px; padding: 1.25rem 1.5rem; border: 1px solid #e8eaf6; box-shadow: 0 4px 16px rgba(99,102,241,0.07); margin-bottom: 1.5rem; }
    .summary-stat { display: flex; align-items: center; gap: 12px; }
    .summary-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .summary-stat-val { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .summary-stat-lbl { font-size: 0.75rem; color: #64748b; font-weight: 500; margin-top: 2px; }
    .summary-divider { width: 1px; background: #e8eaf6; align-self: stretch; margin: 0 8px; }

    /* Cancel attempts */
    .cancel-attempts-bar { background: white; border-radius: 14px; border: 1px solid #e8eaf6; padding: 1rem 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .ca-label { font-size: 0.82rem; font-weight: 600; color: #475569; }
    .ca-count { font-size: 0.82rem; font-weight: 700; color: #10b981; }
    .ca-count-low { color: #f59e0b; } .ca-count-empty { color: #ef4444; }
    .ca-dots { display: flex; gap: 8px; margin-top: 6px; }
    .ca-dot { width: 32px; height: 8px; border-radius: 999px; }
    .ca-dot-free { background: #d1fae5; } .ca-dot-used { background: #fca5a5; }
    .ca-reset-msg { font-size: 0.75rem; color: #ef4444; margin-top: 6px; font-weight: 500; }

    /* Tabs */
    .appt-tabs { border-bottom: 2px solid #e8eaf6; gap: 0; }
    .appt-tab { background: none; border: none; padding: 0.65rem 1.3rem; font-size: 0.875rem; font-weight: 600; color: #64748b; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.15s; cursor: pointer; border-radius: 0; display: inline-flex; align-items: center; gap: 6px; }
    .appt-tab:hover { color: #6366f1; }
    .appt-tab.active { color: #6366f1; border-bottom-color: #6366f1; }
    .tab-count { display: inline-flex; align-items: center; justify-content: center; background: #e8eaf6; color: #475569; width: 22px; height: 22px; border-radius: 50%; font-size: 0.72rem; font-weight: 700; }
    .appt-tab.active .tab-count { background: #eef2ff; color: #6366f1; }

    /* Section header */
    .appt-section-header { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; background: #f8faff; border: 1px solid #e0e7ff; font-size: 0.88rem; color: #0f172a; margin: 12px 0; }
    .appt-section-count { display: inline-flex; align-items: center; justify-content: center; background: #e0e7ff; color: #6366f1; width: 24px; height: 24px; border-radius: 50%; font-size: 0.72rem; font-weight: 700; margin-left: auto; }

    /* Appointment Card */
    .appt-card { background: white; border-radius: 18px; padding: 1.25rem 1.5rem; border: 1px solid #e8eaf6; box-shadow: 0 2px 10px rgba(99,102,241,0.06); transition: box-shadow 0.2s, transform 0.2s; }
    .appt-card:hover { box-shadow: 0 8px 24px rgba(99,102,241,0.12); transform: translateY(-2px); }
    .appt-card-upcoming  { border-left: 4px solid #6366f1; }
    .appt-card-completed { border-left: 4px solid #10b981; }
    .appt-card-cancelled { border-left: 4px solid #ef4444; opacity: 0.85; }

    /* Avatar */
    .appt-avatar { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .appt-avatar-upcoming  { background: #eef2ff; color: #6366f1; }
    .appt-avatar-completed { background: #d1fae5; color: #065f46; }
    .appt-avatar-cancelled { background: #fee2e2; color: #991b1b; }

    .appt-doctor { font-weight: 700; font-size: 0.95rem; color: #0f172a; margin-bottom: 4px; }
    .appt-meta   { font-size: 0.8rem; color: #64748b; margin-bottom: 4px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .appt-reason { font-size: 0.8rem; color: #475569; max-width: 420px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .appt-booked-on { font-size: 0.72rem; color: #94a3b8; margin-top: 0.75rem; }

    /* Status Pills */
    .status-pill { padding: 0.3rem 0.85rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-pending   { background: #fef3c7; color: #92400e; }
    .status-confirmed { background: #eef2ff; color: #6366f1; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* Action Buttons */
    .appt-action-btn { font-size: 0.8rem; font-weight: 600; padding: 0.45rem 1rem; border-radius: 10px; border: none; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 4px; }
    .btn-view       { background: #eef2ff; color: #6366f1; border: 1px solid #c7d2fe; }
    .btn-view:hover { background: #e0e7ff; }
    .btn-reschedule       { background: #f0fdf4; color: #065f46; border: 1px solid #a7f3d0; }
    .btn-reschedule:hover { background: #d1fae5; }
    .btn-cancel       { background: #fff1f2; color: #991b1b; border: 1px solid #fecaca; }
    .btn-cancel:hover { background: #fee2e2; }

    /* Empty State */
    .empty-state { text-align: center; padding: 3.5rem 1rem; color: #94a3b8; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; color: #c7d2fe; }
    .empty-state h6 { font-weight: 700; color: #475569; margin-bottom: 0.4rem; }
    .empty-state p  { font-size: 0.85rem; }

    /* Detail Modal */
    .detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9; gap: 1rem; }
    .detail-label { font-size: 0.82rem; color: #64748b; font-weight: 500; min-width: 90px; }
    .detail-value { font-size: 0.85rem; color: #0f172a; font-weight: 600; text-align: right; }
    .reschedule-info { background: #f8faff; border-radius: 10px; padding: 1rem; border: 1px solid #e0e7ff; font-size: 0.85rem; color: #334155; }
</style>

    .appt-page { min-height: calc(100vh - 60px); }

    /* Page header */
    h4.fw-bold { font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #0f172a; letter-spacing: -0.3px; }
    .text-muted.small { font-family: 'DM Sans', sans-serif; color: #64748b !important; }

    /* Section label */
    .appt-section-label-top {
        font-family: 'DM Sans', sans-serif;
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1.8px; color: #64748b;
    }

    /* Buttons */
    .btn-back {
        background: white; border: 1px solid #dbe4ef; color: #475569;
        font-weight: 600; border-radius: 10px; font-family: 'DM Sans', sans-serif;
    }
    .btn-back:hover { background: #f1f5f9; color: #1e40af; }
    .btn-book {
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        color: white; border: none; font-weight: 600; border-radius: 10px;
        box-shadow: 0 2px 8px rgba(30,64,175,0.25); font-family: 'DM Sans', sans-serif;
    }
    .btn-book:hover { background: linear-gradient(135deg, #1e40af, #1e3a8a); color: white; }

    /* Section Headers */
    .appt-section-header {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 16px; border-radius: 12px;
        background: #f8fafc; border: 1px solid #dbe4ef;
        font-size: 0.92rem; color: #0f172a; font-family: 'DM Sans', sans-serif;
        margin-top: 12px; margin-bottom: 12px;
    }
    .appt-section-count {
        display: inline-flex; align-items: center; justify-content: center;
        background: #e2e8f0; color: #475569;
        width: 24px; height: 24px; border-radius: 50%;
        font-size: 0.75rem; font-weight: 700;
        margin-left: auto;
    }

    /* Summary Badges */
    .summary-badge {
        padding: 0.4rem 1rem; border-radius: 999px;
        font-size: 0.8rem; font-weight: 600; font-family: 'DM Sans', sans-serif;
    }
    .badge-upcoming  { background: #dbeafe; color: #1e40af; }
    .badge-completed { background: #d1fae5; color: #065f46; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }

    /* Section label (matches dashboard .section-label) */
    .section-label-appt {
        font-family: 'DM Sans', sans-serif;
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1.8px; color: #64748b;
        margin-bottom: 0.75rem; display: block;
    }

    /* Tabs */
    .appt-tabs { border-bottom: 2px solid #e2e8f0; gap: 0; }
    .appt-tab {
        background: none; border: none; padding: 0.6rem 1.25rem;
        font-size: 0.85rem; font-weight: 600; color: #64748b;
        border-bottom: 3px solid transparent; margin-bottom: -2px;
        transition: all 0.15s; cursor: pointer; border-radius: 0;
        font-family: 'DM Sans', sans-serif;
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

    /* Appointment Card — matches dashboard .action-card */
    .appt-card {
        background: white; border-radius: 18px; padding: 1.25rem 1.5rem;
        border: 1px solid #dbe4ef;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .appt-card:hover { box-shadow: 0 10px 28px rgba(15,23,42,0.12); transform: translateY(-2px); border-color: #c6d4e4; }
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

    .appt-doctor { font-weight: 700; font-size: 0.95rem; color: #0f172a; margin-bottom: 3px; font-family: 'Plus Jakarta Sans', sans-serif; }
    .appt-meta   { font-size: 0.8rem; color: #64748b; margin-bottom: 4px; font-family: 'DM Sans', sans-serif; }
    .appt-reason { font-size: 0.8rem; color: #475569; max-width: 420px;
                   white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: 'DM Sans', sans-serif; }
    .appt-booked-on { font-size: 0.72rem; color: #94a3b8; margin-top: 0.75rem; font-family: 'DM Sans', sans-serif; }

    /* Status Pills */
    .status-pill {
        padding: 0.3rem 0.75rem; border-radius: 999px;
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .status-pending   { background: #fef3c7; color: #92400e; }
    .status-confirmed { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* Action Buttons — matches dashboard .action-btn */
    .appt-action-btn {
        font-size: 0.78rem; font-weight: 600; padding: 0.4rem 0.9rem;
        border-radius: 8px; border: none; cursor: pointer; transition: all 0.15s;
        font-family: 'DM Sans', sans-serif;
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

    /* Cancel Attempts Bar */
    .cancel-attempts-bar {
        background: white; border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 0.85rem 1.1rem;
        box-shadow: 0 1px 4px rgba(15,23,42,0.05);
    }
    .ca-label { font-size: 0.82rem; font-weight: 600; color: #475569; }
    .ca-count { font-size: 0.82rem; font-weight: 700; color: #10b981; }
    .ca-count-low   { color: #f59e0b; }
    .ca-count-empty { color: #ef4444; }
    .ca-dots { display: flex; gap: 8px; margin-top: 6px; }
    .ca-dot {
        width: 28px; height: 8px; border-radius: 999px;
        transition: background 0.2s;
    }
    .ca-dot-free { background: #d1fae5; }
    .ca-dot-used { background: #fca5a5; }
    .ca-reset-msg {
        font-size: 0.75rem; color: #ef4444; margin-top: 6px; font-weight: 500;
    }
    .empty-state h6 { font-weight: 700; color: #475569; margin-bottom: 0.4rem; font-family: 'Plus Jakarta Sans', sans-serif; }
    .empty-state p  { font-size: 0.85rem; font-family: 'DM Sans', sans-serif; }

    /* Detail Modal */
    .detail-row {
        display: flex; justify-content: space-between; align-items: flex-start;
        padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9; gap: 1rem;
    }
    .detail-label { font-size: 0.82rem; color: #64748b; font-weight: 500; min-width: 90px; font-family: 'DM Sans', sans-serif; }
    .detail-value { font-size: 0.85rem; color: #0f172a; font-weight: 600; text-align: right; font-family: 'DM Sans', sans-serif; }

    /* Reschedule Info */
    .reschedule-info {
        background: #f8fafc; border-radius: 10px; padding: 1rem;
        border: 1px solid #dbe4ef; font-size: 0.85rem; color: #334155;
        font-family: 'DM Sans', sans-serif;
    }
</style>
</body>
</html>
