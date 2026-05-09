    </div><!-- end doc-content -->
</div><!-- end doc-page -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
    body { background: #edf2f7; margin: 0; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .doc-page {
        display: flex;
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        margin-top: 0;
        min-height: calc(100vh - 60px);
        background: #edf2f7;
    }
    .doc-sidebar {
        width: 260px;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-right: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 4px 0 24px rgba(46, 125, 50, 0.08);
        padding: 28px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .doc-content { flex: 1; padding: 32px 28px; min-width: 0; }
    .doc-sidebar-user { display: flex; align-items: center; gap: 10px; padding: 0 8px 4px; }
    .doc-sidebar-avatar {
        width: 44px; height: 44px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: #eaf6ea; color: #2e5c32; font-size: 1.25rem;
        border: 2px solid rgba(46, 92, 50, 0.08);
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
    .doc-nav-item:hover { background: rgba(232, 245, 233, 0.8); color: #1b3a1e; }
    .doc-nav-item.active {
        background: #2e5c32; color: #ffffff;
        font-weight: 600; box-shadow: 0 4px 14px rgba(46, 92, 50, 0.25);
    }
    .doc-nav-badge {
        display: inline-flex; align-items: center; justify-content: center;
        background: #dc2626; color: white;
        width: 22px; height: 22px; border-radius: 50%;
        font-size: 0.7rem; font-weight: 700;
        margin-left: auto;
    }
</style>
</body>
</html>
