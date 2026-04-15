<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Users List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="pl-page">
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="pl-title mb-1">Users List</h4>
            <p class="pl-sub mb-0">All registered users including admin accounts.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('/admin/patients/add') ?>" class="pl-btn pl-btn-filled"><i class="bi bi-person-plus me-1"></i>Add User</a>
            <a href="<?= site_url('/admin/patients/add-role') ?>" class="pl-btn pl-btn-outline"><i class="bi bi-shield-plus me-1"></i>Add Role</a>
            <a href="<?= site_url('/dashboard') ?>" class="pl-btn pl-btn-ghost"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2 mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="pl-card">
        <?php $users = $users ?? []; ?>
        <?php $currentAdminId = (int) (session('user_id') ?? 0); ?>
        <?php if (empty($users)): ?>
            <div class="text-muted text-center py-5">No users found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table pl-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php $isCurrentAdmin = ((int) ($user['id'] ?? 0) === $currentAdminId); ?>
                            <?php $isDeleted = ! empty($user['deleted_at']); ?>
                            <?php
                                $userRole = strtolower((string) ($user['role'] ?? 'client'));
                                $roleLabel = match ($userRole) {
                                    'assistant_admin' => 'Assistant Admin',
                                    default => ucfirst($userRole),
                                };
                                $roleBadgeStyle = match ($userRole) {
                                    'admin'           => 'background:#fee2e2;color:#991b1b;',
                                    'assistant_admin' => 'background:#fef9c3;color:#854d0e;',
                                    'doctor'          => 'background:#cce4ed;color:#1e5a6e;',
                                    'secretary'       => 'background:#dbeafe;color:#1e40af;',
                                    'client'          => 'background:#d1fae5;color:#065f46;',
                                    default           => 'background:#f1f5f9;color:#475569;',
                                };
                            ?>
                            <tr>
                                <td class="pl-id"><?= esc((string) ($user['id'] ?? '')) ?></td>
                                <td>
                                    <span class="pl-name"><?= esc($user['name'] ?? '') ?></span>
                                    <?php if ($isCurrentAdmin): ?>
                                        <span class="pl-you-badge">You</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pl-email"><?= esc($user['email'] ?? '') ?></td>
                                <td>
                                    <span class="pl-role-badge" style="<?= $roleBadgeStyle ?>"><?= esc($roleLabel) ?></span>
                                </td>
                                <td>
                                    <?php if ($isDeleted): ?>
                                        <span class="pl-status-badge" style="background:#f1f5f9;color:#64748b;">Deleted</span>
                                    <?php else: ?>
                                        <span class="pl-status-badge" style="background:#d1fae5;color:#065f46;">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pl-date"><?= esc($user['created_at'] ?? '—') ?></td>
                                <td class="text-end">
                                    <?php if ($isDeleted): ?>
                                        <form action="<?= site_url('/admin/patients/restore/' . (int) ($user['id'] ?? 0)) ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="pl-action-btn pl-action-restore" onclick="return confirm('Restore this user?');">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="<?= site_url('/admin/patients/edit/' . (int) ($user['id'] ?? 0)) ?>" class="pl-action-btn pl-action-edit">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </a>
                                        <form action="<?= site_url('/admin/patients/delete/' . (int) ($user['id'] ?? 0)) ?>" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="pl-action-btn pl-action-delete"
                                                <?= $isCurrentAdmin ? 'disabled title="Cannot delete your own account"' : '' ?>
                                                onclick="return confirm('Soft delete this user? You can restore it later.');">
                                                <i class="bi bi-trash me-1"></i>Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
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
        font-size: 0.8rem; font-weight: 600; padding: 7px 16px;
        border-radius: 10px; border: none; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: all 0.15s;
    }
    .pl-btn-filled  { background: linear-gradient(135deg,#3b556e,#2e445a); color: #fff; box-shadow: 0 2px 8px rgba(15,23,42,0.18); }
    .pl-btn-filled:hover { opacity: 0.9; color: #fff; }
    .pl-btn-outline { background: #edf3f9; color: #334155; border: 1.5px solid #c4d3e2; }
    .pl-btn-outline:hover { background: #e2ebf4; color: #1e40af; }
    .pl-btn-ghost   { background: white; color: #475569; border: 1px solid #dbe4ef; }
    .pl-btn-ghost:hover { background: #f1f5f9; color: #1e40af; }

    .pl-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden;
    }

    .pl-table { font-size: 0.85rem; }
    .pl-table thead tr { background: #f8fafc; }
    .pl-table thead th {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.6px; color: #5a7288; padding: 0.85rem 1rem;
        border-bottom: 1px solid #e2e8f0; white-space: nowrap;
    }
    .pl-table tbody tr { transition: background 0.12s; }
    .pl-table tbody tr:hover { background: #f8fafc; }
    .pl-table tbody td { padding: 0.8rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .pl-table tbody tr:last-child td { border-bottom: none; }

    .pl-id    { color: #94a3b8; font-size: 0.78rem; font-weight: 600; }
    .pl-name  { font-weight: 600; color: #0f172a; }
    .pl-email { color: #475569; font-size: 0.82rem; }
    .pl-date  { color: #94a3b8; font-size: 0.78rem; }

    .pl-you-badge {
        font-size: 0.65rem; font-weight: 700; background: #fef9c3; color: #854d0e;
        padding: 1px 7px; border-radius: 999px; margin-left: 6px; vertical-align: middle;
    }
    .pl-role-badge, .pl-status-badge {
        font-size: 0.72rem; font-weight: 700; padding: 3px 10px;
        border-radius: 999px; white-space: nowrap;
    }

    .pl-action-btn {
        font-size: 0.75rem; font-weight: 600; padding: 5px 12px;
        border-radius: 8px; border: none; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: all 0.15s; margin-left: 4px;
    }
    .pl-action-edit    { background: #dbeafe; color: #1e40af; }
    .pl-action-edit:hover { background: #bfdbfe; color: #1e3a8a; }
    .pl-action-delete  { background: #fee2e2; color: #991b1b; }
    .pl-action-delete:hover:not(:disabled) { background: #fecaca; color: #7f1d1d; }
    .pl-action-delete:disabled { opacity: 0.45; cursor: not-allowed; }
    .pl-action-restore { background: #d1fae5; color: #065f46; }
    .pl-action-restore:hover { background: #a7f3d0; color: #064e3b; }
</style>
</body>
</html>
