<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Clinic Appointment Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #dbeafe;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .auth-card {
            max-width: 420px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.2);
            overflow: hidden;
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            background-color: #1e3a5f;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .brand-badge img.logo {
            width: 28px;
            height: 28px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }
        .brand-badge .icon-fallback {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .form-label {
            font-weight: 400;
            font-size: 0.95rem;
            color: #1f2937;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            font-size: 0.95rem;
            border: 1px solid #d1d5db;
            background: #fff;
        }
        .form-control::placeholder {
            color: #9ca3af;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
            outline: none;
        }
        .btn-register {
            width: 100%;
            border-radius: 10px;
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background-color: #2563eb;
            border: none;
            color: #fff;
            transition: background-color 0.2s;
        }
        .btn-register:hover {
            background-color: #1d4ed8;
            color: #fff;
        }
        .footer-link {
            color: #374151;
            font-size: 0.9rem;
        }
        .footer-link a {
            color: #2563eb;
            text-decoration: underline;
            font-weight: 500;
        }
        .footer-link a:hover {
            color: #1d4ed8;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.15);
        }
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<?php
$errors = session()->getFlashdata('errors') ?? [];
if (! is_array($errors)) {
    $errors = [];
}
$nameErr = $errors['name'] ?? null;
$emailErr = $errors['email'] ?? null;
$passwordErr = $errors['password'] ?? null;
$passwordConfirmErr = $errors['password_confirm'] ?? null;
$formErr = $errors['_form'] ?? null;
?>
<div class="container px-3">
    <div class="auth-card p-4 p-md-5 mx-auto">
        <div class="text-center mb-4">
            <div class="brand-badge mb-3">
                <?php if (is_file(FCPATH . 'images/logo.png')): ?>
                    <img src="<?= base_url('images/logo.png') ?>" alt="" class="logo">
                <?php else: ?>
                    <span class="icon-fallback">⚕</span>
                <?php endif; ?>
                <span>Clinic Appointment Portal</span>
            </div>
            <h1 class="h4 fw-bold text-dark mb-0">Register</h1>
        </div>

        <?php if ($formErr): ?>
            <div class="alert alert-danger py-2 mb-3" role="alert">
                <?= esc($formErr) ?>
            </div>
        <?php endif; ?>

        <?php if ($nameErr || $emailErr || $passwordErr || $passwordConfirmErr): ?>
            <div class="alert alert-danger py-2 mb-3" role="alert">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    <?php if ($nameErr): ?><li><?= esc($nameErr) ?></li><?php endif; ?>
                    <?php if ($emailErr): ?><li><?= esc($emailErr) ?></li><?php endif; ?>
                    <?php if ($passwordErr): ?><li><?= esc($passwordErr) ?></li><?php endif; ?>
                    <?php if ($passwordConfirmErr): ?><li><?= esc($passwordConfirmErr) ?></li><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('/register') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="name" class="form-label">Full name</label>
                <input
                    type="text"
                    class="form-control <?= $nameErr ? 'is-invalid' : '' ?>"
                    id="name"
                    name="name"
                    placeholder="Enter your full name"
                    value="<?= old('name') ?>"
                    required
                    pattern="[A-Za-z\s]+"
                    title="Letters and spaces only"
                >
                <?php if ($nameErr): ?>
                    <div class="invalid-feedback d-block"><?= esc($nameErr) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control <?= $emailErr ? 'is-invalid' : '' ?>"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?= old('email') ?>"
                    required
                >
                <?php if ($emailErr): ?>
                    <div class="invalid-feedback d-block"><?= esc($emailErr) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    class="form-control <?= $passwordErr ? 'is-invalid' : '' ?>"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    minlength="8"
                    required
                >
                <?php if ($passwordErr): ?>
                    <div class="invalid-feedback d-block"><?= esc($passwordErr) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="password_confirm" class="form-label">Confirm password</label>
                <input
                    type="password"
                    class="form-control <?= $passwordConfirmErr ? 'is-invalid' : '' ?>"
                    id="password_confirm"
                    name="password_confirm"
                    placeholder="Confirm your password"
                    required
                >
                <?php if ($passwordConfirmErr): ?>
                    <div class="invalid-feedback d-block"><?= esc($passwordConfirmErr) ?></div>
                <?php endif; ?>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-register">Register for Clinic Portal</button>
            </div>

            <p class="text-center footer-link mb-0">
                Already have an account?
                <a href="<?= site_url('/login') ?>">Login</a>
            </p>
        </form>
    </div>
<script>
(function() {
    var img = document.querySelector('.brand-badge .logo');
    var fallback = document.querySelector('.brand-badge .icon-fallback');
    if (img && fallback && !img.complete || img.naturalWidth === 0) {
        img.style.display = 'none';
        fallback.style.display = 'inline-flex';
    }
})();
</script>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
