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
        <a href="<?= site_url('/admin/patients') ?>" class="btn btn-sm btn-outline-secondary mt-3 mt-md-0">Back</a>
    </header>

    <section class="mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php $users = $users ?? []; ?>
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
                                    <th scope="col">Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= esc((string) ($user['id'] ?? '')) ?></td>
                                        <td><?= esc($user['name'] ?? '') ?></td>
                                        <td><?= esc($user['email'] ?? '') ?></td>
                                        <td>
                                            <span class="badge <?= ($user['role'] ?? '') === 'admin' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' ?>">
                                                <?= esc(ucfirst($user['role'] ?? 'client')) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($user['created_at'] ?? '-') ?></td>
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
