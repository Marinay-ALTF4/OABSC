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

<div class="container py-5">
    <div class="adm-wrapper">

        <!-- Banner -->
        <div class="adm-banner mb-4">
            <div>
                <div class="adm-banner-label">Admin Panel</div>
                <h4 class="adm-banner-name">Patients</h4>
                <p class="adm-banner-sub">Manage patient records: view list, search, and review appointment history.</p>
            </div>
            <div class="adm-banner-date">
                <i class="bi bi-calendar3 me-1"></i><?= esc(date('l, F j, Y')) ?>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="adm-section-label mb-3">Manage Patient Records</div>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="adm-card">
                    <div class="adm-card-icon" style="background:#cce4ed;color:#2a6a7e;"><i class="bi bi-people-fill"></i></div>
                    <div class="adm-card-tag">Records</div>
                    <div class="adm-card-title">View Patient List</div>
                    <div class="adm-card-desc">See all patients registered in the clinic.</div>
                    <a href="<?= site_url('/admin/patients/list') ?>" class="adm-btn adm-btn-filled">Open</a>
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
                    <button class="adm-btn adm-btn-disabled" disabled>History (soon)</button>
                </div>
            </div>
            <div class="col-md-3">
                <div class="adm-card">
                    <div class="adm-card-icon" style="background:#4e8a9e;color:#e0f4fa;"><i class="bi bi-pencil-square"></i></div>
                    <div class="adm-card-tag">Edit</div>
                    <div class="adm-card-title">Edit Patient Info</div>
                    <div class="adm-card-desc">Update contact details and basic information.</div>
                    <button class="adm-btn adm-btn-disabled" disabled>Edit (soon)</button>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    html, body { background: #fce8ec !important; min-height: 100vh; font-family: 'Inter', sans-serif; margin: 0; padding: 0; }

    .adm-wrapper { background: #4e8a9e; border-radius: 24px; padding: 28px; }

    .adm-banner {
        background: linear-gradient(135deg, #5a9aae 0%, #3d7a8e 100%);
        border-radius: 20px; padding: 28px 32px;
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 12px;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .adm-banner-label { font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:1.3px; color:#b8dce8; margin-bottom:5px; }
    .adm-banner-name  { font-size:1.4rem; font-weight:700; color:#ffffff; margin:0 0 5px; letter-spacing:-0.3px; }
    .adm-banner-sub   { font-size:0.84rem; color:#c8e8f4; margin:0; }
    .adm-banner-date  { font-size:0.8rem; font-weight:500; color:#e0f4fa; background:rgba(255,255,255,0.15); padding:8px 18px; border-radius:20px; white-space:nowrap; border:1px solid rgba(255,255,255,0.25); }

    .adm-section-label { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:#c8e8f4; }

    .adm-card {
        background: rgba(255,255,255,0.92);
        border-radius: 18px; padding: 24px 22px;
        border: 1px solid rgba(255,255,255,0.6);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%; display: flex; flex-direction: column;
    }
    .adm-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,0.14); }
    .adm-card-icon { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; margin-bottom:14px; }
    .adm-card-tag   { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#4e8a9e; margin-bottom:5px; }
    .adm-card-title { font-size:0.95rem; font-weight:700; color:#0d2a35; margin-bottom:7px; }
    .adm-card-desc  { font-size:0.8rem; color:#2a5a6e; flex:1; margin-bottom:18px; line-height:1.55; }

    .adm-btn { font-size:0.78rem; font-weight:600; padding:7px 18px; border-radius:10px; border:none; cursor:pointer; align-self:flex-start; transition:all 0.18s ease; text-decoration:none; display:inline-block; }
    .adm-btn:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 4px 14px rgba(0,0,0,0.2); }
    .adm-btn-filled  { background:linear-gradient(135deg,#4e8a9e 0%,#3a7088 100%); color:#fff; box-shadow:0 2px 10px rgba(0,0,0,0.2); }
    .adm-btn-filled:hover { background:linear-gradient(135deg,#3a7088 0%,#2a5a6e 100%); color:#fff; }
    .adm-btn-disabled { background:#f0f4f6; color:#8ab0be; cursor:not-allowed; border:1px solid #c8dce4 !important; }
</style>
</body>
</html>
