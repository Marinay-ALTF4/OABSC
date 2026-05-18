<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="dashboard-wrapper">
    <div class="adm-page">
        <!-- Sidebar -->
        <?= view('admin/_sidebar', ['sidebarActive' => 'announcements']) ?>

        <!-- Main Content -->
        <div class="adm-main-content">
            <div class="adm-wrapper">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-megaphone me-2"></i>Announcements</h4>
                        <p class="text-muted small mb-0">Post and manage clinic announcements for all users.</p>
                    </div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                        <i class="bi bi-plus-lg me-1"></i> New Announcement
                    </button>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success py-2 mb-3"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger py-2 mb-3"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <?php if (empty($announcements)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-megaphone" style="font-size:3rem;color:#c7d2fe;"></i>
                        <h6 class="mt-3 fw-semibold text-muted">No announcements yet</h6>
                        <p class="text-muted small">Click "New Announcement" to post one.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($announcements as $a): ?>
                        <div class="col-12">
                            <div class="ann-card">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="ann-title"><?= esc($a['title']) ?></div>
                                        <div class="ann-body"><?= nl2br(esc($a['body'])) ?></div>
                                        <div class="ann-meta">
                                            <i class="bi bi-clock me-1"></i><?= esc(date('M j, Y g:i A', strtotime($a['created_at']))) ?>
                                            &nbsp;·&nbsp;
                                            <span class="ann-badge"><?= esc(ucfirst($a['type'] ?? 'info')) ?></span>
                                        </div>
                                    </div>
                                    <form action="<?= site_url('/admin/announcements/delete/' . $a['id']) ?>" method="post" class="flex-shrink-0">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this announcement?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div><!-- end adm-wrapper -->
        </div><!-- end adm-main-content -->
    </div><!-- end adm-page -->
</div><!-- end dashboard-wrapper -->

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('/admin/announcements/add') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Announcement title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Message</label>
                        <textarea name="body" class="form-control" rows="4" placeholder="Write your announcement..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.85rem;">Type</label>
                        <select name="type" class="form-select">
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="appointment">Appointment</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Post Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
/* Admin page layout — same as dashboard */
body { background: #edf2f7; font-family: 'Inter', system-ui, sans-serif; }
.dashboard-wrapper { min-height: calc(100vh - 60px); overflow-x: hidden; }
.adm-page {
    display: flex; width: 100vw; position: relative;
    left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw;
    margin-top: 0; min-height: calc(100vh - 60px); background: #edf2f7;
}
.adm-sidebar {
    width: 260px; flex-shrink: 0;
    background: rgba(255,255,255,0.55); backdrop-filter: blur(16px);
    border-right: 1px solid rgba(255,255,255,0.6);
    box-shadow: 4px 0 24px rgba(42,106,126,0.08);
    padding: 28px 16px; display: flex; flex-direction: column; gap: 6px;
}
.adm-main-content { flex: 1; padding: 32px 28px; min-width: 0; }
.adm-wrapper { width: 100%; }
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
/* Announcement cards */
.ann-card {
    background: #fff; border-radius: 14px; padding: 1.25rem 1.5rem;
    border: 1px solid #e2e8f0; box-shadow: 0 1px 4px rgba(15,23,42,0.05);
    transition: box-shadow 0.2s;
}
.ann-card:hover { box-shadow: 0 4px 14px rgba(15,23,42,0.09); }
.ann-title { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
.ann-body  { font-size: 0.85rem; color: #475569; line-height: 1.6; margin-bottom: 10px; }
.ann-meta  { font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 4px; }
.ann-badge {
    background: #dbeafe; color: #1e40af;
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    padding: 2px 8px; border-radius: 999px; letter-spacing: 0.5px;
}
</style>
</body>
</html>
