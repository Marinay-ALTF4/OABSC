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
                    <a class="nav-link px-2 <?= url_is('appointments*') ? 'active fw-semibold' : '' ?>" href="<?= site_url('/appointments/my') ?>">Appointments</a>
                </li>
                <li class="nav-item">
                    <?php if ($role === 'admin'): ?>
                    <a class="nav-link px-2 <?= url_is('admin/patients*') ? 'active fw-semibold' : '' ?>" href="<?= site_url('/admin/patients') ?>">Patients</a>
                    <?php endif; ?>
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
            <div class="dropdown">
                <button class="btn btn-account btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= esc($roleLabel) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end account-menu">
                    <li><a class="dropdown-item" href="<?= site_url('/settings') ?>">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('/logout') ?>">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div id="notif-modal" class="notif-modal d-none">
    <div class="notif-modal-backdrop" onclick="closeNotifModal()"></div>
    <div class="notif-modal-card">
        <div class="notif-modal-card-header">
            <div>
                <div class="notif-modal-heading">Notification</div>
                <div id="notif-modal-time" class="notif-modal-time"></div>
            </div>
            <button type="button" class="notif-modal-close" onclick="closeNotifModal()">×</button>
        </div>
        <div id="notif-modal-title" class="notif-modal-title"></div>
        <div id="notif-modal-body" class="notif-modal-body"></div>
    </div>
</div>

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
    .notif-item-action {
        display: flex;
        align-items: center;
        margin-left: auto;
    }
    .notif-item-btn {
        background: #1d4ed8;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.45rem 0.8rem;
        font-size: 0.75rem;
        cursor: pointer;
        transition: background 0.15s;
    }
    .notif-item-btn:hover { background: #1e40af; }
    .notif-modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10500;
        padding: 1rem;
    }
    .notif-modal.d-none {
        display: none;
    }
    .notif-modal-card {
        width: min(520px, 100%);
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        position: relative;
    }
    .notif-modal-backdrop {
        position: absolute;
        inset: 0;
        cursor: pointer;
    }
    .notif-modal-card-header {
        position: relative;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        border-bottom: 1px solid #f1f5f9;
        z-index: 1;
        background: white;
    }
    .notif-modal-heading {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .notif-modal-time {
        font-size: 0.8rem;
        color: #6b7280;
    }
    .notif-modal-close {
        border: none;
        background: transparent;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
        color: #475569;
        padding: 0;
    }
    .notif-modal-title {
        padding: 1.2rem 1.5rem 0.5rem;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        z-index: 1;
    }
    .notif-modal-body {
        padding: 0 1.5rem 1.5rem;
        color: #475569;
        line-height: 1.7;
        z-index: 1;
    }
</style>

<script>
(function() {
    const STORAGE_KEY = 'oabsc_notifications_read';

    const defaultNotifs = [
        { id: 1, type: 'reminder',  icon: 'bi-alarm',           color: '#3b82f6', bg: '#eff6ff', title: 'Appointment Reminder',         body: 'You have an appointment tomorrow. Please be on time.',                   time: '2 hours ago' },
        { id: 2, type: 'status',    icon: 'bi-check-circle',    color: '#10b981', bg: '#f0fdf4', title: 'Appointment Confirmed',         body: 'Your appointment with Dr. Santos has been confirmed.',                   time: 'Yesterday' },
        { id: 3, type: 'status',    icon: 'bi-x-circle',        color: '#ef4444', bg: '#fff1f2', title: 'Appointment Cancelled',         body: 'Your appointment on March 10 was cancelled by the clinic.',             time: '2 days ago' },
        { id: 4, type: 'message',   icon: 'bi-chat-left-dots',  color: '#8b5cf6', bg: '#f5f3ff', title: 'Message from Dr. Reyes',        body: 'Please bring your previous lab results to your next visit.',            time: '3 days ago' },
        { id: 5, type: 'reminder',  icon: 'bi-calendar-check',  color: '#f59e0b', bg: '#fffbeb', title: 'Follow-up Reminder',            body: 'Your follow-up check-up is scheduled for next week.',                   time: '4 days ago' },
        { id: 6, type: 'message',   icon: 'bi-chat-left-dots',  color: '#8b5cf6', bg: '#f5f3ff', title: 'Message from Dr. Cruz',         body: 'Your prescription is ready for pick-up at the clinic.',                 time: '5 days ago' },
        { id: 7, type: 'request',   icon: 'bi-calendar2-plus',  color: '#0ea5e9', bg: '#f0f9ff', title: 'New Appointment Request',       body: 'A patient has submitted a new appointment request for review.',         time: '30 minutes ago' },
        { id: 8, type: 'cancelled', icon: 'bi-calendar-x',      color: '#ef4444', bg: '#fff1f2', title: 'Booking Cancelled by Patient',  body: 'A patient cancelled their booking scheduled for tomorrow at 10:00 AM.', time: '1 hour ago' },
        { id: 9, type: 'reminder',  icon: 'bi-bell-fill',       color: '#f59e0b', bg: '#fffbeb', title: 'Schedule Reminder',             body: 'You have 3 appointments scheduled for today. Please be prepared.',      time: 'Today' },
    ];

    function getReadIds() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); } catch(e) { return []; }
    }
    function saveReadIds(ids) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
    }

    function renderNotifItem(n, isRead) {
        return `
        <div class="notif-item ${isRead ? 'notif-read' : ''}" id="notif-item-${n.id}" onclick="openNotification(${n.id})">
            <div class="notif-item-icon" style="background:${n.bg};color:${n.color};">
                <i class="bi ${n.icon}"></i>
            </div>
            <div class="notif-item-body">
                <div class="notif-item-title">${n.title}${!isRead ? '<span class="notif-unread-dot"></span>' : ''}</div>
                <div class="notif-item-text">${n.body}</div>
                <div class="notif-item-time">${n.time}</div>
            </div>
            <div class="notif-item-action">
                <button type="button" class="notif-item-btn" onclick="event.stopPropagation(); openNotification(${n.id})">View</button>
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

        // Dashboard panel (client)
        const panel = document.getElementById('notif-list');
        if (panel) {
            panel.innerHTML = defaultNotifs.map(n => renderNotifItem(n, readIds.includes(n.id))).join('');
        }

        // Dashboard panel (admin)
        const panelAdm = document.getElementById('notif-list-adm');
        if (panelAdm) {
            panelAdm.innerHTML = defaultNotifs.map(n => renderNotifItem(n, readIds.includes(n.id))).join('');
        }

        // Dashboard panel (secretary)
        const panelSec = document.getElementById('notif-list-sec');
        if (panelSec) {
            panelSec.innerHTML = defaultNotifs.map(n => renderNotifItem(n, readIds.includes(n.id))).join('');
        }

        // Dashboard panel (doctor)
        const panelDoc = document.getElementById('notif-list-doc');
        if (panelDoc) {
            panelDoc.innerHTML = defaultNotifs.map(n => renderNotifItem(n, readIds.includes(n.id))).join('');
        }

        // Count label (client)
        const label = document.getElementById('notif-count-label');
        if (label) {
            label.textContent = unread.length > 0
                ? `${unread.length} unread notification${unread.length > 1 ? 's' : ''}`
                : 'All caught up!';
        }

        // Count labels (other roles)
        ['notif-count-label-adm', 'notif-count-label-sec', 'notif-count-label-doc'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = unread.length > 0
                    ? `${unread.length} unread notification${unread.length > 1 ? 's' : ''}`
                    : 'All caught up!';
            }
        });
    }

    window.markOneRead = function(id) {
        const readIds = getReadIds();
        if (!readIds.includes(id)) { readIds.push(id); saveReadIds(readIds); renderAll(); }
    };

    window.openNotification = function(id) {
        markOneRead(id);
        const notif = defaultNotifs.find(n => n.id === id);
        if (!notif) return;

        const modal = document.getElementById('notif-modal');
        if (!modal) return;

        document.getElementById('notif-modal-title').textContent = notif.title;
        document.getElementById('notif-modal-time').textContent = notif.time;
        document.getElementById('notif-modal-body').textContent = notif.body;
        modal.classList.remove('d-none');
    };

    window.closeNotifModal = function() {
        const modal = document.getElementById('notif-modal');
        if (modal) modal.classList.add('d-none');
    };

    window.markAllRead = function() {
        saveReadIds(defaultNotifs.map(n => n.id));
        renderAll();
    };

    window.markAllReadAdm = window.markAllReadSec = window.markAllReadDoc = window.markAllRead;

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
<script>
const CLIENT_LANG_KEY = 'oabsc_client_lang';
const CLIENT_TRANSLATIONS = {
    en: {
        tab_personal: 'Personal Info',
        tab_security: 'Security',
        tab_language: 'Language',
        lang_title: 'Language Preference',
        lang_sub: 'Choose the language used across the patient portal.',
        lang_note: 'Changes apply immediately across all client pages.',
        // Global UI
        'Dashboard': 'Dashboard',
        'Settings': 'Settings',
        'Logout': 'Logout',
        'Patient Portal': 'Patient Portal',
        'From here you can request or review your appointments.': 'From here you can request or review your appointments.',
        'Notifications & Alerts': 'Notifications & Alerts',
        'My Appointments': 'My Appointments',
        'View Appointments': 'View Appointments',
        'Book Appointment': 'Book Appointment',
        'Book New Appointment': 'Book New Appointment',
        'Select doctor, date, and time slot, then confirm your booking.': 'Select doctor, date, and time slot, then confirm your booking.',
        'Appointment submitted successfully.': 'Appointment submitted successfully.',
        'Select Your Doctor': 'Select Your Doctor',
        'Please select a doctor.': 'Please select a doctor.',
        'Available Time Slots': 'Available Time Slots',
        'Reason for Visit': 'Reason for Visit',
        'Describe your concern or reason for consultation': 'Describe your concern or reason for consultation',
        'Appointment Summary': 'Appointment Summary',
        'Location:': 'Location:',
        'Submit Appointment': 'Submit Appointment',
        'Review and manage all your clinic appointments.': 'Review and manage all your clinic appointments.',
        'Manage your personal information and account security.': 'Manage your personal information and account security.',
        'Personal Information': 'Personal Information',
        'Update your name, contact details, and address.': 'Update your name, contact details, and address.',
        'Save Changes': 'Save Changes',
        'Reset': 'Reset',
        'Current Password': 'Current Password',
        'New Password': 'New Password',
        'Confirm New Password': 'Confirm New Password',
        'Keep your account secure by using a strong password.': 'Keep your account secure by using a strong password.',
        'Password updated successfully!': 'Password updated successfully!',
        'At least 8 characters': 'At least 8 characters',
        'One uppercase letter': 'One uppercase letter',
        'One number': 'One number',
        'One special character': 'One special character',
        'Back to Dashboard': 'Back to Dashboard',
        'Profile Settings': 'Profile Settings',
        'No upcoming appointments': 'No upcoming appointments',
        'You have no scheduled appointments. Book one now!': 'You have no scheduled appointments. Book one now!',
        'No completed appointments': 'No completed appointments',
        'Your completed appointments will appear here.': 'Your completed appointments will appear here.',
        'No cancelled appointments': 'No cancelled appointments',
        'You have no cancelled appointments.': 'You have no cancelled appointments.',
        'Appointment Details': 'Appointment Details',
        'Reschedule Appointment': 'Reschedule Appointment',
        'Cancel Appointment?': 'Cancel Appointment?',
        'You are about to cancel your appointment with:': 'You are about to cancel your appointment with:',
        'This action cannot be undone.': 'This action cannot be undone.',
        'Close': 'Close',
        'Keep It': 'Keep It',
        'Yes, Cancel': 'Yes, Cancel',
        'Open Chat': 'Open Chat',
        'Patient Portal': 'Patient Portal',
        'New Appointment': 'New Appointment',
        'Book New': 'Book New',
        'Upcoming': 'Upcoming',
        'Completed': 'Completed',
        'Cancelled': 'Cancelled',
        'Doctor': 'Doctor',
        'Date': 'Date',
        'Time': 'Time',
        'Reason': 'Reason',
        'Status': 'Status',
        'Phone:': 'Phone:',
        'Email:': 'Email:',
        'Hours:': 'Hours:',
        'Appointment to reschedule:': 'Appointment to reschedule:',
        'View': 'View',
        'Mark all as read': 'Mark all as read',
        'All caught up!': 'All caught up!',
    },
    fil: {
        tab_personal: 'Personal na Impormasyon',
        tab_security: 'Seguridad',
        tab_language: 'Wika',
        lang_title: 'Piling Wika',
        lang_sub: 'Piliin ang wika na gagamitin sa portal ng pasyente.',
        lang_note: 'Awtomatikong nalalapat ang mga pagbabago sa lahat ng client na pahina.',
        // Global UI
        'Dashboard': 'Dashboard',
        'Settings': 'Mga Setting',
        'Logout': 'Mag-logout',
        'Patient Portal': 'Portal ng Pasyente',
        'From here you can request or review your appointments.': 'Mula rito maaari kang mag-request o mag-review ng iyong mga appointment.',
        'Notifications & Alerts': 'Mga Abiso at Alert',
        'My Appointments': 'Aking Mga Appointment',
        'View Appointments': 'Tingnan ang Mga Appointment',
        'Book Appointment': 'Mag-book ng Appointment',
        'Book New Appointment': 'Mag-book ng Bagong Appointment',
        'Select doctor, date, and time slot, then confirm your booking.': 'Piliin ang doktor, petsa, at oras, pagkatapos ay kumpirmahin ang iyong booking.',
        'Appointment submitted successfully.': 'Matagumpay na naisumite ang appointment.',
        'Select Your Doctor': 'Piliin ang Iyong Doktor',
        'Please select a doctor.': 'Mangyaring pumili ng doktor.',
        'Available Time Slots': 'Mga Available na Oras',
        'Reason for Visit': 'Dahilan ng Pagbisita',
        'Describe your concern or reason for consultation': 'Ilarawan ang iyong alalahanin o dahilan sa konsultasyon',
        'Appointment Summary': 'Buod ng Appointment',
        'Location:': 'Lokasyon:',
        'Submit Appointment': 'Isumite ang Appointment',
        'Review and manage all your clinic appointments.': 'Suriin at pamahalaan ang lahat ng iyong mga appointment sa klinika.',
        'Manage your personal information and account security.': 'Pamahalaan ang iyong personal na impormasyon at seguridad ng account.',
        'Personal Information': 'Personal na Impormasyon',
        'Update your name, contact details, and address.': 'I-update ang iyong pangalan, detalye ng kontak, at address.',
        'Save Changes': 'I-save ang Mga Pagbabago',
        'Reset': 'I-reset',
        'Current Password': 'Kasalukuyang Password',
        'New Password': 'Bagong Password',
        'Confirm New Password': 'Kumpirmahin ang Bagong Password',
        'Keep your account secure by using a strong password.': 'Panatilihing ligtas ang iyong account gamit ang malakas na password.',
        'Password updated successfully!': 'Matagumpay na na-update ang password!',
        'At least 8 characters': 'Hindi bababa sa 8 character',
        'One uppercase letter': 'Isang malaking titik',
        'One number': 'Isang numero',
        'One special character': 'Isang espesyal na character',
        'Back to Dashboard': 'Bumalik sa Dashboard',
        'Profile Settings': 'Mga Setting ng Profile',
        'No upcoming appointments': 'Walang paparating na appointment',
        'You have no scheduled appointments. Book one now!': 'Wala kang naka-schedule na appointment. Mag-book na ngayon!',
        'No completed appointments': 'Walang natapos na appointment',
        'Your completed appointments will appear here.': 'Dito lalabas ang iyong mga natapos na appointment.',
        'No cancelled appointments': 'Walang kinanselang appointment',
        'You have no cancelled appointments.': 'Wala kang kinanselang appointment.',
        'Appointment Details': 'Detalye ng Appointment',
        'Reschedule Appointment': 'I-reschedule ang Appointment',
        'Cancel Appointment?': 'Ikansela ang Appointment?',
        'You are about to cancel your appointment with:': 'Malapit mo nang ikansela ang iyong appointment kay:',
        'This action cannot be undone.': 'Hindi na ito mababawi.',
        'Close': 'Isara',
        'Keep It': 'Panatilihin',
        'Yes, Cancel': 'Oo, Ikansela',
        'Open Chat': 'Buksan ang Chat',
        'New Appointment': 'Bagong Appointment',
        'Book New': 'Mag-book ng Bago',
        'Upcoming': 'Paparan',
        'Completed': 'Natapos',
        'Cancelled': 'Kinansela',
        'Doctor': 'Doktor',
        'Date': 'Petsa',
        'Time': 'Oras',
        'Reason': 'Dahilan',
        'Status': 'Katayuan',
        'Phone:': 'Telepono:',
        'Email:': 'Email:',
        'Hours:': 'Oras:',
        'Appointment to reschedule:': 'Appointment na i-reschedule:',
    },
};

function translateTextNode(node, translations) {
    const text = node.nodeValue.trim();
    if (!text) return;
    const translated = translations[text];
    if (translated) {
        node.nodeValue = node.nodeValue.replace(text, translated);
    }
}

function translateTextNodes(root, translations) {
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
        acceptNode(node) {
            const parentTag = node.parentNode && node.parentNode.nodeName;
            if (!node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
            if (['SCRIPT', 'STYLE', 'NOSCRIPT', 'IFRAME', 'TEXTAREA'].includes(parentTag)) return NodeFilter.FILTER_REJECT;
            return NodeFilter.FILTER_ACCEPT;
        }
    });

    const nodes = [];
    while (walker.nextNode()) {
        nodes.push(walker.currentNode);
    }
    nodes.forEach(node => translateTextNode(node, translations));
}

function translateAttributes(root, translations) {
    const attrNames = ['placeholder', 'title', 'aria-label', 'alt', 'value'];
    root.querySelectorAll('*').forEach(el => {
        attrNames.forEach(name => {
            if (el.hasAttribute(name)) {
                const value = el.getAttribute(name);
                if (value && translations[value]) {
                    el.setAttribute(name, translations[value]);
                }
            }
        });
    });
}

function translateClientText(lang) {
    const translations = CLIENT_TRANSLATIONS[lang] || CLIENT_TRANSLATIONS.en;
    document.querySelectorAll('[data-t]').forEach((el) => {
        const key = el.getAttribute('data-t');
        if (translations[key]) {
            el.textContent = translations[key];
        }
    });
    translateTextNodes(document.body, translations);
    translateAttributes(document.body, translations);
    const titleMap = {
        'Book Appointment': 'Book Appointment',
        'My Appointments': 'My Appointments',
        'Profile Settings': 'Profile Settings',
        'Dashboard': 'Dashboard',
        'Book Appointment': 'Book Appointment',
    };
    const pageTitle = document.title;
    if (translations[pageTitle]) {
        document.title = translations[pageTitle];
    } else if (titleMap[pageTitle] && translations[titleMap[pageTitle]]) {
        document.title = translations[titleMap[pageTitle]];
    }
    document.documentElement.lang = lang === 'fil' ? 'fil' : 'en';
}

function setLanguage(lang) {
    if (!CLIENT_TRANSLATIONS[lang]) {
        lang = 'en';
    }
    localStorage.setItem(CLIENT_LANG_KEY, lang);
    translateClientText(lang);
    const selectedInput = document.querySelector(`input[name="lang"][value="${lang}"]`);
    if (selectedInput) selectedInput.checked = true;
}

function loadClientLanguage() {
    const savedLang = localStorage.getItem(CLIENT_LANG_KEY) || 'en';
    setLanguage(savedLang);
}

document.addEventListener('DOMContentLoaded', loadClientLanguage);
</script>

<script>
// Mark session as active so other tabs can detect it
<?php if (session()->get('isLoggedIn')): ?>
localStorage.setItem('oabsc_session_active', '1');
<?php else: ?>
localStorage.removeItem('oabsc_session_active');
<?php endif; ?>
</script>
