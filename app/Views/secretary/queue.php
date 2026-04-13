<?= view('secretary/_layout_top', ['pageTitle' => "Today's Queue", 'active' => 'queue']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="sec-page-title"><i class="bi bi-list-ol me-2"></i>Patient Queue — <?= date('F j, Y') ?></h5>
</div>

<div class="sec-table-card">
    <div class="table-responsive">
        <table class="sec-table">
            <thead>
                <tr><th>Queue #</th><th>Patient</th><th>Doctor</th><th>Reason</th><th>Time</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if (empty($queue)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No patients in queue today.</td></tr>
                <?php else: ?>
                    <?php foreach ($queue as $i => $q): ?>
                    <tr>
                        <td><strong>#<?= $i + 1 ?></strong></td>
                        <td><?= esc($q['user_id'] ?? '—') ?></td>
                        <td><?= esc($q['doctor_name'] ?? '—') ?></td>
                        <td><?= esc($q['reason'] ?? '—') ?></td>
                        <td><?= esc($q['appointment_time'] ?? '—') ?></td>
                        <td><?php
                            $s = strtolower($q['status'] ?? '');
                            $cls = match($s) { 'confirmed'=>'badge bg-primary', 'completed'=>'badge bg-success', 'serving'=>'badge bg-warning text-dark', 'cancelled'=>'badge bg-danger', default=>'badge bg-secondary' };
                        ?><span class="<?= $cls ?>"><?= ucfirst($s) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= view('secretary/_layout_bottom') ?>
