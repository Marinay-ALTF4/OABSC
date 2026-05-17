<?php $pageTitle = 'Manage Permissions'; ?>
<?= view('layouts/admin', ['pageTitle' => $pageTitle, 'active' => 'permissions']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Manage Permissions</h5>
        <p class="text-muted small mb-0">Toggle permissions per role. Changes apply immediately.</p>
    </div>
</div>

<div id="toast-msg" class="perm-toast d-none"></div>

<?php if (empty($permissions)): ?>
    <div class="alert alert-info">No permissions defined yet.</div>
<?php else: ?>

<!-- Permission Matrix -->
<div class="perm-panel mb-4">
    <div class="perm-panel-header">
        <i class="bi bi-grid-3x3 me-2"></i>Role–Permission Matrix
        <span class="text-muted ms-2" style="font-size:0.75rem;">Toggle to enable/disable. Changes save automatically.</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="perm-matrix-table">
            <thead>
                <tr>
                    <th class="perm-col-label">Permission</th>
                    <?php foreach ($roles as $role): ?>
                    <th class="text-center perm-col-role">
                        <div class="role-pill-header"><?= esc(str_replace('_', ' ', ucfirst($role['name']))) ?></div>
                        <div class="text-muted" style="font-size:0.68rem;"><?= esc((string) ($roleCounts[$role['name']] ?? 0)) ?> user(s)</div>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions as $perm): ?>
                <tr>
                    <td class="perm-col-label">
                        <div class="perm-code"><?= esc($perm['code']) ?></div>
                        <?php if ($perm['description']): ?>
                        <div class="perm-desc"><?= esc($perm['description']) ?></div>
                        <?php endif; ?>
                    </td>
                    <?php foreach ($roles as $role): ?>
                    <?php
                        $hasPermission = in_array($perm['id'], $mapping[$role['id']] ?? []);
                        $isAdminRole   = $role['name'] === 'admin';
                        $toggleId      = 'toggle_' . $role['id'] . '_' . $perm['id'];
                    ?>
                    <td class="text-center">
                        <label class="perm-switch <?= $isAdminRole ? 'perm-switch-locked' : '' ?>" title="<?= $isAdminRole ? 'Admin always has full access' : ($hasPermission ? 'Click to revoke' : 'Click to assign') ?>">
                            <input
                                type="checkbox"
                                id="<?= $toggleId ?>"
                                class="perm-toggle-input"
                                data-role-id="<?= $role['id'] ?>"
                                data-perm-id="<?= $perm['id'] ?>"
                                data-role-name="<?= esc($role['name']) ?>"
                                data-perm-code="<?= esc($perm['code']) ?>"
                                <?= $hasPermission || $isAdminRole ? 'checked' : '' ?>
                                <?= $isAdminRole ? 'disabled' : '' ?>
                            >
                            <span class="perm-slider"></span>
                        </label>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Permission -->
<div class="perm-panel" style="max-width:480px;">
    <div class="perm-panel-header"><i class="bi bi-plus-circle me-2"></i>Add New Permission</div>
    <div class="p-3">
        <form action="<?= site_url('/admin/permissions/add') ?>" method="post">
            <?= csrf_field() ?>
            <div class="row g-2">
                <div class="col-sm-5">
                    <label class="perm-label">Code</label>
                    <input type="text" name="code" class="perm-input" placeholder="e.g. view_reports" required>
                </div>
                <div class="col-sm-5">
                    <label class="perm-label">Description</label>
                    <input type="text" name="description" class="perm-input" placeholder="Short label">
                </div>
                <div class="col-sm-2 d-flex align-items-end">
                    <button type="submit" class="perm-btn w-100">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

<style>
.perm-panel { background:#fff; border-radius:14px; border:1px solid #e2e8f0; box-shadow:0 1px 4px rgba(15,23,42,0.05); overflow:hidden; }
.perm-panel-header { padding:12px 18px; font-size:0.85rem; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; background:#f8fafc; }
.perm-matrix-table { width:100%; border-collapse:collapse; font-size:0.82rem; }
.perm-matrix-table thead tr { background:#f8fafc; }
.perm-matrix-table th { padding:12px 16px; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.6px; color:#64748b; border-bottom:2px solid #e2e8f0; }
.perm-matrix-table td { padding:12px 16px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.perm-matrix-table tbody tr:last-child td { border-bottom:none; }
.perm-matrix-table tbody tr:hover { background:#f8fafc; }
.perm-col-label { min-width:220px; }
.perm-col-role  { min-width:120px; }
.perm-code { font-weight:600; color:#0f172a; font-size:0.82rem; }
.perm-desc { font-size:0.72rem; color:#64748b; margin-top:2px; }
.role-pill-header { font-size:0.78rem; font-weight:700; color:#0f172a; }

/* Toggle Switch */
.perm-switch { position:relative; display:inline-block; width:44px; height:24px; cursor:pointer; margin:0; }
.perm-switch-locked { opacity:0.5; cursor:not-allowed; }
.perm-toggle-input { opacity:0; width:0; height:0; position:absolute; }
.perm-slider {
    position:absolute; inset:0;
    background:#cbd5e1; border-radius:999px;
    transition:background 0.2s;
}
.perm-slider::before {
    content:''; position:absolute;
    width:18px; height:18px; border-radius:50%;
    background:white; left:3px; top:3px;
    transition:transform 0.2s;
    box-shadow:0 1px 3px rgba(0,0,0,0.2);
}
.perm-toggle-input:checked + .perm-slider { background:#10b981; }
.perm-toggle-input:checked + .perm-slider::before { transform:translateX(20px); }

/* Toast */
.perm-toast {
    position:fixed; bottom:24px; right:24px;
    background:#0f172a; color:#fff;
    padding:10px 20px; border-radius:10px;
    font-size:0.82rem; font-weight:500;
    box-shadow:0 4px 16px rgba(0,0,0,0.2);
    z-index:9999; transition:opacity 0.3s;
}
.perm-toast.success { background:#10b981; }
.perm-toast.error   { background:#ef4444; }

.perm-label { display:block; font-size:0.75rem; font-weight:600; color:#475569; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.4px; }
.perm-input { width:100%; padding:8px 12px; border-radius:10px; border:1px solid #d1d5db; font-size:0.85rem; background:#f9fafb; outline:none; transition:border 0.15s; }
.perm-input:focus { border-color:#2563eb; background:#fff; }
.perm-btn { background:#2a6a7e; color:#fff; border:none; padding:9px 16px; border-radius:10px; font-size:0.85rem; font-weight:600; cursor:pointer; }
.perm-btn:hover { background:#164a5c; }
</style>

<script>
const CSRF_TOKEN = '<?= csrf_hash() ?>';
const CSRF_NAME  = '<?= csrf_token() ?>';

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast-msg');
    t.textContent = msg;
    t.className = 'perm-toast ' + type;
    t.classList.remove('d-none');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.add('d-none'), 2500);
}

const ASSIGN_URL = '<?= site_url('/admin/permissions/assign') ?>';

document.querySelectorAll('.perm-toggle-input').forEach(input => {
    input.addEventListener('change', function() {
        const roleId   = this.dataset.roleId;
        const permId   = this.dataset.permId;
        const roleName = this.dataset.roleName;
        const permCode = this.dataset.permCode;
        const action   = this.checked ? 'assign' : 'revoke';
        const toggle   = this;

        toggle.disabled = true;

        const body = new URLSearchParams();
        body.append(CSRF_NAME, CSRF_TOKEN);
        body.append('role_id', roleId);
        body.append('permission_id', permId);
        body.append('action', action);

        fetch(ASSIGN_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString()
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            toggle.disabled = false;
            // Update CSRF token from response header if available
            if (data.csrf_token) {
                window.__csrfToken = data.csrf_token;
            }
            if (data.success) {
                const label = action === 'assign' ? 'enabled' : 'disabled';
                showToast(`✓ ${permCode} ${label} for ${roleName}`, 'success');
            } else {
                toggle.checked = !toggle.checked; // revert
                showToast('Error: ' + (data.message || 'Failed to save'), 'error');
            }
        })
        .catch(err => {
            toggle.disabled = false;
            toggle.checked = !toggle.checked;
            console.error('Permission toggle error:', err);
            showToast('Network error: ' + err.message, 'error');
        });
    });
});
</script>
