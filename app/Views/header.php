<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4 d-flex align-items-center">
        <div class="d-flex align-items-center gap-2">
            <a class="navbar-brand d-flex align-items-center gap-2 mb-0" href="<?= site_url('/dashboard') ?>">
                <span class="badge bg-primary rounded-circle">+</span>
                <span>Clinic Appointment System</span>
            </a>
        </div>

        <?php if ($role === 'admin') : ?>
            <ul class="navbar-nav mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link <?= current_url() === site_url('/dashboard') ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= current_url() === site_url('/admin/patients') ? 'active fw-semibold' : '' ?>" href="<?= site_url('/admin/patients') ?>">
                        Patients
                    </a>
                </li>
            </ul>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <span class="badge bg-secondary-subtle text-secondary role-badge">
                <?= esc(strtoupper($role)) ?>
            </span>
            <a href="<?= site_url('/logout') ?>" class="btn btn-outline-danger btn-sm">
                Logout
            </a>
        </div>
    </div>
</nav>
