<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role | Clinic Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top left, #e0f2ff, #f9fbff);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .card {
            max-width: 440px;
            width: 100%;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15,23,42,0.12);
            border: 1px solid rgba(148,163,184,0.3);
        }
        .role-btn {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            width: 100%;
            text-align: left;
        }
        .role-btn:hover, .role-btn.selected {
            border-color: #2563eb;
            background: #eff6ff;
        }
        .role-btn .role-title { font-weight: 700; font-size: 0.9rem; color: #0f172a; }
        .role-btn .role-sub { font-size: 0.78rem; color: #64748b; }
        .btn-primary {
            border-radius: 999px;
            padding: 0.7rem 1.3rem;
            font-weight: 600;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }
    </style>
</head>
<body>
<div class="container px-3">
    <div class="card bg-white p-4 p-md-5 mx-auto">
        <div class="text-center mb-4">
            <h5 class="fw-bold mb-1">Select Your Role</h5>
            <p class="text-muted small">Enter the clinic access code and choose your role to continue.</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger small py-2">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('/role-selection/verify') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:0.85rem;">Clinic Access Code</label>
                <input type="password" name="clinic_code" class="form-control" placeholder="Enter clinic access code" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size:0.85rem;">Select Role</label>
                <div class="d-flex flex-column gap-2" id="roleOptions">
                    <button type="button" class="role-btn" onclick="selectRole('admin', this)">
                        <div class="role-title">Admin</div>
                        <div class="role-sub">Full access to all system features</div>
                    </button>
                    <button type="button" class="role-btn" onclick="selectRole('assistant_admin', this)">
                        <div class="role-title">Assistant Admin</div>
                        <div class="role-sub">Limited admin access - cannot manage users</div>
                    </button>
                </div>
                <input type="hidden" name="role" id="selectedRole">
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size:0.85rem;">Role Password</label>
                <input type="password" name="role_password" class="form-control" placeholder="Enter your role password" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Continue</button>
            </div>

            <p class="text-center text-muted small mt-3 mb-0">
                <a href="<?= site_url('/logout') ?>">Back to Login</a>
            </p>
        </form>
    </div>
</div>

<script>
function selectRole(role, btn) {
    document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    document.getElementById('selectedRole').value = role;
}
</script>
</body>
</html>
