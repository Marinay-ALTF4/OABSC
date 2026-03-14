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
</head>
<body>
<?= view('header') ?>

<div class="container py-4">

    <?php if ($role === 'admin') : ?>
    <!-- ==================== ADMIN ==================== -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm no-hover">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div>
                        <h5 class="card-title mb-1">Welcome back, <?= esc($name) ?></h5>
                        <p class="text-muted mb-0 small">Quick overview of your clinic's activity today.</p>
                    </div>
                    <span class="badge px-3 py-2 mt-2 mt-md-0" style="background:#dbeafe;color:#1d4ed8;"><?= esc(date('l, F j, Y')) ?></span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Total Appointments</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Today's Appointments</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Total Patients</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Doctors Available</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Pending Requests</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Secretaries</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Users</h6>
                    <h5 class="card-title">Manage Users</h5>
                    <p class="card-text small text-muted">Add, edit, or remove system users and assign their roles.</p>
                    <a href="<?= site_url('/admin/patients/list') ?>" class="btn btn-sm btn-primary">Open</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Patients</h6>
                    <h5 class="card-title">Patient Records</h5>
                    <p class="card-text small text-muted">Browse all registered patient profiles and appointment history.</p>
                    <a href="<?= site_url('/admin/patients') ?>" class="btn btn-sm btn-outline-primary">Open</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Reports</h6>
                    <h5 class="card-title">Clinic Reports</h5>
                    <p class="card-text small text-muted">View statistics and generate reports on clinic activity.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>Coming soon</button>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($role === 'secretary') : ?>
    <!-- ==================== SECRETARY ==================== -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm no-hover">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div>
                        <h5 class="card-title mb-1">Welcome back, <?= esc($name) ?></h5>
                        <p class="text-muted mb-0 small">Here is your front-desk overview for today.</p>
                    </div>
                    <span class="badge px-3 py-2 mt-2 mt-md-0" style="background:#e0f2fe;color:#0284c7;"><?= esc(date('l, F j, Y')) ?></span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Today's Appointments</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Pending Requests</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Total Patients</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Doctors On Duty</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Appointments</h6>
                    <h5 class="card-title">Manage Appointments</h5>
                    <p class="card-text small text-muted">Schedule, reschedule, or cancel patient appointments.</p>
                    <button class="btn btn-sm btn-primary" disabled>Open (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Queue</h6>
                    <h5 class="card-title">Patient Queue</h5>
                    <p class="card-text small text-muted">View and manage today's walk-in and booked patient queue.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>View Queue (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Records</h6>
                    <h5 class="card-title">Patient Records</h5>
                    <p class="card-text small text-muted">Search and view registered patient information.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>Search Patients (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Registration</h6>
                    <h5 class="card-title">Register New Patient</h5>
                    <p class="card-text small text-muted">Add a new patient to the system for their first visit.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>Register Patient (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Schedule</h6>
                    <h5 class="card-title">Doctor Schedules</h5>
                    <p class="card-text small text-muted">View available doctors and their schedules for booking.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>View Schedules (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Notifications</h6>
                    <h5 class="card-title">Pending Approvals</h5>
                    <p class="card-text small text-muted">Review and confirm appointment requests from patients.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>Review Requests (soon)</button>
                </div>
            </div>
        </div>
    </div>

    <?php elseif ($role === 'doctor') : ?>
    <!-- ==================== DOCTOR ==================== -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm no-hover">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div>
                        <h5 class="card-title mb-1">Welcome, Dr. <?= esc($name) ?></h5>
                        <p class="text-muted mb-0 small">Here is your clinical overview for today.</p>
                    </div>
                    <span class="badge px-3 py-2 mt-2 mt-md-0" style="background:#dcfce7;color:#16a34a;"><?= esc(date('l, F j, Y')) ?></span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Today's Patients</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Upcoming</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Completed</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted small mb-1">Total Consultations</p>
                    <h3 class="fw-bold mb-0">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Schedule</h6>
                    <h5 class="card-title">My Appointments</h5>
                    <p class="card-text small text-muted">View your full appointment schedule and manage your calendar.</p>
                    <button class="btn btn-sm btn-primary" disabled>View Schedule (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Queue</h6>
                    <h5 class="card-title">Today's Queue</h5>
                    <p class="card-text small text-muted">See the list of patients waiting for your consultation today.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>View Queue (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Records</h6>
                    <h5 class="card-title">Patient Records</h5>
                    <p class="card-text small text-muted">Access and review patient medical history and past visits.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>View Records (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Consultation</h6>
                    <h5 class="card-title">Write Notes</h5>
                    <p class="card-text small text-muted">Add consultation notes, diagnoses, and recommendations for a patient.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>Add Notes (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Prescription</h6>
                    <h5 class="card-title">Prescriptions</h5>
                    <p class="card-text small text-muted">Issue and manage prescriptions for your patients.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>Manage (soon)</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-2">Availability</h6>
                    <h5 class="card-title">My Schedule Settings</h5>
                    <p class="card-text small text-muted">Set your available days and hours for appointments.</p>
                    <button class="btn btn-sm btn-outline-primary" disabled>Set Availability (soon)</button>
                </div>
            </div>
        </div>
    </div>

    <?php else : ?>
    <!-- ==================== CLIENT ==================== -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3 no-hover">
                <div class="card-body">
                    <h5 class="card-title mb-1">Welcome, <?= esc($name) ?></h5>
                    <p class="text-muted small mb-0">From here you can request or review your appointments.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted small mb-2">Book</h6>
                            <h5 class="card-title">New Appointment</h5>
                            <p class="card-text small text-muted">
                                Choose your doctor, date, and time that works best for you.
                            </p>
                            <button class="btn btn-sm btn-primary" disabled>Book appointment (soon)</button>
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
                            <button class="btn btn-sm btn-outline-primary" disabled>View appointments (soon)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #f5f8fa;
        min-height: 100vh;
    }
    .card {
        border: 1px solid #e1e8ed;
        border-left: 4px solid #4a90e2;
        background: white;
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
    .card.no-hover:hover { transform: none; box-shadow: none !important; }
    .btn-primary { background: #4a90e2; border: none; font-weight: 500; color: white; }
    .btn-primary:hover { background: #357abd; color: white; }
    .btn-outline-primary { border: 1px solid #4a90e2; color: #4a90e2; font-weight: 500; background: transparent; }
    .btn-outline-primary:hover { background: #4a90e2; border-color: #4a90e2; color: white; }
</style>
</body>
</html>
