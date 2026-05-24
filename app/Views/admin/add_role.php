<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Add Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <?= view('admin/_sidebar', ['sidebarActive' => 'users']) ?>

        <div class="adm-main-content">
            <div class="adm-wrapper">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h4 class="pl-title mb-1">Add Role</h4>
                        <p class="pl-sub mb-0">Add an Assistant Admin or Assistant Secretary.</p>
                    </div>
                    <a href="<?= site_url('/admin/patients/list') ?>" class="pl-btn pl-btn-ghost">
                        <i class="bi bi-arrow-left me-1"></i>Back to List
                    </a>
                </div>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger py-2 mb-3">
                        <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                            <div><?= esc($err) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="pl-card" style="max-width:560px;">
                    <form action="<?= site_url('/admin/patients/add-role') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="au-field">
                            <label class="au-label">Full Name</label>
                            <div class="au-input-wrap">
                                <i class="bi bi-person au-icon"></i>
                                <input type="text" name="name" class="au-input"
                                    value="<?= esc(old('name')) ?>" placeholder="Enter full name" required>
                            </div>
                        </div>

                        <div class="au-field">
                            <label class="au-label">Email Address <span style="font-weight:400;color:#94a3b8;">(for records only)</span></label>
                            <div class="au-input-wrap">
                                <i class="bi bi-envelope au-icon"></i>
                                <input type="email" name="email" class="au-input"
                                    value="<?= esc(old('email')) ?>" placeholder="Enter email" required>
                            </div>
                        </div>

                        <div class="au-field">
                            <label class="au-label">Role</label>
                            <div class="au-input-wrap">
                                <i class="bi bi-shield au-icon"></i>
                                <select name="role" class="au-input" required>
                                    <option value="">— Select Role —</option>
                                    <option value="assistant_admin"      <?= old('role') === 'assistant_admin'      ? 'selected' : '' ?>>Assistant Admin</option>
                                    <option value="assistant_secretary"  <?= old('role') === 'assistant_secretary'  ? 'selected' : '' ?>>Assistant Secretary</option>
                                </select>
                            </div>
                        </div>

                        <div class="au-field">
                            <label class="au-label">Role Password</label>
                            <div class="au-input-wrap">
                                <i class="bi bi-lock au-icon"></i>
                                <input type="password" id="role_password" name="role_password" class="au-input"
                                    placeholder="Min. 8 characters" required>
                                <button type="button" class="au-pw-toggle" onclick="togglePw('role_password', this)"><i class="bi bi-eye"></i></button>
                            </div>
                            <div class="au-hint">Used during the role selection screen after login.</div>
                        </div>

                        <div class="au-field">
                            <label class="au-label">Confirm Role Password</label>
                            <div class="au-input-wrap">
                                <i class="bi bi-lock-fill au-icon"></i>
                                <input type="password" id="role_password_confirm" name="role_password_confirm" class="au-input"
                                    placeholder="Confirm password" required>
                                <button type="button" class="au-pw-toggle" onclick="togglePw('role_password_confirm', this)"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="pl-btn pl-btn-filled">
                                <i class="bi bi-check-lg me-1"></i>Add Role
                            </button>
                            <a href="<?= site_url('/admin/patients/list') ?>" class="pl-btn pl-btn-ghost">Cancel</a>
                        </div>
                    </form>
                </div>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}
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
    .pl-btn-ghost  { background: white; color: #475569; border: 1px solid #dbe4ef; }
    .pl-btn-ghost:hover { background: #f1f5f9; color: #1e40af; }
    .pl-card {
        background: white; border-radius: 18px; border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15,23,42,0.06); padding: 1.75rem;
    }
    .au-field { margin-bottom: 1.1rem; }
    .au-label { font-size: 0.8rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block; }
    .au-input-wrap { position: relative; display: flex; align-items: center; }
    .au-icon { position: absolute; left: 12px; color: #94a3b8; font-size: 0.95rem; pointer-events: none; }
    .au-input {
        width: 100%; padding: 0.6rem 0.9rem 0.6rem 2.2rem;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: 0.875rem; color: #0f172a; background: #fafafa; outline: none;
        transition: border-color 0.15s, box-shadow 0.15s; appearance: auto;
    }
    .au-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); background: white; }
    .au-pw-toggle { position: absolute; right: 10px; background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 0.9rem; padding: 0; }
    .au-pw-toggle:hover { color: #475569; }
    .au-hint { font-size: 0.75rem; color: #94a3b8; margin-top: 4px; }
</style>
<?php echo view('layouts/_chat_widget'); ?>
</body>
</html>
