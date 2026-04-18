<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="pl-page">
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="pl-title mb-1">Clinic Settings</h4>
            <p class="pl-sub mb-0">Manage clinic access code.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="pl-btn pl-btn-ghost">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="adm-section-label mb-3">Clinic Settings</div>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="adm-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="adm-card-icon" style="background:#e6f3ef;color:#166a51;"><i class="bi bi-key-fill"></i></div>
                    <div style="flex:1">
                        <div class="adm-card-tag">Security</div>
                        <div class="adm-card-title">Clinic Access Code</div>
                        <div class="adm-card-desc">This code is required during role selection. Share it only with trusted staff.</div>

                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
                        <?php endif; ?>

                        <form action="<?= site_url('/admin/settings') ?>" method="post" class="mt-2">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">New Access Code</label>
                                <input type="password" name="clinic_access_code" class="form-control"
                                    placeholder="Enter new clinic access code" required>
                            </div>
                            <button type="submit" class="adm-btn adm-btn-filled">Update Access Code</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

<style>
    /* Minimal set of adm styles to match patients page */
    .pl-page { min-height: calc(100vh - 60px); }
    .pl-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; }
    .pl-sub   { font-size: 0.85rem; color: #64748b; }
    .pl-btn { font-size: 0.8rem; font-weight: 600; padding: 7px 16px; border-radius: 10px; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.15s; }
    .pl-btn-ghost { background: white; color: #475569; border: 1px solid #dbe4ef; }
    .pl-btn-ghost:hover { background: #f1f5f9; color: #1e40af; }

    .adm-section-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #5a7288; }
    .adm-card { background: rgba(255,255,255,0.96); border-radius: 18px; padding: 22px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,0.06); transition: transform 0.18s ease, box-shadow 0.18s ease; }
    .adm-card-icon  { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-right:14px; }
    .adm-card-tag   { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#5a7288; margin-bottom:6px; }
    .adm-card-title { font-size:0.95rem; font-weight:700; color:#0f172a; margin-bottom:7px; }
    .adm-card-desc  { font-size:0.8rem; color:#334155; margin-bottom:12px; line-height:1.55; }
    .adm-btn { font-size:0.78rem; font-weight:600; padding:7px 18px; border-radius:10px; border:none; cursor:pointer; align-self:flex-start; transition:all 0.18s ease; text-decoration:none; display:inline-block; }
    .adm-btn-filled  { background:linear-gradient(135deg,#2b6b4a,#1a5b3b); color:#fff; box-shadow:0 2px 8px rgba(15,23,42,0.18); }
    .adm-btn-filled:hover { opacity:0.95; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
