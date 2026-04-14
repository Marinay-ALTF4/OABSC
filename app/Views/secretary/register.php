<?= view('secretary/_layout_top', ['pageTitle' => 'Register New Patient', 'active' => 'register']) ?>

<div class="mb-4">
    <h5 class="sec-page-title"><i class="bi bi-person-plus me-2"></i>Register New Patient</h5>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (session('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php foreach ((array) session('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="sec-form-card">
    <form method="post" action="<?= site_url('/secretary/register') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="sec-label">Full Name</label>
            <input type="text" name="name" class="sec-input" value="<?= esc(old('name')) ?>" required>
        </div>
        <div class="mb-3">
            <label class="sec-label">Email Address</label>
            <input type="email" name="email" class="sec-input" value="<?= esc(old('email')) ?>" required>
        </div>
        <div class="mb-3">
            <label class="sec-label">Phone Number</label>
            <input type="text" name="phone" class="sec-input" value="<?= esc(old('phone')) ?>" placeholder="09xxxxxxxxx">
        </div>
        <div class="mb-4">
            <label class="sec-label">Temporary Password</label>
            <input type="password" name="password" class="sec-input" placeholder="Min. 8 characters" required>
        </div>
        <button type="submit" class="sec-save-btn"><i class="bi bi-person-check me-1"></i>Register Patient</button>
    </form>
</div>

<?= view('secretary/_layout_bottom') ?>
