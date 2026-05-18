<?php $pageTitle = 'Announcements'; ?>
<?= view('layouts/admin', ['pageTitle' => $pageTitle, 'active' => 'announcements']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-megaphone me-2"></i>Announcements</h5>
        <p class="text-muted small mb-0">Post and manage clinic announcements for all users.</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
        <i class="bi bi-plus-lg me-1"></i> New Announcement
    </button>
</div>

<?php if (session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2"><?= esc(session('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
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

<style>
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
