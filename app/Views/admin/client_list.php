<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Patient List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="pl-page">
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="pl-title mb-1">Patient List</h4>
            <p class="pl-sub mb-0">All registered patients (clients) in the clinic.</p>
        </div>
        <a href="<?= site_url('/admin/patients') ?>" class="pl-btn pl-btn-ghost">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2 mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="pl-card">
        <?php $users = $users ?? []; ?>
        <?php if (empty($users)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-people" style="font-size:2rem;opacity:0.3;"></i>
                <p class="mt-2 mb-0" style="font-size:0.875rem;">No patients found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table pl-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php $isDeleted = !empty($user['deleted_at']); ?>
                            <tr>
                                <td class="pl-id"><?= esc((string)($user['id'] ?? '')) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="doc-avatar"><?= strtoupper(substr($user['name'] ?? 'P', 0, 2)) ?></div>
                                        <span class="pl-name"><?= esc($user['name'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td class="pl-email"><?= esc($user['email'] ?? '—') ?></td>
                                <td class="pl-date"><?= esc($user['phone'] ?? '—') ?></td>
                                <td>
                                    <?php if ($isDeleted): ?>
                                        <span class="pl-status-badge" style="background:#f1f5f9;color:#64748b;">Deleted</span>
                                    <?php else: ?>
                                        <span class="pl-status-badge" style="background:#d1fae5;color:#065f46;">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pl-date"><?= esc($user['created_at'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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
    .pl-table { font-size: 0.85rem; }
    .pl-table thead th {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.6px; color: #5a7288; padding: 0.85rem 1rem;
        border-bottom: 1px solid #e2e8f0; white-space: nowrap; background: #f8fafc;
    }
    .pl-table tbody tr:hover { background: #f8fafc; }
    .pl-table tbody td { padding: 0.8rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .pl-table tbody tr:last-child td { border-bottom: none; }
    .pl-id    { color: #94a3b8; font-size: 0.78rem; font-weight: 600; }
    .pl-name  { font-weight: 600; color: #0f172a; }
    .pl-email { color: #475569; font-size: 0.82rem; }
    .pl-date  { color: #64748b; font-size: 0.82rem; }
    .pl-status-badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .doc-avatar {
        width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg,#3b556e,#2e445a);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.7rem; font-weight: 700; color: white;
    }
</style>
</body>
</html>
