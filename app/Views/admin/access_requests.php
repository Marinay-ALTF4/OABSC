<?php $pageTitle = 'Access Requests'; ?>
<?= view('layouts/admin', ['pageTitle' => $pageTitle, 'active' => 'access-requests']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-check-circle me-2"></i>Access Requests</h5>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Pending Requests -->
<div class="adm-section-label mb-3">Pending Requests</div>
<?php if (empty($pending)): ?>
    <div class="alert alert-info">No pending access requests.</div>
<?php else: ?>
<div class="row g-3 mb-4">
    <?php foreach ($pending as $req):
        $userModel = new \App\Models\UserModel();
        $requester = $userModel->find($req['user_id']);
        $label = $req['resource'] === 'patient_records' ? 'Patient Records' : 'Clinic Reports';
    ?>
    <div class="col-12">
        <div class="adm-card d-flex align-items-center justify-content-between flex-wrap gap-2 p-3">
            <div>
                <span class="fw-semibold"><?= esc($requester['name'] ?? '—') ?></span>
                <span class="text-muted small ms-1">(<?= esc($requester['email'] ?? '—') ?>)</span>
                <span class="text-muted small ms-2">is requesting access to</span>
                <span class="badge bg-primary ms-1"><?= esc($label) ?></span>
            </div>
            <div class="d-flex gap-2">
                <form action="<?= site_url('/access-request/approve') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                </form>
                <form action="<?= site_url('/access-request/approve') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $req['id'] ?>">
                    <input type="hidden" name="action" value="deny">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Deny</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- All Requests History -->
<div class="adm-section-label mb-3">Request History</div>
<div class="sec-table-card">
    <table class="sec-table">
        <thead>
            <tr><th>#</th><th>User</th><th>Email</th><th>Resource</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if (empty($all)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No requests found.</td></tr>
            <?php else: ?>
                <?php foreach ($all as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($r['user_name'] ?? '—') ?></td>
                    <td><?= esc($r['user_email'] ?? '—') ?></td>
                    <td><?= esc($r['resource'] === 'patient_records' ? 'Patient Records' : 'Clinic Reports') ?></td>
                    <td>
                        <?php $cls = match($r['status']) {
                            'approved' => 'badge bg-success',
                            'denied'   => 'badge bg-danger',
                            default    => 'badge bg-warning text-dark',
                        }; ?>
                        <span class="<?= $cls ?>"><?= ucfirst(esc($r['status'])) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
