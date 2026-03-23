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

    <?php if ($role === 'admin') : ?>
    <!-- ==================== ADMIN ==================== -->
    <div class="adm-wrapper">

    <!-- Welcome Banner -->
    <div class="adm-banner mb-4">
        <div>
            <div class="adm-banner-label">Admin Panel</div>
            <h4 class="adm-banner-name">Welcome back, <?= esc($name) ?></h4>
            <p class="adm-banner-sub">Quick overview of your clinic's activity today.</p>
        </div>
        <div class="adm-banner-date">
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
        <div class="col-md-4">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-people-fill"></i></div>
                <div class="adm-card-tag">Users</div>
                <div class="adm-card-title">Manage Users</div>
                <div class="adm-card-desc">Add, edit, or remove system users and assign their roles.</div>
                <a href="<?= site_url('/admin/patients/list') ?>" class="adm-btn adm-btn-filled">Open</a>
            </div>
        </div>
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
                <button class="adm-btn adm-btn-disabled" disabled>Coming soon</button>
            </div>
        </div>
    </div>

    </div><!-- end adm-wrapper -->

    <?php elseif ($role === 'secretary') : ?>
    <!-- ==================== SECRETARY ==================== -->

    <div class="sec-wrapper">

        <!-- Welcome Banner -->
        <div class="sec-banner mb-4">
            <div class="sec-banner-left">
                <div class="sec-banner-label">Secretary Panel</div>
                <h4 class="sec-banner-name">Welcome back, <?= esc($name) ?></h4>
                <p class="sec-banner-sub">Here is your front-desk overview for today.</p>
            </div>
            <div class="sec-banner-date">
                <i class="bi bi-calendar3 me-1"></i><?= esc(date('l, F j, Y')) ?>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="sec-stat-card">
                    <div class="sec-stat-icon" style="background:#ddeedd;color:#2a4a2e;"><i class="bi bi-calendar-day"></i></div>
                    <div class="sec-stat-value">0</div>
                    <div class="sec-stat-label">Today's Appointments</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sec-stat-card">
                    <div class="sec-stat-icon" style="background:#c8dfc8;color:#1e3820;"><i class="bi bi-hourglass-split"></i></div>
                    <div class="sec-stat-value">0</div>
                    <div class="sec-stat-label">Pending Requests</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sec-stat-card">
                    <div class="sec-stat-icon" style="background:#b8d0ba;color:#1a2e1c;"><i class="bi bi-people"></i></div>
                    <div class="sec-stat-value">0</div>
                    <div class="sec-stat-label">Total Patients</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="sec-stat-card">
                    <div class="sec-stat-icon" style="background:#3a5c3e;color:#c8f0ca;"><i class="bi bi-person-badge"></i></div>
                    <div class="sec-stat-value">0</div>
                    <div class="sec-stat-label">Doctors On Duty</div>
                </div>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="sec-section-label mb-3">Quick Access</div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="sec-card">
                    <div class="sec-card-icon" style="background:#ddeedd;color:#2a4a2e;"><i class="bi bi-calendar2-plus"></i></div>
                    <div class="sec-card-tag">Appointments</div>
                    <div class="sec-card-title">Manage Appointments</div>
                    <div class="sec-card-desc">Schedule, reschedule, or cancel patient appointments.</div>
                    <button class="sec-btn sec-btn-filled" disabled>Open (soon)</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sec-card">
                    <div class="sec-card-icon" style="background:#c8dfc8;color:#1e3820;"><i class="bi bi-list-ol"></i></div>
                    <div class="sec-card-tag">Queue</div>
                    <div class="sec-card-title">Patient Queue</div>
                    <div class="sec-card-desc">View and manage today's walk-in and booked patient queue.</div>
                    <button class="sec-btn sec-btn-outline" disabled>View Queue (soon)</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sec-card">
                    <div class="sec-card-icon" style="background:#b8d0ba;color:#1a2e1c;"><i class="bi bi-folder2-open"></i></div>
                    <div class="sec-card-tag">Records</div>
                    <div class="sec-card-title">Patient Records</div>
                    <div class="sec-card-desc">Search and view registered patient information.</div>
                    <button class="sec-btn sec-btn-outline" disabled>Search Patients (soon)</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sec-card">
                    <div class="sec-card-icon" style="background:#3a5c3e;color:#c8f0ca;"><i class="bi bi-person-plus"></i></div>
                    <div class="sec-card-tag">Registration</div>
                    <div class="sec-card-title">Register New Patient</div>
                    <div class="sec-card-desc">Add a new patient to the system for their first visit.</div>
                    <button class="sec-btn sec-btn-outline" disabled>Register Patient (soon)</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sec-card">
                    <div class="sec-card-icon" style="background:#ddeedd;color:#2a4a2e;"><i class="bi bi-clock-history"></i></div>
                    <div class="sec-card-tag">Schedule</div>
                    <div class="sec-card-title">Doctor Schedules</div>
                    <div class="sec-card-desc">View available doctors and their schedules for booking.</div>
                    <button class="sec-btn sec-btn-outline" disabled>View Schedules (soon)</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sec-card">
                    <div class="sec-card-icon" style="background:#c8dfc8;color:#1e3820;"><i class="bi bi-bell"></i></div>
                    <div class="sec-card-tag">Notifications</div>
                    <div class="sec-card-title">Pending Approvals</div>
                    <div class="sec-card-desc">Review and confirm appointment requests from patients.</div>
                    <button class="sec-btn sec-btn-outline" disabled>Review Requests (soon)</button>
                </div>
            </div>
        </div>

    </div><!-- end sec-wrapper -->

    <?php elseif ($role === 'doctor') : ?>
    <!-- ==================== DOCTOR ==================== -->

    <div class="welcome-banner banner-doctor mb-4">
        <div>
            <div class="welcome-label">Doctor Panel</div>
            <h4 class="welcome-name">Welcome, Dr. <?= esc($name) ?></h4>
            <p class="welcome-sub">Here is your clinical overview for today.</p>
        </div>
        <div class="welcome-date">
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
                <div class="action-icon bg-pink-soft"><i class="bi bi-capsule"></i></div>
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

    <?php else : ?>
    <!-- ==================== CLIENT ==================== -->

<<<<<<< HEAD
    <div class="welcome-banner banner-client mb-4">
        <div>
            <div class="welcome-label">Patient Portal</div>
            <h4 class="welcome-name">Welcome, <?= esc($name) ?></h4>
            <p class="welcome-sub">From here you can request or review your appointments.</p>
        </div>
        <div class="welcome-date">
            <i class="bi bi-calendar3 me-1"></i><?= esc(date('l, F j, Y')) ?>
        </div>
    </div>

    <div class="section-label mb-3">Quick Access</div>
    <div class="row g-3 justify-content-center">
        <div class="col-md-5">
            <div class="action-card">
                <div class="action-icon bg-blue-soft"><i class="bi bi-calendar2-plus"></i></div>
                <div class="action-tag">Book</div>
                <div class="action-title">New Appointment</div>
                <div class="action-desc">Choose your doctor, date, and time that works best for you.</div>
                <button class="action-btn btn-filled" disabled>Book Appointment (soon)</button>
            </div>
        </div>
        <div class="col-md-5">
            <div class="action-card">
                <div class="action-icon bg-teal-soft"><i class="bi bi-card-list"></i></div>
                <div class="action-tag">My Visits</div>
                <div class="action-title">My Appointments</div>
                <div class="action-desc">View or cancel your upcoming visits and see past appointments.</div>
                <button class="action-btn btn-outline" disabled>View Appointments (soon)</button>
=======
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted small mb-2">Book</h6>
                            <h5 class="card-title">New Appointment</h5>
                            <p class="card-text small text-muted">
                                Choose your doctor, date, and time that works best for you.
                            </p>
                            <a href="<?= site_url('/appointments/new') ?>" class="btn btn-sm btn-primary">Book appointment</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted small mb-2">My Visits</h6>
                            <h5 class="card-title">My Appointments</h5>
                            <p class="card-text small text-muted">
                                View or cancel your upcoming visits and see past appointments.
                            </p>
                            <a href="<?= site_url('/appointments/my') ?>" class="btn btn-sm btn-outline-primary">View appointments</a>
                        </div>
                    </div>
                </div>
>>>>>>> de3177a21583bdf9d1fc0cc5f6b67040f4e8c61b
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    html, body {
        background: #fce8ec !important;
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
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
        box-shadow: 0 4px 20px rgba(220,130,160,0.1);
    }
    .banner-admin     { background: linear-gradient(135deg, #fde8f0 0%, #f3e8fd 100%); }
    .banner-secretary { background: linear-gradient(135deg, #fde8f0 0%, #fce4f6 100%); }
    .banner-doctor    { background: linear-gradient(135deg, #fde8f0 0%, #ffeef5 100%); }
    .banner-client    { background: linear-gradient(135deg, #fde8f0 0%, #fff0f8 100%); }

    .welcome-label {
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.3px;
        color: #c47a95;
        margin-bottom: 5px;
    }
    .welcome-name {
        font-size: 1.4rem;
        font-weight: 700;
        color: #3d1a28;
        margin: 0 0 5px;
        letter-spacing: -0.3px;
    }
    .welcome-sub {
        font-size: 0.84rem;
        color: #9e6070;
        margin: 0;
    }
    .welcome-date {
        font-size: 0.8rem;
        font-weight: 500;
        color: #9e6070;
        background: rgba(255,255,255,0.8);
        padding: 8px 18px;
        border-radius: 20px;
        white-space: nowrap;
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255,255,255,0.95);
        box-shadow: 0 2px 8px rgba(220,130,160,0.1);
    }

   
    .section-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #c9a0b0;
    }

    .stat-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 20px 18px;
        box-shadow: 0 2px 8px rgba(220,130,160,0.08), 0 1px 2px rgba(220,130,160,0.05);
        border: 1px solid #f5dde6;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(220,130,160,0.15);
        border-color: #f0c8d8;
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
        font-size: 2rem;
        font-weight: 700;
        color: #3d1a28;
        line-height: 1;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
    }
    .stat-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #c9a0b0;
        font-weight: 600;
    }

 
    .action-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 24px 22px;
        box-shadow: 0 2px 8px rgba(220,130,160,0.08), 0 1px 2px rgba(220,130,160,0.05);
        border: 1px solid #f5dde6;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(220,130,160,0.15);
        border-color: #f0c8d8;
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
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #c9a0b0;
        margin-bottom: 5px;
    }
    .action-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #3d1a28;
        margin-bottom: 7px;
        letter-spacing: -0.1px;
    }
    .action-desc {
        font-size: 0.8rem;
        color: #9e6070;
        flex: 1;
        margin-bottom: 18px;
        line-height: 1.55;
    }
    .action-btn {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 7px 18px;
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
        box-shadow: 0 4px 14px rgba(214,90,140,0.25);
    }
    .btn-filled {
        background: linear-gradient(135deg, #e8608a 0%, #d44a78 100%);
        color: #fff;
        box-shadow: 0 2px 10px rgba(214,90,140,0.3);
    }
    .btn-filled:hover:not(:disabled) {
        background: linear-gradient(135deg, #d44a78 0%, #c03a68 100%);
    }
    .btn-outline {
        background: #fff0f5;
        color: #d44a78;
        border: 1.5px solid #f5c0d0 !important;
    }
    .btn-outline:hover:not(:disabled) {
        background: #ffe4ee;
        border-color: #f0a8c0 !important;
    }
    .btn-disabled {
        background: #fdf5f7;
        color: #d4a8b8;
        cursor: not-allowed;
        border: 1px solid #f5dde6 !important;
    }

  
    .bg-blue-soft   { background: #e8f0fe; color: #3b6fd4; }
    .bg-teal-soft   { background: #d6f5f0; color: #0d9488; }
    .bg-purple-soft { background: #f3e8fd; color: #9333ea; }
    .bg-green-soft  { background: #d9f5e5; color: #16a34a; }
    .bg-orange-soft { background: #fff0e0; color: #d97706; }
    .bg-pink-soft   { background: #fde8f0; color: #e05585; }

    /* ══════════════════════════════════════
       ADMIN — Steel Blue/Teal Theme
    ══════════════════════════════════════ */
    .adm-wrapper {
        background: #4e8a9e;
        border-radius: 24px;
        padding: 28px;
        margin: -12px;
    }
    .adm-banner {
        background: linear-gradient(135deg, #5a9aae 0%, #3d7a8e 100%);
        border-radius: 20px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .adm-banner-label { font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:1.3px; color:#b8dce8; margin-bottom:5px; }
    .adm-banner-name  { font-size:1.4rem; font-weight:700; color:#ffffff; margin:0 0 5px; letter-spacing:-0.3px; }
    .adm-banner-sub   { font-size:0.84rem; color:#c8e8f4; margin:0; }
    .adm-banner-date  { font-size:0.8rem; font-weight:500; color:#e0f4fa; background:rgba(255,255,255,0.15); padding:8px 18px; border-radius:20px; white-space:nowrap; border:1px solid rgba(255,255,255,0.25); }
    .adm-section-label { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:#c8e8f4; }
    .adm-stat-card {
        background: rgba(255,255,255,0.92);
        border-radius: 18px; padding: 20px 18px;
        border: 1px solid rgba(255,255,255,0.6);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease; height: 100%;
    }
    .adm-stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,0.14); }
    .adm-stat-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; margin-bottom:14px; }
    .adm-stat-value { font-size:2rem; font-weight:700; color:#0d2a35; line-height:1; margin-bottom:5px; letter-spacing:-0.5px; }
    .adm-stat-label { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.7px; color:#4e8a9e; font-weight:600; }
    .adm-card {
        background: rgba(255,255,255,0.92);
        border-radius: 18px; padding: 24px 22px;
        border: 1px solid rgba(255,255,255,0.6);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%; display: flex; flex-direction: column;
    }
    .adm-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,0.14); }
    .adm-card-icon { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:14px; }
    .adm-card-tag   { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#4e8a9e; margin-bottom:5px; }
    .adm-card-title { font-size:0.95rem; font-weight:700; color:#0d2a35; margin-bottom:7px; }
    .adm-card-desc  { font-size:0.8rem; color:#2a5a6e; flex:1; margin-bottom:18px; line-height:1.55; }
    .adm-btn { font-size:0.78rem; font-weight:600; padding:7px 18px; border-radius:10px; border:none; cursor:pointer; align-self:flex-start; transition:all 0.18s ease; text-decoration:none; display:inline-block; }
    .adm-btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 4px 14px rgba(0,0,0,0.2); }
    .adm-btn-filled  { background:linear-gradient(135deg,#4e8a9e 0%,#3a7088 100%); color:#fff; box-shadow:0 2px 10px rgba(0,0,0,0.2); }
    .adm-btn-filled:hover:not(:disabled) { background:linear-gradient(135deg,#3a7088 0%,#2a5a6e 100%); }
    .adm-btn-outline { background:#eaf4f8; color:#2a5a6e; border:1.5px solid #a4ccd8 !important; }
    .adm-btn-outline:hover:not(:disabled) { background:#d4eaf4; border-color:#7ab4c8 !important; }
    .adm-btn-disabled { background:#f0f4f6; color:#8ab0be; cursor:not-allowed; border:1px solid #c8dce4 !important; }

    .sec-wrapper {
        background: #fdf0f3;
        border-radius: 24px;
        padding: 28px;
        margin: -12px;
    }

    /* Banner */
    .sec-banner {
        background: linear-gradient(135deg, #ddeedd 0%, #c8dfc8 100%);
        border-radius: 20px;
        padding: 28px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        border: 1px solid rgba(255,255,255,0.9);
        box-shadow: 0 4px 20px rgba(58,92,62,0.12);
    }
    .sec-banner-label {
        font-size: 10.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.3px;
        color: #3a5c3e;
        margin-bottom: 5px;
    }
    .sec-banner-name {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1a2e1c;
        margin: 0 0 5px;
        letter-spacing: -0.3px;
    }
    .sec-banner-sub {
        font-size: 0.84rem;
        color: #4a6e4e;
        margin: 0;
    }
    .sec-banner-date {
        font-size: 0.8rem;
        font-weight: 500;
        color: #2a4a2e;
        background: rgba(255,255,255,0.8);
        padding: 8px 18px;
        border-radius: 20px;
        white-space: nowrap;
        border: 1px solid rgba(255,255,255,0.95);
        box-shadow: 0 2px 8px rgba(58,92,62,0.1);
    }

    /* Section label */
    .sec-section-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #7aaa80;
    }

    /* Stat cards */
    .sec-stat-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 20px 18px;
        border: 1px solid #c0d8c2;
        box-shadow: 0 2px 8px rgba(58,92,62,0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .sec-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(58,92,62,0.14);
        border-color: #9ac09e;
    }
    .sec-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 14px;
    }
    .sec-stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1a2e1c;
        line-height: 1;
        margin-bottom: 5px;
        letter-spacing: -0.5px;
    }
    .sec-stat-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #7aaa80;
        font-weight: 600;
    }

    /* Action cards */
    .sec-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 24px 22px;
        border: 1px solid #c0d8c2;
        box-shadow: 0 2px 8px rgba(58,92,62,0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .sec-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(58,92,62,0.14);
        border-color: #9ac09e;
    }
    .sec-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 14px;
    }
    .sec-card-tag {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #7aaa80;
        margin-bottom: 5px;
    }
    .sec-card-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1a2e1c;
        margin-bottom: 7px;
        letter-spacing: -0.1px;
    }
    .sec-card-desc {
        font-size: 0.8rem;
        color: #4a6e4e;
        flex: 1;
        margin-bottom: 18px;
        line-height: 1.55;
    }
    .sec-btn {
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
    .sec-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(58,92,62,0.28);
    }
    .sec-btn-filled {
        background: linear-gradient(135deg, #3a5c3e 0%, #2a4a2e 100%);
        color: #fff;
        box-shadow: 0 2px 10px rgba(58,92,62,0.3);
    }
    .sec-btn-filled:hover:not(:disabled) {
        background: linear-gradient(135deg, #2a4a2e 0%, #1e3820 100%);
    }
    .sec-btn-outline {
        background: #eef6ef;
        color: #2a4a2e;
        border: 1.5px solid #a8c8aa !important;
    }
    .sec-btn-outline:hover:not(:disabled) {
        background: #daeeda;
        border-color: #80aa84 !important;
    }
</style>
</body>
</html>
