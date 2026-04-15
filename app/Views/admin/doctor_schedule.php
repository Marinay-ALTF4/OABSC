<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Availability Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="pl-page">
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="pl-title mb-1">Availability Schedule</h4>
            <p class="pl-sub mb-0">Weekly availability of all registered doctors.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('/admin/doctors') ?>" class="pl-btn pl-btn-ghost">
                <i class="bi bi-list-ul me-1"></i>All Doctors
            </a>
            <a href="<?= site_url('/dashboard') ?>" class="pl-btn pl-btn-ghost">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Legend -->
    <div class="d-flex align-items-center gap-3 mb-3" style="font-size:0.78rem;">
        <span class="sched-legend-dot" style="background:#d1fae5;color:#065f46;">Available</span>
        <span class="sched-legend-dot" style="background:#fee2e2;color:#991b1b;">Unavailable</span>
        <span class="sched-legend-dot" style="background:#f1f5f9;color:#94a3b8;">Day Off</span>
    </div>

    <?php
    // Static schedule data keyed by doctor name initials
    $schedules = [
        'Dr. John Smith'    => ['Mon'=>'8AM–12PM','Tue'=>'8AM–12PM','Wed'=>null,'Thu'=>'8AM–12PM','Fri'=>'8AM–12PM','Sat'=>null],
        'Dr. Sarah Johnson' => ['Mon'=>null,'Tue'=>'1PM–5PM','Wed'=>'1PM–5PM','Thu'=>null,'Fri'=>'1PM–5PM','Sat'=>'8AM–12PM'],
        'Dr. Michael Chen'  => ['Mon'=>'8AM–5PM','Tue'=>null,'Wed'=>'8AM–5PM','Thu'=>null,'Fri'=>'8AM–5PM','Sat'=>null],
        'Dr. Emily Davis'   => ['Mon'=>null,'Tue'=>'8AM–12PM','Wed'=>null,'Thu'=>'8AM–12PM','Fri'=>null,'Sat'=>'8AM–12PM'],
    ];
    $days = ['Mon','Tue','Wed','Thu','Fri','Sat'];
    ?>

    <!-- Weekly grid table -->
    <div class="pl-card mb-4">
        <div class="sched-table-wrap">
            <table class="sched-table">
                <thead>
                    <tr>
                        <th class="sched-th-doctor">Doctor</th>
                        <?php foreach ($days as $day): ?>
                            <th><?= $day ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Use DB doctors if available, else fall back to static names
                    $doctorNames = !empty($doctors)
                        ? array_map(fn($d) => $d['name'], $doctors)
                        : array_keys($schedules);
                    foreach ($doctorNames as $name):
                        $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $name), -2))));
                        $sched = $schedules[$name] ?? array_fill_keys($days, null);
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="doc-avatar-sm"><?= esc($initials) ?></div>
                                <div>
                                    <div class="sched-doc-name"><?= esc($name) ?></div>
                                    <?php
                                        $doc = array_values(array_filter($doctors ?? [], fn($d) => $d['name'] === $name))[0] ?? null;
                                        if ($doc && !empty($doc['specialization'])):
                                    ?>
                                        <div class="sched-doc-spec"><?= esc($doc['specialization']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <?php foreach ($days as $day): ?>
                            <td>
                                <?php if (!empty($sched[$day])): ?>
                                    <span class="sched-slot sched-on"><?= esc($sched[$day]) ?></span>
                                <?php else: ?>
                                    <span class="sched-slot sched-off">Day Off</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Doctor cards with full schedule detail -->
    <div class="adm-section-label mb-3">Doctor Schedule Details</div>
    <div class="row g-3">
        <?php foreach ($doctorNames as $name):
            $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', $name), -2))));
            $sched = $schedules[$name] ?? array_fill_keys($days, null);
            $doc = array_values(array_filter($doctors ?? [], fn($d) => $d['name'] === $name))[0] ?? null;
            $availDays = array_keys(array_filter($sched));
        ?>
        <div class="col-md-6 col-lg-3">
            <div class="sched-card">
                <div class="sched-card-top">
                    <div class="doc-avatar-md"><?= esc($initials) ?></div>
                    <div>
                        <div class="sched-card-name"><?= esc($name) ?></div>
                        <div class="sched-card-spec"><?= esc($doc['specialization'] ?? 'Doctor') ?></div>
                    </div>
                </div>
                <div class="sched-card-body">
                    <?php foreach ($days as $day): ?>
                    <div class="sched-day-row">
                        <span class="sched-day-label"><?= $day ?></span>
                        <?php if (!empty($sched[$day])): ?>
                            <span class="sched-day-time sched-on"><?= esc($sched[$day]) ?></span>
                        <?php else: ?>
                            <span class="sched-day-time sched-off">Day Off</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="sched-card-footer">
                    <i class="bi bi-calendar-check me-1"></i>
                    <?= count($availDays) ?> day<?= count($availDays) !== 1 ? 's' : '' ?> / week
                    <?php if ($doc && !empty($doc['phone'])): ?>
                        &nbsp;·&nbsp;<i class="bi bi-telephone me-1"></i><?= esc($doc['phone']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    body { background: #edf2f7; }
    .pl-page { min-height: calc(100vh - 60px); }
    .pl-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
    .pl-sub   { font-size: 0.85rem; color: #64748b; }
    .pl-btn {
        font-size: 0.8rem; font-weight: 600; padding: 7px 16px; border-radius: 10px;
        border: none; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; transition: all 0.15s;
    }
    .pl-btn-ghost { background: white; color: #475569; border: 1px solid #dbe4ef; }
    .pl-btn-ghost:hover { background: #f1f5f9; color: #1e40af; }
    .pl-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden;
    }
    .adm-section-label {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; color: #5a7288;
    }
    .sched-legend-dot {
        font-size: 0.72rem; font-weight: 700; padding: 3px 10px;
        border-radius: 999px;
    }

    /* Table */
    .sched-table-wrap { overflow-x: auto; }
    .sched-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .sched-table thead th {
        padding: 0.75rem 1rem; background: #f8fafc;
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: #5a7288; border-bottom: 1px solid #e2e8f0;
        white-space: nowrap; text-align: center;
    }
    .sched-th-doctor { text-align: left !important; min-width: 200px; }
    .sched-table tbody td {
        padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9;
        text-align: center; vertical-align: middle;
    }
    .sched-table tbody tr:last-child td { border-bottom: none; }
    .sched-table tbody tr:hover { background: #f8fafc; }
    .sched-slot {
        font-size: 0.7rem; font-weight: 600; padding: 3px 8px;
        border-radius: 6px; white-space: nowrap; display: inline-block;
    }
    .sched-on  { background: #d1fae5; color: #065f46; }
    .sched-off { background: #f1f5f9; color: #94a3b8; }
    .sched-doc-name { font-size: 0.85rem; font-weight: 700; color: #0f172a; }
    .sched-doc-spec { font-size: 0.72rem; color: #64748b; }

    /* Doctor cards */
    .sched-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden; height: 100%;
    }
    .sched-card-top {
        display: flex; align-items: center; gap: 0.85rem;
        padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: #f8fafc;
    }
    .doc-avatar-sm {
        width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg,#3b556e,#2e445a);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 700; color: white;
    }
    .doc-avatar-md {
        width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg,#3b556e,#2e445a);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.82rem; font-weight: 700; color: white;
    }
    .sched-card-name { font-size: 0.88rem; font-weight: 700; color: #0f172a; }
    .sched-card-spec { font-size: 0.72rem; color: #64748b; margin-top: 1px; }
    .sched-card-body { padding: 0.5rem 0; }
    .sched-day-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.45rem 1.25rem; border-bottom: 1px solid #f8fafc;
    }
    .sched-day-row:last-child { border-bottom: none; }
    .sched-day-label { font-size: 0.78rem; font-weight: 600; color: #475569; width: 36px; }
    .sched-day-time  { font-size: 0.72rem; font-weight: 600; padding: 2px 8px; border-radius: 6px; }
    .sched-card-footer {
        padding: 0.6rem 1.25rem; background: #f8fafc;
        border-top: 1px solid #f1f5f9; font-size: 0.72rem; color: #64748b;
    }
</style>
</body>
</html>
