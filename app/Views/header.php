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
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isPatientsPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/admin/patients') ?>">
                        Patients
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
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    html, body {
        margin: 0;
        padding: 0;
        font-family: 'Inter', sans-serif;
        background: #fce8ec !important;
    }
    .navbar {
        margin-top: 0;
        background: #ffffff !important;
        border-bottom: 1px solid #f5d8e3 !important;
        box-shadow: 0 1px 10px rgba(220,130,160,0.08) !important;
    }
    .navbar-brand span {
        font-weight: 700;
        font-size: 0.95rem;
        color: #3d1a28;
        letter-spacing: -0.1px;
    }
    .nav-link {
        font-size: 0.875rem;
        color: #9e6070 !important;
        font-weight: 500;
        border-radius: 8px;
        transition: background 0.15s, color 0.15s;
    }
    .nav-link:hover, .nav-link.active {
        color: #d44a78 !important;
        background: #fff0f5;
    }
    .nav-link.active.fw-semibold {
        font-weight: 600 !important;
    }
    .role-badge {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.8px;
        background: #fff0f5 !important;
        color: #d44a78 !important;
        border-radius: 6px;
        padding: 5px 10px !important;
        border: 1px solid #f5c0d0;
    }
    .btn-outline-danger {
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 5px 14px;
        border-color: #f5c0d0;
        color: #d44a78;
    }
    .btn-outline-danger:hover {
        background: #fff0f5;
        border-color: #f0a8c0;
        color: #c03a68;
    }
</style>
