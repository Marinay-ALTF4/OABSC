<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Specializations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="pl-page">
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="pl-title mb-1">Doctor Specializations</h4>
            <p class="pl-sub mb-0">Doctors grouped by their medical specialization.</p>
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

    <?php if (empty($grouped)): ?>
        <div class="pl-card">
            <div class="text-center text-muted py-5">
                <i class="bi bi-heart-pulse" style="font-size:2rem;opacity:0.3;"></i>
                <p class="mt-2 mb-0" style="font-size:0.875rem;">No doctors registered yet.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($grouped as $spec => $docs): ?>
            <div class="col-md-6 col-lg-4">
                <div class="spec-card">
                    <div class="spec-card-header">
                        <div class="spec-icon"><i class="bi bi-heart-pulse"></i></div>
                        <div>
                            <div class="spec-name"><?= esc($spec) ?></div>
                            <div class="spec-count"><?= count($docs) ?> doctor<?= count($docs) !== 1 ? 's' : '' ?></div>
                        </div>
                    </div>
                    <div class="spec-doctor-list">
                        <?php foreach ($docs as $doc): ?>
                        <div class="spec-doctor-item">
                            <div class="doc-avatar-sm"><?= strtoupper(substr($doc['name'] ?? 'D', 0, 2)) ?></div>
                            <div>
                                <div class="spec-doctor-name"><?= esc($doc['name'] ?? '') ?></div>
                                <div class="spec-doctor-email"><?= esc($doc['email'] ?? '') ?></div>
                            </div>
                            <span class="spec-avail-badge" style="<?= ($doc['available'] ?? false) ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;' ?>">
                                <?= ($doc['available'] ?? false) ? 'Available' : 'Unavailable' ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

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
    .spec-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden; height: 100%;
    }
    .spec-card-header {
        display: flex; align-items: center; gap: 0.85rem;
        padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .spec-icon {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
        background: #cce4ed; color: #1e5a6e;
        display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }
    .spec-name  { font-size: 0.9rem; font-weight: 700; color: #0f172a; }
    .spec-count { font-size: 0.72rem; color: #64748b; margin-top: 1px; }
    .spec-doctor-list { padding: 0.5rem 0; }
    .spec-doctor-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.6rem 1.25rem; border-bottom: 1px solid #f8fafc;
    }
    .spec-doctor-item:last-child { border-bottom: none; }
    .doc-avatar-sm {
        width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg,#3b556e,#2e445a);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 700; color: white;
    }
    .spec-doctor-name  { font-size: 0.82rem; font-weight: 600; color: #0f172a; }
    .spec-doctor-email { font-size: 0.72rem; color: #94a3b8; }
    .spec-avail-badge  {
        font-size: 0.65rem; font-weight: 700; padding: 2px 8px;
        border-radius: 999px; white-space: nowrap; margin-left: auto; flex-shrink: 0;
    }
</style>
</body>
</html>
