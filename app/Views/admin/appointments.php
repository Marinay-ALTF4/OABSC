<?php $pageTitle = 'Appointments'; ?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-calendar-event me-2"></i>All Appointments</h5>
    <span class="text-muted small"><?= esc((string) count($appointments)) ?> total</span>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="sec-table-card">
    <div class="table-responsive">
        <table class="sec-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No appointments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($appointments as $i => $a): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($a['patient_name'] ?? '—') ?></td>
                        <td><?= esc($a['doctor_name'] ?? '—') ?></td>
                        <td><?= esc($a['reason'] ?? '—') ?></td>
                        <td><?= esc($a['appointment_date'] ?? '—') ?></td>
                        <td><?= esc(substr((string) ($a['appointment_time'] ?? ''), 0, 5)) ?></td>
                        <td>
                            <?php
                            $s   = strtolower($a['status'] ?? '');
                            $cls = match($s) {
                                'confirmed' => 'badge bg-primary',
                                'completed' => 'badge bg-success',
                                'cancelled' => 'badge bg-danger',
                                'serving'   => 'badge bg-warning text-dark',
                                default     => 'badge bg-secondary',
                            };
                            ?>
                            <span class="<?= $cls ?>"><?= ucfirst($s) ?></span>
                        </td>
                        <td>
                            <form method="post" action="<?= site_url('/admin/appointments/update-status') ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width:120px;">
                                    <option value="pending"   <?= $a['status']==='pending'   ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $a['status']==='confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="cancelled" <?= $a['status']==='cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button class="btn btn-sm btn-dark">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
