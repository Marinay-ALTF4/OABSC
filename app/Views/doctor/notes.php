<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body style="background:#f0f4f8;">
<?= view('header') ?>

<?php
$notes = $notes ?? [];
$patients = $patients ?? [];
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Write Notes</h4>
            <p class="text-muted small mb-0">Create private doctor notes and tag them to your patients.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="notes-card p-3 p-md-4">
                <div class="fw-semibold mb-3"><i class="bi bi-journal-plus me-2 text-primary"></i>New Note</div>
                <form method="post" action="<?= site_url('/doctor/notes') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Title</label>
                        <input type="text" name="title" class="form-control form-control-sm" maxlength="120" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small text-muted mb-1">Patient (optional)</label>
                        <select name="patient_id" class="form-select form-select-sm" id="patientSelect">
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
                        <label class="form-label small text-muted mb-1">Note</label>
                        <textarea name="body" class="form-control form-control-sm" rows="6" maxlength="3000" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm px-3">Save Note</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="notes-card p-0">
                <div class="notes-head d-flex justify-content-between align-items-center">
                    <div class="fw-semibold"><i class="bi bi-journal-text me-2 text-primary"></i>Saved Notes</div>
                    <span class="badge bg-primary rounded-pill"><?= count($notes) ?></span>
                </div>

                <?php if (empty($notes)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox d-block mb-2" style="font-size:1.5rem;"></i>
                        No notes yet.
                    </div>
                <?php else: ?>
                    <div class="notes-list">
                        <?php foreach ($notes as $note): ?>
                            <div class="note-item">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold"><?= esc((string) ($note['title'] ?? 'Untitled')) ?></div>
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
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Delete</button>
                                    </form>
                                </div>
                                <div class="mt-2 text-dark small" style="white-space: pre-wrap;"><?= esc((string) ($note['body'] ?? '')) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<style>
    .notes-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .notes-head {
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .notes-list {
        max-height: 560px;
        overflow: auto;
    }

    .note-item {
        padding: 12px 14px;
        border-bottom: 1px solid #eef2f7;
    }

    .note-item:last-child {
        border-bottom: none;
    }
</style>
