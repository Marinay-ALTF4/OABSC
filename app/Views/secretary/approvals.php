<?= view('secretary/_layout_top', ['pageTitle' => 'Pending Approvals', 'active' => 'approvals']) ?>

<div class="mb-4">
    <h5 class="sec-page-title"><i class="bi bi-bell me-2"></i>Pending Approvals</h5>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="sec-table-card">
    <div class="table-responsive">
        <table class="sec-table">
            <thead>
                <tr><th>#</th><th>Patient</th><th>Doctor</th><th>Reason</th><th>Date</th><th>Time</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php if (empty($pending)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No pending appointments.</td></tr>
                <?php else: ?>
                    <?php foreach ($pending as $i => $a): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($a['user_id'] ?? '—') ?></td>
                        <td><?= esc($a['doctor_name'] ?? '—') ?></td>
                        <td><?= esc($a['reason'] ?? '—') ?></td>
                        <td><?= esc($a['appointment_date'] ?? '—') ?></td>
                        <td><?= esc($a['appointment_time'] ?? '—') ?></td>
                        <td>
                            <form method="post" action="<?= site_url('/secretary/update-status') ?>" class="d-flex gap-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button name="status" value="confirmed" class="btn btn-sm btn-success">Confirm</button>
                                <button name="status" value="cancelled" class="btn btn-sm btn-outline-danger">Cancel</button>
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
