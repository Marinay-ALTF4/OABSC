<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Permissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <?= view('admin/_sidebar', ['sidebarActive' => 'permissions']) ?>

        <div class="adm-main-content">
            <div class="adm-wrapper">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="pl-title mb-1"><i class="bi bi-shield-lock me-2"></i>Manage Permissions</h4>
                        <p class="pl-sub mb-0">Toggle permissions per role. Changes apply immediately.</p>
                    </div>
                </div>

                <div id="toast-msg" class="perm-toast d-none"></div>

                <?php if (empty($permissions)): ?>
                    <div class="alert alert-info">No permissions defined yet.</div>
                <?php else: ?>

                <!-- Permission Matrix -->
                <div class="pl-card mb-4" style="overflow:visible;">
                    <div class="perm-panel-header">
                        <i class="bi bi-grid-3x3 me-2"></i>Role–Permission Matrix
                        <span class="text-muted ms-2" style="font-size:0.75rem;">Toggle to enable/disable. Changes save automatically.</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="perm-matrix-table">
                            <thead>
                                <tr>
                                    <th class="perm-col-label">Permission</th>
                                    <?php foreach ($roles as $r): ?>
                                    <th class="text-center perm-col-role">
                                        <div class="perm-role-name"><?= esc(str_replace('_', ' ', ucfirst($r['name']))) ?></div>
                                        <div class="text-muted" style="font-size:0.68rem;"><?= esc((string) ($roleCounts[$r['name']] ?? 0)) ?> user(s)</div>
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
                                    <?php foreach ($roles as $r): ?>
                                    <?php
                                        $hasPermission = in_array($perm['id'], $mapping[$r['id']] ?? []);
                                        $isAdminRole   = $r['name'] === 'admin';
                                        $toggleId      = 'toggle_' . $r['id'] . '_' . $perm['id'];
                                    ?>
                                    <td class="text-center">
                                        <label class="perm-switch <?= $isAdminRole ? 'perm-switch-locked' : '' ?>"
                                            title="<?= $isAdminRole ? 'Admin always has full access' : ($hasPermission ? 'Click to revoke' : 'Click to assign') ?>">
                                            <input
                                                type="checkbox"
                                                id="<?= $toggleId ?>"
                                                class="perm-toggle-input"
                                                data-role-id="<?= $r['id'] ?>"
                                                data-perm-id="<?= $perm['id'] ?>"
                                                data-role-name="<?= esc($r['name']) ?>"
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
                <div class="pl-card" style="max-width:480px;padding:0;overflow:hidden;">
                    <div class="perm-panel-header"><i class="bi bi-plus-circle me-2"></i>Add New Permission</div>
                    <div class="p-3">
                        <form action="<?= site_url('/admin/permissions/add') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="row g-2">
                                <div class="col-sm-5">
                                    <label class="au-label">Code</label>
                                    <input type="text" name="code" class="au-input" placeholder="e.g. view_reports" required>
                                </div>
                                <div class="col-sm-5">
                                    <label class="au-label">Description</label>
                                    <input type="text" name="description" class="au-input" placeholder="Short label">
                                </div>
                                <div class="col-sm-2 d-flex align-items-end">
                                    <button type="submit" class="pl-btn pl-btn-filled w-100">Add</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php endif; ?>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            if (data.csrf_token) { window.__csrfToken = data.csrf_token; }
            if (data.success) {
                const label = action === 'assign' ? 'enabled' : 'disabled';
                showToast(`✓ ${permCode} ${label} for ${roleName}`, 'success');
            } else {
                toggle.checked = !toggle.checked;
                showToast('Error: ' + (data.message || 'Failed to save'), 'error');
            }
        })
        .catch(err => {
            toggle.disabled = false;
            toggle.checked = !toggle.checked;
            showToast('Network error: ' + err.message, 'error');
        });
    });
});
</script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { background: #edf2f7; font-family: 'Inter', sans-serif; }
    .dashboard-wrapper { width: 100%; }
    .adm-page {
        display: flex; width: 100vw; position: relative;
        left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw;
        margin-top: 0; min-height: calc(100vh - 60px);
        background: #edf2f7; overflow-x: hidden;
    }
    .adm-sidebar {
        width: 260px; flex-shrink: 0;
        background: rgba(255,255,255,0.55); backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255,255,255,0.6);
        box-shadow: 4px 0 24px rgba(42,106,126,0.08);
        padding: 28px 16px; display: flex; flex-direction: column; gap: 6px;
    }
    .adm-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
    .adm-sidebar-avatar {
        width: 44px; height: 44px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #e0f0ff; color: #2a6a7e; font-size: 1.25rem;
        border: 2px solid rgba(42,106,126,0.08);
    }
    .adm-sidebar-name { font-size: 0.9rem; font-weight: 700; color: #0f172a; margin: 0; }
    .adm-sidebar-role { font-size: 0.72rem; color: #2a6a7e; text-transform: uppercase; letter-spacing: 0.8px; }
    .adm-sidebar-divider { border-color: #cce4ed; margin: 10px 0; }
    .adm-nav-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; border-radius: 12px;
        font-size: 0.92rem; font-weight: 500;
        color: #2a6a7e; text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }
    .adm-nav-item i { font-size: 1.15rem; }
    .adm-nav-item:hover { background: rgba(204,228,237,0.6); color: #164a5c; }
    .adm-nav-item.active {
        background: #2a6a7e; color: #ffffff;
        font-weight: 600; box-shadow: 0 4px 14px rgba(42,106,126,0.25);
    }
    .adm-main-content { flex: 1; padding: 32px 28px; min-width: 0; }
    .adm-wrapper { width: 100%; }
    .pl-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
    .pl-sub   { font-size: 0.85rem; color: #64748b; }
    .pl-btn {
        font-size: 0.8rem; font-weight: 600; padding: 7px 16px;
        border-radius: 10px; border: none; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: all 0.15s;
    }
    .pl-btn-filled { background: linear-gradient(135deg,#3b556e,#2e445a); color: #fff; box-shadow: 0 2px 8px rgba(15,23,42,0.18); }
    .pl-btn-filled:hover { opacity: 0.9; color: #fff; }
    .pl-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); overflow: hidden;
    }
    /* Permission matrix */
    .perm-panel-header {
        padding: 14px 18px; font-size: 0.85rem; font-weight: 700;
        color: #0f172a; border-bottom: 1px solid #f1f5f9; background: #f8fafc;
    }
    .perm-matrix-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .perm-matrix-table thead tr { background: #f8fafc; }
    .perm-matrix-table th {
        padding: 12px 16px; font-size: 0.72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.6px; color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }
    .perm-matrix-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .perm-matrix-table tbody tr:last-child td { border-bottom: none; }
    .perm-matrix-table tbody tr:hover { background: #f8fafc; }
    .perm-col-label { min-width: 220px; }
    .perm-col-role  { min-width: 120px; }
    .perm-code { font-weight: 600; color: #0f172a; font-size: 0.82rem; }
    .perm-desc { font-size: 0.72rem; color: #64748b; margin-top: 2px; }
    .perm-role-name { font-size: 0.78rem; font-weight: 700; color: #0f172a; }
    /* Toggle Switch */
    .perm-switch { position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer; margin: 0; }
    .perm-switch-locked { opacity: 0.5; cursor: not-allowed; }
    .perm-toggle-input { opacity: 0; width: 0; height: 0; position: absolute; }
    .perm-slider {
        position: absolute; inset: 0;
        background: #cbd5e1; border-radius: 999px;
        transition: background 0.2s;
    }
    .perm-slider::before {
        content: ''; position: absolute;
        width: 18px; height: 18px; border-radius: 50%;
        background: white; left: 3px; top: 3px;
        transition: transform 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    .perm-toggle-input:checked + .perm-slider { background: #10b981; }
    .perm-toggle-input:checked + .perm-slider::before { transform: translateX(20px); }
    /* Toast */
    .perm-toast {
        position: fixed; bottom: 24px; right: 24px;
        background: #0f172a; color: #fff;
        padding: 10px 20px; border-radius: 10px;
        font-size: 0.82rem; font-weight: 500;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        z-index: 9999; transition: opacity 0.3s;
    }
    .perm-toast.success { background: #10b981; }
    .perm-toast.error   { background: #ef4444; }
    /* Form inputs */
    .au-label { display: block; font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.4px; }
    .au-input {
        width: 100%; padding: 8px 12px; border-radius: 10px;
        border: 1.5px solid #e2e8f0; font-size: 0.85rem;
        background: #fafafa; outline: none; transition: border 0.15s;
    }
    .au-input:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }
</style>
</body>
</html>
