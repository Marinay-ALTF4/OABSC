<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="container py-4" style="max-width:600px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Add Role</h4>
            <p class="text-muted small mb-0">Add an Assistant Admin or Assistant Secretary.</p>
        </div>
        <a href="<?= site_url('/admin/patients/list') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger py-2">
            <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                <div><?= esc($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm p-4">
        <form action="<?= site_url('/admin/patients/add-role') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control"
                    value="<?= esc(old('name')) ?>" placeholder="Enter full name" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address <span class="text-muted fw-normal">(for records only)</span></label>
                <input type="email" name="email" class="form-control"
                    value="<?= esc(old('email')) ?>" placeholder="Enter email" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Select Role --</option>
                    <option value="assistant_admin" <?= old('role') === 'assistant_admin' ? 'selected' : '' ?>>Assistant Admin</option>
                    <option value="assistant_secretary" <?= old('role') === 'assistant_secretary' ? 'selected' : '' ?>>Assistant Secretary</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Role Password</label>
                <input type="password" name="role_password" class="form-control"
                    placeholder="Min. 8 characters" required>
                <small class="text-muted">This will be used during role selection screen.</small>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Confirm Role Password</label>
                <input type="password" name="role_password_confirm" class="form-control"
                    placeholder="Confirm password" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Add Role</button>
                <a href="<?= site_url('/admin/patients/list') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
