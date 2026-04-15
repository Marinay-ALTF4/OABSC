<?php
$role = session('user_role') ?? 'guest';
$roleLabel = match($role) {
    'admin'           => 'Admin',
    'assistant_admin' => 'Assistant Admin',
    'secretary'       => 'Secretary',
    'doctor'          => 'Doctor',
    default           => 'Client',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="profile-page">
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="page-title mb-1">Profile Settings</h4>
            <p class="page-sub mb-0">Manage your personal information and account security.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="btn-back-link">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="row g-4">

        <!-- Left: Avatar + quick info -->
        <div class="col-lg-3">
            <div class="profile-card text-center">
                <?php if (!empty($user['profile_photo'])): ?>
                    <img src="<?= base_url($user['profile_photo']) ?>" alt="Profile Photo" class="avatar-circle mx-auto mb-3" style="object-fit:cover;">
                <?php else: ?>
                    <div class="avatar-circle mx-auto mb-3" id="avatarCircle">
                        <span id="avatarInitials"><?= strtoupper(substr(session('user_name') ?? 'U', 0, 2)) ?></span>
                    </div>
                <?php endif; ?>
                <div class="profile-card-name" id="sidebarName"><?= esc(session('user_name') ?? 'User') ?></div>
                <div class="profile-card-role"><?= esc($roleLabel) ?></div>
                <hr class="my-3">
                <div class="profile-card-meta">
                    <i class="bi bi-envelope me-2 text-primary"></i><?= esc($user['email'] ?? '—') ?>
                </div>
                <div class="profile-card-meta mt-2">
                    <i class="bi bi-telephone me-2 text-primary"></i><span id="sidebarPhoneVal"><?= esc($user['phone'] ?? '—') ?></span>
                </div>
                <div class="profile-card-meta mt-2">
                    <i class="bi bi-geo-alt me-2 text-primary"></i><span id="sidebarAddressVal"><?= esc($user['address'] ?? '—') ?></span>
                </div>
            </div>
        </div>

        <!-- Right: Forms -->
        <div class="col-lg-9">

            <!-- Tabs -->
            <ul class="nav profile-tabs mb-4">
                <li class="nav-item">
                    <button class="profile-tab active" onclick="switchTab('personal', this)">
                        <i class="bi bi-person me-1"></i> Personal Info
                    </button>
                </li>
                <li class="nav-item">
                    <button class="profile-tab" onclick="switchTab('security', this)">
                        <i class="bi bi-shield-lock me-1"></i> Security
                    </button>
                </li>
                <li class="nav-item">
                    <button class="profile-tab" onclick="switchTab('language', this)">
                        <i class="bi bi-translate me-1"></i> Language
                    </button>
                </li>
                <li class="nav-item">
                    <button class="profile-tab" onclick="switchTab('history', this)">
                        <i class="bi bi-clock-history me-1"></i> Activity History
                    </button>
                </li>
            </ul>

            <!-- Alert -->
            <div id="profileAlert" class="profile-alert d-none"></div>

            <?php if (session('success')): ?>
                <div class="profile-alert profile-alert-success mb-3"><?= esc(session('success')) ?></div>
            <?php endif; ?>
            <?php if (session('errors')): ?>
                <div class="profile-alert profile-alert-error mb-3">
                    <?php foreach ((array) session('errors') as $err): ?><div><?= esc($err) ?></div><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Personal Info Tab -->
            <div id="tab-personal" class="tab-section">
                <div class="section-card">
                    <div class="section-card-title">Personal Information</div>
                    <div class="section-card-sub">Update your name, contact details, and address.</div>
                    <hr class="my-3">
                    <form action="<?= site_url('/settings/update') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="field-label">Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">Full Name</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" name="name" class="field-input" id="fieldName"
                                        placeholder="Enter your full name"
                                        value="<?= esc(old('name', $user['name'] ?? '')) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">Email Address</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input type="email" class="field-input" value="<?= esc($user['email'] ?? '') ?>" disabled>
                                </div>
                                <small class="text-muted" style="font-size:0.75rem;">Email cannot be changed.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">Phone Number</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-telephone input-icon"></i>
                                    <input type="tel" name="phone" class="field-input" id="fieldPhone"
                                        placeholder="+63 9XX XXX XXXX"
                                        value="<?= esc(old('phone', $user['phone'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">City / Municipality</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-geo-alt input-icon"></i>
                                    <input type="text" name="city" class="field-input" id="fieldCity"
                                        placeholder="e.g. General Santos City"
                                        value="<?= esc(old('city', $user['city'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="field-label">Home Address</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-house input-icon"></i>
                                    <input type="text" name="address" class="field-input" id="fieldAddress"
                                        placeholder="Street, Barangay, City"
                                        value="<?= esc(old('address', $user['address'] ?? '')) ?>">
                                </div>
                            </div>
                            <?php if ($role === 'doctor'): ?>
                            <div class="col-12"><hr class="my-1"><p class="field-label text-muted mb-0">Professional Information</p></div>
                            <div class="col-md-6">
                                <label class="field-label">Specialization</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-heart-pulse input-icon"></i>
                                    <input type="text" name="specialization" class="field-input" id="fieldSpec" placeholder="e.g. Cardiologist" value="<?= esc(old('specialization', $user['specialization'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">Experience</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-clock-history input-icon"></i>
                                    <input type="text" name="experience" class="field-input" id="fieldExp" placeholder="e.g. 10 years" value="<?= esc(old('experience', $user['experience'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">Degree</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-mortarboard input-icon"></i>
                                    <input type="text" name="degree" class="field-input" id="fieldDegree" placeholder="e.g. MD, University of Santo Tomas" value="<?= esc(old('degree', $user['degree'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="field-label">About</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-info-circle input-icon"></i>
                                    <input type="text" name="bio" class="field-input" id="fieldBio" placeholder="Brief description about yourself" value="<?= esc(old('bio', $user['bio'] ?? '')) ?>">
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn-save">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="tab-security" class="tab-section d-none">
                <div class="section-card">
                    <div class="section-card-title">Change Password</div>
                    <div class="section-card-sub">Keep your account secure by using a strong password.</div>
                    <hr class="my-3">
                    <form action="<?= site_url('/settings/update') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="name" value="<?= esc($user['name'] ?? '') ?>">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="field-label">Current Password</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-lock input-icon"></i>
                                    <input type="password" name="current_password" class="field-input" id="fieldCurrentPw" placeholder="Enter current password">
                                    <button type="button" class="pw-toggle" onclick="togglePw('fieldCurrentPw', this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="field-label">New Password</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-lock-fill input-icon"></i>
                                    <input type="password" name="new_password" class="field-input" id="fieldNewPw" placeholder="At least 8 characters" oninput="checkStrength(this.value)">
                                    <button type="button" class="pw-toggle" onclick="togglePw('fieldNewPw', this)"><i class="bi bi-eye"></i></button>
                                </div>
                                <div class="strength-bar mt-2"><div class="strength-fill" id="strengthFill"></div></div>
                                <div class="strength-label" id="strengthLabel"></div>
                            </div>
                            <div class="col-md-8">
                                <label class="field-label">Confirm New Password</label>
                                <div class="input-group-custom">
                                    <i class="bi bi-lock-fill input-icon"></i>
                                    <input type="password" class="field-input" id="fieldConfirmPw" placeholder="Re-enter new password">
                                    <button type="button" class="pw-toggle" onclick="togglePw('fieldConfirmPw', this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="pw-tips mt-3">
                            <div class="pw-tip" id="tip-len"><i class="bi bi-circle me-2"></i>At least 8 characters</div>
                            <div class="pw-tip" id="tip-upper"><i class="bi bi-circle me-2"></i>One uppercase letter</div>
                            <div class="pw-tip" id="tip-num"><i class="bi bi-circle me-2"></i>One number</div>
                            <div class="pw-tip" id="tip-special"><i class="bi bi-circle me-2"></i>One special character</div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn-save" onclick="return validatePassword()">
                                <i class="bi bi-shield-check me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Language Tab -->
            <div id="tab-language" class="tab-section d-none">
                <div class="section-card">
                    <div class="section-card-title">Language Preference</div>
                    <div class="section-card-sub">Choose the language used across the portal.</div>
                    <hr class="my-3">
                    <div class="lang-options">
                        <label class="lang-option" id="lang-opt-en">
                            <input type="radio" name="lang" value="en" onchange="setLanguage('en')" checked>
                            <div class="lang-option-inner">
                                <span class="lang-flag">🇺🇸</span>
                                <div>
                                    <div class="lang-name">English</div>
                                    <div class="lang-desc">Use the portal in English</div>
                                </div>
                                <i class="bi bi-check-circle-fill lang-check ms-auto"></i>
                            </div>
                        </label>
                        <label class="lang-option" id="lang-opt-fil">
                            <input type="radio" name="lang" value="fil" onchange="setLanguage('fil')">
                            <div class="lang-option-inner">
                                <span class="lang-flag">🇵🇭</span>
                                <div>
                                    <div class="lang-name">Filipino</div>
                                    <div class="lang-desc">Gamitin ang portal sa Filipino</div>
                                </div>
                                <i class="bi bi-check-circle-fill lang-check ms-auto"></i>
                            </div>
                        </label>
                    </div>
                    <p class="lang-note mt-3">Changes apply immediately across all portal pages.</p>
                </div>
            </div>

            <!-- Activity History Tab -->
            <div id="tab-history" class="tab-section d-none">
                <div class="section-card">
                    <div class="section-card-title">Activity History</div>
                    <div class="section-card-sub">Recent actions stored locally in your browser.</div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="history-summary">Recent actions are stored locally in your browser.</div>
                        <button type="button" class="btn-clear-history" onclick="clearHistory()">
                            <i class="bi bi-trash me-1"></i> Clear History
                        </button>
                    </div>
                    <div id="historyList" class="history-list"></div>
                    <div id="historyEmpty" class="history-empty text-center text-muted py-4">
                        No history yet. Use the dashboard to perform actions.
                    </div>
                </div>
            </div>

        </div><!-- end right col -->
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-section').forEach(s => s.classList.add('d-none'));
    document.querySelectorAll('.profile-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.remove('d-none');
    btn.classList.add('active');
}

function togglePw(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function validatePassword() {
    const newPw   = document.getElementById('fieldNewPw').value;
    const confirm = document.getElementById('fieldConfirmPw').value;
    if (newPw && newPw !== confirm) {
        alert('Passwords do not match.');
        return false;
    }
    return true;
}

function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    const checks = {
        'tip-len':     val.length >= 8,
        'tip-upper':   /[A-Z]/.test(val),
        'tip-num':     /[0-9]/.test(val),
        'tip-special': /[^A-Za-z0-9]/.test(val),
    };
    Object.entries(checks).forEach(([id, pass]) => {
        const el = document.getElementById(id);
        el.classList.toggle('tip-pass', pass);
        el.querySelector('i').className = pass ? 'bi bi-check-circle-fill me-2' : 'bi bi-circle me-2';
        if (pass) score++;
    });
    const levels = [
        { w:'25%', color:'#ef4444', text:'Weak' },
        { w:'50%', color:'#f59e0b', text:'Fair' },
        { w:'75%', color:'#3b82f6', text:'Good' },
        { w:'100%',color:'#10b981', text:'Strong' },
    ];
    const lvl = levels[score - 1] || { w:'0', color:'transparent', text:'' };
    fill.style.width = lvl.w;
    fill.style.background = lvl.color;
    label.textContent = lvl.text;
    label.style.color = lvl.color;
}

// Language - CLIENT_LANG_KEY and setLanguage already defined in header.php
(function() {
    const saved = localStorage.getItem('oabsc_client_lang') || 'en';
    const input = document.querySelector(`input[name="lang"][value="${saved}"]`);
    if (input) input.checked = true;
})();

// Activity History
const HISTORY_KEY = 'oabsc_client_dashboard_history';
function addHistoryEntry(action, details) {
    const entries = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
    entries.unshift({ time: new Date().toISOString(), action, details });
    localStorage.setItem(HISTORY_KEY, JSON.stringify(entries.slice(0, 30)));
    renderHistory();
}
function renderHistory() {
    const history = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
    const list  = document.getElementById('historyList');
    const empty = document.getElementById('historyEmpty');
    list.innerHTML = '';
    if (!history.length) { empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    history.forEach(entry => {
        const item = document.createElement('div');
        item.className = 'history-item';
        item.innerHTML = `<div class="history-item-meta"><strong>${entry.action}</strong><span>${new Date(entry.time).toLocaleString()}</span></div><div class="history-item-details">${entry.details}</div>`;
        list.appendChild(item);
    });
}
function clearHistory() {
    localStorage.removeItem(HISTORY_KEY);
    renderHistory();
}
document.addEventListener('DOMContentLoaded', function() {
    renderHistory();
    addHistoryEntry('Visited settings', 'Opened profile settings page');
});
</script>

<style>
    body { background: #edf2f7; min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
    .profile-page { min-height: calc(100vh - 60px); }
    .page-title { font-size: 1.4rem; font-weight: 700; color: #0f172a; }
    .page-sub   { font-size: 0.875rem; color: #64748b; }
    .btn-back-link {
        font-size: 0.85rem; font-weight: 600; color: #475569;
        background: white; border: 1px solid #dbe4ef;
        padding: 0.45rem 1rem; border-radius: 10px;
        text-decoration: none; transition: background 0.15s;
    }
    .btn-back-link:hover { background: #f1f5f9; color: #1e40af; }
    .profile-card {
        background: white; border-radius: 18px; padding: 1.75rem 1.25rem;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,0.06);
    }
    .avatar-circle {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, #1d4ed8, #6d28d9);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; font-weight: 700; color: white;
        box-shadow: 0 4px 14px rgba(30,64,175,0.3);
    }
    .profile-card-name { font-size: 1rem; font-weight: 700; color: #0f172a; }
    .profile-card-role {
        display: inline-block; margin-top: 4px;
        background: #dbeafe; color: #1e40af;
        font-size: 0.72rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.6px; padding: 2px 10px; border-radius: 999px;
    }
    .profile-card-meta { font-size: 0.8rem; color: #475569; word-break: break-all; }
    .profile-tabs { border-bottom: 2px solid #e2e8f0; gap: 0; list-style: none; padding: 0; margin: 0; }
    .profile-tab {
        background: none; border: none; padding: 0.6rem 1.25rem;
        font-size: 0.875rem; font-weight: 600; color: #64748b;
        border-bottom: 3px solid transparent; margin-bottom: -2px;
        cursor: pointer; transition: all 0.15s;
    }
    .profile-tab:hover { color: #1e40af; }
    .profile-tab.active { color: #1e40af; border-bottom-color: #1e40af; }
    .profile-alert {
        padding: 0.75rem 1rem; border-radius: 10px;
        font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem;
    }
    .profile-alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .profile-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .profile-alert-info    { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .section-card {
        background: white; border-radius: 18px; padding: 1.75rem;
        border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(15,23,42,0.06);
    }
    .section-card-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
    .section-card-sub   { font-size: 0.83rem; color: #64748b; margin-top: 3px; }
    .field-label { font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block; }
    .input-group-custom { position: relative; display: flex; align-items: center; }
    .input-icon { position: absolute; left: 12px; color: #94a3b8; font-size: 0.95rem; pointer-events: none; }
    .field-input {
        width: 100%; padding: 0.6rem 0.9rem 0.6rem 2.2rem;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        font-size: 0.875rem; color: #0f172a; background: #fafafa; outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .field-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); background: white; }
    .field-input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
    .pw-toggle { position: absolute; right: 10px; background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 0.9rem; padding: 0; }
    .pw-toggle:hover { color: #475569; }
    .strength-bar { height: 5px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
    .strength-fill { height: 100%; width: 0; border-radius: 999px; transition: width 0.3s, background 0.3s; }
    .strength-label { font-size: 0.75rem; font-weight: 600; margin-top: 4px; }
    .pw-tips { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; }
    .pw-tip  { font-size: 0.78rem; color: #94a3b8; transition: color 0.2s; }
    .pw-tip.tip-pass { color: #10b981; }
    .btn-save {
        background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        color: white; border: none; font-weight: 600;
        font-size: 0.875rem; padding: 0.6rem 1.5rem;
        border-radius: 10px; cursor: pointer;
        box-shadow: 0 2px 8px rgba(30,64,175,0.25); transition: opacity 0.15s;
    }
    .btn-save:hover { opacity: 0.9; }
    .lang-options { display: flex; flex-direction: column; gap: 0.75rem; }
    .lang-option { cursor: pointer; }
    .lang-option input { display: none; }
    .lang-option-inner {
        display: flex; align-items: center; gap: 1rem;
        padding: 1rem 1.25rem; border-radius: 14px;
        border: 2px solid #e2e8f0; background: #f8fafc; transition: all 0.15s;
    }
    .lang-option input:checked + .lang-option-inner { border-color: #3b82f6; background: #eff6ff; }
    .lang-flag { font-size: 1.6rem; }
    .lang-name { font-size: 0.9rem; font-weight: 700; color: #0f172a; }
    .lang-desc { font-size: 0.78rem; color: #64748b; }
    .lang-check { color: #e2e8f0; font-size: 1.1rem; transition: color 0.15s; }
    .lang-option input:checked + .lang-option-inner .lang-check { color: #3b82f6; }
    .lang-note { font-size: 0.78rem; color: #94a3b8; }
    .history-summary { font-size: 0.9rem; color: #475569; }
    .btn-clear-history { background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; padding: 0.5rem 0.85rem; border-radius: 10px; font-weight: 600; cursor: pointer; }
    .history-list { display: grid; gap: 0.9rem; }
    .history-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 0.95rem 1rem; }
    .history-item-meta { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; font-size: 0.92rem; color: #0f172a; }
    .history-item-meta strong { font-weight: 700; }
    .history-item-meta span { color: #64748b; font-size: 0.82rem; }
    .history-item-details { margin-top: 0.5rem; color: #475569; font-size: 0.875rem; }
    .history-empty { background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 14px; }
    .tab-section { display: block; }
    .tab-section.d-none { display: none !important; }
</style>
</body>
</html>
