<?= view('doctor/_layout_top', ['pageTitle' => 'Schedule Settings', 'active' => 'schedule']) ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="doc-page-title"><i class="bi bi-clock me-2"></i>My Schedule Settings</h5>
            <p class="doc-page-sub">Set your available days and hours for appointments.</p>
        </div>
    </div>

    <?php
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $savedSchedules = [];
    foreach ($schedules as $s) {
        $savedSchedules[$s['day']] = $s;
    }
    ?>

    <div class="doc-form-card" style="max-width:700px;">
        <div class="doc-form-card-head">
            <i class="bi bi-calendar-week me-2" style="color:#2e5c32;"></i>Weekly Availability
        </div>
        <div class="p-3">
            <form action="<?= site_url('/doctor/schedule/save') ?>" method="post">
                <?= csrf_field() ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($days as $day): ?>
                        <?php $saved = $savedSchedules[$day] ?? null; ?>
                        <div class="doc-schedule-row" id="row_<?= $day ?>">
                            <div style="width:120px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="available_<?= $day ?>"
                                        id="check_<?= $day ?>"
                                        value="1"
                                        <?= $saved ? 'checked' : '' ?>
                                        onchange="toggleDay('<?= $day ?>', this.checked)">
                                    <label class="form-check-label fw-semibold" for="check_<?= $day ?>" style="color:#1b3a1e;"><?= $day ?></label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-grow-1"
                                 id="time_<?= $day ?>"
                                 <?= !$saved ? 'style="opacity:0.4;pointer-events:none;"' : '' ?>>
                                <input type="time" name="start_<?= $day ?>" class="doc-input" style="max-width:130px;"
                                    value="<?= esc($saved['start_time'] ?? '08:00') ?>">
                                <span class="text-muted small">to</span>
                                <input type="time" name="end_<?= $day ?>" class="doc-input" style="max-width:130px;"
                                    value="<?= esc($saved['end_time'] ?? '17:00') ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4">
                    <button type="submit" class="doc-save-btn">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>

<script>
function toggleDay(day, checked) {
    const el = document.getElementById('time_' + day);
    el.style.opacity = checked ? '1' : '0.4';
    el.style.pointerEvents = checked ? 'auto' : 'none';
}
</script>

<?= view('doctor/_layout_bottom') ?>
