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
            background: radial-gradient(circle at top left, #e0f2ff, #f9fbff);
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
            background: rgba(59, 130, 246, 0.08);
            color: #1d4ed8;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .brand-pill span.icon {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 30% 0, #38bdf8, #1d4ed8);
            color: #eff6ff;
            font-size: 0.9rem;
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
        .btn-primary {
            border-radius: 999px;
            padding: 0.7rem 1.3rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-size: 0.8rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.35);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
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
                <span class="icon">+</span>
                <span>Clinic Appointment Portal</span>
            </div>
            <h1 class="h4 fw-bold mb-1">Create account</h1>
            <p class="text-muted small-text mb-0">Register as a client to book appointments.</p>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger small py-2 mb-3">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('/register') ?>" method="post" class="mt-2">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="name" class="form-label">Full name</label>
                <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    value="<?= old('name') ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    value="<?= old('email') ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm password</label>
                <input
                    type="password"
                    class="form-control"
                    id="password_confirm"
                    name="password_confirm"
                    required
                >
            </div>

            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-primary">
                    Register
                </button>
            </div>

            <p class="text-center text-muted small mb-0">
                Already have an account?
                <a href="<?= site_url('/login') ?>" class="fw-semibold">Login</a>
            </p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

