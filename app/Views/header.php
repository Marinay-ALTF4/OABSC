<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
$roleLabel = ucfirst((string) $role);
if ($role === 'assistant_admin') {
    $roleLabel = 'Assistant Admin';
}
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
        <?php elseif ($role === 'assistant_admin') : ?>
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
        <?php elseif ($role === 'secretary') : ?>
            <ul class="navbar-nav flex-row align-items-center gap-1 mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isDashboardPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2 <?= url_is('appointments*') ? 'active fw-semibold' : '' ?>" href="<?= site_url('/appointments/my') ?>">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2 <?= url_is('admin/patients*') ? 'active fw-semibold' : '' ?>" href="<?= site_url('/admin/patients') ?>">Patients</a>
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
                    
                </li>
                <li class="nav-item">
                </li>
            </ul>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <div class="dropdown">
                <button class="btn btn-account btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= esc($roleLabel) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end account-menu">
                    <li><a class="dropdown-item" href="#" onclick="return false;">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('/logout') ?>">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    html, body {
        margin: 0;
        padding: 0;
        font-family: 'Inter', sans-serif;
        background: #edf2f7 !important;
    }
    .navbar {
        margin-top: 0;
        background: #ffffff !important;
        border-bottom: 1px solid #dbe4ef !important;
        box-shadow: 0 1px 10px rgba(15,23,42,0.08) !important;
    }
    .navbar-brand span {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
        letter-spacing: -0.1px;
    }
    .nav-link {
        font-size: 0.875rem;
        color: #475569 !important;
        font-weight: 500;
        border-radius: 8px;
        transition: background 0.15s, color 0.15s;
    }
    .nav-link:hover, .nav-link.active {
        color: #1e3a8a !important;
        background: #eaf0ff;
    }
    .nav-link.active.fw-semibold {
        font-weight: 600 !important;
    }
    .btn-account {
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 5px 14px;
        border-color: #93b0f2;
        color: #1e3a8a;
        background: #ffffff;
    }
    .btn-account:hover,
    .btn-account:focus,
    .btn-account:active,
    .btn-account.show {
        background: #eaf0ff;
        border-color: #6f94ea;
        color: #1e40af;
    }
    .account-menu {
        border-radius: 10px;
        border: 1px solid #dbe4ef;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        padding: 0.35rem;
    }
    .account-menu .dropdown-item {
        font-size: 0.84rem;
        border-radius: 6px;
        font-weight: 500;
    }
    .account-menu .dropdown-item:hover {
        background: #eef3ff;
        color: #1e3a8a;
    }
</style>
