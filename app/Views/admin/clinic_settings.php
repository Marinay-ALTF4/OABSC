<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
?>
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

<div class="dashboard-wrapper">
    <div class="adm-page">
        <!-- Sidebar -->
        <div class="adm-sidebar">
            <div class="adm-sidebar-user">
                <div class="adm-sidebar-avatar"><i class="bi bi-person-circle"></i></div>
                <div>
                    <div class="adm-sidebar-name"><?= esc($name) ?></div>
                    <div class="adm-sidebar-role"><?= $role === 'assistant_admin' ? 'Assistant Admin' : 'Admin' ?></div>
                </div>
            </div>
            <hr class="adm-sidebar-divider">
            <a href="<?= site_url('/dashboard') ?>" class="adm-nav-item">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <?php if ($role === 'admin'): ?>
            <a href="<?= site_url('/admin/patients/list') ?>" class="adm-nav-item">
                <i class="bi bi-people-fill"></i> Manage Users
            </a>
            <a href="<?= site_url('/admin/patients') ?>" class="adm-nav-item">
                <i class="bi bi-folder2-open"></i> Patient Records
            </a>
            <a href="<?= site_url('/admin/permissions') ?>" class="adm-nav-item">
                <i class="bi bi-shield-lock"></i> Manage Permissions
            </a>
            <a href="<?= site_url('/admin/appointments') ?>" class="adm-nav-item">
                <i class="bi bi-calendar-event"></i> Appointments
            </a>
            <a href="<?= site_url('/admin/doctor-schedules') ?>" class="adm-nav-item">
                <i class="bi bi-calendar2-check"></i> Doctor Schedules
            </a>
            <a href="<?= site_url('/admin/settings') ?>" class="adm-nav-item active">
                <i class="bi bi-gear"></i> System Settings
            </a>
            <a href="<?= site_url('/admin/reports') ?>" class="adm-nav-item">
                <i class="bi bi-bar-chart"></i> Reports
            </a>
            <a href="<?= site_url('/admin/access-requests') ?>" class="adm-nav-item">
                <i class="bi bi-check-circle"></i> Access Requests
            </a>
            <a href="<?= site_url('/admin/announcements') ?>" class="adm-nav-item">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
            <?php else: ?>
            <a href="<?= site_url('/dashboard') ?>" class="adm-nav-item">
                <i class="bi bi-people-fill"></i> Manage Users
            </a>
            <a href="<?= site_url('/dashboard') ?>" class="adm-nav-item">
                <i class="bi bi-folder2-open"></i> Patient Records
            </a>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="adm-main-content">
            <div class="adm-wrapper">

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="pl-title mb-1">Clinic Settings</h4>
        <p class="pl-sub mb-0">Manage clinic access code.</p>
    </div>
</div>

    <div class="adm-section-label mb-3">Clinic Settings</div>

    <div class="row">
        <div class="col-md-6">
            <div class="adm-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="adm-card-icon" style="background:#e6f3ef;color:#166a51;"><i class="bi bi-key-fill"></i></div>
                    <div style="flex:1">
                        <div class="adm-card-tag">Security</div>
                        <div class="adm-card-title">Clinic Access Code</div>
                        <div class="adm-card-desc">This code is required during role selection. Share it only with trusted staff.</div>

                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
                        <?php endif; ?>

                        <form action="<?= site_url('/admin/settings') ?>" method="post" class="mt-2">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">New Access Code</label>
                                <input type="password" name="clinic_access_code" class="form-control"
                                    placeholder="Enter new clinic access code" required>
                            </div>
                            <button type="submit" class="adm-btn adm-btn-filled">Update Access Code</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<style>
    .dashboard-wrapper {
        display: flex;
        margin-top: 0;
        min-height: calc(100vh - 60px);
        background: #edf2f7;
        overflow-x: hidden;
    }
    .adm-page { display: flex; width: 100%; }
    .adm-sidebar {
        width: 260px;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 4px 0 24px rgba(42,106,126,0.08);
        padding: 28px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .adm-main-content { flex: 1; padding: 32px 28px; min-width: 0; }
    .adm-wrapper { width: 100%; }
    .adm-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
    .adm-sidebar-avatar {
        width: 44px; height: 44px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #e0f0ff; color: #2a6a7e; font-size: 1.25rem;
        border: 2px solid rgba(42,106,126,0.08);
    }
    .adm-sidebar-name { font-size: 0.9rem; font-weight: 700; color: #0f172a; margin: 0; }
    .adm-sidebar-role { font-size: 0.72rem; color: #2a6a7e; text-transform: uppercase; letter-spacing: 0.8px; }
    .adm-sidebar-divider { border-color: #cce4ed; margin: 10px 0; }
    .adm-nav-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; border-radius: 12px;
        font-size: 0.92rem; font-weight: 500;
        color: #2a6a7e; text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }
    .adm-nav-item i { font-size: 1.15rem; }
    .adm-nav-item:hover { background: rgba(204,228,237,0.6); color: #164a5c; }
    .adm-nav-item.active {
        background: #2a6a7e; color: #ffffff;
        font-weight: 600; box-shadow: 0 4px 14px rgba(42,106,126,0.25);
    }
    
    .pl-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
    .pl-sub   { font-size: 0.85rem; color: #64748b; }
    .adm-section-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #5a7288; margin-bottom: 16px; }
    .adm-card { background: rgba(255,255,255,0.96); border-radius: 18px; padding: 22px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,0.06); transition: transform 0.18s ease, box-shadow 0.18s ease; }
    .adm-card-icon  { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-right:14px; }
    .adm-card-tag   { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#5a7288; margin-bottom:6px; }
    .adm-card-title { font-size:0.95rem; font-weight:700; color:#0f172a; margin-bottom:7px; }
    .adm-card-desc  { font-size:0.8rem; color:#334155; margin-bottom:12px; line-height:1.55; }
    .adm-btn { font-size:0.78rem; font-weight:600; padding:7px 18px; border-radius:10px; border:none; cursor:pointer; align-self:flex-start; transition:all 0.18s ease; text-decoration:none; display:inline-block; }
    .adm-btn-filled  { background:linear-gradient(135deg,#2b6b4a,#1a5b3b); color:#fff; box-shadow:0 2px 8px rgba(15,23,42,0.18); }
    .adm-btn-filled:hover { opacity:0.95; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
