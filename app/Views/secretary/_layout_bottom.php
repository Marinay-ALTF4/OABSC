    </div><!-- end sec-content -->
</div><!-- end sec-page -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background: #edf2f7; margin: 0; }
    .sec-page { display: flex; min-height: calc(100vh - 60px); }
    .sec-sidebar {
        width: 250px; flex-shrink: 0;
        background: rgba(255,255,255,0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255,255,255,0.7);
        box-shadow: 4px 0 20px rgba(46,125,50,0.07);
        padding: 24px 14px;
        display: flex; flex-direction: column; gap: 4px;
    }
    .sec-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
    .sec-sidebar-avatar { font-size: 2rem; color: #2e5c32; }
    .sec-sidebar-name { font-size: 0.88rem; font-weight: 700; color: #1b3a1e; }
    .sec-sidebar-role { font-size: 0.72rem; color: #6aaa70; text-transform: uppercase; letter-spacing: 0.8px; }
    .sec-sidebar-divider { border-color: #d0e8d2; margin: 10px 0; }
    .sec-nav-item {
        display: flex; align-items: center; gap: 12px;
        padding: 11px 14px; border-radius: 12px;
        font-size: 0.88rem; font-weight: 500;
        color: #2e5c32; text-decoration: none;
        transition: background 0.15s, color 0.15s;
    }
    .sec-nav-item i { font-size: 1.1rem; }
    .sec-nav-item:hover { background: rgba(232,245,233,0.85); color: #1b3a1e; }
    .sec-nav-item.active { background: #2e5c32; color: #fff; font-weight: 600; box-shadow: 0 4px 12px rgba(46,92,50,0.22); }
    .sec-content { flex: 1; padding: 32px 28px; min-width: 0; }
    .sec-page-title { font-size: 1.1rem; font-weight: 700; color: #1b3a1e; margin: 0; }
    .sec-table-card { background: #fff; border-radius: 16px; border: 1px solid #d0e8d2; box-shadow: 0 1px 6px rgba(46,125,50,0.07); overflow: hidden; }
    .sec-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
    .sec-table thead tr { background: #f4f9f4; }
    .sec-table th { padding: 10px 14px; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #6aaa70; border-bottom: 1px solid #e0f0e1; white-space: nowrap; }
    .sec-table td { padding: 11px 14px; color: #2d3748; border-bottom: 1px solid #f0f7f0; vertical-align: middle; }
    .sec-table tbody tr:last-child td { border-bottom: none; }
    .sec-table tbody tr:hover { background: #f9fdf9; }
    .sec-form-card { background: #fff; border-radius: 16px; border: 1px solid #d0e8d2; box-shadow: 0 1px 6px rgba(46,125,50,0.07); padding: 28px; max-width: 520px; }
    .sec-label { display: block; font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.4px; }
    .sec-input { width: 100%; padding: 9px 13px; border-radius: 10px; border: 1px solid #d1d5db; font-size: 0.87rem; background: #f9fafb; outline: none; transition: border 0.15s; }
    .sec-input:focus { border-color: #2e7d32; background: #fff; }
    .sec-save-btn { background: #2e5c32; color: #fff; border: none; padding: 10px 26px; border-radius: 10px; font-size: 0.87rem; font-weight: 600; cursor: pointer; }
    .sec-save-btn:hover { background: #245228; }
    .sec-doctor-card { background: #fff; border-radius: 14px; border: 1px solid #d0e8d2; padding: 20px; box-shadow: 0 1px 5px rgba(46,125,50,0.06); }
</style>
</body>
</html>
