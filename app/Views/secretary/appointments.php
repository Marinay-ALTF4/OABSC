<?php $pageTitle = 'Manage Appointments'; ?>
<?= view('secretary/_layout_top', ['pageTitle' => $pageTitle, 'active' => 'appointments']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="sec-page-title"><i class="bi bi-calendar2-plus me-2"></i>Manage Appointments</h5>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><<?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="sec-table-card">
    <div class="table-responsive">
        <table class="sec-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Service / Reason</th>
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
                        <td><?= esc($a['user_id'] ?? '—') ?></td>
                        <td><?= esc($a['doctor_name'] ?? '—') ?></td>
                        <td><?= esc($a['reason'] ?? '—') ?></td>
                        <td><?= esc($a['appointment_date'] ?? '—') ?></td>
                        <td><?= esc($a['appointment_time'] ?? '—') ?></td>
                        <td><?php
                            $s = strtolower($a['status'] ?? '');
                            $cls = match($s) { 'confirmed'=>'badge bg-primary', 'completed'=>'badge bg-success', 'cancelled'=>'badge bg-danger', 'serving'=>'badge bg-warning text-dark', default=>'badge bg-secondary' };
                        ?><span class="<?= $cls ?>"><?= ucfirst($s) ?></span></td>
                        <td>
                            <form method="post" action="<?= site_url('/secretary/update-status') ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width:120px;">
                                    <option value="pending" <?= $a['status']==='pending'?'selected':'' ?>>Pending</option>
                                    <option value="confirmed" <?= $a['status']==='confirmed'?'selected':'' ?>>Confirmed</option>
                                    <option value="cancelled" <?= $a['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
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

<?= view('secretary/_layout_bottom') ?>
