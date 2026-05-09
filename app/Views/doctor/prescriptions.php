<?php
$prescriptions = $prescriptions ?? [];
$patients      = $patients ?? [];
?>
<?= view('doctor/_layout_top', ['pageTitle' => 'Prescriptions', 'active' => 'prescriptions']) ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Prescriptions</h4>
            <p class="text-muted small mb-0">Create and manage prescriptions linked to your patients.</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="rx-card p-3 p-md-4">
                <div class="fw-semibold mb-3"><i class="bi bi-capsule-pill me-2 text-primary"></i>New Prescription</div>
                <form method="post" action="<?= site_url('/doctor/prescriptions') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Patient</label>
                        <select name="patient_id" class="form-select form-select-sm" id="rxPatientSelect" required>
                            <option value="">Select patient...</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= (int) ($p['id'] ?? 0) ?>" data-name="<?= esc((string) ($p['name'] ?? 'Unknown')) ?>">
                                    <?= esc((string) ($p['name'] ?? 'Unknown')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="patient_name" id="rxPatientName" value="">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Medicine</label>
                        <input type="text" name="medicine" class="form-control form-control-sm" maxlength="200" required>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted mb-1">Dosage</label>
                            <input type="text" name="dosage" class="form-control form-control-sm" placeholder="e.g. 500mg" maxlength="120" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted mb-1">Frequency</label>
                            <input type="text" name="frequency" class="form-control form-control-sm" placeholder="e.g. 2x/day" maxlength="120" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted mb-1">Duration</label>
                            <input type="text" name="duration" class="form-control form-control-sm" placeholder="e.g. 7 days" maxlength="120" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Instructions (optional)</label>
                        <textarea name="instructions" class="form-control form-control-sm" rows="4" maxlength="2000" placeholder="Take after meals, avoid alcohol, etc."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Save Prescription</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="rx-card p-0">
                <div class="rx-head d-flex justify-content-between align-items-center">
                    <div class="fw-semibold"><i class="bi bi-prescription2 me-2 text-primary"></i>Saved Prescriptions</div>
                    <span class="badge bg-primary rounded-pill"><?= count($prescriptions) ?></span>
                </div>
                <?php if (empty($prescriptions)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox d-block mb-2" style="font-size:1.5rem;"></i>
                        No prescriptions yet.
                    </div>
                <?php else: ?>
                    <div class="rx-list">
                        <?php foreach ($prescriptions as $rx): ?>
                            <div class="rx-item">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-semibold"><?= esc((string) ($rx['patient_name'] ?? 'Unknown Patient')) ?></div>
                                        <div class="small text-muted">
                                            <?= esc(date('M j, Y g:i A', strtotime((string) ($rx['created_at'] ?? date('Y-m-d H:i:s'))))) ?>
                                        </div>
                                    </div>
                                    <form method="post" action="<?= site_url('/doctor/prescriptions') ?>" onsubmit="return confirm('Delete this prescription?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="prescription_id" value="<?= esc((string) ($rx['id'] ?? '')) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                                    </form>
                                </div>
                                <div class="small mb-1"><span class="text-muted">Medicine:</span> <strong><?= esc((string) ($rx['medicine'] ?? '')) ?></strong></div>
                                <div class="small mb-1"><span class="text-muted">Dose:</span> <?= esc((string) ($rx['dosage'] ?? '-')) ?> | <span class="text-muted">Frequency:</span> <?= esc((string) ($rx['frequency'] ?? '-')) ?> | <span class="text-muted">Duration:</span> <?= esc((string) ($rx['duration'] ?? '-')) ?></div>
                                <?php if (! empty($rx['instructions'])): ?>
                                    <div class="small text-dark mt-2" style="white-space: pre-wrap;"><span class="text-muted">Instructions:</span> <?= esc((string) $rx['instructions']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<style>
    .rx-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .rx-head {
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }
    .rx-list { max-height: 620px; overflow: auto; }
    .rx-item { padding: 12px 14px; border-bottom: 1px solid #eef2f7; }
    .rx-item:last-child { border-bottom: none; }
</style>

<script>
const rxPatientSelect = document.getElementById('rxPatientSelect');
const rxPatientName = document.getElementById('rxPatientName');
if (rxPatientSelect && rxPatientName) {
    rxPatientSelect.addEventListener('change', function () {
        const selected = rxPatientSelect.options[rxPatientSelect.selectedIndex];
        rxPatientName.value = selected ? (selected.getAttribute('data-name') || '') : '';
    });
}
</script>

<?= view('doctor/_layout_bottom') ?>
