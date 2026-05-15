<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Patients</title>
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
            <a href="<?= site_url('/admin/patients') ?>" class="adm-nav-item active">
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
            <a href="<?= site_url('/admin/reports') ?>" class="adm-nav-item">
                <i class="bi bi-bar-chart"></i> Reports
            </a>
            <a href="<?= site_url('/admin/access-requests') ?>" class="adm-nav-item">
                <i class="bi bi-check-circle"></i> Access Requests
            </a>
            <a href="<?= site_url('/admin/announcements') ?>" class="adm-nav-item">
                <i class="bi bi-megaphone"></i> Announcements
            </a>
            <a href="<?= site_url('/admin/audit-log') ?>" class="adm-nav-item">
                <i class="bi bi-clock-history"></i> System Audit Log
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
                        <h4 class="pl-title mb-1">Patients</h4>
                        <p class="pl-sub mb-0">Manage patient records: view list, search, and review appointment history.</p>
                    </div>
                </div>

                <div class="adm-section-label mb-3">Manage Patient Records</div>
                <div class="row g-3">
        <div class="col-md-3">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-people-fill"></i></div>
                <div class="adm-card-tag">Records</div>
                <div class="adm-card-title">View Patient List</div>
                <div class="adm-card-desc">See all patients registered in the clinic.</div>
                <a href="<?= site_url('/admin/patients/clients') ?>" class="adm-btn adm-btn-filled">Open</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#b8d8e4;color:#1e5a6e;"><i class="bi bi-search"></i></div>
                <div class="adm-card-tag">Search</div>
                <div class="adm-card-title">Search Patient</div>
                <div class="adm-card-desc">Quickly find a patient by name or ID.</div>
                <button class="adm-btn adm-btn-disabled" disabled>Search (soon)</button>
            </div>
        </div>
        <div class="col-md-3">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#a4ccd8;color:#164a5c;"><i class="bi bi-clock-history"></i></div>
                <div class="adm-card-tag">History</div>
                <div class="adm-card-title">Appointment History</div>
                <div class="adm-card-desc">Review a patient's visit and booking history.</div>
                <a href="<?= site_url('/admin/patients/history') ?>" class="adm-btn adm-btn-outline">Open</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#4e8a9e;color:#e0f4fa;"><i class="bi bi-pencil-square"></i></div>
                <div class="adm-card-tag">Edit</div>
                <div class="adm-card-title">Edit Patient Info</div>
                <div class="adm-card-desc">Update contact details and basic information.</div>
                <a href="<?= site_url('/admin/patients/list') ?>" class="adm-btn adm-btn-outline">Open</a>
            </div>
        </div>
        </div>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { background: #edf2f7; font-family: 'Inter', sans-serif; }
    
    /* Dashboard wrapper */
    .dashboard-wrapper { width: 100%; }
    
    /* Admin sidebar layout */
    .adm-page {
        display: flex;
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        margin-top: 0;
        min-height: calc(100vh - 60px);
        background: #edf2f7;
        overflow-x: hidden;
    }
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
    
    /* Patient records page */
    .pl-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
    .pl-sub   { font-size: 0.85rem; color: #64748b; }
    .pl-btn {
        font-size: 0.8rem; font-weight: 600; padding: 7px 16px; border-radius: 10px;
        border: none; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; transition: all 0.15s;
    }
    .pl-btn-ghost { background: white; color: #475569; border: 1px solid #dbe4ef; }
    .pl-btn-ghost:hover { background: #f1f5f9; color: #1e40af; }

    .adm-section-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #5a7288; }

    .adm-card {
        background: rgba(255,255,255,0.96); border-radius: 18px; padding: 24px 22px;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,0.06);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        height: 100%; display: flex; flex-direction: column;
    }
    .adm-card:hover { transform: translateY(-2px); box-shadow: 0 7px 18px rgba(15,23,42,0.12); }
    .adm-card-icon  { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:14px; }
    .adm-card-tag   { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#5a7288; margin-bottom:5px; }
    .adm-card-title { font-size:0.95rem; font-weight:700; color:#0f172a; margin-bottom:7px; }
    .adm-card-desc  { font-size:0.8rem; color:#334155; flex:1; margin-bottom:18px; line-height:1.55; }

    .adm-btn { font-size:0.78rem; font-weight:600; padding:7px 18px; border-radius:10px; border:none; cursor:pointer; align-self:flex-start; transition:all 0.18s ease; text-decoration:none; display:inline-block; }
    .adm-btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 3px 10px rgba(15,23,42,0.18); }
    .adm-btn-filled  { background:linear-gradient(135deg,#3b556e,#2e445a); color:#fff; box-shadow:0 2px 8px rgba(15,23,42,0.18); }
    .adm-btn-filled:hover { opacity:0.9; color:#fff; }
    .adm-btn-outline { background:#edf3f9; color:#334155; border:1.5px solid #c4d3e2 !important; }
    .adm-btn-outline:hover { background:#e2ebf4; }
    .adm-btn-disabled { background:#f1f5f9; color:#8aa0b3; cursor:not-allowed; border:1px solid #d2dde8 !important; }
</style>
</body>
</html>
