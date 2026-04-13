<?= view('secretary/_layout_top', ['pageTitle' => 'Doctor Schedules', 'active' => 'schedules']) ?>

<div class="mb-4">
    <h5 class="sec-page-title"><i class="bi bi-clock-history me-2"></i>Doctor Schedules</h5>
</div>

<?php if (empty($doctors)): ?>
    <div class="alert alert-info">No doctors registered in the system yet.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($doctors as $d): ?>
        <div class="col-md-6 col-lg-4">
            <div class="sec-doctor-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <?php if (!empty($d['profile_photo'])): ?>
                        <img src="<?= base_url($d['profile_photo']) ?>" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                    <?php else: ?>
                        <div style="width:48px;height:48px;border-radius:50%;background:#e8f5e9;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#2e7d32;"><i class="bi bi-person-circle"></i></div>
                    <?php endif; ?>
                    <div>
                        <div style="font-weight:700;color:#1b3a1e;">Dr. <?= esc($d['name']) ?></div>
                        <div style="font-size:0.78rem;color:#6aaa70;"><?= esc($d['specialization'] ?? 'General') ?></div>
                    </div>
                </div>
                <div style="font-size:0.8rem;color:#475569;">
                    <div><i class="bi bi-award me-1"></i><?= esc($d['degree'] ?? 'MD') ?></div>
                    <div><i class="bi bi-briefcase me-1"></i><?= esc($d['experience'] ?? 'N/A') ?> experience</div>
                    <?php if (!empty($d['phone'])): ?>
                    <div><i class="bi bi-telephone me-1"></i><?= esc($d['phone']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= view('secretary/_layout_bottom') ?>
