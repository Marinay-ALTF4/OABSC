<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
<div class="container py-5">

    <?php if ($role === 'admin' || $role === 'assistant_admin') : ?>
    <!-- ==================== ADMIN ==================== -->
    <div class="adm-wrapper">

    <!-- Welcome Banner -->
    <div class="welcome-banner banner-client mb-4">
        <svg class="client-banner-illustration" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 420 220" fill="none">
            <rect x="60" y="80" width="160" height="130" rx="6" fill="#c8daf5"/>
            <rect x="60" y="80" width="160" height="130" rx="6" fill="url(#bldg-grad-adm)"/>
            <rect x="50" y="74" width="180" height="12" rx="4" fill="#a0bce8"/>
            <rect x="80"  y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="120" y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="160" y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="80"  y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="120" y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="160" y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="118" y="172" width="44" height="38" rx="4" fill="#7baee8"/>
            <rect x="128" y="56" width="24" height="8"  rx="2" fill="#e05c5c"/>
            <rect x="136" y="48" width="8"  height="24" rx="2" fill="#e05c5c"/>
            <line x1="140" y1="48" x2="140" y2="30" stroke="#94a3b8" stroke-width="2"/>
            <polygon points="140,30 158,37 140,44" fill="#4a90e2"/>
            <rect x="268" y="118" width="52" height="72" rx="10" fill="white" stroke="#c8daf5" stroke-width="2"/>
            <polygon points="294,118 280,138 294,132" fill="#e8f2ff"/>
            <polygon points="294,118 308,138 294,132" fill="#e8f2ff"/>
            <path d="M282 138 Q274 155 280 165 Q286 175 294 170 Q302 175 308 165 Q314 155 306 138" stroke="#4a90e2" stroke-width="2.5" fill="none" stroke-linecap="round"/>
            <circle cx="294" cy="170" r="5" fill="#4a90e2"/>
            <rect x="289" y="118" width="10" height="20" rx="2" fill="#4a90e2"/>
            <circle cx="294" cy="100" r="22" fill="#fde8c8"/>
            <path d="M272 96 Q272 74 294 72 Q316 74 316 96" fill="#5c3d2e"/>
            <circle cx="286" cy="98" r="3" fill="#2d3748"/>
            <circle cx="302" cy="98" r="3" fill="#2d3748"/>
            <path d="M286 108 Q294 115 302 108" stroke="#c97b4b" stroke-width="2" fill="none" stroke-linecap="round"/>
            <ellipse cx="272" cy="100" rx="4" ry="6" fill="#fde8c8"/>
            <ellipse cx="316" cy="100" rx="4" ry="6" fill="#fde8c8"/>
            <rect x="308" y="138" width="28" height="36" rx="4" fill="#f8fafc" stroke="#c8daf5" stroke-width="1.5"/>
            <rect x="316" y="133" width="12" height="8" rx="2" fill="#a0bce8"/>
            <line x1="313" y1="150" x2="331" y2="150" stroke="#c8daf5" stroke-width="1.5"/>
            <line x1="313" y1="158" x2="331" y2="158" stroke="#c8daf5" stroke-width="1.5"/>
            <line x1="313" y1="166" x2="325" y2="166" stroke="#c8daf5" stroke-width="1.5"/>
            <rect x="278" y="188" width="14" height="22" rx="4" fill="#4a90e2"/>
            <rect x="296" y="188" width="14" height="22" rx="4" fill="#4a90e2"/>
            <ellipse cx="285" cy="210" rx="10" ry="5" fill="#2d3748"/>
            <ellipse cx="303" cy="210" rx="10" ry="5" fill="#2d3748"/>
            <line x1="40" y1="210" x2="380" y2="210" stroke="#c8daf5" stroke-width="2"/>
            <rect x="30" y="175" width="6" height="35" rx="2" fill="#94a3b8"/>
            <ellipse cx="33" cy="165" rx="18" ry="20" fill="#86c98e"/>
            <ellipse cx="33" cy="158" rx="13" ry="15" fill="#6ab872"/>
            <rect x="370" y="180" width="6" height="30" rx="2" fill="#94a3b8"/>
            <ellipse cx="373" cy="170" rx="16" ry="18" fill="#86c98e"/>
            <ellipse cx="373" cy="163" rx="11" ry="13" fill="#6ab872"/>
            <ellipse cx="350" cy="50" rx="28" ry="14" fill="white" opacity="0.7"/>
            <ellipse cx="368" cy="46" rx="18" ry="12" fill="white" opacity="0.7"/>
            <ellipse cx="332" cy="48" rx="16" ry="10" fill="white" opacity="0.7"/>
            <ellipse cx="90"  cy="40" rx="22" ry="11" fill="white" opacity="0.6"/>
            <ellipse cx="106" cy="37" rx="14" ry="9"  fill="white" opacity="0.6"/>
            <defs>
                <linearGradient id="bldg-grad-adm" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#d6e8f8"/>
                    <stop offset="100%" stop-color="#b8d0ee"/>
                </linearGradient>
            </defs>
        </svg>
        <div style="position:relative;z-index:2;">
            <div class="welcome-label">Admin Panel</div>
            <h4 class="welcome-name">Welcome back, <?= esc($name) ?></h4>
            <p class="welcome-sub">Quick overview of your clinic's activity today.</p>
        </div>
        <div class="welcome-date" style="position:relative;z-index:2;">
            <i class="bi bi-calendar3 me-1"></i><?= esc(date('l, F j, Y')) ?>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-calendar-check"></i></div>
                <div class="adm-stat-value">0</div>
                <div class="adm-stat-label">Total Appointments</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background:#b8d8e4;color:#1e5a6e;"><i class="bi bi-calendar-day"></i></div>
                <div class="adm-stat-value">0</div>
                <div class="adm-stat-label">Today's Appointments</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background:#a4ccd8;color:#164a5c;"><i class="bi bi-people"></i></div>
                <div class="adm-stat-value">0</div>
                <div class="adm-stat-label">Total Patients</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background:#4e8a9e;color:#e0f4fa;"><i class="bi bi-person-badge"></i></div>
                <div class="adm-stat-value">0</div>
                <div class="adm-stat-label">Doctors Available</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-hourglass-split"></i></div>
                <div class="adm-stat-value">0</div>
                <div class="adm-stat-label">Pending Requests</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="adm-stat-card">
                <div class="adm-stat-icon" style="background:#b8d8e4;color:#1e5a6e;"><i class="bi bi-person-workspace"></i></div>
                <div class="adm-stat-value">0</div>
                <div class="adm-stat-label">Secretaries</div>
            </div>
        </div>
    </div>

    <!-- Quick Access -->
    <div class="adm-section-label mb-3">Quick Access</div>
    <div class="row g-3">
        <?php if ($role === 'admin'): ?>
        <div class="col-md-4">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-people-fill"></i></div>
                <div class="adm-card-tag">Users</div>
                <div class="adm-card-title">Manage Users</div>
                <div class="adm-card-desc">Add, edit, or remove system users and assign their roles.</div>
                <a href="<?= site_url('/admin/patients/list') ?>" class="adm-btn adm-btn-filled">Open</a>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-md-4">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#b8d8e4;color:#1e5a6e;"><i class="bi bi-folder2-open"></i></div>
                <div class="adm-card-tag">Patients</div>
                <div class="adm-card-title">Patient Records</div>
                <div class="adm-card-desc">Browse all registered patient profiles and appointment history.</div>
                <a href="<?= site_url('/admin/patients') ?>" class="adm-btn adm-btn-outline">Open</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#a4ccd8;color:#164a5c;"><i class="bi bi-bar-chart-line"></i></div>
                <div class="adm-card-tag">Reports</div>
                <div class="adm-card-title">Clinic Reports</div>
                <div class="adm-card-desc">View statistics and generate reports on clinic activity.</div>
                <a href="<?= site_url('/admin/patients/list') ?>" class="adm-btn adm-btn-outline">View Records</a>
            </div>
        </div>
        <?php if ($role === 'admin'): ?>
        <div class="col-md-4">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#d4edda;color:#155724;"><i class="bi bi-key-fill"></i></div>
                <div class="adm-card-tag">Security</div>
                <div class="adm-card-title">Clinic Settings</div>
                <div class="adm-card-desc">Manage clinic access code for role selection screen.</div>
                <a href="<?= site_url('/admin/settings') ?>" class="adm-btn adm-btn-outline">Open</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($role === 'assistant_admin'): ?>
    <!-- Compact List Sections for Assistant Admin -->
    <div class="row g-3 mt-2">
        <div class="col-lg-4">
            <div class="aa-list-panel">
                <div class="aa-list-header"><i class="bi bi-people-fill me-2"></i>Patient Management</div>
                <a href="<?= site_url('/admin/patients/list') ?>" class="aa-list-item">
                    <span class="aa-list-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-list-ul"></i></span>
                    <span class="aa-list-label">View Patient List</span>
                    <i class="bi bi-chevron-right aa-list-arrow"></i>
                </a>
                <a href="<?= site_url('/admin/patients/add') ?>" class="aa-list-item">
                    <span class="aa-list-icon" style="background:#b8d8e4;color:#1e5a6e;"><i class="bi bi-person-plus-fill"></i></span>
                    <span class="aa-list-label">Add New Patient</span>
                    <i class="bi bi-chevron-right aa-list-arrow"></i>
                </a>
                <a href="<?= site_url('/admin/patients/list') ?>" class="aa-list-item">
                    <span class="aa-list-icon" style="background:#a4ccd8;color:#164a5c;"><i class="bi bi-pencil-square"></i></span>
                    <span class="aa-list-label">Edit Patient Details</span>
                    <i class="bi bi-chevron-right aa-list-arrow"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="aa-list-panel">
                <div class="aa-list-header"><i class="bi bi-journal-medical me-2"></i>Medical Records</div>
                <a href="<?= site_url('/admin/patients/history') ?>" class="aa-list-item">
                    <span class="aa-list-icon" style="background:#e0f0ff;color:#1e5a9e;"><i class="bi bi-journal-medical"></i></span>
                    <span class="aa-list-label">Patient History</span>
                    <i class="bi bi-chevron-right aa-list-arrow"></i>
                </a>
                <a href="<?= site_url('/admin/patients/history') ?>" class="aa-list-item">
                    <span class="aa-list-icon" style="background:#d8eef8;color:#164a6e;"><i class="bi bi-calendar2-check"></i></span>
                    <span class="aa-list-label">Previous Appointments</span>
                    <i class="bi bi-chevron-right aa-list-arrow"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="aa-list-panel">
                <div class="aa-list-header"><i class="bi bi-person-badge me-2"></i>Doctor / Staff Info</div>
                <span class="aa-list-item aa-list-disabled">
                    <span class="aa-list-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-person-badge"></i></span>
                    <span class="aa-list-label">List of Doctors</span>
                    <span class="aa-list-soon">Soon</span>
                </span>
                <span class="aa-list-item aa-list-disabled">
                    <span class="aa-list-icon" style="background:#b8d8e4;color:#1e5a6e;"><i class="bi bi-heart-pulse"></i></span>
                    <span class="aa-list-label">Specialization</span>
                    <span class="aa-list-soon">Soon</span>
                </span>
                <span class="aa-list-item aa-list-disabled">
                    <span class="aa-list-icon" style="background:#a4ccd8;color:#164a5c;"><i class="bi bi-clock-history"></i></span>
                    <span class="aa-list-label">Availability Schedule</span>
                    <span class="aa-list-soon">Soon</span>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Notifications Section -->
    <div class="adm-section-label mt-4 mb-3"><i class="bi bi-bell me-1"></i> Notifications &amp; Alerts</div>
    <div class="row g-3">
        <div class="col-12">
            <div class="notif-panel">
                <div class="notif-panel-header">
                    <span id="notif-count-label-adm">Loading...</span>
                    <button class="notif-mark-all" onclick="markAllReadAdm()">Mark all as read</button>
                </div>
                <div class="notif-panel-body">
                    <div id="notif-list-adm"></div>
                </div>
            </div>
        </div>
    </div>

    </div><!-- end adm-wrapper -->

    <?php elseif ($role === 'secretary') : ?>
    <!-- ==================== SECRETARY ==================== -->

    <div class="sec2-page">

        <!-- Left Sidebar -->
        <div class="sec2-sidebar">
            <div class="sec2-sidebar-label"></div>
            <a href="<?= site_url('/dashboard') ?>" class="sec2-sidebar-item active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?= site_url('/appointments/my') ?>" class="sec2-sidebar-item">
                <i class="bi bi-calendar2-plus"></i> Manage Appointments
            </a>
            <a href="<?= site_url('/secretary/queue') ?>" class="sec2-sidebar-item" onclick="showComingSoon('Patient Queue'); return false;">
                <i class="bi bi-list-ol"></i> Patient Queue
            </a>
            <a href="<?= site_url('/admin/patients') ?>" class="sec2-sidebar-item">
                <i class="bi bi-folder2-open"></i> Patient Records
            </a>
            <a href="<?= site_url('/secretary/register') ?>" class="sec2-sidebar-item" onclick="showComingSoon('Register New Patient'); return false;">
                <i class="bi bi-person-plus"></i> Register New Patient
            </a>
            <a href="<?= site_url('/secretary/schedules') ?>" class="sec2-sidebar-item" onclick="showComingSoon('Doctor Schedules'); return false;">
                <i class="bi bi-clock-history"></i> Doctor Schedules
            </a>
            <a href="<?= site_url('/secretary/approvals') ?>" class="sec2-sidebar-item" onclick="showComingSoon('Pending Approvals'); return false;">
                <i class="bi bi-bell"></i> Pending Approvals
            </a>
        </div>

        <!-- Right Content -->
        <div class="sec2-content">

            <!-- Welcome Banner -->
            <div class="welcome-banner banner-client mb-4">
                <svg class="client-banner-illustration" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 420 220" fill="none">
                    <rect x="60" y="80" width="160" height="130" rx="6" fill="#c8daf5"/>
                    <rect x="60" y="80" width="160" height="130" rx="6" fill="url(#bldg-grad-sec)"/>
                    <rect x="50" y="74" width="180" height="12" rx="4" fill="#a0bce8"/>
                    <rect x="80"  y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
                    <rect x="120" y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
                    <rect x="160" y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
                    <rect x="80"  y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
                    <rect x="120" y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
                    <rect x="160" y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
                    <rect x="118" y="172" width="44" height="38" rx="4" fill="#7baee8"/>
                    <rect x="128" y="56" width="24" height="8"  rx="2" fill="#e05c5c"/>
                    <rect x="136" y="48" width="8"  height="24" rx="2" fill="#e05c5c"/>
                    <line x1="140" y1="48" x2="140" y2="30" stroke="#94a3b8" stroke-width="2"/>
                    <polygon points="140,30 158,37 140,44" fill="#4a90e2"/>
                    <rect x="268" y="118" width="52" height="72" rx="10" fill="white" stroke="#c8daf5" stroke-width="2"/>
                    <polygon points="294,118 280,138 294,132" fill="#e8f2ff"/>
                    <polygon points="294,118 308,138 294,132" fill="#e8f2ff"/>
                    <path d="M282 138 Q274 155 280 165 Q286 175 294 170 Q302 175 308 165 Q314 155 306 138" stroke="#4a90e2" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    <circle cx="294" cy="170" r="5" fill="#4a90e2"/>
                    <rect x="289" y="118" width="10" height="20" rx="2" fill="#4a90e2"/>
                    <circle cx="294" cy="100" r="22" fill="#fde8c8"/>
                    <path d="M272 96 Q272 74 294 72 Q316 74 316 96" fill="#5c3d2e"/>
                    <circle cx="286" cy="98" r="3" fill="#2d3748"/>
                    <circle cx="302" cy="98" r="3" fill="#2d3748"/>
                    <path d="M286 108 Q294 115 302 108" stroke="#c97b4b" stroke-width="2" fill="none" stroke-linecap="round"/>
                    <ellipse cx="272" cy="100" rx="4" ry="6" fill="#fde8c8"/>
                    <ellipse cx="316" cy="100" rx="4" ry="6" fill="#fde8c8"/>
                    <rect x="308" y="138" width="28" height="36" rx="4" fill="#f8fafc" stroke="#c8daf5" stroke-width="1.5"/>
                    <rect x="316" y="133" width="12" height="8" rx="2" fill="#a0bce8"/>
                    <line x1="313" y1="150" x2="331" y2="150" stroke="#c8daf5" stroke-width="1.5"/>
                    <line x1="313" y1="158" x2="331" y2="158" stroke="#c8daf5" stroke-width="1.5"/>
                    <line x1="313" y1="166" x2="325" y2="166" stroke="#c8daf5" stroke-width="1.5"/>
                    <rect x="278" y="188" width="14" height="22" rx="4" fill="#4a90e2"/>
                    <rect x="296" y="188" width="14" height="22" rx="4" fill="#4a90e2"/>
                    <ellipse cx="285" cy="210" rx="10" ry="5" fill="#2d3748"/>
                    <ellipse cx="303" cy="210" rx="10" ry="5" fill="#2d3748"/>
                    <line x1="40" y1="210" x2="380" y2="210" stroke="#c8daf5" stroke-width="2"/>
                    <rect x="30" y="175" width="6" height="35" rx="2" fill="#94a3b8"/>
                    <ellipse cx="33" cy="165" rx="18" ry="20" fill="#86c98e"/>
                    <ellipse cx="33" cy="158" rx="13" ry="15" fill="#6ab872"/>
                    <rect x="370" y="180" width="6" height="30" rx="2" fill="#94a3b8"/>
                    <ellipse cx="373" cy="170" rx="16" ry="18" fill="#86c98e"/>
                    <ellipse cx="373" cy="163" rx="11" ry="13" fill="#6ab872"/>
                    <ellipse cx="350" cy="50" rx="28" ry="14" fill="white" opacity="0.7"/>
                    <ellipse cx="368" cy="46" rx="18" ry="12" fill="white" opacity="0.7"/>
                    <ellipse cx="332" cy="48" rx="16" ry="10" fill="white" opacity="0.7"/>
                    <ellipse cx="90"  cy="40" rx="22" ry="11" fill="white" opacity="0.6"/>
                    <ellipse cx="106" cy="37" rx="14" ry="9"  fill="white" opacity="0.6"/>
                    <defs>
                        <linearGradient id="bldg-grad-sec" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#d6e8f8"/>
                            <stop offset="100%" stop-color="#b8d0ee"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div style="position:relative;z-index:2;">
                    <div class="welcome-label">Secretary Panel</div>
                    <h4 class="welcome-name">Welcome back, <?= esc($name) ?></h4>
                    <p class="welcome-sub">Here is your front-desk overview for today.</p>
                </div>
                <div class="welcome-date" style="position:relative;z-index:2;">
                    <i class="bi bi-calendar3 me-1"></i><?= esc(date('l, F j, Y')) ?>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="sec2-stat">
                        <div class="sec2-stat-icon" style="background:#e8f5e9;color:#2e7d32;"><i class="bi bi-calendar-day"></i></div>
                        <div class="sec2-stat-val"><?= $total_today ?? 0 ?></div>
                        <div class="sec2-stat-lbl">Today's Appointments</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sec2-stat">
                        <div class="sec2-stat-icon" style="background:#fff8e1;color:#f59e0b;"><i class="bi bi-hourglass-split"></i></div>
                        <div class="sec2-stat-val"><?= $total_pending ?? 0 ?></div>
                        <div class="sec2-stat-lbl">Pending Requests</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sec2-stat">
                        <div class="sec2-stat-icon" style="background:#e8f5e9;color:#388e3c;"><i class="bi bi-people"></i></div>
                        <div class="sec2-stat-val"><?= $total_patients ?? 0 ?></div>
                        <div class="sec2-stat-lbl">Total Patients</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sec2-stat">
                        <div class="sec2-stat-icon" style="background:#2e5c32;color:#c8f0ca;"><i class="bi bi-person-badge"></i></div>
                        <div class="sec2-stat-val">0</div>
                        <div class="sec2-stat-lbl">Doctors On Duty</div>
                    </div>
                </div>
            </div>

            <!-- Notifications Section -->
            <div class="section-label mt-4 mb-3"><i class="bi bi-bell me-1"></i> Notifications &amp; Alerts</div>
            <div class="row g-3">
                <div class="col-12">
                    <div class="notif-panel">
                        <div class="notif-panel-header">
                            <span id="notif-count-label-sec">Loading...</span>
                            <button class="notif-mark-all" onclick="markAllReadSec()">Mark all as read</button>
                        </div>
                        <div class="notif-panel-body">
                            <div id="notif-list-sec"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end sec2-content -->

    </div><!-- end sec2-page -->

    <?php elseif ($role === 'doctor') : ?>
    <!-- ==================== DOCTOR ==================== -->

    <div class="welcome-banner banner-client mb-4">
        <svg class="client-banner-illustration" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 420 220" fill="none">
            <rect x="60" y="80" width="160" height="130" rx="6" fill="#c8daf5"/>
            <rect x="60" y="80" width="160" height="130" rx="6" fill="url(#bldg-grad-doc)"/>
            <rect x="50" y="74" width="180" height="12" rx="4" fill="#a0bce8"/>
            <rect x="80"  y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="120" y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="160" y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="80"  y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="120" y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="160" y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="118" y="172" width="44" height="38" rx="4" fill="#7baee8"/>
            <rect x="128" y="56" width="24" height="8"  rx="2" fill="#e05c5c"/>
            <rect x="136" y="48" width="8"  height="24" rx="2" fill="#e05c5c"/>
            <line x1="140" y1="48" x2="140" y2="30" stroke="#94a3b8" stroke-width="2"/>
            <polygon points="140,30 158,37 140,44" fill="#4a90e2"/>
            <rect x="268" y="118" width="52" height="72" rx="10" fill="white" stroke="#c8daf5" stroke-width="2"/>
            <polygon points="294,118 280,138 294,132" fill="#e8f2ff"/>
            <polygon points="294,118 308,138 294,132" fill="#e8f2ff"/>
            <path d="M282 138 Q274 155 280 165 Q286 175 294 170 Q302 175 308 165 Q314 155 306 138" stroke="#4a90e2" stroke-width="2.5" fill="none" stroke-linecap="round"/>
            <circle cx="294" cy="170" r="5" fill="#4a90e2"/>
            <rect x="289" y="118" width="10" height="20" rx="2" fill="#4a90e2"/>
            <circle cx="294" cy="100" r="22" fill="#fde8c8"/>
            <path d="M272 96 Q272 74 294 72 Q316 74 316 96" fill="#5c3d2e"/>
            <circle cx="286" cy="98" r="3" fill="#2d3748"/>
            <circle cx="302" cy="98" r="3" fill="#2d3748"/>
            <path d="M286 108 Q294 115 302 108" stroke="#c97b4b" stroke-width="2" fill="none" stroke-linecap="round"/>
            <ellipse cx="272" cy="100" rx="4" ry="6" fill="#fde8c8"/>
            <ellipse cx="316" cy="100" rx="4" ry="6" fill="#fde8c8"/>
            <rect x="308" y="138" width="28" height="36" rx="4" fill="#f8fafc" stroke="#c8daf5" stroke-width="1.5"/>
            <rect x="316" y="133" width="12" height="8" rx="2" fill="#a0bce8"/>
            <line x1="313" y1="150" x2="331" y2="150" stroke="#c8daf5" stroke-width="1.5"/>
            <line x1="313" y1="158" x2="331" y2="158" stroke="#c8daf5" stroke-width="1.5"/>
            <line x1="313" y1="166" x2="325" y2="166" stroke="#c8daf5" stroke-width="1.5"/>
            <rect x="278" y="188" width="14" height="22" rx="4" fill="#4a90e2"/>
            <rect x="296" y="188" width="14" height="22" rx="4" fill="#4a90e2"/>
            <ellipse cx="285" cy="210" rx="10" ry="5" fill="#2d3748"/>
            <ellipse cx="303" cy="210" rx="10" ry="5" fill="#2d3748"/>
            <line x1="40" y1="210" x2="380" y2="210" stroke="#c8daf5" stroke-width="2"/>
            <rect x="30" y="175" width="6" height="35" rx="2" fill="#94a3b8"/>
            <ellipse cx="33" cy="165" rx="18" ry="20" fill="#86c98e"/>
            <ellipse cx="33" cy="158" rx="13" ry="15" fill="#6ab872"/>
            <rect x="370" y="180" width="6" height="30" rx="2" fill="#94a3b8"/>
            <ellipse cx="373" cy="170" rx="16" ry="18" fill="#86c98e"/>
            <ellipse cx="373" cy="163" rx="11" ry="13" fill="#6ab872"/>
            <ellipse cx="350" cy="50" rx="28" ry="14" fill="white" opacity="0.7"/>
            <ellipse cx="368" cy="46" rx="18" ry="12" fill="white" opacity="0.7"/>
            <ellipse cx="332" cy="48" rx="16" ry="10" fill="white" opacity="0.7"/>
            <ellipse cx="90"  cy="40" rx="22" ry="11" fill="white" opacity="0.6"/>
            <ellipse cx="106" cy="37" rx="14" ry="9"  fill="white" opacity="0.6"/>
            <defs>
                <linearGradient id="bldg-grad-doc" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#d6e8f8"/>
                    <stop offset="100%" stop-color="#b8d0ee"/>
                </linearGradient>
            </defs>
        </svg>
        <div style="position:relative;z-index:2;">
            <div class="welcome-label">Doctor Panel</div>
            <h4 class="welcome-name">Welcome, Dr. <?= esc($name) ?></h4>
            <p class="welcome-sub">Here is your clinical overview for today.</p>
        </div>
        <div class="welcome-date" style="position:relative;z-index:2;">
            <i class="bi bi-calendar3 me-1"></i><?= esc(date('l, F j, Y')) ?>
        </div>
    </div>


    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-green-soft"><i class="bi bi-person-check"></i></div>
                <div class="stat-value">0</div>
                <div class="stat-label">Today's Patients</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-blue-soft"><i class="bi bi-calendar-event"></i></div>
                <div class="stat-value">0</div>
                <div class="stat-label">Upcoming</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-teal-soft"><i class="bi bi-check2-circle"></i></div>
                <div class="stat-value">0</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-purple-soft"><i class="bi bi-clipboard2-pulse"></i></div>
                <div class="stat-value">0</div>
                <div class="stat-label">Total Consultations</div>
            </div>
        </div>
    </div>

    <div class="section-label mb-3">Quick Access</div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-green-soft"><i class="bi bi-calendar2-week"></i></div>
                <div class="action-tag">Schedule</div>
                <div class="action-title">My Appointments</div>
                <div class="action-desc">View your full appointment schedule and manage your calendar.</div>
                <button class="action-btn btn-filled" disabled>View Schedule (soon)</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-teal-soft"><i class="bi bi-list-ol"></i></div>
                <div class="action-tag">Queue</div>
                <div class="action-title">Today's Queue</div>
                <div class="action-desc">See the list of patients waiting for your consultation today.</div>
                <button class="action-btn btn-outline" disabled>View Queue (soon)</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-purple-soft"><i class="bi bi-folder2-open"></i></div>
                <div class="action-tag">Records</div>
                <div class="action-title">Patient Records</div>
                <div class="action-desc">Access and review patient medical history and past visits.</div>
                <button class="action-btn btn-outline" disabled>View Records (soon)</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-blue-soft"><i class="bi bi-journal-text"></i></div>
                <div class="action-tag">Consultation</div>
                <div class="action-title">Write Notes</div>
                <div class="action-desc">Add consultation notes, diagnoses, and recommendations for a patient.</div>
                <button class="action-btn btn-outline" disabled>Add Notes (soon)</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-slate-soft"><i class="bi bi-capsule"></i></div>
                <div class="action-tag">Prescription</div>
                <div class="action-title">Prescriptions</div>
                <div class="action-desc">Issue and manage prescriptions for your patients.</div>
                <button class="action-btn btn-outline" disabled>Manage (soon)</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-orange-soft"><i class="bi bi-clock"></i></div>
                <div class="action-tag">Availability</div>
                <div class="action-title">My Schedule Settings</div>
                <div class="action-desc">Set your available days and hours for appointments.</div>
                <button class="action-btn btn-outline" disabled>Set Availability (soon)</button>
            </div>
        </div>
    </div>

    <!-- Notifications Section -->
    <div class="section-label mt-4 mb-3"><i class="bi bi-bell me-1"></i> Notifications &amp; Alerts</div>
    <div class="row g-3">
        <div class="col-12">
            <div class="notif-panel">
                <div class="notif-panel-header">
                    <span id="notif-count-label-doc">Loading...</span>
                    <button class="notif-mark-all" onclick="markAllReadDoc()">Mark all as read</button>
                </div>
                <div class="notif-panel-body">
                    <div id="notif-list-doc"></div>
                </div>
            </div>
        </div>
    </div>

    <?php else : ?>
    <!-- ==================== CLIENT ==================== -->
    <div class="welcome-banner banner-client mb-4">
        <!-- Illustration -->
        <svg class="client-banner-illustration" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 420 220" fill="none">
            <!-- Building body -->
            <rect x="60" y="80" width="160" height="130" rx="6" fill="#c8daf5"/>
            <rect x="60" y="80" width="160" height="130" rx="6" fill="url(#bldg-grad)"/>
            <!-- Building top / roof line -->
            <rect x="50" y="74" width="180" height="12" rx="4" fill="#a0bce8"/>
            <!-- Windows row 1 -->
            <rect x="80"  y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="120" y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="160" y="100" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <!-- Windows row 2 -->
            <rect x="80"  y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="120" y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <rect x="160" y="136" width="28" height="22" rx="3" fill="#e8f2ff" stroke="#a0bce8" stroke-width="1.5"/>
            <!-- Door -->
            <rect x="118" y="172" width="44" height="38" rx="4" fill="#7baee8"/>
            <!-- Cross sign on building -->
            <rect x="128" y="56" width="24" height="8"  rx="2" fill="#e05c5c"/>
            <rect x="136" y="48" width="8"  height="24" rx="2" fill="#e05c5c"/>
            <!-- Flagpole -->
            <line x1="140" y1="48" x2="140" y2="30" stroke="#94a3b8" stroke-width="2"/>
            <polygon points="140,30 158,37 140,44" fill="#4a90e2"/>

            <!-- Doctor cartoon -->
            <!-- Body coat -->
            <rect x="268" y="118" width="52" height="72" rx="10" fill="white" stroke="#c8daf5" stroke-width="2"/>
            <!-- Coat lapels -->
            <polygon points="294,118 280,138 294,132" fill="#e8f2ff"/>
            <polygon points="294,118 308,138 294,132" fill="#e8f2ff"/>
            <!-- Stethoscope -->
            <path d="M282 138 Q274 155 280 165 Q286 175 294 170 Q302 175 308 165 Q314 155 306 138" stroke="#4a90e2" stroke-width="2.5" fill="none" stroke-linecap="round"/>
            <circle cx="294" cy="170" r="5" fill="#4a90e2"/>
            <!-- Shirt & tie -->
            <rect x="289" y="118" width="10" height="20" rx="2" fill="#4a90e2"/>
            <!-- Head -->
            <circle cx="294" cy="100" r="22" fill="#fde8c8"/>
            <!-- Hair -->
            <path d="M272 96 Q272 74 294 72 Q316 74 316 96" fill="#5c3d2e"/>
            <!-- Eyes -->
            <circle cx="286" cy="98" r="3" fill="#2d3748"/>
            <circle cx="302" cy="98" r="3" fill="#2d3748"/>
            <!-- Smile -->
            <path d="M286 108 Q294 115 302 108" stroke="#c97b4b" stroke-width="2" fill="none" stroke-linecap="round"/>
            <!-- Ears -->
            <ellipse cx="272" cy="100" rx="4" ry="6" fill="#fde8c8"/>
            <ellipse cx="316" cy="100" rx="4" ry="6" fill="#fde8c8"/>
            <!-- Clipboard -->
            <rect x="308" y="138" width="28" height="36" rx="4" fill="#f8fafc" stroke="#c8daf5" stroke-width="1.5"/>
            <rect x="316" y="133" width="12" height="8" rx="2" fill="#a0bce8"/>
            <line x1="313" y1="150" x2="331" y2="150" stroke="#c8daf5" stroke-width="1.5"/>
            <line x1="313" y1="158" x2="331" y2="158" stroke="#c8daf5" stroke-width="1.5"/>
            <line x1="313" y1="166" x2="325" y2="166" stroke="#c8daf5" stroke-width="1.5"/>
            <!-- Legs -->
            <rect x="278" y="188" width="14" height="22" rx="4" fill="#4a90e2"/>
            <rect x="296" y="188" width="14" height="22" rx="4" fill="#4a90e2"/>
            <!-- Shoes -->
            <ellipse cx="285" cy="210" rx="10" ry="5" fill="#2d3748"/>
            <ellipse cx="303" cy="210" rx="10" ry="5" fill="#2d3748"/>

            <!-- Ground line -->
            <line x1="40" y1="210" x2="380" y2="210" stroke="#c8daf5" stroke-width="2"/>
            <!-- Small tree left -->
            <rect x="30" y="175" width="6" height="35" rx="2" fill="#94a3b8"/>
            <ellipse cx="33" cy="165" rx="18" ry="20" fill="#86c98e"/>
            <ellipse cx="33" cy="158" rx="13" ry="15" fill="#6ab872"/>
            <!-- Small tree right -->
            <rect x="370" y="180" width="6" height="30" rx="2" fill="#94a3b8"/>
            <ellipse cx="373" cy="170" rx="16" ry="18" fill="#86c98e"/>
            <ellipse cx="373" cy="163" rx="11" ry="13" fill="#6ab872"/>
            <!-- Clouds -->
            <ellipse cx="350" cy="50" rx="28" ry="14" fill="white" opacity="0.7"/>
            <ellipse cx="368" cy="46" rx="18" ry="12" fill="white" opacity="0.7"/>
            <ellipse cx="332" cy="48" rx="16" ry="10" fill="white" opacity="0.7"/>
            <ellipse cx="90"  cy="40" rx="22" ry="11" fill="white" opacity="0.6"/>
            <ellipse cx="106" cy="37" rx="14" ry="9"  fill="white" opacity="0.6"/>

            <defs>
                <linearGradient id="bldg-grad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#d6e8f8"/>
                    <stop offset="100%" stop-color="#b8d0ee"/>
                </linearGradient>
            </defs>
        </svg>

        <div style="position:relative;z-index:2;">
            <div class="welcome-label">Patient Portal</div>
            <h4 class="welcome-name">Welcome, <?= esc($name) ?></h4>
            <p class="welcome-sub">From here you can request or review your appointments.</p>
        </div>
        <div class="welcome-date" style="position:relative;z-index:2;">
            <i class="bi bi-calendar3 me-1"></i><?= esc(date('l, F j, Y')) ?>
        </div>
    </div>

    <div class="section-label mb-3">Quick Access</div>
    <div class="row g-3 justify-content-center">
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-blue-soft"><i class="bi bi-calendar2-plus"></i></div>
                <div class="action-tag">Book</div>
                <div class="action-title">New Appointment</div>
                <div class="action-desc">Choose your doctor, date, and time that works best for you.</div>
                <a href="<?= site_url('/appointments/new') ?>" class="action-btn btn-filled">Book Appointment</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-teal-soft"><i class="bi bi-card-list"></i></div>
                <div class="action-tag">My Visits</div>
                <div class="action-title">My Appointments</div>
                <div class="action-desc">View or cancel your upcoming visits and see past appointments.</div>
                <a href="<?= site_url('/appointments/my') ?>" class="action-btn btn-outline">View Appointments</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon bg-purple-soft"><i class="bi bi-chat-dots"></i></div>
                <div class="action-tag">Messaging</div>
                <div class="action-title">Chat with Clinic</div>
                <div class="action-desc">Ask questions or send a message to your doctor or clinic staff before your visit.</div>
                <button class="action-btn btn-outline" onclick="openChat()" style="border-color:#c4b5fd!important;color:#6d28d9;background:#f5f3ff;">Open Chat</button>
            </div>
        </div>
    </div>

    <!-- Notifications Section -->
    <div class="section-label mt-4 mb-3">
        <i class="bi bi-bell me-1"></i> Notifications &amp; Alerts
    </div>
    <div class="row g-3">
        <div class="col-12">
            <div class="notif-panel">
                <div class="notif-panel-header">
                    <span id="notif-count-label">Loading...</span>
                    <button class="notif-mark-all" onclick="markAllRead()">Mark all as read</button>
                </div>
                <div class="notif-panel-body">
                    <div id="notif-list"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const badge = document.getElementById('secDateBadge');
    const dateText = document.getElementById('secDateText');
    if (badge) {
        const fp = flatpickr('#secDatePicker', {
            defaultDate: new Date(),
            disableMobile: true,
            onChange: function(selectedDates, dateStr) {
                const d = selectedDates[0];
                const formatted = d.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
                dateText.textContent = formatted;
            }
        });
        badge.addEventListener('click', () => fp.open());
    }
</script>

<!-- ── Chat Widget ── -->
<div id="chat-widget" class="chat-widget d-none">
    <div class="chat-header">
        <div class="d-flex align-items-center gap-2">
            <div class="chat-avatar-sm"><i class="bi bi-hospital"></i></div>
            <div>
                <div class="chat-header-name">Clinic Support</div>
                <div class="chat-header-status"><span class="chat-online-dot"></span> Online</div>
            </div>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button class="chat-header-btn" onclick="switchContact()" title="Switch contact"><i class="bi bi-people"></i></button>
            <button class="chat-header-btn" onclick="closeChat()" title="Close"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>

    <!-- Contact Switcher -->
    <div id="chat-contacts" class="chat-contacts d-none">
        <div class="chat-contacts-title">Select a contact</div>
        <div class="chat-contact-item active" onclick="selectContact('clinic','Clinic Support','bi-hospital','#3b82f6')">
            <div class="chat-contact-avatar" style="background:#eff6ff;color:#3b82f6;"><i class="bi bi-hospital"></i></div>
            <div><div class="chat-contact-name">Clinic Support</div><div class="chat-contact-sub">General inquiries</div></div>
        </div>
        <div class="chat-contact-item" onclick="selectContact('dr-santos','Dr. Santos','bi-person-fill','#10b981')">
            <div class="chat-contact-avatar" style="background:#f0fdf4;color:#10b981;"><i class="bi bi-person-fill"></i></div>
            <div><div class="chat-contact-name">Dr. Santos</div><div class="chat-contact-sub">General Practitioner</div></div>
        </div>
        <div class="chat-contact-item" onclick="selectContact('dr-reyes','Dr. Reyes','bi-person-fill','#8b5cf6')">
            <div class="chat-contact-avatar" style="background:#f5f3ff;color:#8b5cf6;"><i class="bi bi-person-fill"></i></div>
            <div><div class="chat-contact-name">Dr. Reyes</div><div class="chat-contact-sub">Cardiologist</div></div>
        </div>
        <div class="chat-contact-item" onclick="selectContact('dr-cruz','Dr. Cruz','bi-person-fill','#f59e0b')">
            <div class="chat-contact-avatar" style="background:#fffbeb;color:#f59e0b;"><i class="bi bi-person-fill"></i></div>
            <div><div class="chat-contact-name">Dr. Cruz</div><div class="chat-contact-sub">Pediatrician</div></div>
        </div>
        <div class="chat-contact-item" onclick="selectContact('dr-garcia','Dr. Garcia','bi-person-fill','#ef4444')">
            <div class="chat-contact-avatar" style="background:#fff1f2;color:#ef4444;"><i class="bi bi-person-fill"></i></div>
            <div><div class="chat-contact-name">Dr. Garcia</div><div class="chat-contact-sub">Dermatologist</div></div>
        </div>
    </div>

    <div id="chat-messages" class="chat-messages">
        <!-- messages rendered by JS -->
    </div>

    <div class="chat-input-row">
        <input type="text" id="chat-input" class="chat-input" placeholder="Type a message..." onkeydown="if(event.key==='Enter') sendMessage()">
        <button class="chat-send-btn" onclick="sendMessage()"><i class="bi bi-send-fill"></i></button>
    </div>
</div>

<!-- Floating Chat Button -->
<button class="chat-fab" id="chat-fab" onclick="openChat()" title="Chat with clinic">
    <i class="bi bi-chat-dots-fill"></i>
    <span class="chat-fab-dot d-none" id="chat-fab-dot"></span>
</button>

<script>
(function () {
    const CHAT_KEY = 'oabsc_chat_messages';
    let currentContact = { id: 'clinic', name: 'Clinic Support', icon: 'bi-hospital', color: '#3b82f6' };

    const autoReplies = {
        clinic: [
            "Thank you for reaching out! How can we help you today?",
            "Our clinic hours are Monday to Saturday, 8:00 AM – 5:00 PM.",
            "For urgent concerns, please call us at (02) 8123-4567.",
            "We'll get back to you as soon as possible. Is there anything else?",
        ],
        'dr-santos': [
            "Hello! This is Dr. Santos' office. How can I assist you?",
            "Please bring your previous lab results to your next visit.",
            "Your prescription is ready for pick-up at the clinic.",
        ],
        'dr-reyes': [
            "Hi! Dr. Reyes' office here. What can we help you with?",
            "Please avoid strenuous activity before your cardiology check-up.",
            "Your ECG results are ready. Please schedule a follow-up.",
        ],
        'dr-cruz': [
            "Hello! Dr. Cruz's clinic here. How may I help?",
            "Please bring your child's vaccination record to the next visit.",
            "The doctor recommends a follow-up in 2 weeks.",
        ],
        'dr-garcia': [
            "Hi! Dr. Garcia's office. What can we do for you?",
            "Please avoid sun exposure 24 hours before your skin procedure.",
            "Your dermatology results are ready for review.",
        ],
    };

    function getMessages() {
        try { return JSON.parse(localStorage.getItem(CHAT_KEY) || '{}'); } catch(e) { return {}; }
    }
    function saveMessages(data) { localStorage.setItem(CHAT_KEY, JSON.stringify(data)); }

    function getContactMessages() {
        const all = getMessages();
        return all[currentContact.id] || [];
    }
    function addMessage(msg) {
        const all = getMessages();
        if (!all[currentContact.id]) all[currentContact.id] = [];
        all[currentContact.id].push(msg);
        saveMessages(all);
    }

    function renderMessages() {
        const container = document.getElementById('chat-messages');
        const msgs = getContactMessages();

        if (msgs.length === 0) {
            container.innerHTML = `
            <div class="chat-empty">
                <i class="bi bi-chat-left-dots" style="font-size:2rem;opacity:0.25;display:block;margin-bottom:8px;"></i>
                <div>No messages yet.</div>
                <div style="font-size:0.75rem;margin-top:4px;">Send a message to start the conversation.</div>
            </div>`;
            return;
        }

        container.innerHTML = msgs.map(m => {
            if (m.from === 'me') {
                return `<div class="chat-bubble-row chat-bubble-right">
                    <div class="chat-bubble chat-bubble-me">${escHtml(m.text)}</div>
                    <div class="chat-time">${m.time}</div>
                </div>`;
            } else {
                return `<div class="chat-bubble-row chat-bubble-left">
                    <div class="chat-avatar-xs" style="background:#f1f5f9;color:${currentContact.color};">
                        <i class="bi ${currentContact.icon}"></i>
                    </div>
                    <div>
                        <div class="chat-bubble chat-bubble-them">${escHtml(m.text)}</div>
                        <div class="chat-time">${m.time}</div>
                    </div>
                </div>`;
            }
        }).join('');

        container.scrollTop = container.scrollHeight;
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function nowTime() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    window.sendMessage = function () {
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text) return;

        addMessage({ from: 'me', text, time: nowTime() });
        input.value = '';
        renderMessages();

        // Auto-reply after delay
        const replies = autoReplies[currentContact.id] || autoReplies.clinic;
        const reply = replies[Math.floor(Math.random() * replies.length)];
        setTimeout(() => {
            addMessage({ from: 'them', text: reply, time: nowTime() });
            renderMessages();
            document.getElementById('chat-fab-dot').classList.remove('d-none');
        }, 1200);
    };

    window.openChat = function () {
        document.getElementById('chat-widget').classList.remove('d-none');
        document.getElementById('chat-fab').classList.add('d-none');
        document.getElementById('chat-contacts').classList.add('d-none');
        document.getElementById('chat-fab-dot').classList.add('d-none');
        renderMessages();
        setTimeout(() => document.getElementById('chat-input').focus(), 100);
    };

    window.closeChat = function () {
        document.getElementById('chat-widget').classList.add('d-none');
        document.getElementById('chat-fab').classList.remove('d-none');
    };

    window.switchContact = function () {
        const panel = document.getElementById('chat-contacts');
        panel.classList.toggle('d-none');
    };

    window.selectContact = function (id, name, icon, color) {
        currentContact = { id, name, icon, color };

        // Update header
        document.querySelector('.chat-header-name').textContent = name;
        document.querySelector('.chat-avatar-sm i').className = `bi ${icon}`;
        document.querySelector('.chat-avatar-sm').style.background = color + '22';
        document.querySelector('.chat-avatar-sm').style.color = color;

        // Highlight active
        document.querySelectorAll('.chat-contact-item').forEach(el => el.classList.remove('active'));
        event.currentTarget.classList.add('active');

        document.getElementById('chat-contacts').classList.add('d-none');
        renderMessages();
    };
})();
</script>

<style>
    /* ── Chat FAB ── */
    .chat-fab {
        position: fixed; bottom: 28px; right: 28px;
        width: 54px; height: 54px; border-radius: 50%;
        background: linear-gradient(135deg, #6d28d9, #4f46e5);
        color: white; border: none; font-size: 1.3rem;
        box-shadow: 0 6px 20px rgba(109,40,217,0.4);
        cursor: pointer; z-index: 1050;
        display: flex; align-items: center; justify-content: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .chat-fab:hover { transform: scale(1.08); box-shadow: 0 8px 24px rgba(109,40,217,0.5); }
    .chat-fab-dot {
        position: absolute; top: 6px; right: 6px;
        width: 10px; height: 10px; border-radius: 50%;
        background: #ef4444; border: 2px solid white;
    }

    /* ── Chat Widget ── */
    .chat-widget {
        position: fixed; bottom: 28px; right: 28px;
        width: 360px; max-height: 540px;
        background: white; border-radius: 20px;
        box-shadow: 0 16px 48px rgba(15,23,42,0.18);
        border: 1px solid #e2e8f0;
        display: flex; flex-direction: column;
        z-index: 1050; overflow: hidden;
    }

    /* Header */
    .chat-header {
        background: linear-gradient(135deg, #6d28d9, #4f46e5);
        color: white; padding: 0.85rem 1rem;
        display: flex; justify-content: space-between; align-items: center;
        flex-shrink: 0;
    }
    .chat-avatar-sm {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; background: rgba(255,255,255,0.2); color: white;
        transition: all 0.2s;
    }
    .chat-header-name { font-size: 0.88rem; font-weight: 700; }
    .chat-header-status { font-size: 0.72rem; opacity: 0.85; display: flex; align-items: center; gap: 4px; }
    .chat-online-dot { width: 7px; height: 7px; border-radius: 50%; background: #4ade80; display: inline-block; }
    .chat-header-btn {
        background: rgba(255,255,255,0.15); border: none; color: white;
        width: 28px; height: 28px; border-radius: 8px; font-size: 0.8rem;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: background 0.15s;
    }
    .chat-header-btn:hover { background: rgba(255,255,255,0.28); }

    /* Contacts */
    .chat-contacts {
        border-bottom: 1px solid #f1f5f9; background: #fafafa; flex-shrink: 0;
    }
    .chat-contacts-title {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; color: #94a3b8; padding: 0.6rem 1rem 0.3rem;
    }
    .chat-contact-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.6rem 1rem; cursor: pointer; transition: background 0.15s;
    }
    .chat-contact-item:hover, .chat-contact-item.active { background: #f0f7ff; }
    .chat-contact-avatar {
        width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 0.95rem;
    }
    .chat-contact-name { font-size: 0.83rem; font-weight: 600; color: #0f172a; }
    .chat-contact-sub  { font-size: 0.72rem; color: #94a3b8; }

    /* Messages */
    .chat-messages {
        flex: 1; overflow-y: auto; padding: 1rem;
        display: flex; flex-direction: column; gap: 0.5rem;
        min-height: 200px;
    }
    .chat-empty { text-align: center; color: #94a3b8; font-size: 0.82rem; margin: auto; padding: 2rem 0; }
    .chat-bubble-row { display: flex; align-items: flex-end; gap: 6px; }
    .chat-bubble-right { flex-direction: row-reverse; }
    .chat-bubble-left  { flex-direction: row; }
    .chat-bubble {
        max-width: 220px; padding: 0.55rem 0.85rem;
        border-radius: 14px; font-size: 0.83rem; line-height: 1.45; word-break: break-word;
    }
    .chat-bubble-me   { background: linear-gradient(135deg,#6d28d9,#4f46e5); color: white; border-bottom-right-radius: 4px; }
    .chat-bubble-them { background: #f1f5f9; color: #0f172a; border-bottom-left-radius: 4px; }
    .chat-time { font-size: 0.68rem; color: #94a3b8; margin-top: 2px; padding: 0 4px; }
    .chat-avatar-xs {
        width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 0.8rem;
    }

    /* Input */
    .chat-input-row {
        display: flex; gap: 8px; padding: 0.75rem 1rem;
        border-top: 1px solid #f1f5f9; flex-shrink: 0;
    }
    .chat-input {
        flex: 1; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 0.5rem 0.85rem; font-size: 0.83rem; outline: none;
        transition: border-color 0.15s;
    }
    .chat-input:focus { border-color: #6d28d9; }
    .chat-send-btn {
        background: linear-gradient(135deg,#6d28d9,#4f46e5); color: white;
        border: none; border-radius: 10px; width: 38px; height: 38px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; cursor: pointer; transition: opacity 0.15s;
    }
    .chat-send-btn:hover { opacity: 0.88; }

    @media (max-width: 480px) {
        .chat-widget { width: calc(100vw - 24px); right: 12px; bottom: 12px; }
        .chat-fab    { right: 16px; bottom: 16px; }
    }
</style>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap');

    html, body {
        background: #edf2f7 !important;
        min-height: 100vh;
        font-family: 'DM Sans', sans-serif;
        margin: 0;
        padding: 0;
    }

    .dashboard-wrapper {
        min-height: calc(100vh - 60px);
    }

    /* ── Welcome Banner ── */
    .welcome-banner {
        border-radius: 20px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        border: 1px solid rgba(255,255,255,0.85);
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
    }
    .banner-admin     { background: linear-gradient(135deg, #e8eff7 0%, #dbe7f4 100%); }
    .banner-secretary { background: linear-gradient(135deg, #e7eef5 0%, #d9e5f0 100%); }
    .banner-doctor    { background: linear-gradient(135deg, #eaf0f6 0%, #dce6f1 100%); }
    .banner-client    { background: linear-gradient(135deg, #e9eff6 0%, #dce7f3 100%); position: relative; overflow: hidden; min-height: 130px; }

    .client-banner-illustration {
        position: absolute;
        right: 0;
        bottom: 0;
        height: 220px;
        width: auto;
        opacity: 0.55;
        pointer-events: none;
        user-select: none;
    }

    @media (max-width: 576px) {
        .client-banner-illustration { height: 140px; opacity: 0.3; }
    }

    .welcome-label {
        font-family: 'DM Sans', sans-serif;
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.6px;
        color: #64748b;
        margin-bottom: 6px;
    }
    .welcome-name {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 5px;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    .welcome-sub {
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
        font-weight: 400;
    }
    .welcome-date {
        font-family: 'DM Sans', sans-serif;
        font-size: 0.8rem;
        font-weight: 500;
        color: #475569;
        background: rgba(255,255,255,0.8);
        padding: 8px 18px;
        border-radius: 20px;
        white-space: nowrap;
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255,255,255,0.95);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }

   
    .section-label {
        font-family: 'DM Sans', sans-serif;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.8px;
        color: #64748b;
    }

    .stat-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 20px 18px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
        border: 1px solid #dbe4ef;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
        border-color: #c6d4e4;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 14px;
    }
    .stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
    }
    .stat-label {
        font-family: 'DM Sans', sans-serif;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #64748b;
        font-weight: 600;
    }

    .action-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 24px 22px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
        border: 1px solid #dbe4ef;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
        border-color: #c6d4e4;
    }
    .action-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 14px;
    }
    .action-tag {
        font-family: 'DM Sans', sans-serif;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.4px;
        color: #94a3b8;
        margin-bottom: 5px;
    }
    .action-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 7px;
        letter-spacing: -0.2px;
    }
    .action-desc {
        font-family: 'DM Sans', sans-serif;
        font-size: 0.83rem;
        color: #64748b;
        flex: 1;
        margin-bottom: 18px;
        line-height: 1.6;
        font-weight: 400;
    }
    .action-btn {
        font-family: 'DM Sans', sans-serif;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        align-self: flex-start;
        transition: all 0.18s ease;
        text-decoration: none;
        display: inline-block;
        letter-spacing: 0.1px;
    }
    .action-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(30, 64, 175, 0.22);
    }
    .btn-filled {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
        color: #fff;
        box-shadow: 0 2px 10px rgba(30, 64, 175, 0.3);
    }
    .btn-filled:hover:not(:disabled) {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
    }
    .btn-outline {
        background: #eff6ff;
        color: #1e40af;
        border: 1.5px solid #bfdbfe !important;
    }
    .btn-outline:hover:not(:disabled) {
        background: #dbeafe;
        border-color: #93c5fd !important;
    }
    .btn-disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
        border: 1px solid #dbe4ef !important;
    }

  
    .bg-blue-soft   { background: #e8f0fe; color: #3b6fd4; }
    .bg-teal-soft   { background: #d6f5f0; color: #0d9488; }
    .bg-purple-soft { background: #e0ecff; color: #1e3a8a; }
    .bg-green-soft  { background: #d9f5e5; color: #16a34a; }
    .bg-orange-soft { background: #fff0e0; color: #d97706; }
    .bg-slate-soft  { background: #e2e8f0; color: #334155; }

    /* ── Notifications Panel ── */
    .notif-panel {
        background: white; border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06);
        overflow: hidden;
    }
    .notif-panel-body {
        max-height: 420px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    .notif-panel-body::-webkit-scrollbar { width: 5px; }
    .notif-panel-body::-webkit-scrollbar-track { background: #f1f5f9; }
    .notif-panel-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .notif-panel-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .notif-panel-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.82rem; font-weight: 600; color: #475569;
    }
    .notif-mark-all {
        background: none; border: none; color: #3b82f6;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.78rem; font-weight: 600; cursor: pointer; padding: 0;
    }
    .notif-mark-all:hover { text-decoration: underline; }
    .notif-item {
        display: flex; align-items: flex-start; gap: 0.85rem;
        padding: 0.9rem 1.25rem; border-bottom: 1px solid #f8fafc;
        cursor: pointer; transition: background 0.15s;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: #f8fafc; }
    .notif-item.notif-read { opacity: 0.6; }
    .notif-item-icon {
        width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .notif-item-body { flex: 1; min-width: 0; }
    .notif-item-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.85rem; font-weight: 700; color: #0f172a;
        margin-bottom: 2px; display: flex; align-items: center; gap: 6px;
    }
    .notif-unread-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #3b82f6; display: inline-block; flex-shrink: 0;
    }
    .notif-item-text {
        font-family: 'DM Sans', sans-serif;
        font-size: 0.8rem; color: #475569; line-height: 1.5; margin-bottom: 3px;
    }
    .notif-item-time {
        font-family: 'DM Sans', sans-serif;
        font-size: 0.72rem; color: #94a3b8;
    }
    /* ══════════════════════════════════════
       ADMIN — Steel Blue/Teal Theme
    ══════════════════════════════════════ */
    .adm-wrapper {
        background: #dce6ef;
        border-radius: 24px;
        padding: 28px;
        margin: -12px;
    }
    .adm-banner {
        background: linear-gradient(135deg, #c8d8e6 0%, #b7c8d8 100%);
        border-radius: 20px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        border: 1px solid rgba(255,255,255,0.55);
        box-shadow: 0 3px 14px rgba(15, 23, 42, 0.08);
    }
    .adm-banner-label { font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:1.3px; color:#51657a; margin-bottom:5px; }
    .adm-banner-name  { font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 5px; letter-spacing:-0.3px; }
    .adm-banner-sub   { font-size:0.84rem; color:#334155; margin:0; }
    .adm-banner-date  { font-size:0.8rem; font-weight:500; color:#1e293b; background:rgba(255,255,255,0.72); padding:8px 18px; border-radius:20px; white-space:nowrap; border:1px solid rgba(148,163,184,0.35); }
    .adm-section-label { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:#475569; }
    .adm-stat-card {
        background: rgba(255,255,255,0.96);
        border-radius: 18px; padding: 20px 18px;
        border: 1px solid #d5e0ea;
        box-shadow: 0 2px 7px rgba(15, 23, 42, 0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease; height: 100%;
    }
    .adm-stat-card:hover { transform: translateY(-2px); box-shadow: 0 7px 18px rgba(15, 23, 42, 0.12); }
    .adm-stat-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; margin-bottom:14px; }
    .adm-stat-value { font-size:2rem; font-weight:700; color:#0f172a; line-height:1; margin-bottom:5px; letter-spacing:-0.5px; }
    .adm-stat-label { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.7px; color:#5a7288; font-weight:600; }
    .adm-card {
        background: rgba(255,255,255,0.96);
        border-radius: 18px; padding: 24px 22px;
        border: 1px solid #d5e0ea;
        box-shadow: 0 2px 7px rgba(15, 23, 42, 0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%; display: flex; flex-direction: column;
    }
    .adm-card:hover { transform: translateY(-2px); box-shadow: 0 7px 18px rgba(15, 23, 42, 0.12); }
    .adm-card-icon { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:14px; }
    .adm-card-tag   { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#5a7288; margin-bottom:5px; }
    .adm-card-title { font-size:0.95rem; font-weight:700; color:#0f172a; margin-bottom:7px; }
    .adm-card-desc  { font-size:0.8rem; color:#334155; flex:1; margin-bottom:18px; line-height:1.55; }
    .adm-btn { font-size:0.78rem; font-weight:600; padding:7px 18px; border-radius:10px; border:none; cursor:pointer; align-self:flex-start; transition:all 0.18s ease; text-decoration:none; display:inline-block; }
    .adm-btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 3px 10px rgba(15, 23, 42, 0.18); }
    .adm-btn-filled  { background:linear-gradient(135deg,#3b556e 0%,#2e445a 100%); color:#fff; box-shadow:0 2px 8px rgba(15, 23, 42, 0.18); }
    .adm-btn-filled:hover:not(:disabled) { background:linear-gradient(135deg,#32495f 0%,#24394d 100%); }
    .adm-btn-outline { background:#edf3f9; color:#334155; border:1.5px solid #c4d3e2 !important; }
    .adm-btn-outline:hover:not(:disabled) { background:#e2ebf4; border-color:#a9bfd4 !important; }
    .adm-btn-disabled { background:#f1f5f9; color:#8aa0b3; cursor:not-allowed; border:1px solid #d2dde8 !important; }

    /* ── Assistant Admin Compact List ── */
    .aa-list-panel {
        background: white; border-radius: 16px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden;
    }
    .aa-list-header {
        font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.7px; color: #5a7288;
        padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .aa-list-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.65rem 1rem; border-bottom: 1px solid #f1f5f9;
        text-decoration: none; color: #0f172a;
        transition: background 0.12s;
    }
    .aa-list-item:last-child { border-bottom: none; }
    a.aa-list-item:hover { background: #f0f6ff; color: #1e40af; }
    .aa-list-disabled { opacity: 0.55; cursor: not-allowed; }
    .aa-list-icon {
        width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 0.85rem;
    }
    .aa-list-label { flex: 1; font-size: 0.85rem; font-weight: 600; }
    .aa-list-arrow { color: #94a3b8; font-size: 0.75rem; }
    .aa-list-soon {
        font-size: 0.68rem; font-weight: 700; background: #f1f5f9;
        color: #94a3b8; padding: 2px 7px; border-radius: 999px;
    }

    /* ══════════════════════════════════════
       SECRETARY — Redesigned Green Theme
    ══════════════════════════════════════ */
    .sec2-wrapper {
        background: #f4f9f4;
        border-radius: 20px;
        padding: 28px;
        margin: -12px;
    }
    .sec2-banner {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-radius: 16px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        border: 1px solid rgba(255,255,255,0.9);
        box-shadow: 0 2px 12px rgba(46,125,50,0.10);
    }
    .sec2-banner-label { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:1.4px; color:#2e7d32; margin-bottom:4px; }
    .sec2-banner-name  { font-size:1.5rem; font-weight:700; color:#1b3a1e; margin:0 0 4px; letter-spacing:-0.4px; }
    .sec2-banner-sub   { font-size:0.85rem; color:#4a7a4e; margin:0; }
    .sec2-banner-date  { font-size:0.8rem; font-weight:500; color:#2e5c32; background:rgba(255,255,255,0.85); padding:8px 18px; border-radius:20px; white-space:nowrap; border:1px solid rgba(255,255,255,0.95); box-shadow:0 1px 6px rgba(46,125,50,0.10); }
    .sec2-section-label { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:#6aaa70; }
    .sec2-stat {
        background: #ffffff;
        border-radius: 16px;
        padding: 22px 20px;
        border: 1px solid #d0e8d2;
        box-shadow: 0 1px 6px rgba(46,125,50,0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .sec2-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(46,125,50,0.13); border-color: #a5d6a7; }
    .sec2-stat-icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; margin-bottom:14px; }
    .sec2-stat-val  { font-size:2.2rem; font-weight:700; color:#1b3a1e; line-height:1; margin-bottom:6px; letter-spacing:-0.5px; }
    .sec2-stat-lbl  { font-size:0.68rem; text-transform:uppercase; letter-spacing:0.8px; color:#6aaa70; font-weight:600; }
    .sec2-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px 22px;
        border: 1px solid #d0e8d2;
        box-shadow: 0 1px 6px rgba(46,125,50,0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .sec2-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(46,125,50,0.13); border-color: #a5d6a7; }
    .sec2-card-icon  { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:14px; }
    .sec2-card-tag   { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#6aaa70; margin-bottom:5px; }
    .sec2-card-title { font-size:0.97rem; font-weight:700; color:#1b3a1e; margin-bottom:7px; letter-spacing:-0.1px; }
    .sec2-card-desc  { font-size:0.8rem; color:#4a7a4e; flex:1; margin-bottom:18px; line-height:1.55; }
    .sec2-btn {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 7px 18px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        align-self: flex-start;
        transition: all 0.18s ease;
        display: inline-block;
        letter-spacing: 0.1px;
    }
    .sec2-btn:disabled { cursor: not-allowed; opacity: 0.85; }
    .sec2-btn-filled  { background:#2e5c32; color:#fff; box-shadow:0 2px 8px rgba(46,92,50,0.25); }
    .sec2-btn-filled:hover:not(:disabled)  { background:#245228; box-shadow:0 4px 14px rgba(46,92,50,0.35); transform:translateY(-1px); }
    .sec2-btn-outline { background:#f4f9f4; color:#2e5c32; border:1.5px solid #a5d6a7 !important; }
    .sec2-btn-outline:hover:not(:disabled) { background:#e8f5e9; border-color:#81c784 !important; transform:translateY(-1px); }

    /* Sidebar layout */
    .sec2-page {
        display: flex;
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        margin-top: -3rem;
        min-height: calc(100vh - 60px);
        background: #edf2f7;
    }
    .sec2-sidebar {
        width: 260px;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 4px 0 24px rgba(46, 125, 50, 0.08);
        padding: 28px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .sec2-content {
        flex: 1;
        padding: 32px 28px;
        min-width: 0;
    }

    /* Table */
    .sec2-table-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #d0e8d2;
        box-shadow: 0 1px 6px rgba(46,125,50,0.07);
        overflow: hidden;
    }
    .sec2-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #e8f5e9;
    }
    .sec2-table-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1b3a1e;
    }
    .sec2-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }
    .sec2-table thead tr {
        background: #f4f9f4;
    }
    .sec2-table th {
        padding: 10px 16px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #6aaa70;
        border-bottom: 1px solid #e0f0e1;
        white-space: nowrap;
    }
    .sec2-table td {
        padding: 12px 16px;
        color: #2d3748;
        border-bottom: 1px solid #f0f7f0;
        vertical-align: middle;
    }
    .sec2-table tbody tr:last-child td { border-bottom: none; }
    .sec2-table tbody tr:hover { background: #f9fdf9; }
    .sec2-queue { font-weight: 700; color: #2e5c32; }
    .sec2-patient { font-weight: 600; color: #1b3a1e; }
    .sec2-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .sec2-badge-confirmed  { background:#dbeafe; color:#1d4ed8; }
    .sec2-badge-completed  { background:#d1fae5; color:#065f46; }
    .sec2-badge-serving    { background:#fef3c7; color:#92400e; }
    .sec2-badge-pending    { background:#fde68a; color:#78350f; }
    .sec2-badge-cancelled  { background:#fee2e2; color:#991b1b; }
    .sec2-sidebar-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.3px;
        color: #6aaa70;
        padding: 0 8px;
        margin-bottom: 8px;
    }
    .sec2-sidebar-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 0.92rem;
        font-weight: 500;
        color: #2e5c32;
        text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }
    .sec2-sidebar-item i { font-size: 1.15rem; }
    .sec2-sidebar-item:hover {
        background: rgba(232, 245, 233, 0.8);
        color: #1b3a1e;
    }
    .sec2-sidebar-item.active {
        background: #2e5c32;
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 4px 14px rgba(46, 92, 50, 0.25);
    }
    .sec2-main { flex: 1; min-width: 0; }
</style>
</body>
</html>
