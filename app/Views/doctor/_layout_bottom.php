    </div><!-- end doc-content -->
</div><!-- end doc-page -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { background: #edf2f7; margin: 0; font-family: 'Inter', sans-serif; }

    /* ── Layout ── */
    .doc-page {
        display: flex;
        width: 100vw;
        position: relative;
        left: 50%; right: 50%;
        margin-left: -50vw; margin-right: -50vw;
        margin-top: 0;
        min-height: calc(100vh - 60px);
        background: #edf2f7;
    }
    .doc-sidebar {
        width: 260px; flex-shrink: 0;
        background: rgba(255,255,255,0.55);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255,255,255,0.6);
        box-shadow: 4px 0 24px rgba(46,125,50,0.08);
        padding: 28px 16px;
        display: flex; flex-direction: column; gap: 6px;
    }
    .doc-content { flex: 1; padding: 32px 28px; min-width: 0; }

    /* ── Sidebar ── */
    .doc-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
    .doc-sidebar-avatar {
        width: 44px; height: 44px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #eaf6ea; color: #2e5c32; font-size: 1.25rem;
        border: 2px solid rgba(46,92,50,0.08);
    }
    .doc-sidebar-name { font-size: 0.9rem; font-weight: 700; color: #1b3a1e; margin: 0; }
    .doc-sidebar-role { font-size: 0.72rem; color: #6aaa70; text-transform: uppercase; letter-spacing: 0.8px; }
    .doc-sidebar-divider { border-color: #d0e8d2; margin: 10px 0; }
    .doc-nav-item {
        display: flex; align-items: center; gap: 12px;
        padding: 12px 16px; border-radius: 12px;
        font-size: 0.92rem; font-weight: 500;
        color: #2e5c32; text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }
    .doc-nav-item i { font-size: 1.15rem; }
    .doc-nav-item:hover { background: rgba(232,245,233,0.8); color: #1b3a1e; }
    .doc-nav-item.active {
        background: #2e5c32; color: #fff;
        font-weight: 600; box-shadow: 0 4px 14px rgba(46,92,50,0.25);
    }
    .doc-nav-badge {
        display: inline-flex; align-items: center; justify-content: center;
        background: #dc2626; color: white;
        width: 22px; height: 22px; border-radius: 50%;
        font-size: 0.7rem; font-weight: 700; margin-left: auto;
    }

    /* ── Page Header ── */
    .doc-page-title { font-size: 1.1rem; font-weight: 700; color: #1b3a1e; margin: 0; }
    .doc-page-sub   { font-size: 0.8rem; color: #6aaa70; margin: 2px 0 0; }

    /* ── Stat Cards ── */
    .doc-stat-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #d0e8d2;
        box-shadow: 0 1px 6px rgba(46,125,50,0.07);
        padding: 14px 16px; height: 100%;
    }
    .doc-stat-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1rem; margin-bottom: 8px;
    }
    .doc-stat-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #6aaa70; }
    .doc-stat-value { font-size: 1.6rem; font-weight: 700; color: #1b3a1e; line-height: 1.1; }
    .doc-stat-sub   { font-size: 0.75rem; color: #6aaa70; margin-top: 2px; }

    /* ── Table Card ── */
    .doc-table-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #d0e8d2;
        box-shadow: 0 1px 6px rgba(46,125,50,0.07);
        overflow: hidden;
    }
    .doc-table-card-head {
        padding: 12px 16px 8px;
        border-bottom: 1px solid #e0f0e1;
        background: #f4f9f4;
    }
    .doc-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .doc-table thead tr { background: #f4f9f4; }
    .doc-table th {
        padding: 10px 14px; font-size: 0.68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.7px;
        color: #6aaa70; border-bottom: 1px solid #e0f0e1; white-space: nowrap;
    }
    .doc-table td {
        padding: 11px 14px; color: #2d3748;
        border-bottom: 1px solid #f0f7f0; vertical-align: middle;
    }
    .doc-table tbody tr:last-child td { border-bottom: none; }
    .doc-table tbody tr:hover { background: #f9fdf9; }

    /* ── Form Card ── */
    .doc-form-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #d0e8d2;
        box-shadow: 0 1px 6px rgba(46,125,50,0.07);
        overflow: hidden;
    }
    .doc-form-card-head {
        padding: 12px 16px;
        border-bottom: 1px solid #e0f0e1;
        background: #f4f9f4;
        font-size: 0.88rem; font-weight: 600; color: #1b3a1e;
    }
    .doc-label {
        display: block; font-size: 0.72rem; font-weight: 600;
        color: #475569; margin-bottom: 4px;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .doc-input {
        width: 100%; padding: 8px 12px; border-radius: 10px;
        border: 1px solid #d0e8d2; font-size: 0.87rem;
        background: #f9fafb; outline: none;
        transition: border 0.15s; font-family: inherit;
    }
    .doc-input:focus { border-color: #2e7d32; background: #fff; }

    /* ── Schedule Row ── */
    .doc-schedule-row {
        display: flex; align-items: center; gap: 16px;
        padding: 12px 14px; border-radius: 12px;
        background: #f4f9f4; border: 1px solid #d0e8d2;
    }

    /* ── List Items (notes / prescriptions) ── */
    .doc-list-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f7f0;
    }
    .doc-list-item:last-child { border-bottom: none; }

    /* ── Buttons ── */
    .doc-save-btn {
        background: #2e5c32; color: #fff; border: none;
        padding: 9px 22px; border-radius: 10px;
        font-size: 0.87rem; font-weight: 600; cursor: pointer;
        font-family: inherit;
    }
    .doc-save-btn:hover { background: #245228; }

    .doc-filter-btn {
        display: inline-block; padding: 6px 16px; border-radius: 10px;
        font-size: 0.82rem; font-weight: 500; text-decoration: none;
        border: 1px solid #d0e8d2; color: #2e5c32; background: #fff;
        transition: background 0.15s, color 0.15s;
    }
    .doc-filter-btn:hover { background: #eaf6ea; color: #1b3a1e; }
    .doc-filter-btn.active { background: #2e5c32; color: #fff; border-color: #2e5c32; font-weight: 600; }

    .doc-action-btn {
        display: inline-block; padding: 5px 12px; border-radius: 8px;
        font-size: 0.78rem; font-weight: 600; cursor: pointer;
        border: none; text-decoration: none; font-family: inherit;
        transition: opacity 0.15s;
    }
    .doc-action-btn:hover { opacity: 0.85; }
    .doc-action-approve { background: #2e5c32; color: #fff; }
    .doc-action-done    { background: #1b3a1e; color: #fff; }
    .doc-action-cancel  { background: #fff; color: #dc2626; border: 1px solid #fca5a5; }
    .doc-action-view    { background: #eaf6ea; color: #2e5c32; border: 1px solid #d0e8d2; }

    /* ── Badges ── */
    .doc-badge {
        display: inline-block; padding: 3px 10px; border-radius: 20px;
        font-size: 0.72rem; font-weight: 600;
    }
    .doc-badge-approved  { background: #dcfce7; color: #166534; }
    .doc-badge-pending   { background: #fef9c3; color: #854d0e; }
    .doc-badge-completed { background: #dbeafe; color: #1e40af; }
    .doc-badge-cancelled { background: #fee2e2; color: #991b1b; }
    .doc-badge-default   { background: #f1f5f9; color: #475569; }

    .doc-count-badge {
        display: inline-flex; align-items: center; justify-content: center;
        background: #2e5c32; color: #fff;
        padding: 3px 10px; border-radius: 20px;
        font-size: 0.72rem; font-weight: 700;
    }

    /* ── Profile Card ── */
    .doc-profile-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #d0e8d2;
        box-shadow: 0 1px 6px rgba(46,125,50,0.07);
        padding: 14px 16px;
        display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
    }
    .doc-profile-avatar {
        width: 46px; height: 46px; border-radius: 14px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #2e5c32; color: #fff; font-weight: 700; font-size: 1rem;
    }
    .doc-profile-name { font-size: 1rem; font-weight: 700; color: #1b3a1e; }
    .doc-profile-meta { font-size: 0.8rem; color: #6aaa70; }

    /* ── Search Input ── */
    .doc-search-input {
        padding: 7px 12px; border-radius: 10px;
        border: 1px solid #d0e8d2; font-size: 0.84rem;
        background: #f9fafb; outline: none; min-width: 240px;
        font-family: inherit; transition: border 0.15s;
    }
    .doc-search-input:focus { border-color: #2e7d32; background: #fff; }
</style>
</body>
</html>
