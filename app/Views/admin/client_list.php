<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Patient List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="container py-4">
    <header class="page-header mb-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h4 mb-1">Patient List</h1>
            <p class="text-muted small mb-0">All registered patients (clients) in the clinic.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="<?= site_url('/admin/patients') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
        </div>
    </header>

    <section class="mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <?php $users = $users ?? []; ?>
                <?php if (empty($users)): ?>
                    <div class="alert alert-info mb-0">No patients found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php $isDeleted = !empty($user['deleted_at']); ?>
                                    <tr>
                                        <td><?= esc((string) ($user['id'] ?? '')) ?></td>
                                        <td><?= esc($user['name'] ?? '') ?></td>
                                        <td><?= esc($user['email'] ?? '') ?></td>
                                        <td>
                                            <?php if ($isDeleted): ?>
                                                <span class="badge bg-secondary-subtle text-secondary">Deleted</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            <?php endif; ?>
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
