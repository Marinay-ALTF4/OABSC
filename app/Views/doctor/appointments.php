<?= view('doctor/_layout_top', ['pageTitle' => 'My Appointments', 'active' => 'appointments']) ?>

<?php
$filter = $filter ?? 'today';
$todayCount = $todayCount ?? 0;
$upcomingCount = $upcomingCount ?? 0;
$completeCount = $completeCount ?? 0;
$allCount = $allCount ?? 0;
?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="doc-page-title"><i class="bi bi-calendar2-week me-2"></i>My Appointments</h5>
            <p class="doc-page-sub">View and manage your patient appointments.</p>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="?filter=today"     class="doc-filter-btn doc-filter-btn-count <?= $filter === 'today'     ? 'active' : '' ?>">
            <span>Today</span>
            <span class="doc-filter-badge">&nbsp;<?= (int) $todayCount ?></span>
        </a>
        <a href="?filter=upcoming"  class="doc-filter-btn doc-filter-btn-count <?= $filter === 'upcoming'  ? 'active' : '' ?>">
            <span>Upcoming</span>
            <span class="doc-filter-badge">&nbsp;<?= (int) $upcomingCount ?></span>
        </a>
        <a href="?filter=complete"  class="doc-filter-btn <?= $filter === 'complete'  ? 'active' : '' ?>">Complete</a>
        <a href="?filter=approved"  class="doc-filter-btn <?= $filter === 'approved'  ? 'active' : '' ?>">Approved</a>
        <a href="?filter=cancelled" class="doc-filter-btn <?= $filter === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
        <a href="?filter=all"       class="doc-filter-btn <?= $filter === 'all'       ? 'active' : '' ?>">All</a>
    </div>

    <div class="doc-table-card">
        <div class="table-responsive">
            <?php if (empty($appointments)): ?>
                <?php
                $emptyMsg = match ($filter) {
                    'approved'  => 'No approved appointments.',
                    'cancelled' => 'No cancelled appointments.',
                    'upcoming'  => 'No upcoming appointments.',
                    'today'     => 'No appointments scheduled for today.',
                    'complete'  => 'No completed appointments.',
                    'all'       => 'No appointments found.',
                    default     => 'No appointments in this view.',
                };
                ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;color:#6aaa70;"></i>
                    <p class="mb-0"><?= esc($emptyMsg) ?></p>
                </div>
            <?php else: ?>
                <table class="doc-table">
                    <thead>
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
                                'approved', 'confirmed' => 'doc-badge-approved',
                                'pending'               => 'doc-badge-pending',
                                'completed'             => 'doc-badge-completed',
                                'cancelled'             => 'doc-badge-cancelled',
                                default                 => 'doc-badge-default',
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
                                <td><span class="doc-badge <?= $statusClass ?>"><?= esc(ucfirst($appt['status'])) ?></span></td>
                                <td>
                                    <?php if ($appt['status'] === 'pending'): ?>
                                        <form action="<?= site_url('/doctor/appointments/status') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="doc-action-btn doc-action-approve">Approve</button>
                                        </form>
                                        <form action="<?= site_url('/doctor/appointments/status') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="doc-action-btn doc-action-cancel" onclick="return confirm('Cancel this appointment?')">Cancel</button>
                                        </form>
                                    <?php elseif (in_array($appt['status'], ['approved', 'confirmed'], true)): ?>
                                        <form action="<?= site_url('/doctor/appointments/status') ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="doc-action-btn doc-action-done">Mark Done</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

<?= view('doctor/_layout_bottom') ?>

<style>
    .doc-filter-btn-count {
        position: relative;
        padding-right: 2.25rem;
    }
    .doc-filter-badge {
        position: absolute;
        top: 0.35rem;
        right: 0.45rem;
        min-width: 1.1rem;
        height: 1.1rem;
        padding: 0 0.28rem;
        border-radius: 999px;
        background: #e5f2e7;
        color: #2f6b3b;
        font-size: 0.68rem;
        font-weight: 700;
        line-height: 1.1rem;
        text-align: center;
    }
    .doc-filter-btn.active .doc-filter-badge {
        background: rgba(255,255,255,0.2);
        color: #fff;
    }
</style>
