<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication | Clinic Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container px-3">
    <div class="mfa-card bg-white p-4 p-md-5 mx-auto">
        <div class="mb-3 text-center">
            <div class="brand-pill mb-2">
                <img src="/OABSC/images/logo.png" alt="Clinic Logo" class="logo">
                <span>Clinic Appointment Portal</span>
            </div>
            <h1 class="h4 fw-bold text-slate-900 mb-1">Two-Factor Authentication</h1>
            <p class="text-muted small-text mb-0">
                We sent a 6-digit code to <strong><?= esc($pendingEmail) ?></strong>
            </p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger small-text py-2 mb-3">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success small-text py-2 mb-3">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php $errors = session()->getFlashdata('errors') ?? []; ?>
        <?php if (! empty($errors['_form'])): ?>
            <div class="alert alert-danger small-text py-2 mb-3">
                <?= esc($errors['_form']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('/login/verify-mfa') ?>" method="post" class="mt-2">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="mfa_code" class="form-label">Verification Code</label>
                <input
                    type="text"
                    class="form-control text-center fs-4 fw-bold letter-spacing-wide <?= ! empty($errors['mfa_code']) ? 'is-invalid' : '' ?>"
                    id="mfa_code"
                    name="mfa_code"
                    placeholder="000000"
                    maxlength="6"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    required
                >
                <?php if (! empty($errors['mfa_code'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['mfa_code']) ?></div>
                <?php endif; ?>
                <div class="form-text text-center">
                    Code expires in <span id="countdown" class="fw-semibold text-danger"></span>
                </div>
            </div>

            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-primary">Verify & Sign In</button>
            </div>
        </form>

        <form action="<?= site_url('/login/resend-mfa') ?>" method="post" class="text-center">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-link small-text p-0">Resend code</button>
        </form>

        <p class="text-center text-muted small-text mt-2 mb-0">
            <a href="<?= site_url('/login') ?>">Back to login</a>
        </p>
    </div>
</div>

<style>
    body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at top left, #e0f2ff, #f9fbff);
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .mfa-card {
        max-width: 420px;
        width: 100%;
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
        border: 1px solid rgba(148, 163, 184, 0.3);
    }
    .brand-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.3rem 0.85rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.08);
        color: #1d4ed8;
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .brand-pill img.logo {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }
    .form-label {
        font-weight: 600;
        font-size: 0.86rem;
        color: #0f172a;
    }
    .form-control {
        border-radius: 0.8rem;
        padding: 0.7rem 0.9rem;
        border-color: rgba(148, 163, 184, 0.6);
    }
    .form-control:focus {
        box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.35);
        border-color: #2563eb;
    }
    .letter-spacing-wide { letter-spacing: 0.4em; }
    .btn-primary {
        border-radius: 999px;
        padding: 0.7rem 1.3rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        font-size: 0.8rem;
        background: linear-gradient(135deg, #30374f, #23293a);
        box-shadow: 0 14px 28px rgba(35, 41, 58, 0.35);
    }
    .btn-primary:hover { background: linear-gradient(135deg, #23293a, #171c28); }
    .small-text { font-size: 0.78rem; }
</style>

<script>
    const expiresAt = <?= (int) $expiresAt ?>;
    const countdownEl = document.getElementById('countdown');

    function updateCountdown() {
        const remaining = expiresAt - Math.floor(Date.now() / 1000);
        if (remaining <= 0) {
            countdownEl.textContent = 'expired';
            return;
        }
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        countdownEl.textContent = m + ':' + String(s).padStart(2, '0');
        setTimeout(updateCountdown, 1000);
    }
    updateCountdown();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
