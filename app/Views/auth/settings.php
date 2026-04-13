<?php
$role = session('user_role') ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
<div class="container py-5">

<div class="settings-wrapper">

    <div class="settings-header mb-4">
        <a href="<?= site_url('/dashboard') ?>" class="settings-back">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
        <h4 class="settings-title mt-2">Account Settings</h4>
        <p class="settings-sub">Manage your profile and security preferences.</p>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php foreach ((array) session('errors') as $err): ?>
                <div><?= esc($err) ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('/settings/update') ?>" method="post">
        <?= csrf_field() ?>

        <!-- Profile Info -->
        <div class="settings-card mb-4">
            <div class="settings-card-title"><i class="bi bi-person-circle me-2"></i>Profile Information</div>

            <div class="mb-3">
                <label class="settings-label">Full Name</label>
                <input type="text" name="name" class="settings-input"
                    value="<?= esc(old('name', $user['name'] ?? '')) ?>" required>
            </div>

            <div class="mb-3">
                <label class="settings-label">Email Address</label>
                <input type="email" class="settings-input" value="<?= esc($user['email'] ?? '') ?>" disabled>
                <small class="text-muted">Email cannot be changed.</small>
            </div>

            <div class="mb-3">
                <label class="settings-label">Phone Number</label>
                <input type="text" name="phone" class="settings-input"
                    value="<?= esc(old('phone', $user['phone'] ?? '')) ?>"
                    placeholder="e.g. 09xxxxxxxxx">
            </div>
        </div>

        <!-- Change Password -->
        <div class="settings-card mb-4">
            <div class="settings-card-title"><i class="bi bi-shield-lock me-2"></i>Change Password</div>
            <p class="settings-hint">Leave blank if you don't want to change your password.</p>

            <div class="mb-3">
                <label class="settings-label">Current Password</label>
                <input type="password" name="current_password" class="settings-input" placeholder="Enter current password">
            </div>

            <div class="mb-3">
                <label class="settings-label">New Password</label>
                <input type="password" name="new_password" class="settings-input" placeholder="Min. 8 characters">
            </div>
        </div>

        <button type="submit" class="settings-save-btn">
            <i class="bi bi-check-lg me-1"></i> Save Changes
        </button>
    </form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background: #edf2f7; }
    .settings-wrapper { max-width: 620px; margin: 0 auto; }
    .settings-back { font-size: 0.83rem; color: #475569; text-decoration: none; font-weight: 500; }
    .settings-back:hover { color: #1e3a8a; }
    .settings-title { font-size: 1.4rem; font-weight: 700; color: #0f172a; margin: 0; }
    .settings-sub { font-size: 0.84rem; color: #64748b; margin: 0; }
    .settings-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 6px rgba(15,23,42,0.06);
    }
    .settings-card-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1b3a1e;
        margin-bottom: 18px;
    }
    .settings-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .settings-input {
        width: 100%;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        font-size: 0.88rem;
        color: #0f172a;
        background: #f9fafb;
        transition: border 0.15s;
        outline: none;
    }
    .settings-input:focus { border-color: #2e7d32; background: #fff; }
    .settings-input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
    .settings-hint { font-size: 0.78rem; color: #94a3b8; margin-bottom: 16px; }
    .settings-save-btn {
        background: #2e5c32;
        color: #fff;
        border: none;
        padding: 11px 28px;
        border-radius: 10px;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }
    .settings-save-btn:hover { background: #245228; }
</style>
</body>
</html>
