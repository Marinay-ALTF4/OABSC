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
    <style>
        body {
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
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }
        .card.no-hover:hover {
            transform: none;
            box-shadow: none !important;
        }
        .btn-primary {
            background: #4a90e2;
            border: none;
            font-weight: 500;
            color: white;
        }
        .btn-primary:hover {
            background: #357abd;
            color: white;
        }
        .btn-outline-primary {
            border: 1px solid #4a90e2;
            color: #4a90e2;
            font-weight: 500;
            background: transparent;
        }
        .btn-outline-primary:hover {
            background: #4a90e2;
            border-color: #4a90e2;
            color: white;
        }
    </style>
</head>
<body>
<?= view('header') ?>

<div class="container py-4">
    <?php if ($role === 'admin') : ?>
        <!-- Admin Dashboard -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-3 no-hover">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <h5 class="card-title mb-1">
                                Welcome back, <?= esc($name) ?>
                            </h5>
                            <p class="text-muted mb-0 small">
                                Quick overview of your clinic’s activity today.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview cards -->
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-uppercase text-muted small mb-1">Total appointments</p>
                        <h3 class="fw-bold mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-uppercase text-muted small mb-1">Today’s appointments</p>
                        <h3 class="fw-bold mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-uppercase text-muted small mb-1">Total patients</p>
                        <h3 class="fw-bold mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-uppercase text-muted small mb-1">Doctors available</p>
                        <h3 class="fw-bold mb-0">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-uppercase text-muted small mb-1">Pending requests</p>
                        <h3 class="fw-bold mb-0">0</h3>
                    </div>
                </div>
            </div>
        </div>

    <?php else : ?>
        <!-- Client Dashboard -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h5 class="card-title mb-1">Welcome to our clinic</h5>
                        <p class="text-muted small mb-0">
                            From here you can request or review your appointments.
                        </p>
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
                                <button class="btn btn-sm btn-primary" disabled>Book appointment</button>
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
                                <button class="btn btn-sm btn-outline-primary" disabled>View my appointments</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>