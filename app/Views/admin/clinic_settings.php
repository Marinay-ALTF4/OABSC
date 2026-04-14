<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="container py-4" style="max-width:500px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Clinic Settings</h4>
            <p class="text-muted small mb-0">Manage clinic access code.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm p-4">
        <h6 class="fw-bold mb-1"><i class="bi bi-key me-2"></i>Clinic Access Code</h6>
        <p class="text-muted small mb-3">This code is required during role selection. Share it only with trusted staff.</p>

        <form action="<?= site_url('/admin/settings') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">New Access Code</label>
                <input type="password" name="clinic_access_code" class="form-control"
                    placeholder="Enter new clinic access code" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Access Code</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
