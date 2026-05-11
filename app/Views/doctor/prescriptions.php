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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="doc-page-title"><i class="bi bi-capsule me-2"></i>Prescriptions</h5>
            <p class="doc-page-sub">Create and manage prescriptions linked to your patients.</p>
        </div>
    </div>

    <div class="row g-3">
        <!-- New Prescription Form -->
        <div class="col-12 col-xl-5">
            <div class="doc-form-card">
                <div class="doc-form-card-head">
                    <i class="bi bi-capsule-pill me-2" style="color:#2e5c32;"></i>New Prescription
                </div>
                <div class="p-3">
                    <form method="post" action="<?= site_url('/doctor/prescriptions') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <label class="doc-label">Patient</label>
                            <select name="patient_id" class="doc-input" id="rxPatientSelect" required>
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
                            <label class="doc-label">Medicine</label>
                            <input type="text" name="medicine" class="doc-input" maxlength="200" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-md-4">
                                <label class="doc-label">Dosage</label>
                                <input type="text" name="dosage" class="doc-input" placeholder="e.g. 500mg" maxlength="120" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="doc-label">Frequency</label>
                                <input type="text" name="frequency" class="doc-input" placeholder="e.g. 2x/day" maxlength="120" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="doc-label">Duration</label>
                                <input type="text" name="duration" class="doc-input" placeholder="e.g. 7 days" maxlength="120" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="doc-label">Instructions (optional)</label>
                            <textarea name="instructions" class="doc-input" rows="4" maxlength="2000" placeholder="Take after meals, avoid alcohol, etc."></textarea>
                        </div>
                        <button type="submit" class="doc-save-btn">Save Prescription</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Saved Prescriptions List -->
        <div class="col-12 col-xl-7">
            <div class="doc-table-card">
                <div class="doc-table-card-head d-flex justify-content-between align-items-center">
                    <div class="fw-semibold" style="font-size:0.88rem;color:#1b3a1e;">
                        <i class="bi bi-prescription2 me-2" style="color:#2e5c32;"></i>Saved Prescriptions
                    </div>
                    <span class="doc-count-badge"><?= count($prescriptions) ?></span>
                </div>
                <?php if (empty($prescriptions)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox d-block mb-2" style="font-size:1.5rem;color:#6aaa70;"></i>
                        No prescriptions yet.
                    </div>
                <?php else: ?>
                    <div style="max-height:620px;overflow:auto;">
                        <?php foreach ($prescriptions as $rx): ?>
                            <div class="doc-list-item">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-semibold" style="color:#1b3a1e;"><?= esc((string) ($rx['patient_name'] ?? 'Unknown Patient')) ?></div>
                                        <div class="small text-muted">
                                            <?= esc(date('M j, Y g:i A', strtotime((string) ($rx['created_at'] ?? date('Y-m-d H:i:s'))))) ?>
                                        </div>
                                    </div>
                                    <form method="post" action="<?= site_url('/doctor/prescriptions') ?>" onsubmit="return confirm('Delete this prescription?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="prescription_id" value="<?= esc((string) ($rx['id'] ?? '')) ?>">
                                        <button type="submit" class="doc-action-btn doc-action-cancel">Delete</button>
                                    </form>
                                </div>
                                <div class="small mb-1"><span class="text-muted">Medicine:</span> <strong><?= esc((string) ($rx['medicine'] ?? '')) ?></strong></div>
                                <div class="small mb-1">
                                    <span class="text-muted">Dose:</span> <?= esc((string) ($rx['dosage'] ?? '-')) ?>
                                    &nbsp;|&nbsp;<span class="text-muted">Frequency:</span> <?= esc((string) ($rx['frequency'] ?? '-')) ?>
                                    &nbsp;|&nbsp;<span class="text-muted">Duration:</span> <?= esc((string) ($rx['duration'] ?? '-')) ?>
                                </div>
                                <?php if (! empty($rx['instructions'])): ?>
                                    <div class="small mt-2" style="color:#2d3748;white-space:pre-wrap;">
                                        <span class="text-muted">Instructions:</span> <?= esc((string) $rx['instructions']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
