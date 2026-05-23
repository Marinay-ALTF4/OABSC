<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';

// Define which permissions are relevant per role
$rolePermGroups = [
    'assistant_admin' => [
        'label' => 'Assistant Admin',
        'icon'  => 'bi-person-badge',
        'color' => '#1e40af',
        'bg'    => '#dbeafe',
        'desc'  => 'Admin-panel access for assistant administrators.',
        'perms' => [
            'view_patients', 'manage_users', 'view_appointments',
            'view_doctors', 'view_reports', 'view_audit_log',
            'access_requests', 'announcements', 'manage_permissions',
        ],
    ],
    'secretary' => [
        'label' => 'Secretary',
        'icon'  => 'bi-person-workspace',
        'color' => '#065f46',
        'bg'    => '#d1fae5',
        'desc'  => 'Front-desk features for clinic secretaries.',
        'perms' => [
            'secretary_appointments', 'secretary_queue', 'secretary_records',
            'secretary_register', 'secretary_schedules', 'secretary_approvals',
        ],
    ],
    'doctor' => [
        'label' => 'Doctor',
        'icon'  => 'bi-heart-pulse',
        'color' => '#7c3aed',
        'bg'    => '#ede9fe',
        'desc'  => 'Clinical features for medical practitioners.',
        'perms' => [
            'doctor_appointments', 'doctor_queue', 'doctor_patient_records',
            'doctor_notes', 'doctor_prescriptions', 'doctor_schedule',
        ],
    ],
    'client' => [
        'label' => 'Client / Patient',
        'icon'  => 'bi-person-heart',
        'color' => '#b45309',
        'bg'    => '#fef3c7',
        'desc'  => 'Patient portal features for registered clients.',
        'perms' => [
            'client_book_appointment', 'client_my_appointments', 'client_profile',
        ],
    ],
    'assistant_secretary' => [
        'label' => 'Assistant Secretary',
        'icon'  => 'bi-person-lines-fill',
        'color' => '#0e7490',
        'bg'    => '#cffafe',
        'desc'  => 'Limited front-desk access for assistant secretaries.',
        'perms' => [
            'secretary_appointments', 'secretary_queue', 'secretary_records',
        ],
    ],
];

// Build permission lookup by code
$permByCode = [];
foreach ($permissions as $p) {
    $permByCode[$p['code']] = $p;
}

// Build role lookup by name
$roleByName = [];
foreach ($roles as $r) {
    $roleByName[$r['name']] = $r;
}
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
                        <p class="pl-sub mb-0">Configure access per role. Changes apply immediately.</p>
                    </div>
                </div>

                <div id="toast-msg" class="perm-toast d-none"></div>

                <?php if (empty($permissions)): ?>
                    <div class="alert alert-info">No permissions defined yet.</div>
                <?php else: ?>

                <!-- Role Tabs -->
                <div class="role-tabs-wrap mb-4">
                    <ul class="role-tabs" id="roleTabs">
                        <?php $first = true; foreach ($rolePermGroups as $roleName => $group): ?>
                        <?php $roleData = $roleByName[$roleName] ?? null; if (! $roleData) continue; ?>
                        <li>
                            <button class="role-tab <?= $first ? 'active' : '' ?>"
                                    data-target="role-panel-<?= $roleName ?>"
                                    style="--tab-color:<?= $group['color'] ?>;--tab-bg:<?= $group['bg'] ?>;">
                                <i class="bi <?= $group['icon'] ?> me-2"></i>
                                <?= esc($group['label']) ?>
                                <span class="role-tab-count"><?= esc((string)($roleCounts[$roleName] ?? 0)) ?></span>
                            </button>
                        </li>
                        <?php $first = false; endforeach; ?>
                    </ul>
                </div>

                <!-- Role Panels -->
                <?php $first = true; foreach ($rolePermGroups as $roleName => $group): ?>
                <?php $roleData = $roleByName[$roleName] ?? null; if (! $roleData) continue; ?>
                <?php $roleId = $roleData['id']; $isAdmin = ($roleName === 'admin'); ?>

                <div id="role-panel-<?= $roleName ?>" class="role-panel <?= $first ? '' : 'd-none' ?>">

                    <!-- Role Header -->
                    <div class="role-panel-header mb-3" style="border-left:4px solid <?= $group['color'] ?>;">
                        <div class="role-panel-icon" style="background:<?= $group['bg'] ?>;color:<?= $group['color'] ?>;">
                            <i class="bi <?= $group['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="role-panel-title"><?= esc($group['label']) ?></div>
                            <div class="role-panel-desc"><?= esc($group['desc']) ?></div>
                        </div>
                        <div class="ms-auto text-end">
                            <div class="role-panel-count"><?= esc((string)($roleCounts[$roleName] ?? 0)) ?></div>
                            <div style="font-size:0.72rem;color:#94a3b8;">user(s)</div>
                        </div>
                    </div>

                    <!-- Permissions for this role -->
                    <div class="pl-card mb-5">
                        <div class="perm-panel-header">
                            <i class="bi bi-toggles me-2"></i>Feature Access
                            <span class="text-muted ms-2" style="font-size:0.75rem;">Toggle to enable/disable. Changes save automatically.</span>
                        </div>
                        <div class="perm-list">
                            <?php foreach ($group['perms'] as $permCode): ?>
                            <?php $perm = $permByCode[$permCode] ?? null; if (! $perm) continue; ?>
                            <?php
                                $hasPermission = in_array($perm['id'], $mapping[$roleId] ?? []);
                                $toggleId      = 'toggle_' . $roleId . '_' . $perm['id'];
                            ?>
                            <div class="perm-row">
                                <div class="perm-row-info">
                                    <div class="perm-code"><?= esc($perm['code']) ?></div>
                                    <?php if ($perm['description']): ?>
                                        <div class="perm-desc"><?= esc($perm['description']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="perm-row-toggle">
                                    <label class="perm-switch" title="<?= $hasPermission ? 'Click to revoke' : 'Click to assign' ?>">
                                        <input
                                            type="checkbox"
                                            id="<?= $toggleId ?>"
                                            class="perm-toggle-input"
                                            data-role-id="<?= $roleId ?>"
                                            data-perm-id="<?= $perm['id'] ?>"
                                            data-role-name="<?= esc($roleName) ?>"
                                            data-perm-code="<?= esc($perm['code']) ?>"
                                            <?= $hasPermission ? 'checked' : '' ?>
                                        >
                                        <span class="perm-slider"></span>
                                    </label>
                                    <span class="perm-status-label <?= $hasPermission ? 'enabled' : 'disabled' ?>" id="label_<?= $toggleId ?>">
                                        <?= $hasPermission ? 'Enabled' : 'Disabled' ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
                <?php $first = false; endforeach; ?>

                <?php endif; ?>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const CSRF_NAME  = '<?= csrf_token() ?>';
let   csrfToken  = '<?= csrf_hash() ?>';
const ASSIGN_URL = '<?= site_url('/admin/permissions/assign') ?>';

// Tab switching
document.querySelectorAll('.role-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.role-panel').forEach(p => p.classList.add('d-none'));
        this.classList.add('active');
        document.getElementById(this.dataset.target).classList.remove('d-none');
    });
});

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast-msg');
    t.textContent = msg;
    t.className = 'perm-toast ' + type;
    t.classList.remove('d-none');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.add('d-none'), 2500);
}

document.querySelectorAll('.perm-toggle-input').forEach(input => {
    input.addEventListener('change', function() {
        const roleId   = this.dataset.roleId;
        const permId   = this.dataset.permId;
        const roleName = this.dataset.roleName;
        const permCode = this.dataset.permCode;
        const action   = this.checked ? 'assign' : 'revoke';
        const toggle   = this;
        const labelEl  = document.getElementById('label_toggle_' + roleId + '_' + permId);

        toggle.disabled = true;

        const body = new URLSearchParams();
        body.append(CSRF_NAME, csrfToken);
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
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            toggle.disabled = false;
            if (data.csrf_token) { csrfToken = data.csrf_token; }
            if (data.success) {
                if (labelEl) {
                    labelEl.textContent = action === 'assign' ? 'Enabled' : 'Disabled';
                    labelEl.className = 'perm-status-label ' + (action === 'assign' ? 'enabled' : 'disabled');
                }
                showToast((action === 'assign' ? '✓ Enabled' : '✕ Disabled') + ': ' + permCode + ' for ' + roleName, 'success');
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
        min-height: calc(100vh - 60px); background: #edf2f7; overflow-x: hidden;
    }
    .adm-sidebar {
        width: 260px; flex-shrink: 0;
        background: rgba(255,255,255,0.55); backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255,255,255,0.6);
        box-shadow: 4px 0 24px rgba(42,106,126,0.08);
        padding: 28px 16px; display: flex; flex-direction: column; gap: 6px;
    }
    .adm-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
    .adm-sidebar-avatar { width:44px;height:44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#e0f0ff;color:#2a6a7e;font-size:1.25rem; }
    .adm-sidebar-name { font-size:0.9rem;font-weight:700;color:#0f172a;margin:0; }
    .adm-sidebar-role { font-size:0.72rem;color:#2a6a7e;text-transform:uppercase;letter-spacing:0.8px; }
    .adm-sidebar-divider { border-color:#cce4ed;margin:10px 0; }
    .adm-nav-item { display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;font-size:0.92rem;font-weight:500;color:#2a6a7e;text-decoration:none;transition:background 0.15s,color 0.15s; }
    .adm-nav-item i { font-size:1.15rem; }
    .adm-nav-item:hover { background:rgba(204,228,237,0.6);color:#164a5c; }
    .adm-nav-item.active { background:#2a6a7e;color:#fff;font-weight:600;box-shadow:0 4px 14px rgba(42,106,126,0.25); }
    .adm-nav-item.nav-disabled { color:#94a3b8!important;cursor:not-allowed;pointer-events:none;opacity:0.55; }
    .adm-main-content { flex:1;padding:32px 28px;min-width:0; }
    .adm-wrapper { width:100%; }
    .pl-title { font-size:1.3rem;font-weight:700;color:#0f172a; }
    .pl-sub   { font-size:0.85rem;color:#64748b; }
    .pl-card  { background:white;border-radius:18px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(15,23,42,0.06);overflow:hidden; }

    /* Role Tabs */
    .role-tabs-wrap { border-bottom: 2px solid #e2e8f0; }
    .role-tabs { display:flex;gap:4px;list-style:none;padding:0;margin:0;flex-wrap:wrap; }
    .role-tab {
        background:none;border:none;padding:10px 18px;
        font-size:0.85rem;font-weight:600;color:#64748b;
        border-bottom:3px solid transparent;margin-bottom:-2px;
        cursor:pointer;display:flex;align-items:center;gap:6px;
        transition:color 0.15s,border-color 0.15s,background 0.15s;
        border-radius:8px 8px 0 0;
    }
    .role-tab:hover { color:var(--tab-color);background:var(--tab-bg); }
    .role-tab.active { color:var(--tab-color);border-bottom-color:var(--tab-color);background:var(--tab-bg); }
    .role-tab-count {
        background:#e2e8f0;color:#475569;
        font-size:0.68rem;font-weight:700;
        padding:1px 7px;border-radius:999px;
    }
    .role-tab.active .role-tab-count { background:var(--tab-color);color:white; }

    /* Role Panel Header */
    .role-panel-header {
        background:white;border-radius:16px;border:1px solid #e2e8f0;
        box-shadow:0 1px 4px rgba(15,23,42,0.05);
        padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;
    }
    .role-panel-icon {
        width:48px;height:48px;border-radius:14px;flex-shrink:0;
        display:flex;align-items:center;justify-content:center;font-size:1.3rem;
    }
    .role-panel-title { font-size:1rem;font-weight:700;color:#0f172a; }
    .role-panel-desc  { font-size:0.8rem;color:#64748b;margin-top:2px; }
    .role-panel-count { font-size:1.4rem;font-weight:800;color:#0f172a;line-height:1; }

    /* Permission List */
    .perm-panel-header {
        padding:14px 18px;font-size:0.85rem;font-weight:700;
        color:#0f172a;border-bottom:1px solid #f1f5f9;background:#f8fafc;
    }
    .perm-list { padding:0; }
    .perm-row {
        display:flex;align-items:center;justify-content:space-between;
        padding:14px 18px;border-bottom:1px solid #f1f5f9;
        transition:background 0.12s;
    }
    .perm-row:last-child { border-bottom:none; }
    .perm-row:hover { background:#f8fafc; }
    .perm-row-info { flex:1; }
    .perm-code { font-weight:600;color:#0f172a;font-size:0.85rem; }
    .perm-desc { font-size:0.75rem;color:#64748b;margin-top:2px; }
    .perm-row-toggle { display:flex;align-items:center;gap:10px;flex-shrink:0; }

    /* Toggle Switch */
    .perm-switch { position:relative;display:inline-block;width:44px;height:24px;cursor:pointer;margin:0; }
    .perm-toggle-input { opacity:0;width:0;height:0;position:absolute; }
    .perm-slider { position:absolute;inset:0;background:#cbd5e1;border-radius:999px;transition:background 0.2s; }
    .perm-slider::before { content:'';position:absolute;width:18px;height:18px;border-radius:50%;background:white;left:3px;top:3px;transition:transform 0.2s;box-shadow:0 1px 3px rgba(0,0,0,0.2); }
    .perm-toggle-input:checked + .perm-slider { background:#10b981; }
    .perm-toggle-input:checked + .perm-slider::before { transform:translateX(20px); }

    /* Status label */
    .perm-status-label { font-size:0.75rem;font-weight:600;min-width:52px;text-align:right; }
    .perm-status-label.enabled  { color:#10b981; }
    .perm-status-label.disabled { color:#94a3b8; }

    /* Toast */
    .perm-toast { position:fixed;bottom:24px;right:24px;background:#0f172a;color:#fff;padding:10px 20px;border-radius:10px;font-size:0.82rem;font-weight:500;box-shadow:0 4px 16px rgba(0,0,0,0.2);z-index:9999; }
    .perm-toast.success { background:#10b981; }
    .perm-toast.error   { background:#ef4444; }
</style>
</body>
</html>
