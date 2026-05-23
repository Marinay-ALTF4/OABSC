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
<div style="max-width:1100px; margin:0 auto; padding: 0 2rem;">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="page-title mb-1">Profile Settings</h4>
            <p class="page-sub mb-0">Manage your personal information and account security.</p>
        </div>
        <a href="<?= site_url('/dashboard') ?>" class="btn-back-link">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="row g-4 mt-2" style="align-items:stretch;">

        <!-- Left: Avatar + quick info -->
        <div class="col-lg-3 col-md-4" style="display:flex; flex-direction:column;">
            <div class="profile-card text-center" style="flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                <!-- Header with dots -->
                <div class="profile-card-header">
                    <div class="profile-card-header-dots">
                        <?php for($i=0;$i<12;$i++): ?><span></span><?php endfor; ?>
                    </div>
                </div>
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
                    <i class="bi bi-envelope"></i><?= esc($user['email'] ?? '—') ?>
                </div>
                <div class="profile-card-meta">
                    <i class="bi bi-telephone"></i><span id="sidebarPhoneVal"><?= esc($user['phone'] ?? '—') ?></span>
                </div>
                <div class="profile-card-meta">
                    <i class="bi bi-geo-alt"></i><span id="sidebarAddressVal"><?= esc($user['address'] ?? '—') ?></span>
                </div>

                <!-- Profile Completion -->
                <?php
                $filled = 0;
                $fields = ['name','email','phone','city','address'];
                foreach ($fields as $f) if (!empty($user[$f])) $filled++;
                $pct = (int) round(($filled / count($fields)) * 100);
                $circumference = 2 * M_PI * 20;
                $offset = $circumference - ($pct / 100) * $circumference;
                $msg = $pct === 100 ? 'Great! Your profile is complete.' : 'Complete your profile for better experience.';
                ?>
                <div class="profile-completion">
                    <div class="completion-ring">
                        <svg width="52" height="52" viewBox="0 0 52 52">
                            <circle class="completion-ring-bg" cx="26" cy="26" r="20"/>
                            <circle class="completion-ring-fill" cx="26" cy="26" r="20"
                                stroke-dasharray="<?= $circumference ?>"
                                stroke-dashoffset="<?= $offset ?>"/>
                        </svg>
                        <div class="completion-pct"><?= $pct ?>%</div>
                    </div>
                    <div>
                        <div class="completion-label">Profile Completion</div>
                        <div class="completion-sub"><?= $msg ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Forms -->
        <div class="col-lg-9 col-md-8">

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
                        <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="text-muted" style="font-size:0.78rem;">
                                <i class="bi bi-clock me-1"></i>
                                Last updated: <?= esc(isset($user['updated_at']) ? date('M j, Y g:i A', strtotime($user['updated_at'])) : 'Never') ?>
                            </div>
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
                    <div id="historyEmpty" class="history-empty py-4">
                        <i class="bi bi-clock-history"></i>
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
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    body {
        background: #edf2f7;
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }

    .profile-page { min-height: calc(100vh - 60px); padding: 2.5rem 0 4rem; }

    .page-title { font-size: 1.8rem; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
    .page-sub   { font-size: 0.9rem; color: #64748b; margin-top: 4px; }

    .btn-back-link {
        font-size: 0.9rem; font-weight: 600; color: #2563eb;
        background: white; border: 1.5px solid #dbeafe;
        padding: 0.65rem 1.4rem; border-radius: 12px;
        text-decoration: none; transition: all 0.2s;
        display: inline-flex; align-items: center; gap: 6px;
        box-shadow: 0 2px 10px rgba(37,99,235,0.12);
    }
    .btn-back-link:hover { background: #dbeafe; color: #1d4ed8; }

    /* Profile Card */
    .profile-card {
        background: white; border-radius: 22px;
        padding: 0 2rem 2rem;
        border: 1px solid #dbeafe;
        box-shadow: 0 6px 24px rgba(37,99,235,0.09);
        overflow: hidden; height: 100%;
    }
    .profile-card-header {
        background: linear-gradient(135deg, #dbe7f4 0%, #dbeafe 100%);
        margin: 0 -2rem; height: 130px;
        position: relative; overflow: hidden;
    }
    .profile-card-header::after {
        content: ''; position: absolute;
        bottom: -50px; right: -30px;
        width: 160px; height: 160px; border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .profile-card-header::before {
        content: ''; position: absolute;
        bottom: -70px; right: 60px;
        width: 120px; height: 120px; border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .profile-card-header-dots {
        position: absolute; top: 18px; right: 22px;
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 7px;
    }
    .profile-card-header-dots span {
        width: 6px; height: 6px; border-radius: 50%;
        background: rgba(255,255,255,0.45); display: block;
    }
    .avatar-circle {
        width: 100px; height: 100px; border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 800; color: white;
        box-shadow: 0 8px 28px rgba(37,99,235,0.4);
        border: 5px solid white; margin-top: -50px;
        position: relative; z-index: 1;
    }
    .profile-card-name { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin-top: 0.75rem; }
    .profile-card-role {
        display: inline-block; margin-top: 7px;
        background: #dbeafe; color: #2563eb; border: 1px solid #93c5fd;
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; padding: 4px 16px; border-radius: 999px;
    }
    .profile-card-meta {
        font-size: 0.9rem; color: #64748b; word-break: break-all;
        display: flex; align-items: center; gap: 12px;
        padding: 11px 0; border-bottom: 1px solid #f1f5f9;
    }
    .profile-card-meta:last-child { border-bottom: none; }
    .profile-card-meta i { color: #2563eb; font-size: 1rem; flex-shrink: 0; }

    /* Profile Completion */
    .profile-completion {
        background: #dbeafe; border-radius: 18px;
        padding: 1.1rem 1.25rem;
        display: flex; align-items: center; gap: 1.1rem; margin-top: 1.25rem;
    }
    .completion-ring { width: 64px; height: 64px; flex-shrink: 0; position: relative; }
    .completion-ring svg { transform: rotate(-90deg); }
    .completion-ring-bg   { fill: none; stroke: #bfdbfe; stroke-width: 5; }
    .completion-ring-fill { fill: none; stroke: #2563eb; stroke-width: 5; stroke-linecap: round; }
    .completion-pct {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.78rem; font-weight: 800; color: #2563eb;
    }
    .completion-label { font-size: 0.9rem; font-weight: 700; color: #1d4ed8; }
    .completion-sub   { font-size: 0.78rem; color: #64748b; margin-top: 3px; line-height: 1.4; }

    /* Tabs */
    .profile-tabs {
        border-bottom: none; gap: 4px; list-style: none;
        padding: 6px; margin: 0 0 1.75rem 0;
        background: white; border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        display: flex; flex-wrap: wrap;
    }
    .profile-tab {
        background: none; border: none; padding: 0.7rem 1.4rem;
        font-size: 0.9rem; font-weight: 600; color: #64748b;
        border-radius: 12px; cursor: pointer; transition: all 0.2s;
        white-space: nowrap; display: flex; align-items: center; gap: 6px;
    }
    .profile-tab:hover { background: #f1f5f9; color: #1d4ed8; }
    .profile-tab.active {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white; box-shadow: 0 4px 14px rgba(37,99,235,0.35);
    }

    /* Alerts */
    .profile-alert { padding: 0.9rem 1.2rem; border-radius: 12px; font-size: 0.9rem; font-weight: 500; margin-bottom: 1rem; }
    .profile-alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .profile-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Section Card */
    .section-card {
        background: white; border-radius: 22px; padding: 2rem 2.25rem;
        border: 1px solid #dbeafe;
        box-shadow: 0 6px 24px rgba(37,99,235,0.07);
    }
    .section-card-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
    .section-card-sub   { font-size: 0.875rem; color: #64748b; margin-top: 4px; }

    /* Fields */
    .field-label { font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px; display: block; }
    .input-group-custom { position: relative; display: flex; align-items: center; }
    .input-icon { position: absolute; left: 15px; color: #bfdbfe; font-size: 1rem; pointer-events: none; z-index: 1; }
    .field-input {
        width: 100%; padding: 0.85rem 1rem 0.85rem 2.8rem;
        border: 1.5px solid #e2e8f0; border-radius: 12px;
        font-size: 0.95rem; color: #0f172a; background: white; outline: none;
        transition: all 0.2s;
    }
    .field-input:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); background: white; }
    .field-input:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
    .pw-toggle { position: absolute; right: 14px; background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1rem; padding: 0; z-index: 1; }
    .pw-toggle:hover { color: #2563eb; }

    /* Password strength */
    .strength-bar { height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-top: 8px; }
    .strength-fill { height: 100%; width: 0; border-radius: 999px; transition: width 0.3s, background 0.3s; }
    .strength-label { font-size: 0.78rem; font-weight: 600; margin-top: 4px; }
    .pw-tips { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-top: 0.75rem; }
    .pw-tip  { font-size: 0.82rem; color: #94a3b8; transition: color 0.2s; display: flex; align-items: center; gap: 4px; }
    .pw-tip.tip-pass { color: #10b981; }

    /* Save button */
    .btn-save {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white; border: none; font-weight: 700;
        font-size: 0.95rem; padding: 0.85rem 2.5rem;
        border-radius: 12px; cursor: pointer;
        box-shadow: 0 6px 20px rgba(37,99,235,0.4);
        transition: all 0.2s; letter-spacing: 0.2px;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(37,99,235,0.5); }

    /* Language */
    .lang-options { display: flex; flex-direction: column; gap: 0.85rem; }
    .lang-option { cursor: pointer; }
    .lang-option input { display: none; }
    .lang-option-inner {
        display: flex; align-items: center; gap: 1.1rem;
        padding: 1.1rem 1.4rem; border-radius: 16px;
        border: 2px solid #e2e8f0; background: white; transition: all 0.2s;
    }
    .lang-option input:checked + .lang-option-inner { border-color: #2563eb; background: #dbeafe; box-shadow: 0 4px 12px rgba(37,99,235,0.12); }
    .lang-flag { font-size: 2rem; }
    .lang-name { font-size: 0.95rem; font-weight: 700; color: #0f172a; }
    .lang-desc { font-size: 0.82rem; color: #64748b; }
    .lang-check { color: #e2e8f0; font-size: 1.3rem; transition: color 0.2s; }
    .lang-option input:checked + .lang-option-inner .lang-check { color: #2563eb; }
    .lang-note { font-size: 0.82rem; color: #94a3b8; }

    /* History */
    .btn-clear-history { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; padding: 0.5rem 1rem; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 0.85rem; }
    .history-list { display: grid; gap: 0.85rem; }
    .history-item { background: white; border: 1px solid #dbeafe; border-radius: 14px; padding: 1.1rem; }
    .history-item-meta { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; font-size: 0.92rem; color: #0f172a; }
    .history-item-meta strong { font-weight: 700; }
    .history-item-meta span { color: #94a3b8; font-size: 0.82rem; }
    .history-item-details { margin-top: 0.4rem; color: #64748b; font-size: 0.85rem; }
    .history-empty { background: white; border: 2px dashed #e0e7ff; border-radius: 16px; padding: 3rem; text-align: center; color: #94a3b8; }
    .history-empty i { font-size: 2.5rem; color: #c7d2fe; display: block; margin-bottom: 0.75rem; }

    .tab-section { display: block; }
    .tab-section.d-none { display: none !important; }

    @media (max-width: 992px) {
        .profile-card { position: static; margin-bottom: 1.5rem; height: auto; }
        .section-card { padding: 1.75rem; }
    }

    .btn-back-link {
        font-size: 0.85rem; font-weight: 600; color: #2563eb;
        background: white; border: 1.5px solid #dbeafe;
        padding: 0.6rem 1.3rem; border-radius: 12px;
        text-decoration: none; transition: all 0.2s;
        display: inline-flex; align-items: center; gap: 6px;
        box-shadow: 0 2px 10px rgba(37,99,235,0.12);
    }
    .btn-back-link:hover { background: #dbeafe; color: #1d4ed8; }

    /* Profile Card */
    .profile-card {
        background: white; border-radius: 22px;
        padding: 0 1.75rem 1.75rem;
        border: 1px solid #e8eaf6;
        box-shadow: 0 6px 24px rgba(99,102,241,0.09);
        position: sticky; top: 80px; overflow: hidden;
    }
    .profile-card-header {
        background: linear-gradient(135deg, #5c6bc0 0%, #7c3aed 100%);
        margin: 0 -1.75rem; height: 140px;
        position: relative; overflow: hidden;
    }
    .profile-card-header::after {
        content: ''; position: absolute;
        bottom: -40px; right: -25px;
        width: 140px; height: 140px; border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .profile-card-header::before {
        content: ''; position: absolute;
        bottom: -60px; right: 50px;
        width: 100px; height: 100px; border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .profile-card-header-dots {
        position: absolute; top: 16px; right: 20px;
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;
    }
    .profile-card-header-dots span {
        width: 5px; height: 5px; border-radius: 50%;
        background: rgba(255,255,255,0.45); display: block;
    }
    .avatar-circle {
        width: 110px; height: 110px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; font-weight: 800; color: white;
        box-shadow: 0 8px 28px rgba(99,102,241,0.4);
        border: 5px solid white; margin-top: -55px;
        position: relative; z-index: 1;
    }
    .profile-card-name { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin-top: 0.75rem; }
    .profile-card-role {
        display: inline-block; margin-top: 6px;
        background: #dbeafe; color: #2563eb; border: 1px solid #93c5fd;
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; padding: 3px 14px; border-radius: 999px;
    }
    .profile-card-meta {
        font-size: 0.83rem; color: #64748b; word-break: break-all;
        display: flex; align-items: center; gap: 10px;
        padding: 9px 0; border-bottom: 1px solid #f1f5f9;
    }
    .profile-card-meta:last-child { border-bottom: none; }
    .profile-card-meta i { color: #2563eb; font-size: 0.9rem; flex-shrink: 0; }

    /* Profile Completion */
    .profile-completion {
        background: #dbeafe; border-radius: 16px;
        padding: 1rem 1.1rem;
        display: flex; align-items: center; gap: 1rem; margin-top: 1.1rem;
    }
    .completion-ring { width: 56px; height: 56px; flex-shrink: 0; position: relative; }
    .completion-ring svg { transform: rotate(-90deg); }
    .completion-ring-bg   { fill: none; stroke: #bfdbfe; stroke-width: 5; }
    .completion-ring-fill { fill: none; stroke: #2563eb; stroke-width: 5; stroke-linecap: round; }
    .completion-pct {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 800; color: #2563eb;
    }
    .completion-label { font-size: 0.82rem; font-weight: 700; color: #1d4ed8; }
    .completion-sub   { font-size: 0.73rem; color: #64748b; margin-top: 2px; line-height: 1.4; }

    /* Tabs */
    .profile-tabs {
        border-bottom: none; gap: 4px; list-style: none;
        padding: 6px; margin: 0 0 1.5rem 0;
        background: white; border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        display: flex; flex-wrap: wrap;
    }
    .profile-tab {
        background: none; border: none; padding: 0.65rem 1.3rem;
        font-size: 0.875rem; font-weight: 600; color: #64748b;
        border-radius: 12px; cursor: pointer; transition: all 0.2s;
        white-space: nowrap; display: flex; align-items: center; gap: 6px;
    }
    .profile-tab:hover { background: #f1f5f9; color: #1d4ed8; }
    .profile-tab.active {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white; box-shadow: 0 4px 14px rgba(37,99,235,0.35);
    }

    /* Alerts */
    .profile-alert { padding: 0.85rem 1.1rem; border-radius: 12px; font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem; }
    .profile-alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .profile-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Section Card */
    .section-card {
        background: white; border-radius: 22px; padding: 2.25rem 2.5rem;
        border: 1px solid #e8eaf6;
        box-shadow: 0 6px 24px rgba(99,102,241,0.07);
    }
    .section-card-title { font-size: 1.15rem; font-weight: 700; color: #0f172a; }
    .section-card-sub   { font-size: 0.875rem; color: #64748b; margin-top: 4px; }

    /* Fields */
    .field-label { font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 7px; display: block; }
    .input-group-custom { position: relative; display: flex; align-items: center; }
    .input-icon { position: absolute; left: 14px; color: #a5b4fc; font-size: 1rem; pointer-events: none; z-index: 1; }
    .field-input {
        width: 100%; padding: 0.75rem 1rem 0.75rem 2.6rem;
        border: 1.5px solid #e2e8f0; border-radius: 12px;
        font-size: 0.9rem; color: #0f172a; background: #fafbff; outline: none;
        transition: all 0.2s;
    }
    .field-input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.1); background: white; }
    .field-input:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
    .pw-toggle { position: absolute; right: 12px; background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 0.9rem; padding: 0; z-index: 1; }
    .pw-toggle:hover { color: #6366f1; }

    /* Password strength */
    .strength-bar { height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-top: 8px; }
    .strength-fill { height: 100%; width: 0; border-radius: 999px; transition: width 0.3s, background 0.3s; }
    .strength-label { font-size: 0.75rem; font-weight: 600; margin-top: 4px; }
    .pw-tips { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-top: 0.75rem; }
    .pw-tip  { font-size: 0.78rem; color: #94a3b8; transition: color 0.2s; display: flex; align-items: center; gap: 4px; }
    .pw-tip.tip-pass { color: #10b981; }

    /* Save button */
    .btn-save {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white; border: none; font-weight: 700;
        font-size: 0.9rem; padding: 0.75rem 2.25rem;
        border-radius: 12px; cursor: pointer;
        box-shadow: 0 6px 20px rgba(99,102,241,0.4);
        transition: all 0.2s; letter-spacing: 0.2px;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,99,235,0.5); }

    /* Language */
    .lang-options { display: flex; flex-direction: column; gap: 0.75rem; }
    .lang-option { cursor: pointer; }
    .lang-option input { display: none; }
    .lang-option-inner {
        display: flex; align-items: center; gap: 1rem;
        padding: 1.1rem 1.25rem; border-radius: 16px;
        border: 2px solid #e2e8f0; background: #fafbff; transition: all 0.2s;
    }
    .lang-option input:checked + .lang-option-inner { border-color: #6366f1; background: #eef2ff; box-shadow: 0 4px 12px rgba(99,102,241,0.12); }
    .lang-flag { font-size: 1.8rem; }
    .lang-name { font-size: 0.9rem; font-weight: 700; color: #0f172a; }
    .lang-desc { font-size: 0.78rem; color: #64748b; }
    .lang-check { color: #e2e8f0; font-size: 1.2rem; transition: color 0.2s; }
    .lang-option input:checked + .lang-option-inner .lang-check { color: #6366f1; }
    .lang-note { font-size: 0.78rem; color: #94a3b8; }

    /* History */
    .btn-clear-history { background: #fef2f2; color: #ef4444; border: 1px solid #fecaca; padding: 0.45rem 0.85rem; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 0.8rem; }
    .history-list { display: grid; gap: 0.75rem; }
    .history-item { background: #fafbff; border: 1px solid #e0e7ff; border-radius: 14px; padding: 1rem; }
    .history-item-meta { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; font-size: 0.88rem; color: #0f172a; }
    .history-item-meta strong { font-weight: 700; }
    .history-item-meta span { color: #94a3b8; font-size: 0.78rem; }
    .history-item-details { margin-top: 0.4rem; color: #64748b; font-size: 0.82rem; }
    .history-empty { background: white; border: 2px dashed #e0e7ff; border-radius: 16px; padding: 3rem; text-align: center; color: #94a3b8; }
    .history-empty i { font-size: 2.5rem; color: #c7d2fe; display: block; margin-bottom: 0.75rem; }

    .tab-section { display: block; }
    .tab-section.d-none { display: none !important; }

    @media (max-width: 768px) {
        .profile-card { position: static; margin-bottom: 1.5rem; }
        .section-card { padding: 1.5rem; }
    }
    .btn-back-link {
        font-size: 0.82rem; font-weight: 600; color: #6366f1;
        background: white; border: 1.5px solid #e0e7ff;
        padding: 0.5rem 1.1rem; border-radius: 12px;
        text-decoration: none; transition: all 0.2s;
        display: inline-flex; align-items: center; gap: 5px;
        box-shadow: 0 2px 8px rgba(99,102,241,0.1);
    }
    .btn-back-link:hover { background: #eef2ff; color: #4f46e5; border-color: #c7d2fe; }

    /* Profile Card */
    .profile-card {
        background: white;
        border-radius: 20px;
        padding: 0 1.5rem 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(99,102,241,0.08);
        position: sticky; top: 80px;
        overflow: hidden;
    }
    .profile-card-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        margin: 0 -1.5rem;
        height: 140px;
        position: relative;
        overflow: hidden;
    }
    .profile-card-header::after {
        content: '';
        position: absolute;
        bottom: -30px; right: -20px;
        width: 120px; height: 120px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
    }
    .profile-card-header::before {
        content: '';
        position: absolute;
        bottom: -50px; right: 40px;
        width: 90px; height: 90px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .profile-card-header-dots {
        position: absolute;
        top: 14px; right: 18px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 5px;
    }
    .profile-card-header-dots span {
        width: 5px; height: 5px;
        border-radius: 50%;
        background: rgba(255,255,255,0.4);
        display: block;
    }
    .avatar-circle {
        width: 110px; height: 110px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; font-weight: 800; color: white;
        box-shadow: 0 8px 28px rgba(99,102,241,0.4);
        border: 5px solid white;
        margin-top: -55px;
        position: relative; z-index: 1;
    }
    .profile-card-name { font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-top: 0.7rem; }
    .profile-card-role {
        display: inline-block; margin-top: 5px;
        background: #eef2ff; color: #6366f1;
        border: 1px solid #c7d2fe;
        font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; padding: 3px 14px; border-radius: 999px;
    }
    .profile-card-meta {
        font-size: 0.82rem; color: #64748b;
        word-break: break-all; text-align: left;
        display: flex; align-items: center; gap: 10px;
        padding: 8px 0; border-bottom: 1px solid #f1f5f9;
    }
    .profile-card-meta:last-child { border-bottom: none; }
    .profile-card-meta i { color: #6366f1; font-size: 0.9rem; flex-shrink: 0; }

    /* Profile Completion */
    .profile-completion {
        background: #f5f3ff; border-radius: 16px;
        padding: 0.85rem 1rem;
        display: flex; align-items: center; gap: 0.85rem;
        margin-top: 1rem;
    }
    .completion-ring {
        width: 52px; height: 52px; flex-shrink: 0;
        position: relative;
    }
    .completion-ring svg { transform: rotate(-90deg); }
    .completion-ring-bg { fill: none; stroke: #e0e7ff; stroke-width: 5; }
    .completion-ring-fill { fill: none; stroke: #6366f1; stroke-width: 5; stroke-linecap: round; transition: stroke-dashoffset 0.5s; }
    .completion-pct {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 800; color: #6366f1;
    }
    .completion-label { font-size: 0.78rem; font-weight: 700; color: #4f46e5; }
    .completion-sub   { font-size: 0.72rem; color: #64748b; margin-top: 2px; }

    /* Tabs */
    .profile-tabs {
        border-bottom: none; gap: 4px; list-style: none;
        padding: 5px; margin: 0 0 1.5rem 0;
        background: white; border-radius: 14px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        display: flex; flex-wrap: wrap;
    }
    .profile-tab {
        background: none; border: none; padding: 0.6rem 1.2rem;
        font-size: 0.85rem; font-weight: 600; color: #64748b;
        border-radius: 10px; cursor: pointer; transition: all 0.2s;
        white-space: nowrap; display: flex; align-items: center; gap: 5px;
    }
    .profile-tab:hover { background: #f1f5f9; color: #4f46e5; }
    .profile-tab.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        box-shadow: 0 4px 12px rgba(99,102,241,0.3);
    }

    /* Alerts */
    .profile-alert { padding: 0.85rem 1.1rem; border-radius: 12px; font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem; }
    .profile-alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .profile-alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Section Card */
    .section-card {
        background: white; border-radius: 20px; padding: 2rem 2.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(99,102,241,0.06);
    }
    .section-card-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
    .section-card-sub   { font-size: 0.85rem; color: #64748b; margin-top: 3px; }

    /* Fields */
    .field-label { font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px; display: block; letter-spacing: 0.2px; }
    .input-group-custom { position: relative; display: flex; align-items: center; }
    .input-icon { position: absolute; left: 14px; color: #a5b4fc; font-size: 0.95rem; pointer-events: none; z-index: 1; }
    .field-input {
        width: 100%; padding: 0.7rem 1rem 0.7rem 2.5rem;
        border: 1.5px solid #e2e8f0; border-radius: 12px;
        font-size: 0.875rem; color: #0f172a; background: #fafbff; outline: none;
        transition: all 0.2s;
    }
    .field-input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        background: white;
    }
    .field-input:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
    .pw-toggle { position: absolute; right: 12px; background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 0.9rem; padding: 0; z-index: 1; }
    .pw-toggle:hover { color: #6366f1; }

    /* Password strength */
    .strength-bar { height: 6px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-top: 8px; }
    .strength-fill { height: 100%; width: 0; border-radius: 999px; transition: width 0.3s, background 0.3s; }
    .strength-label { font-size: 0.75rem; font-weight: 600; margin-top: 4px; }
    .pw-tips { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-top: 0.75rem; }
    .pw-tip  { font-size: 0.78rem; color: #94a3b8; transition: color 0.2s; display: flex; align-items: center; gap: 4px; }
    .pw-tip.tip-pass { color: #10b981; }

    /* Save button */
    .btn-save {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white; border: none; font-weight: 700;
        font-size: 0.875rem; padding: 0.7rem 2rem;
        border-radius: 12px; cursor: pointer;
        box-shadow: 0 4px 16px rgba(99,102,241,0.35);
        transition: all 0.2s; letter-spacing: 0.2px;
    }
    .btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.45); }
    .btn-save:active { transform: translateY(0); }

    /* Language */
    .lang-options { display: flex; flex-direction: column; gap: 0.75rem; }
    .lang-option { cursor: pointer; }
    .lang-option input { display: none; }
    .lang-option-inner {
        display: flex; align-items: center; gap: 1rem;
        padding: 1rem 1.25rem; border-radius: 16px;
        border: 2px solid #e2e8f0; background: #fafbff; transition: all 0.2s;
    }
    .lang-option input:checked + .lang-option-inner {
        border-color: #6366f1; background: #eef2ff;
        box-shadow: 0 4px 12px rgba(99,102,241,0.12);
    }
    .lang-flag { font-size: 1.8rem; }
    .lang-name { font-size: 0.9rem; font-weight: 700; color: #0f172a; }
    .lang-desc { font-size: 0.78rem; color: #64748b; }
    .lang-check { color: #e2e8f0; font-size: 1.2rem; transition: color 0.2s; }
    .lang-option input:checked + .lang-option-inner .lang-check { color: #6366f1; }
    .lang-note { font-size: 0.78rem; color: #94a3b8; }

    /* History */
    .btn-clear-history {
        background: #fef2f2; color: #ef4444; border: 1px solid #fecaca;
        padding: 0.45rem 0.85rem; border-radius: 10px; font-weight: 600;
        cursor: pointer; font-size: 0.8rem; transition: all 0.2s;
    }
    .btn-clear-history:hover { background: #fee2e2; }
    .history-list { display: grid; gap: 0.75rem; }
    .history-item {
        background: #fafbff; border: 1px solid #e0e7ff;
        border-radius: 14px; padding: 1rem;
        transition: box-shadow 0.2s;
    }
    .history-item:hover { box-shadow: 0 4px 12px rgba(99,102,241,0.1); }
    .history-item-meta { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; font-size: 0.88rem; color: #0f172a; }
    .history-item-meta strong { font-weight: 700; }
    .history-item-meta span { color: #94a3b8; font-size: 0.78rem; }
    .history-item-details { margin-top: 0.4rem; color: #64748b; font-size: 0.82rem; }
    .history-empty {
        background: white; border: 2px dashed #e0e7ff;
        border-radius: 16px; padding: 2.5rem;
        text-align: center; color: #94a3b8;
    }
    .history-empty i { font-size: 2.5rem; color: #c7d2fe; display: block; margin-bottom: 0.75rem; }

    .tab-section { display: block; }
    .tab-section.d-none { display: none !important; }

    @media (max-width: 768px) {
        .profile-card { position: static; margin-bottom: 1.5rem; }
        .section-card { padding: 1.25rem; }
        .profile-tabs { gap: 4px; }
        .profile-tab { padding: 0.45rem 0.8rem; font-size: 0.78rem; }
    }
</style>
</body>
</html>
