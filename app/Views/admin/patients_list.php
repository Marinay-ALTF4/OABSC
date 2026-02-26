<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Patients List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">
    <header class="page-header mb-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h4 mb-1">Patient List</h1>
            <p class="text-muted small mb-0">
                All registered users including admin accounts.
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= site_url('/admin/patients/add') ?>" class="btn btn-sm btn-primary">Add User</a>
            <a href="<?= site_url('/admin/patients') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
    </header>

    <section class="mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success py-2" role="alert">
                        <?= esc(session()->getFlashdata('success')) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger py-2" role="alert">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <?php $users = $users ?? []; ?>
                <?php $currentAdminId = (int) (session('user_id') ?? 0); ?>
                <?php if (empty($users)): ?>
                    <div class="alert alert-info mb-0" role="alert">
                        No users found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Registered</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php $isCurrentAdmin = ((int) ($user['id'] ?? 0) === $currentAdminId); ?>
                                    <?php $isDeleted = ! empty($user['deleted_at']); ?>
                                    <tr>
                                        <td><?= esc((string) ($user['id'] ?? '')) ?></td>
                                        <td>
                                            <?= esc($user['name'] ?? '') ?>
                                            <?php if ($isCurrentAdmin): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis ms-1">Current User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($user['email'] ?? '') ?></td>
                                        <td>
                                            <span class="badge <?= ($user['role'] ?? '') === 'admin' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' ?>">
                                                <?= esc(ucfirst($user['role'] ?? 'client')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($isDeleted): ?>
                                                <span class="badge bg-secondary-subtle text-secondary">Deleted</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($user['created_at'] ?? '-') ?></td>
                                        <td class="text-end">
                                            <?php if ($isDeleted): ?>
                                                <form action="<?= site_url('/admin/patients/restore/' . (int) ($user['id'] ?? 0)) ?>" method="post" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this user?');">Restore</button>
                                                </form>
                                            <?php else: ?>
                                                <a href="<?= site_url('/admin/patients/edit/' . (int) ($user['id'] ?? 0)) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form action="<?= site_url('/admin/patients/delete/' . (int) ($user['id'] ?? 0)) ?>" method="post" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" <?= $isCurrentAdmin ? 'disabled title="Current admin cannot delete this account"' : '' ?> onclick="return confirm('Soft delete this user? You can restore it later.');">Delete</button>
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
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
