<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="pl-page">
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="pl-title mb-1">Patients</h4>
            <p class="pl-sub mb-0">Manage patient records: view list, search, and review appointment history.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="pl-btn pl-btn-ghost">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="adm-section-label mb-3">Manage Patient Records</div>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-people-fill"></i></div>
                <div class="adm-card-tag">Records</div>
                <div class="adm-card-title">View Patient List</div>
                <div class="adm-card-desc">See all patients registered in the clinic.</div>
                <a href="<?= site_url('/admin/patients/clients') ?>" class="adm-btn adm-btn-filled">Open</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#b8d8e4;color:#1e5a6e;"><i class="bi bi-search"></i></div>
                <div class="adm-card-tag">Search</div>
                <div class="adm-card-title">Search Patient</div>
                <div class="adm-card-desc">Quickly find a patient by name or ID.</div>
                <button class="adm-btn adm-btn-disabled" disabled>Search (soon)</button>
            </div>
        </div>
        <div class="col-md-3">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#a4ccd8;color:#164a5c;"><i class="bi bi-clock-history"></i></div>
                <div class="adm-card-tag">History</div>
                <div class="adm-card-title">Appointment History</div>
                <div class="adm-card-desc">Review a patient's visit and booking history.</div>
                <a href="<?= site_url('/admin/patients/history') ?>" class="adm-btn adm-btn-outline">Open</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="adm-card">
                <div class="adm-card-icon" style="background:#4e8a9e;color:#e0f4fa;"><i class="bi bi-pencil-square"></i></div>
                <div class="adm-card-tag">Edit</div>
                <div class="adm-card-title">Edit Patient Info</div>
                <div class="adm-card-desc">Update contact details and basic information.</div>
                <a href="<?= site_url('/admin/patients/list') ?>" class="adm-btn adm-btn-outline">Open</a>
            </div>
        </div>
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
        font-size: 0.8rem; font-weight: 600; padding: 7px 16px; border-radius: 10px;
        border: none; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; transition: all 0.15s;
    }
    .pl-btn-ghost { background: white; color: #475569; border: 1px solid #dbe4ef; }
    .pl-btn-ghost:hover { background: #f1f5f9; color: #1e40af; }

    .adm-section-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #5a7288; }

    .adm-card {
        background: rgba(255,255,255,0.96); border-radius: 18px; padding: 24px 22px;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,0.06);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        height: 100%; display: flex; flex-direction: column;
    }
    .adm-card:hover { transform: translateY(-2px); box-shadow: 0 7px 18px rgba(15,23,42,0.12); }
    .adm-card-icon  { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:14px; }
    .adm-card-tag   { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#5a7288; margin-bottom:5px; }
    .adm-card-title { font-size:0.95rem; font-weight:700; color:#0f172a; margin-bottom:7px; }
    .adm-card-desc  { font-size:0.8rem; color:#334155; flex:1; margin-bottom:18px; line-height:1.55; }

    .adm-btn { font-size:0.78rem; font-weight:600; padding:7px 18px; border-radius:10px; border:none; cursor:pointer; align-self:flex-start; transition:all 0.18s ease; text-decoration:none; display:inline-block; }
    .adm-btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 3px 10px rgba(15,23,42,0.18); }
    .adm-btn-filled  { background:linear-gradient(135deg,#3b556e,#2e445a); color:#fff; box-shadow:0 2px 8px rgba(15,23,42,0.18); }
    .adm-btn-filled:hover { opacity:0.9; color:#fff; }
    .adm-btn-outline { background:#edf3f9; color:#334155; border:1.5px solid #c4d3e2 !important; }
    .adm-btn-outline:hover { background:#e2ebf4; }
    .adm-btn-disabled { background:#f1f5f9; color:#8aa0b3; cursor:not-allowed; border:1px solid #d2dde8 !important; }
</style>
</body>
</html>
