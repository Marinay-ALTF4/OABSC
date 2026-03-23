<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
$isDashboardPage = url_is('dashboard');
$isPatientsPage = url_is('admin/patients*');
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= site_url('/dashboard') ?>">
            <img src="/OABSC/images/logo.png" alt="Clinic Logo" style="width: 32px; height: 32px; object-fit: contain;">
            <span>Clinic Appointment System</span>
        </a>

        <?php if ($role === 'admin') : ?>
            <ul class="navbar-nav flex-row align-items-center gap-1 mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isDashboardPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                
            </ul>
        <?php elseif ($role === 'secretary') : ?>
            <ul class="navbar-nav flex-row align-items-center gap-1 mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isDashboardPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="#" onclick="return false;">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="#" onclick="return false;">Patients</a>
                </li>
            </ul>
        <?php elseif ($role === 'doctor') : ?>
            <ul class="navbar-nav flex-row align-items-center gap-1 mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isDashboardPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="#" onclick="return false;">My Schedule</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="#" onclick="return false;">Patients</a>
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

<style>
    html,
    body {
        margin: 0;
        padding: 0;
    }
    .navbar {
        margin-top: 0;
    }
</style>
