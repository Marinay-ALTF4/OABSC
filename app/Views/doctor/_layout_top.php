<?php
$name = session('user_name') ?? 'Doctor';
$doctorDisplayName = preg_replace('/^Dr\.\s*/i', '', trim((string) $name));
$doctorLookupName = trim((string) ($name ?? ''));
$active = $active ?? '';

if (! isset($doc_pending_count) && session('user_role') === 'doctor') {
    $apptModel = new \App\Models\AppointmentModel();
    $doc_pending_count = $apptModel->countPendingForDoctor(
        (int) session('user_id'),
        $doctorLookupName
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Doctor Panel') ?> — Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="doc-page">

    <!-- Sidebar -->
    <div class="doc-sidebar">
        <div class="doc-sidebar-user">
            <div class="doc-sidebar-avatar"><i class="bi bi-person-circle"></i></div>
            <div>
                <div class="doc-sidebar-name">Dr. <?= esc($doctorDisplayName !== '' ? $doctorDisplayName : 'Doctor') ?></div>
                <div class="doc-sidebar-role">Doctor</div>
            </div>
        </div>
        <hr class="doc-sidebar-divider">
        <a href="<?= site_url('/dashboard') ?>" class="doc-nav-item <?= $active === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?= site_url('/doctor/appointments') ?>" class="doc-nav-item <?= $active === 'appointments' ? 'active' : '' ?>">
            <i class="bi bi-calendar2-week"></i> My Appointments
            <?php if (!empty($doc_pending_count) && $doc_pending_count > 0): ?>
                <span class="doc-nav-badge"><?= (int) $doc_pending_count ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= site_url('/doctor/queue') ?>" class="doc-nav-item <?= $active === 'queue' ? 'active' : '' ?>">
            <i class="bi bi-list-ol"></i> Today's Queue
        </a>
        <a href="<?= site_url('/doctor/records') ?>" class="doc-nav-item <?= $active === 'records' ? 'active' : '' ?>">
            <i class="bi bi-folder2-open"></i> Patient Records
        </a>
        <a href="<?= site_url('/doctor/notes') ?>" class="doc-nav-item <?= $active === 'notes' ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i> Write Notes
        </a>
        <a href="<?= site_url('/doctor/prescriptions') ?>" class="doc-nav-item <?= $active === 'prescriptions' ? 'active' : '' ?>">
            <i class="bi bi-capsule"></i> Prescriptions
        </a>
        <a href="<?= site_url('/doctor/schedule') ?>" class="doc-nav-item <?= $active === 'schedule' ? 'active' : '' ?>">
            <i class="bi bi-clock"></i> Schedule Settings
        </a>
    </div>

    <!-- Main Content -->
    <div class="doc-content">
