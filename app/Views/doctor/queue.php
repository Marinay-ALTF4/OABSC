<?php
$todayQueue    = $todayQueue ?? [];
$upcomingQueue = $upcomingQueue ?? [];
$confirmedCount = $confirmedCount ?? 0;
$doneCount      = $doneCount ?? 0;
$today         = $today ?? date('Y-m-d');

function queueBadgeClass(string $status): string {
    return match ($status) {
        'approved'  => 'doc-badge-approved',
        'pending'   => 'doc-badge-pending',
        'completed' => 'doc-badge-completed',
        'cancelled' => 'doc-badge-cancelled',
        default     => 'doc-badge-default',
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="doc-page-title"><i class="bi bi-list-ol me-2"></i>Today's Queue</h5>
            <p class="doc-page-sub">View and organize today's schedule alongside upcoming appointments.</p>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="doc-stat-card">
                <div class="doc-stat-icon" style="background:#eaf6ea;color:#2e5c32;"><i class="bi bi-calendar-day"></i></div>
                <div class="doc-stat-label">Today</div>
                <div class="doc-stat-value"><?= count($todayQueue) ?></div>
                <div class="doc-stat-sub">Appointments for <?= esc(date('M j, Y', strtotime($today))) ?></div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="doc-stat-card">
                <div class="doc-stat-icon" style="background:#eaf6ea;color:#2e5c32;"><i class="bi bi-arrow-right-circle"></i></div>
                <div class="doc-stat-label">Upcoming</div>
                <div class="doc-stat-value"><?= count($upcomingQueue) ?></div>
                <div class="doc-stat-sub">Future approved and pending</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="doc-stat-card">
                <div class="doc-stat-icon" style="background:#eaf6ea;color:#2e5c32;"><i class="bi bi-check-circle"></i></div>
                <div class="doc-stat-label">Confirmed</div>
                <div class="doc-stat-value"><?= (int) $confirmedCount ?></div>
                <div class="doc-stat-sub">Appointments confirmed by staff</div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="doc-stat-card">
                <div class="doc-stat-icon" style="background:#eaf6ea;color:#2e5c32;"><i class="bi bi-check2-square"></i></div>
                <div class="doc-stat-label">Done</div>
                <div class="doc-stat-value"><?= (int) $doneCount ?></div>
                <div class="doc-stat-sub">Completed appointments</div>
            </div>
        </div>
    </div>

    <!-- Today's Schedule -->
    <div class="doc-table-card mb-4">
        <div class="doc-table-card-head d-flex justify-content-between align-items-start gap-3">
            <div>
                <div class="fw-semibold" style="font-size:0.88rem;color:#1b3a1e;">
                    <i class="bi bi-calendar-day me-2" style="color:#2e5c32;"></i>Today's Schedule
                </div>
                <div class="doc-page-sub">Appointments scheduled for <?= esc(date('F j, Y', strtotime($today))) ?></div>
            </div>
            <span class="doc-count-badge"><?= count($todayQueue) ?></span>
        </div>
        <div class="table-responsive">
            <table class="doc-table">
                <thead>
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
                                <i class="bi bi-inbox d-block mb-2" style="font-size:1.4rem;color:#6aaa70;"></i>
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
                                <td><span class="doc-badge <?= queueBadgeClass($status) ?>"><?= esc(ucfirst($status)) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upcoming Schedule -->
    <div class="doc-table-card">
        <div class="doc-table-card-head d-flex justify-content-between align-items-start gap-3">
            <div>
                <div class="fw-semibold" style="font-size:0.88rem;color:#1b3a1e;">
                    <i class="bi bi-arrow-right-circle me-2" style="color:#2e5c32;"></i>Upcoming Schedule
                </div>
                <div class="doc-page-sub">Future approved and pending appointments</div>
            </div>
            <span class="doc-count-badge"><?= count($upcomingQueue) ?></span>
        </div>
        <div class="table-responsive">
            <table class="doc-table">
                <thead>
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
                                <i class="bi bi-calendar2-x d-block mb-2" style="font-size:1.4rem;color:#6aaa70;"></i>
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
                                <td><span class="doc-badge <?= queueBadgeClass($status) ?>"><?= esc(ucfirst($status)) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?= view('doctor/_layout_bottom') ?>
