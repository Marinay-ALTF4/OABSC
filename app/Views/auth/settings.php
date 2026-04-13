<?php
$role = session('user_role') ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="mb-1" style="font-weight:700;">Profile Settings</h4>
            <p class="text-muted small mb-0">Manage your personal information and account security.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php foreach ((array) session('errors') as $err): ?>
                <div><?= esc($err) ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm text-center p-4">
                <?php if (!empty($user['profile_photo'])): ?>
                    <img src="<?= base_url($user['profile_photo']) ?>" class="rounded-circle mx-auto mb-3" style="width:80px;height:80px;object-fit:cover;">
                <?php else: ?>
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold text-white" style="width:80px;height:80px;background:linear-gradient(135deg,#1d4ed8,#6d28d9);font-size:1.4rem;">
                        <?= strtoupper(substr(session('user_name') ?? 'U', 0, 2)) ?>
                    </div>
                <?php endif; ?>
                <div class="fw-bold"><?= esc(session('user_name') ?? '') ?></div>
                <span class="badge bg-primary-subtle text-primary mt-1"><?= esc(strtoupper($role)) ?></span>
                <hr>
                <div class="small text-muted text-start">
                    <div class="mb-1"><i class="bi bi-envelope me-2 text-primary"></i><?= esc($user['email'] ?? '—') ?></div>
                    <div class="mb-1"><i class="bi bi-telephone me-2 text-primary"></i><?= esc($user['phone'] ?? '—') ?></div>
                    <div><i class="bi bi-geo-alt me-2 text-primary"></i><?= esc($user['address'] ?? '—') ?></div>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm p-4">

    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php foreach ((array) session('errors') as $err): ?>
                <div><?= esc($err) ?></div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('/settings/update') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Profile Info -->
        <div class="settings-card mb-4">
            <div class="settings-card-title"><i class="bi bi-person-circle me-2"></i>Profile Information</div>

            <?php if (session('user_role') === 'doctor'): ?>
            <div class="mb-3">
                <label class="settings-label">Profile Photo</label>
                <input type="file" name="profile_photo" class="settings-input" accept="image/*">
            </div>
            <?php endif; ?>

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

            <div class="mb-3">
                <label class="settings-label">City / Municipality</label>
                <input type="text" name="city" class="settings-input"
                    value="<?= esc(old('city', $user['city'] ?? '')) ?>"
                    placeholder="e.g. General Santos City">
            </div>

            <div class="mb-3">
                <label class="settings-label">Home Address</label>
                <input type="text" name="address" class="settings-input"
                    value="<?= esc(old('address', $user['address'] ?? '')) ?>"
                    placeholder="Street, Barangay, City">
            </div>

            <?php if (session('user_role') === 'doctor'): ?>
            <hr>
            <div class="settings-card-title mt-3"><i class="bi bi-briefcase-medical me-2"></i>Professional Information</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="settings-label">Specialization</label>
                    <input type="text" name="specialization" class="settings-input"
                        value="<?= esc(old('specialization', $user['specialization'] ?? '')) ?>"
                        placeholder="e.g. Cardiologist">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Experience</label>
                    <input type="text" name="experience" class="settings-input"
                        value="<?= esc(old('experience', $user['experience'] ?? '')) ?>"
                        placeholder="e.g. 10 years">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Degree</label>
                    <input type="text" name="degree" class="settings-input"
                        value="<?= esc(old('degree', $user['degree'] ?? '')) ?>"
                        placeholder="e.g. MD, University of Santo Tomas">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">About</label>
                    <input type="text" name="bio" class="settings-input"
                        value="<?= esc(old('bio', $user['bio'] ?? '')) ?>"
                        placeholder="Brief description about yourself">
                </div>
            </div>
            <?php endif; ?>
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
            </div><!-- end card -->
        </div><!-- end col-lg-9 -->
    </div><!-- end row -->
</div><!-- end container -->

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
