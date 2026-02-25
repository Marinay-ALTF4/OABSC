<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Online Appointment Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top left, #fef3c7, #fef9e7);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .login-card {
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
            background: rgba(245, 158, 11, 0.1);
            color: #b45309;
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
            box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.35);
            border-color: #f59e0b;
        }
        .btn-warning {
            border-radius: 999px;
            padding: 0.7rem 1.3rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-size: 0.8rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            box-shadow: 0 14px 28px rgba(245, 158, 11, 0.35);
        }
        .btn-warning:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
        }
        .badge-role {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
        }
        .small-text {
            font-size: 0.78rem;
        }
    </style>
</head>
<body>
<div class="container px-3">
    <div class="login-card bg-white p-4 p-md-5 mx-auto">
        <div class="mb-3 text-center">
            <div class="brand-pill mb-2">
                <img src="<?= base_url('images/logo.png') ?>" alt="Clinic Logo" class="logo">
                <span>Clinic Management System</span>
            </div>
            <h1 class="h4 fw-bold text-slate-900 mb-1">Admin Login</h1>
            <span class="badge-role">Staff Access</span>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger small-text py-2 mb-3">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('/login') ?>" method="post" class="mt-2">
            <?= csrf_field() ?>
            <input type="hidden" name="login_type" value="admin">

            <div class="mb-3">
                <label for="email" class="form-label">Admin Email</label>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Enter admin email"
                    value="<?= old('email') ?>"
                    required
                >
            </div>

            <div class="mb-2">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="Enter admin password"
                    required
                >
            </div>

            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-warning">
                    Sign in as Admin
                </button>
            </div>

            <hr class="my-3">

            <p class="text-center text-muted small-text mb-0">
                Not an admin?
                <a href="<?= site_url('/login') ?>" class="fw-semibold">Patient Login</a>
            </p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
