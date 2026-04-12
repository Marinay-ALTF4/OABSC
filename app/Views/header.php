<?php
$role = session('user_role') ?? 'guest';
$name = session('user_name') ?? 'User';
$roleLabel = ucfirst((string) $role);
if ($role === 'assistant_admin') {
    $roleLabel = 'Assistant Admin';
}
$isDashboardPage = url_is('dashboard');
$isPatientsPage = url_is('admin/patients*');
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= site_url('/dashboard') ?>">
            <img src="/OABSC/images/logo.png" alt="Clinic Logo" style="width: 32px; height: 32px; object-fit: contain;">
            <span>Clinic Appointment System</span>
        </a>

        <?php if ($role === 'admin') : ?>
            <ul class="navbar-nav flex-row align-items-center gap-1 mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isDashboardPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                
            </ul>
        <?php elseif ($role === 'assistant_admin') : ?>
            <ul class="navbar-nav flex-row align-items-center gap-1 mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isDashboardPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="#" onclick="return false;">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="#" onclick="return false;">Patients</a>
                </li>
            </ul>
        <?php elseif ($role === 'secretary') : ?>
            <ul class="navbar-nav flex-row align-items-center gap-1 mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isDashboardPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="#" onclick="return false;">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-2" href="#" onclick="return false;">Patients</a>
                </li>
            </ul>
        <?php elseif ($role === 'doctor') : ?>
            <ul class="navbar-nav flex-row align-items-center gap-1 mb-0 ms-4">
                <li class="nav-item">
                    <a class="nav-link px-2 <?= $isDashboardPage ? 'active fw-semibold' : '' ?>" href="<?= site_url('/dashboard') ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    
                </li>
                <li class="nav-item">
                </li>
            </ul>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <?php if ($role === 'client'): ?>
            <div class="position-relative" id="notif-bell-wrap">
                <button class="notif-bell-btn" onclick="toggleNotifDropdown()" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notif-dot d-none" id="notif-dot"></span>
                </button>
                <div class="notif-dropdown d-none" id="notif-dropdown">
                    <div class="notif-dropdown-header">
                        <span class="fw-bold" style="font-size:0.85rem;">Notifications</span>
                        <button class="notif-mark-all-sm" onclick="markAllRead()">Mark all read</button>
                    </div>
                    <div id="notif-dropdown-list" style="max-height:320px;overflow-y:auto;"></div>
                </div>
            </div>
            <?php endif; ?>
            <div class="dropdown">
                <button class="btn btn-account btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= esc($roleLabel) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end account-menu">
                    <li><a class="dropdown-item" href="<?= site_url('/profile') ?>">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('/logout') ?>">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    html, body {
        margin: 0;
        padding: 0;
        font-family: 'Inter', sans-serif;
        background: #edf2f7 !important;
    }
    .navbar {
        margin-top: 0;
        background: #ffffff !important;
        border-bottom: 1px solid #dbe4ef !important;
        box-shadow: 0 1px 10px rgba(15,23,42,0.08) !important;
    }
    .navbar-brand span {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
        letter-spacing: -0.1px;
    }
    .nav-link {
        font-size: 0.875rem;
        color: #475569 !important;
        font-weight: 500;
        border-radius: 8px;
        transition: background 0.15s, color 0.15s;
    }
    .nav-link:hover, .nav-link.active {
        color: #1e3a8a !important;
        background: #eaf0ff;
    }
    .nav-link.active.fw-semibold {
        font-weight: 600 !important;
    }
    .btn-account {
        font-size: 0.78rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 5px 14px;
        border-color: #93b0f2;
        color: #1e3a8a;
        background: #ffffff;
    }
    .btn-account:hover,
    .btn-account:focus,
    .btn-account:active,
    .btn-account.show {
        background: #eaf0ff;
        border-color: #6f94ea;
        color: #1e40af;
    }
    .account-menu {
        border-radius: 10px;
        border: 1px solid #dbe4ef;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        padding: 0.35rem;
    }
    .account-menu .dropdown-item {
        font-size: 0.84rem;
        border-radius: 6px;
        font-weight: 500;
    }
    .account-menu .dropdown-item:hover {
        background: #eef3ff;
        color: #1e3a8a;
    }

    /* Notification Bell */
    .notif-bell-btn {
        position: relative;
        background: #f1f5f9;
        border: 1px solid #dbe4ef;
        border-radius: 10px;
        width: 36px; height: 36px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; color: #475569; cursor: pointer;
        transition: all 0.15s;
    }
    .notif-bell-btn:hover { background: #e2e8f0; color: #1e3a8a; }
    .notif-dot {
        position: absolute; top: 5px; right: 5px;
        width: 8px; height: 8px; border-radius: 50%;
        background: #ef4444; border: 2px solid white;
    }
    .notif-dropdown {
        position: absolute; right: 0; top: calc(100% + 8px);
        width: 320px; background: white;
        border: 1px solid #e2e8f0; border-radius: 14px;
        box-shadow: 0 8px 28px rgba(15,23,42,0.14);
        z-index: 9999;
    }
    .notif-dropdown-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9;
    }
    .notif-mark-all-sm {
        background: none; border: none; color: #3b82f6;
        font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0;
    }
    .notif-mark-all-sm:hover { text-decoration: underline; }
</style>

<script>
(function() {
    const STORAGE_KEY = 'oabsc_notifications_read';

    const defaultNotifs = [
        { id: 1, type: 'reminder',  icon: 'bi-alarm',           color: '#3b82f6', bg: '#eff6ff', title: 'Appointment Reminder',       body: 'You have an appointment tomorrow. Please be on time.',         time: '2 hours ago' },
        { id: 2, type: 'status',    icon: 'bi-check-circle',    color: '#10b981', bg: '#f0fdf4', title: 'Appointment Confirmed',       body: 'Your appointment with Dr. Santos has been confirmed.',         time: 'Yesterday' },
        { id: 3, type: 'status',    icon: 'bi-x-circle',        color: '#ef4444', bg: '#fff1f2', title: 'Appointment Cancelled',       body: 'Your appointment on March 10 was cancelled by the clinic.',   time: '2 days ago' },
        { id: 4, type: 'message',   icon: 'bi-chat-left-dots',  color: '#8b5cf6', bg: '#f5f3ff', title: 'Message from Dr. Reyes',      body: 'Please bring your previous lab results to your next visit.',  time: '3 days ago' },
        { id: 5, type: 'reminder',  icon: 'bi-calendar-check',  color: '#f59e0b', bg: '#fffbeb', title: 'Follow-up Reminder',          body: 'Your follow-up check-up is scheduled for next week.',         time: '4 days ago' },
        { id: 6, type: 'message',   icon: 'bi-chat-left-dots',  color: '#8b5cf6', bg: '#f5f3ff', title: 'Message from Dr. Cruz',       body: 'Your prescription is ready for pick-up at the clinic.',       time: '5 days ago' },
    ];

    function getReadIds() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch(e) { return []; }
    }
    function saveReadIds(ids) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
    }

    function renderNotifItem(n, isRead) {
        return `
        <div class="notif-item ${isRead ? 'notif-read' : ''}" id="notif-item-${n.id}" onclick="markOneRead(${n.id})">
            <div class="notif-item-icon" style="background:${n.bg};color:${n.color};">
                <i class="bi ${n.icon}"></i>
            </div>
            <div class="notif-item-body">
                <div class="notif-item-title">${n.title}${!isRead ? '<span class="notif-unread-dot"></span>' : ''}</div>
                <div class="notif-item-text">${n.body}</div>
                <div class="notif-item-time">${n.time}</div>
            </div>
        </div>`;
    }

    function renderAll() {
        const readIds = getReadIds();
        const unread  = defaultNotifs.filter(n => !readIds.includes(n.id));

        // Bell dot
        const dot = document.getElementById('notif-dot');
        if (dot) { unread.length > 0 ? dot.classList.remove('d-none') : dot.classList.add('d-none'); }

        // Dropdown list
        const ddList = document.getElementById('notif-dropdown-list');
        if (ddList) {
            ddList.innerHTML = defaultNotifs.map(n => renderNotifItem(n, readIds.includes(n.id))).join('');
        }

        // Dashboard panel
        const panel = document.getElementById('notif-list');
        if (panel) {
            panel.innerHTML = defaultNotifs.map(n => renderNotifItem(n, readIds.includes(n.id))).join('');
        }

        // Count label
        const label = document.getElementById('notif-count-label');
        if (label) {
            label.textContent = unread.length > 0
                ? `${unread.length} unread notification${unread.length > 1 ? 's' : ''}`
                : 'All caught up!';
        }
    }

    window.markOneRead = function(id) {
        const readIds = getReadIds();
        if (!readIds.includes(id)) { readIds.push(id); saveReadIds(readIds); renderAll(); }
    };

    window.markAllRead = function() {
        saveReadIds(defaultNotifs.map(n => n.id));
        renderAll();
    };

    window.toggleNotifDropdown = function() {
        const dd = document.getElementById('notif-dropdown');
        if (dd) dd.classList.toggle('d-none');
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('notif-bell-wrap');
        if (wrap && !wrap.contains(e.target)) {
            const dd = document.getElementById('notif-dropdown');
            if (dd) dd.classList.add('d-none');
        }
    });

    document.addEventListener('DOMContentLoaded', renderAll);
})();
</script>
