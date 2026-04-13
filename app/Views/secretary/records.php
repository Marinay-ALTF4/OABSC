<?= view('secretary/_layout_top', ['pageTitle' => 'Patient Records', 'active' => 'records']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="sec-page-title"><i class="bi bi-folder2-open me-2"></i>Patient Records</h5>
    <form method="get" class="d-flex gap-2">
        <input type="text" name="search" class="sec-input" style="width:220px;" placeholder="Search name or email..." value="<?= esc($search ?? '') ?>">
        <button class="sec-save-btn">Search</button>
    </form>
</div>

<div class="sec-table-card">
    <div class="table-responsive">
        <table class="sec-table">
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th></tr>
            </thead>
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No patients found.</td></tr>
                <?php else: ?>
                    <?php foreach ($patients as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($p['name']) ?></td>
                        <td><?= esc($p['email']) ?></td>
                        <td><?= esc($p['phone'] ?? '—') ?></td>
                        <td><?= esc(date('M j, Y', strtotime($p['created_at']))) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= view('secretary/_layout_bottom') ?>
