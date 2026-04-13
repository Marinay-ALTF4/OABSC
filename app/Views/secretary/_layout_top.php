<?php
$name = session('user_name') ?? 'Secretary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Secretary') ?> — Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="sec-page">

    <!-- Sidebar -->
    <div class="sec-sidebar">
        <div class="sec-sidebar-user">
            <div class="sec-sidebar-avatar"><i class="bi bi-person-circle"></i></div>
            <div>
                <div class="sec-sidebar-name"><?= esc($name) ?></div>
                <div class="sec-sidebar-role">Secretary</div>
            </div>
        </div>
        <hr class="sec-sidebar-divider">
        <a href="<?= site_url('/dashboard') ?>" class="sec-nav-item <?= ($active??'')==='dashboard'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?= site_url('/secretary/appointments') ?>" class="sec-nav-item <?= ($active??'')==='appointments'?'active':'' ?>">
            <i class="bi bi-calendar2-plus"></i> Manage Appointments
        </a>
        <a href="<?= site_url('/secretary/queue') ?>" class="sec-nav-item <?= ($active??'')==='queue'?'active':'' ?>">
            <i class="bi bi-list-ol"></i> Patient Queue
        </a>
        <a href="<?= site_url('/secretary/records') ?>" class="sec-nav-item <?= ($active??'')==='records'?'active':'' ?>">
            <i class="bi bi-folder2-open"></i> Patient Records
        </a>
        <a href="<?= site_url('/secretary/register') ?>" class="sec-nav-item <?= ($active??'')==='register'?'active':'' ?>">
            <i class="bi bi-person-plus"></i> Register New Patient
        </a>
        <a href="<?= site_url('/secretary/schedules') ?>" class="sec-nav-item <?= ($active??'')==='schedules'?'active':'' ?>">
            <i class="bi bi-clock-history"></i> Doctor Schedules
        </a>
        <a href="<?= site_url('/secretary/approvals') ?>" class="sec-nav-item <?= ($active??'')==='approvals'?'active':'' ?>">
            <i class="bi bi-bell"></i> Pending Approvals
        </a>
    </div>

    <!-- Main Content -->
    <div class="sec-content">
