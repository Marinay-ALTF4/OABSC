<?php
$role = session('user_role') ?? 'guest';

// Pull real schedules from DB grouped by doctor_id
$scheduleModel = new \App\Models\DoctorScheduleModel();
$allSchedules  = $scheduleModel->findAll();

// Index schedules by doctor_id → day (day column stores full name e.g. "Monday")
$schedByDoctor = [];
foreach ($allSchedules as $row) {
    $schedByDoctor[(int)$row['doctor_id']][ucfirst(strtolower($row['day']))] = $row;
}

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$dayShort = ['Monday'=>'Mon','Tuesday'=>'Tue','Wednesday'=>'Wed','Thursday'=>'Thu','Friday'=>'Fri','Saturday'=>'Sat','Sunday'=>'Sun'];

// Avatar color palette
$avatarColors = [
    ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
    ['bg'=>'#d1fae5','color'=>'#065f46'],
    ['bg'=>'#fce7f3','color'=>'#9d174d'],
    ['bg'=>'#ede9fe','color'=>'#5b21b6'],
    ['bg'=>'#fef3c7','color'=>'#92400e'],
    ['bg'=>'#fee2e2','color'=>'#991b1b'],
    ['bg'=>'#e0f2fe','color'=>'#0369a1'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { background: #edf2f7; font-family: 'Inter', sans-serif; }

        /* ── Layout ── */
        .dashboard-wrapper { width: 100%; }
        .adm-page {
            display: flex;
            width: 100vw;
            position: relative;
            left: 50%; right: 50%;
            margin-left: -50vw; margin-right: -50vw;
            min-height: calc(100vh - 60px);
            background: #edf2f7;
            overflow-x: hidden;
        }
        .adm-main-content { flex: 1; padding: 32px 28px; min-width: 0; }
        .adm-wrapper { width: 100%; }

        /* ── Sidebar (reuse existing styles) ── */
        .adm-sidebar {
            width: 260px; flex-shrink: 0;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(255,255,255,0.6);
            box-shadow: 4px 0 24px rgba(42,106,126,0.08);
            padding: 28px 16px;
            display: flex; flex-direction: column; gap: 6px;
        }
        .adm-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
        .adm-sidebar-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: #e0f0ff; color: #2a6a7e; font-size: 1.25rem;
            border: 2px solid rgba(42,106,126,0.08);
        }
        .adm-sidebar-name  { font-size: 0.9rem; font-weight: 700; color: #0f172a; margin: 0; }
        .adm-sidebar-role  { font-size: 0.72rem; color: #2a6a7e; text-transform: uppercase; letter-spacing: 0.8px; }
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
            background: #2a6a7e; color: #fff;
            font-weight: 600; box-shadow: 0 4px 14px rgba(42,106,126,0.25);
        }

        /* ── Page header ── */
        .pl-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
        .pl-sub   { font-size: 0.85rem; color: #64748b; }

        /* ── Legend ── */
        .legend-pill {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 0.75rem; font-weight: 600;
            padding: 4px 12px; border-radius: 999px;
        }
        .legend-pill .dot { width: 8px; height: 8px; border-radius: 50%; }

        /* ── Summary stats ── */
        .stat-card {
            background: white; border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(15,23,42,0.05);
            padding: 1.1rem 1.4rem;
            display: flex; align-items: center; gap: 1rem;
        }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #0f172a; line-height: 1; }
        .stat-label { font-size: 0.75rem; color: #64748b; margin-top: 2px; }

        /* ── Weekly grid table ── */
        .sched-table-wrap { overflow-x: auto; }
        .sched-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
        .sched-table thead th {
            padding: 0.8rem 1rem; background: #f8fafc;
            font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.6px; color: #5a7288;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap; text-align: center;
        }
        .sched-th-doctor { text-align: left !important; min-width: 220px; padding-left: 1.25rem !important; }
        .sched-table tbody td {
            padding: 0.8rem 1rem; border-bottom: 1px solid #f1f5f9;
            text-align: center; vertical-align: middle;
        }
        .sched-table tbody td:first-child { text-align: left; padding-left: 1.25rem; }
        .sched-table tbody tr:last-child td { border-bottom: none; }
        .sched-table tbody tr:hover { background: #f8fafc; }

        /* ── Slot badges ── */
        .slot-on {
            display: inline-flex; flex-direction: column; align-items: center;
            background: #d1fae5; color: #065f46;
            border-radius: 8px; padding: 4px 10px;
            font-size: 0.68rem; font-weight: 700; line-height: 1.4;
            white-space: nowrap;
        }
        .slot-on .slot-time { font-size: 0.65rem; font-weight: 500; opacity: 0.85; }
        .slot-off {
            display: inline-block;
            background: #f1f5f9; color: #94a3b8;
            border-radius: 8px; padding: 4px 10px;
            font-size: 0.68rem; font-weight: 600;
        }
        .slot-unavail {
            display: inline-block;
            background: #fee2e2; color: #991b1b;
            border-radius: 8px; padding: 4px 10px;
            font-size: 0.68rem; font-weight: 600;
        }

        /* ── Doctor avatar ── */
        .doc-avatar {
            width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700;
        }
        .doc-name { font-size: 0.85rem; font-weight: 700; color: #0f172a; }
        .doc-spec { font-size: 0.7rem; color: #64748b; }

        /* ── View button ── */
        .btn-view {
            font-size: 0.72rem; font-weight: 600;
            padding: 4px 12px; border-radius: 8px;
            border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8;
            cursor: pointer; transition: all 0.15s; white-space: nowrap;
        }
        .btn-view:hover { background: #dbeafe; border-color: #93c5fd; }

        /* ── Modal detail rows ── */
        .day-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.55rem 0; border-bottom: 1px solid #f1f5f9;
        }
        .day-row:last-child { border-bottom: none; }
        .day-label { font-size: 0.82rem; font-weight: 600; color: #475569; width: 90px; }
        .avail-badge {
            background: #dbeafe; color: #1d4ed8;
            border-radius: 999px; padding: 2px 10px;
            font-size: 0.7rem; font-weight: 700;
        }

        /* ── Empty state ── */
        .empty-state {
            text-align: center; padding: 3rem 1rem; color: #94a3b8;
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; }
        .empty-state p { font-size: 0.85rem; margin: 0; }

        /* ── pl-card wrapper ── */
        .pl-card {
            background: white; border-radius: 18px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(15,23,42,0.06);
            overflow: hidden;
        }
    </style>
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <?= view('admin/_sidebar', ['sidebarActive' => 'schedules']) ?>

        <div class="adm-main-content">
            <div class="adm-wrapper">

                <!-- Page header -->
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                    <div>
                        <h4 class="pl-title mb-1">
                            <i class="bi bi-calendar2-check me-2" style="color:#2a6a7e;"></i>Doctor Schedules
                        </h4>
                        <p class="pl-sub mb-0">Weekly availability of all registered doctors.</p>
                    </div>
                    <!-- Legend -->
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                        <span class="legend-pill" style="background:#d1fae5;color:#065f46;">
                            <span class="dot" style="background:#065f46;"></span> Available
                        </span>
                        <span class="legend-pill" style="background:#fee2e2;color:#991b1b;">
                            <span class="dot" style="background:#991b1b;"></span> Unavailable
                        </span>
                        <span class="legend-pill" style="background:#f1f5f9;color:#64748b;">
                            <span class="dot" style="background:#94a3b8;"></span> Day Off
                        </span>
                    </div>
                </div>

                <?php
                // ── Compute stats ──
                $totalDoctors   = count($doctors ?? []);
                $totalAvailDays = 0;
                $doctorsWithSched = 0;
                foreach ($doctors ?? [] as $doc) {
                    $ds = $schedByDoctor[(int)$doc['id']] ?? [];
                    $avail = array_filter($ds, fn($r) => (int)$r['is_available'] === 1);
                    if (count($avail) > 0) $doctorsWithSched++;
                    $totalAvailDays += count($avail);
                }
                $avgDays = $doctorsWithSched > 0 ? round($totalAvailDays / $doctorsWithSched, 1) : 0;
                ?>

                <!-- Summary stats -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8;">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= $totalDoctors ?></div>
                                <div class="stat-label">Total Doctors</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#d1fae5;color:#065f46;">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= $doctorsWithSched ?></div>
                                <div class="stat-label">With Schedule Set</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#ede9fe;color:#5b21b6;">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= $totalAvailDays ?></div>
                                <div class="stat-label">Total Available Days</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon" style="background:#fef3c7;color:#92400e;">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                            <div>
                                <div class="stat-value"><?= $avgDays ?></div>
                                <div class="stat-label">Avg Days / Doctor</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly grid table -->
                <div class="pl-card mb-4">
                    <div class="sched-table-wrap">
                        <table class="sched-table">
                            <thead>
                                <tr>
                                    <th class="sched-th-doctor">Doctor</th>
                                    <?php foreach ($days as $day): ?>
                                        <th><?= $dayShort[$day] ?></th>
                                    <?php endforeach; ?>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($doctors)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted" style="font-size:0.85rem;">
                                        No doctors registered yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($doctors as $i => $doc):
                                    $color   = $avatarColors[$i % count($avatarColors)];
                                    $words   = explode(' ', trim($doc['name']));
                                    $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($words, 0, 2))));
                                    $ds      = $schedByDoctor[(int)$doc['id']] ?? [];

                                    // Build schedule JSON for modal
                                    $modalSched = [];
                                    foreach ($days as $day) {
                                        $row = $ds[$day] ?? null;
                                        if ($row === null) {
                                            $modalSched[] = ['day' => $day, 'status' => 'off', 'time' => ''];
                                        } elseif ((int)$row['is_available'] === 0) {
                                            $modalSched[] = ['day' => $day, 'status' => 'unavail', 'time' => ''];
                                        } else {
                                            $t = (!empty($row['start_time']) && !empty($row['end_time']))
                                                ? date('g:i A', strtotime($row['start_time'])) . '–' . date('g:i A', strtotime($row['end_time']))
                                                : '';
                                            $modalSched[] = ['day' => $day, 'status' => 'on', 'time' => $t];
                                        }
                                    }
                                    $availCount = count(array_filter($ds, fn($r) => (int)$r['is_available'] === 1));
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="doc-avatar" style="background:<?= $color['bg'] ?>;color:<?= $color['color'] ?>;">
                                                <?= esc($initials) ?>
                                            </div>
                                            <div>
                                                <div class="doc-name">Dr. <?= esc($doc['name']) ?></div>
                                                <?php if (!empty($doc['specialization'])): ?>
                                                    <div class="doc-spec"><?= esc($doc['specialization']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <?php foreach ($days as $day):
                                        $row = $ds[$day] ?? null;
                                    ?>
                                    <td>
                                        <?php if ($row === null): ?>
                                            <span class="slot-off">Day Off</span>
                                        <?php elseif ((int)$row['is_available'] === 0): ?>
                                            <span class="slot-unavail">Unavailable</span>
                                        <?php else: ?>
                                            <span class="slot-on">
                                                Available
                                                <?php if (!empty($row['start_time']) && !empty($row['end_time'])): ?>
                                                    <span class="slot-time">
                                                        <?= date('g:i A', strtotime($row['start_time'])) ?>–<?= date('g:i A', strtotime($row['end_time'])) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                    <td>
                                        <button class="btn-view"
                                            onclick="openSchedModal(
                                                <?= htmlspecialchars(json_encode('Dr. ' . $doc['name']), ENT_QUOTES) ?>,
                                                <?= htmlspecialchars(json_encode($doc['specialization'] ?? 'Doctor'), ENT_QUOTES) ?>,
                                                <?= htmlspecialchars(json_encode($color), ENT_QUOTES) ?>,
                                                <?= htmlspecialchars(json_encode($initials), ENT_QUOTES) ?>,
                                                <?= htmlspecialchars(json_encode($modalSched), ENT_QUOTES) ?>,
                                                <?= $availCount ?>,
                                                <?= htmlspecialchars(json_encode($doc['phone'] ?? ''), ENT_QUOTES) ?>
                                            )">
                                            <i class="bi bi-eye me-1"></i>View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Schedule Detail Modal -->
                <div class="modal fade" id="schedModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                        <div class="modal-content" style="border-radius:20px;border:none;box-shadow:0 20px 50px rgba(15,23,42,0.18);">
                            <div class="modal-header border-0 pb-0 px-4 pt-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div id="modal-avatar" style="width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;flex-shrink:0;"></div>
                                    <div>
                                        <div id="modal-name" style="font-size:1rem;font-weight:700;color:#0f172a;"></div>
                                        <div id="modal-spec" style="font-size:0.75rem;color:#64748b;margin-top:2px;"></div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body px-4 pt-3 pb-2">
                                <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.7px;color:#94a3b8;margin-bottom:0.5rem;">Weekly Schedule</div>
                                <div id="modal-days"></div>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex align-items-center gap-2 flex-wrap">
                                <span id="modal-avail-badge" class="avail-badge"></span>
                                <span id="modal-phone" style="font-size:0.75rem;color:#64748b;"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openSchedModal(name, spec, color, initials, sched, availCount, phone) {
    document.getElementById('modal-name').textContent = name;
    document.getElementById('modal-spec').textContent = spec;

    const av = document.getElementById('modal-avatar');
    av.textContent = initials;
    av.style.background = color.bg;
    av.style.color = color.color;

    const slotMap = {
        on:      { cls: 'slot-on',      label: 'Available' },
        off:     { cls: 'slot-off',     label: 'Day Off'   },
        unavail: { cls: 'slot-unavail', label: 'Unavailable' },
    };

    let html = '';
    sched.forEach(function(s) {
        const m = slotMap[s.status] || slotMap.off;
        const timeHtml = s.time ? `<span class="slot-time">${s.time}</span>` : '';
        html += `<div class="day-row">
            <span class="day-label">${s.day}</span>
            <span class="${m.cls}">${m.label}${timeHtml}</span>
        </div>`;
    });
    document.getElementById('modal-days').innerHTML = html;

    document.getElementById('modal-avail-badge').innerHTML =
        `<i class="bi bi-calendar-check me-1"></i>${availCount} day${availCount !== 1 ? 's' : ''}/week`;

    const phoneEl = document.getElementById('modal-phone');
    phoneEl.innerHTML = phone ? `<i class="bi bi-telephone me-1"></i>${phone}` : '';

    new bootstrap.Modal(document.getElementById('schedModal')).show();
}
</script>

<?php echo view('layouts/_chat_widget'); ?>
</body>
</html>
