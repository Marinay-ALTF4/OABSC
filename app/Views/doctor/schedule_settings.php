<?= view('doctor/_layout_top', ['pageTitle' => 'Schedule Settings', 'active' => 'schedule']) ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">My Schedule Settings</h4>
            <p class="text-muted small mb-0">Set your available days and hours for appointments.</p>
        </div>
    </div>

    <?php
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $savedSchedules = [];
    foreach ($schedules as $s) {
        $savedSchedules[$s['day']] = $s;
    }
    ?>

    <div class="card border-0 shadow-sm p-4" style="max-width:700px;">
        <form action="<?= site_url('/doctor/schedule/save') ?>" method="post">
            <?= csrf_field() ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($days as $day): ?>
                    <?php $saved = $savedSchedules[$day] ?? null; ?>
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div style="width:110px;">
                            <div class="form-check">
                                <input class="form-check-input day-check" type="checkbox"
                                    name="available_<?= $day ?>"
                                    id="check_<?= $day ?>"
                                    value="1"
                                    <?= $saved ? 'checked' : '' ?>
                                    onchange="toggleDay('<?= $day ?>', this.checked)">
                                <label class="form-check-label fw-semibold" for="check_<?= $day ?>"><?= $day ?></label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-grow-1" id="time_<?= $day ?>" <?= !$saved ? 'style="opacity:0.4;pointer-events:none;"' : '' ?>>
                            <input type="time" name="start_<?= $day ?>" class="form-control form-control-sm"
                                value="<?= esc($saved['start_time'] ?? '08:00') ?>" style="max-width:130px;">
                            <span class="text-muted small">to</span>
                            <input type="time" name="end_<?= $day ?>" class="form-control form-control-sm"
                                value="<?= esc($saved['end_time'] ?? '17:00') ?>" style="max-width:130px;">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Save Schedule</button>
            </div>
        </form>
    </div>

<script>
function toggleDay(day, checked) {
    const el = document.getElementById('time_' + day);
    el.style.opacity = checked ? '1' : '0.4';
    el.style.pointerEvents = checked ? 'auto' : 'none';
}
</script>

<?= view('doctor/_layout_bottom') ?>
