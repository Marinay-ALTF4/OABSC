<?php
$notes    = $notes ?? [];
$patients = $patients ?? [];
?>
<?= view('doctor/_layout_top', ['pageTitle' => 'Write Notes', 'active' => 'notes']) ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="doc-page-title"><i class="bi bi-journal-text me-2"></i>Write Notes</h5>
            <p class="doc-page-sub">Create private doctor notes and tag them to your patients.</p>
        </div>
    </div>

    <div class="row g-3">
        <!-- New Note Form -->
        <div class="col-12 col-lg-5">
            <div class="doc-form-card">
                <div class="doc-form-card-head">
                    <i class="bi bi-journal-plus me-2" style="color:#2e5c32;"></i>New Note
                </div>
                <div class="p-3">
                    <form method="post" action="<?= site_url('/doctor/notes') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <label class="doc-label">Title</label>
                            <input type="text" name="title" class="doc-input" maxlength="120" required>
                        </div>
                        <div class="mb-2">
                            <label class="doc-label">Patient (optional)</label>
                            <select name="patient_id" class="doc-input" id="patientSelect">
                                <option value="">No specific patient</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= (int) ($p['id'] ?? 0) ?>" data-name="<?= esc((string) ($p['name'] ?? 'Unknown')) ?>">
                                        <?= esc((string) ($p['name'] ?? 'Unknown')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="patient_name" id="patientNameInput" value="">
                        </div>
                        <div class="mb-3">
                            <label class="doc-label">Note</label>
                            <textarea name="body" class="doc-input" rows="6" maxlength="3000" required></textarea>
                        </div>
                        <button type="submit" class="doc-save-btn">Save Note</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Saved Notes List -->
        <div class="col-12 col-lg-7">
            <div class="doc-table-card">
                <div class="doc-table-card-head d-flex justify-content-between align-items-center">
                    <div class="fw-semibold" style="font-size:0.88rem;color:#1b3a1e;">
                        <i class="bi bi-journal-text me-2" style="color:#2e5c32;"></i>Saved Notes
                    </div>
                    <span class="doc-count-badge"><?= count($notes) ?></span>
                </div>
                <?php if (empty($notes)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox d-block mb-2" style="font-size:1.5rem;color:#6aaa70;"></i>
                        No notes yet.
                    </div>
                <?php else: ?>
                    <div style="max-height:560px;overflow:auto;">
                        <?php foreach ($notes as $note): ?>
                            <div class="doc-list-item">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold" style="color:#1b3a1e;"><?= esc((string) ($note['title'] ?? 'Untitled')) ?></div>
                                        <div class="small text-muted">
                                            <?= esc(date('M j, Y g:i A', strtotime((string) ($note['created_at'] ?? date('Y-m-d H:i:s'))))) ?>
                                            <?php if (! empty($note['patient_name'])): ?>
                                                <span class="mx-1">•</span>
                                                <i class="bi bi-person me-1"></i><?= esc((string) $note['patient_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <form method="post" action="<?= site_url('/doctor/notes') ?>" onsubmit="return confirm('Delete this note?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="note_id" value="<?= esc((string) ($note['id'] ?? '')) ?>">
                                        <button type="submit" class="doc-action-btn doc-action-cancel">Delete</button>
                                    </form>
                                </div>
                                <div class="mt-2 small" style="color:#2d3748;white-space:pre-wrap;"><?= esc((string) ($note['body'] ?? '')) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script>
const patientSelect = document.getElementById('patientSelect');
const patientNameInput = document.getElementById('patientNameInput');
if (patientSelect && patientNameInput) {
    patientSelect.addEventListener('change', function () {
        const selected = patientSelect.options[patientSelect.selectedIndex];
        patientNameInput.value = selected ? (selected.getAttribute('data-name') || '') : '';
    });
}
</script>

<?= view('doctor/_layout_bottom') ?>
