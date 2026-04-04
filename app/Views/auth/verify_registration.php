<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | Clinic Appointment Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
$success = session()->getFlashdata('success');
$error = session()->getFlashdata('error');
$errors = session()->getFlashdata('errors') ?? [];
if (! is_array($errors)) {
    $errors = [];
}
$codeErr = $errors['verification_code'] ?? null;
$formErr = $errors['_form'] ?? null;
$pendingEmail = $pendingEmail ?? '';
$expiresAt = (int) ($expiresAt ?? 0);
$minutesLeft = $expiresAt > time() ? (int) ceil(($expiresAt - time()) / 60) : 0;
?>
<div class="container px-3">
    <div class="verify-card bg-white p-4 p-md-5 mx-auto">
        <div class="mb-3 text-center">
            <div class="brand-pill mb-2">
                <?php if (is_file(FCPATH . 'images/logo.png')): ?>
                    <img src="<?= base_url('images/logo.png') ?>" alt="" class="logo">
                <?php else: ?>
                    <span class="icon-fallback">⚕</span>
                <?php endif; ?>
                <span>Clinic Appointment Portal</span>
            </div>
            <h1 class="h4 fw-bold text-dark mb-1">Verify Email</h1>
        </div>

        <p class="verify-copy text-center">Enter the 6-digit code we sent to <?= esc($pendingEmail) ?>. Your account will be created after the code matches.</p>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 mb-3"><?= esc($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 mb-3"><?= esc($error) ?></div>
        <?php endif; ?>

        <?php if ($formErr): ?>
            <div class="alert alert-danger py-2 mb-3"><?= esc($formErr) ?></div>
        <?php endif; ?>

        <?php if ($minutesLeft > 0): ?>
            <div class="alert alert-info py-2 mb-3">Code expires in <?= esc((string) $minutesLeft) ?> minute<?= $minutesLeft === 1 ? '' : 's' ?>.</div>
        <?php endif; ?>

        <form action="<?= site_url('/register/verify') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="verification_code" class="form-label">Verification code</label>
                <input
                    type="text"
                    class="form-control <?= $codeErr ? 'is-invalid' : '' ?>"
                    id="verification_code"
                    name="verification_code"
                    inputmode="numeric"
                    maxlength="6"
                    minlength="6"
                    placeholder="Enter 6-digit code"
                    value="<?= old('verification_code') ?>"
                    required
                >
                <?php if ($codeErr): ?>
                    <div class="invalid-feedback d-block"><?= esc($codeErr) ?></div>
                <?php endif; ?>
            </div>

            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-primary btn-verify">Verify and Create Account</button>
            </div>
        </form>

        <form action="<?= site_url('/register/resend-code') ?>" method="post" class="mb-3 mt-2">
            <?= csrf_field() ?>
            <div class="d-grid">
                <button type="submit" class="btn btn-outline-secondary">Resend code</button>
            </div>
        </form>

        <p class="text-center text-muted small-text mb-0">
            Wrong email?
            <a href="<?= site_url('/register/reset') ?>" class="fw-semibold">Try Again</a>
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
    .verify-card {
        max-width: 420px;
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
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
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .brand-pill img.logo {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }
    .verify-copy {
        color: #475569;
        font-size: 0.95rem;
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
    .form-control::placeholder {
        color: #9ca3af;
    }
    .form-control:focus {
        box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.35);
        border-color: #2563eb;
    }
    .btn-verify {
        border-radius: 999px;
        padding: 0.7rem 1.3rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        font-size: 0.8rem;
        background: linear-gradient(135deg, #30374f, #23293a);
        border: none;
        box-shadow: 0 14px 28px rgba(35, 41, 58, 0.35);
        color: #fff;
    }
    .btn-verify:hover {
        background: linear-gradient(135deg, #23293a, #171c28);
        color: #fff;
    }
    .btn-outline-secondary {
        border-radius: 999px;
        padding: 0.7rem 1.3rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        font-size: 0.8rem;
    }
    .small-text {
        font-size: 0.78rem;
    }
</style>
</body>
</html>
