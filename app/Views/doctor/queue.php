<?php
$todayQueue    = $todayQueue ?? [];
$upcomingQueue = $upcomingQueue ?? [];
$today         = $today ?? date('Y-m-d');

function queueBadgeClass(string $status): string {
    return match ($status) {
        'approved'  => 'bg-success-subtle text-success',
        'pending'   => 'bg-warning-subtle text-warning',
        'completed' => 'bg-primary-subtle text-primary',
        'cancelled' => 'bg-danger-subtle text-danger',
        default     => 'bg-secondary-subtle text-secondary',
    };
}
?>
<?= view('doctor/_layout_top', ['pageTitle' => "Today's Queue", 'active' => 'queue']) ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="queue-hero p-2 p-md-3 mb-3">
        <div class="position-relative" style="z-index:1;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-uppercase small fw-semibold opacity-75 mb-1">Doctor Queue</div>
                    <h5 class="fw-bold mb-1">Today's Queue</h5>
                    <p class="mb-0 opacity-90 small">View and organize today's schedule alongside upcoming appointments.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="queue-stat">
                <div class="queue-stat-icon text-primary" style="background:#dbeafe;"><i class="bi bi-calendar-day"></i></div>
                <div class="text-muted small text-uppercase fw-semibold mb-0">Today</div>
                <div class="h4 fw-bold mb-0"><?= count($todayQueue) ?></div>
                <div class="text-muted small">Appointments for <?= esc(date('M j, Y', strtotime($today))) ?></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="queue-stat">
                <div class="queue-stat-icon text-info" style="background:#e0f2fe;"><i class="bi bi-arrow-right-circle"></i></div>
                <div class="text-muted small text-uppercase fw-semibold mb-0">Upcoming</div>
                <div class="h4 fw-bold mb-0"><?= count($upcomingQueue) ?></div>
                <div class="text-muted small">Future approved and pending</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="queue-stat">
                <div class="queue-stat-icon text-success" style="background:#dcfce7;"><i class="bi bi-list-check"></i></div>
                <div class="text-muted small text-uppercase fw-semibold mb-0">Total</div>
                <div class="h4 fw-bold mb-0"><?= count($todayQueue) + count($upcomingQueue) ?></div>
                <div class="text-muted small">All active entries</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="queue-section">
                <div class="queue-section-head d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold text-dark small"><i class="bi bi-calendar-day me-2 text-primary"></i>Today's Schedule</div>
                        <div class="queue-section-desc">Appointments scheduled for <?= esc(date('F j, Y', strtotime($today))) ?></div>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2"><?= count($todayQueue) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table queue-table table-hover align-middle mb-0 table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($todayQueue)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox d-block mb-2" style="font-size:1.4rem;"></i>
                                        No appointments scheduled for today.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($todayQueue as $appt): ?>
                                    <?php $status = strtolower((string) ($appt['status'] ?? '')); ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc(substr((string) ($appt['appointment_time'] ?? ''), 0, 5)) ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= esc($appt['patient_name'] ?? 'Unknown') ?></div>
                                            <?php if (! empty($appt['patient_phone'])): ?>
                                                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= esc($appt['patient_phone']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width:260px;">
                                            <span class="d-block text-truncate" title="<?= esc($appt['reason'] ?? '') ?>"><?= esc($appt['reason'] ?? '-') ?></span>
                                        </td>
                                        <td><span class="badge <?= queueBadgeClass($status) ?> px-3 py-2 rounded-pill"><?= esc(ucfirst($status)) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="queue-section">
                <div class="queue-section-head d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-semibold text-dark small"><i class="bi bi-arrow-right-circle me-2 text-info"></i>Upcoming Schedule</div>
                        <div class="queue-section-desc">Future approved and pending appointments</div>
                    </div>
                    <span class="badge bg-info rounded-pill px-3 py-2"><?= count($upcomingQueue) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table queue-table table-hover align-middle mb-0 table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($upcomingQueue)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-calendar2-x d-block mb-2" style="font-size:1.4rem;"></i>
                                        No upcoming appointments found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($upcomingQueue as $appt): ?>
                                    <?php $status = strtolower((string) ($appt['status'] ?? '')); ?>
                                    <tr>
                                        <td><?= esc($appt['appointment_date'] ?? '-') ?></td>
                                        <td class="fw-semibold"><?= esc(substr((string) ($appt['appointment_time'] ?? ''), 0, 5)) ?></td>
                                        <td>
                                            <div class="fw-semibold"><?= esc($appt['patient_name'] ?? 'Unknown') ?></div>
                                            <?php if (! empty($appt['patient_phone'])): ?>
                                                <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= esc($appt['patient_phone']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width:260px;">
                                            <span class="d-block text-truncate" title="<?= esc($appt['reason'] ?? '') ?>"><?= esc($appt['reason'] ?? '-') ?></span>
                                        </td>
                                        <td><span class="badge <?= queueBadgeClass($status) ?> px-3 py-2 rounded-pill"><?= esc(ucfirst($status)) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<style>
    .queue-hero {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 35%, #60a5fa 100%);
        border-radius: 14px;
        color: #fff;
        overflow: hidden;
        position: relative;
        box-shadow: 0 10px 22px rgba(37, 99, 235, 0.14);
    }
    .queue-hero::after {
        content: '';
        position: absolute;
        inset: auto -40px -60px auto;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.12);
    }
    .queue-hero::before {
        content: '';
        position: absolute;
        inset: 18px auto auto 18px;
        width: 90px; height: 90px;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.08);
        transform: rotate(15deg);
    }
    .queue-stat {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
        padding: 10px 12px;
        height: 100%;
    }
    .queue-stat-icon {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.9rem; margin-bottom: 8px;
    }
    .queue-section {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
    }
    .queue-section-head { padding: 10px 12px 6px; }
    .queue-section-desc { color: #64748b; font-size: 0.78rem; }
    .queue-table thead th {
        font-size: 0.68rem; text-transform: uppercase;
        letter-spacing: 0.04em; color: #475569;
        border-bottom: 1px solid #e2e8f0;
    }
    .queue-table { font-size: 0.86rem; }
    .queue-table tbody td { vertical-align: middle; border-color: #edf2f7; }
    .queue-table td, .queue-table th { padding-top: 0.55rem; padding-bottom: 0.55rem; }
</style>

<?= view('doctor/_layout_bottom') ?>
