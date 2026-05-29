<?= view('secretary/_layout_top', ['pageTitle' => 'Doctor Schedules', 'active' => 'schedules']) ?>

<div class="mb-4">
    <h5 class="sec-page-title"><i class="bi bi-clock-history me-2"></i>Doctor Schedules</h5>
</div>

<?php if (empty($doctors)): ?>
    <div class="alert alert-info">No doctors registered in the system yet.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($doctors as $i => $d): ?>
        <div class="col-md-6 col-lg-4">
            <div class="sec-doctor-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <?php if (!empty($d['profile_photo'])): ?>
                        <img src="<?= base_url($d['profile_photo']) ?>" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                    <?php else: ?>
                        <div style="width:48px;height:48px;border-radius:50%;background:#e8f5e9;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#2e7d32;"><i class="bi bi-person-circle"></i></div>
                    <?php endif; ?>
                    <div>
                        <div style="font-weight:700;color:#1b3a1e;">Dr. <?= esc(preg_replace('/^Dr\.\s*/i', '', trim((string) ($d['name'] ?? $d['username'] ?? 'Unknown')))) ?></div>
                        <div style="font-size:0.78rem;color:#6aaa70;"><?= esc($d['specialization'] ?? 'General') ?></div>
                        <?php
                            $docSched = $d['schedules'] ?? [];
                            $avail = array_filter($docSched, fn($r) => (int)$r['is_available'] === 1);
                            $availCount = count($avail);
                        ?>
                        <div style="font-size:0.78rem;color:#64748b;margin-top:6px;">
                            <?php if ($availCount > 0): ?>
                                <i class="bi bi-calendar-check me-1"></i><?= $availCount ?> day<?= $availCount !== 1 ? 's' : '' ?> available
                            <?php else: ?>
                                <i class="bi bi-calendar-x me-1"></i>No schedule set
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div style="font-size:0.8rem;color:#475569;">
                    <div><i class="bi bi-award me-1"></i><?= esc($d['degree'] ?? 'MD') ?></div>
                    <div><i class="bi bi-briefcase me-1"></i><?= esc($d['experience'] ?? 'N/A') ?> experience</div>
                    <?php if (!empty($d['phone'])): ?>
                    <div><i class="bi bi-telephone me-1"></i><?= esc($d['phone']) ?></div>
                    <?php endif; ?>
                    <div style="margin-top:8px;">
                        <?php
                            // Prepare modal data
                            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                            $modalSched = [];
                            foreach ($days as $day) {
                                $row = $docSched[$day] ?? null;
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

                            // Avatar colors and initials (small palette)
                            $avatarColors = [
                                ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
                                ['bg'=>'#d1fae5','color'=>'#065f46'],
                                ['bg'=>'#fce7f3','color'=>'#9d174d'],
                                ['bg'=>'#ede9fe','color'=>'#5b21b6'],
                                ['bg'=>'#fef3c7','color'=>'#92400e'],
                                ['bg'=>'#fee2e2','color'=>'#991b1b'],
                                ['bg'=>'#e0f2fe','color'=>'#0369a1'],
                            ];
                            $color = $avatarColors[$i % count($avatarColors)];
                            $words = explode(' ', trim($d['name'] ?? ($d['username'] ?? '')));
                            $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($words, 0, 2))));
                            $doctorDisplayName = preg_replace('/^Dr\.\s*/i', '', trim((string) ($d['name'] ?? $d['username'] ?? 'Unknown')));
                        ?>
                        <button class="btn-view"
                            onclick="openSchedModal(
                                <?= htmlspecialchars(json_encode('Dr. ' . $doctorDisplayName), ENT_QUOTES) ?>,
                                <?= htmlspecialchars(json_encode($d['specialization'] ?? 'Doctor'), ENT_QUOTES) ?>,
                                <?= htmlspecialchars(json_encode($color), ENT_QUOTES) ?>,
                                <?= htmlspecialchars(json_encode($initials), ENT_QUOTES) ?>,
                                <?= htmlspecialchars(json_encode($modalSched), ENT_QUOTES) ?>,
                                <?= $availCount ?>,
                                <?= htmlspecialchars(json_encode($d['phone'] ?? ''), ENT_QUOTES) ?>
                            )">
                            <i class="bi bi-eye me-1"></i>View
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Schedule Detail Modal (Secretary) -->
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

<?= view('secretary/_layout_bottom') ?>
