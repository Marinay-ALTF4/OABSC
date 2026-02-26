<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php
$errors = session()->getFlashdata('errors') ?? [];
if (! is_array($errors)) {
    $errors = [];
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Add User</h1>
        <a href="<?= site_url('/admin/patients/list') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('/admin/patients/add') ?>" method="post" id="addUserForm" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                        value="<?= old('name') ?>"
                        pattern="[A-Za-zÑñ\s]+"
                        title="Letters, spaces, and ñ only"
                        required
                    >
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['name']) ?></div>
                    <?php endif; ?>
                    <div id="nameLiveError" class="invalid-feedback" style="display:none;">Name allows letters and spaces only (ñ is allowed).</div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                        value="<?= old('email') ?>"
                        required
                    >
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['email']) ?></div>
                    <?php endif; ?>
                    <div id="emailLiveError" class="invalid-feedback" style="display:none;">Email allows maximum 5 numbers and 3 special characters before @.</div>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" required>
                        <option value="client" <?= old('role', 'client') === 'client' ? 'selected' : '' ?>>Client</option>
                        <option value="admin" <?= old('role', 'client') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <?php if (isset($errors['role'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['role']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                        minlength="8"
                        required
                    >
                    <div class="form-text">Reminder: Password must be at least 8 characters.</div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['password']) ?></div>
                    <?php endif; ?>
                    <div id="passwordLiveError" class="invalid-feedback" style="display:none;">Password must be at least 8 characters.</div>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label">Confirm Password</label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                        minlength="8"
                        required
                    >
                    <?php if (isset($errors['password_confirm'])): ?>
                        <div class="invalid-feedback"><?= esc($errors['password_confirm']) ?></div>
                    <?php endif; ?>
                    <div id="passwordConfirmLiveError" class="invalid-feedback" style="display:none;">Please confirm your password.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Add user</button>
                    <a href="<?= site_url('/admin/patients/list') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    var form = document.getElementById('addUserForm');
    var nameInput = document.getElementById('name');
    var emailInput = document.getElementById('email');
    var passwordInput = document.getElementById('password');
    var passwordConfirmInput = document.getElementById('password_confirm');

    var nameLiveError = document.getElementById('nameLiveError');
    var emailLiveError = document.getElementById('emailLiveError');
    var passwordLiveError = document.getElementById('passwordLiveError');
    var passwordConfirmLiveError = document.getElementById('passwordConfirmLiveError');

    function toggleError(input, errorEl, isInvalid, message) {
        if (!input || !errorEl) {
            return;
        }

        if (isInvalid) {
            input.classList.add('is-invalid');
            if (message) {
                errorEl.textContent = message;
            }
            errorEl.style.display = 'block';
        } else {
            input.classList.remove('is-invalid');
            errorEl.style.display = 'none';
        }
    }

    function validateName(force) {
        var value = (nameInput.value || '').trim();
        if (force && value === '') {
            toggleError(nameInput, nameLiveError, true, 'Full name is required.');
            return false;
        }

        if (value.length > 0 && value.length < 3) {
            toggleError(nameInput, nameLiveError, true, 'Full name must be at least 3 characters.');
            return false;
        }

        var valid = /^[A-Za-zÑñ\s]+$/.test(value);
        var isInvalid = value.length > 0 && !valid;
        toggleError(nameInput, nameLiveError, isInvalid, 'Name allows letters and spaces only (ñ is allowed).');
        return !isInvalid;
    }

    function validateEmailLimits(force) {
        var value = (emailInput.value || '').trim();

        if (force && value === '') {
            toggleError(emailInput, emailLiveError, true, 'Email is required.');
            return false;
        }

        if (value.length > 0 && value.indexOf('@') === -1) {
            toggleError(emailInput, emailLiveError, true, 'Please enter a valid email address.');
            return false;
        }

        if (value === '') {
            toggleError(emailInput, emailLiveError, false);
            return true;
        }

        var localPart = value.split('@')[0] || '';
        var numberCount = (localPart.match(/\d/g) || []).length;
        var specialCount = (localPart.match(/[^a-z0-9]/gi) || []).length;
        var isInvalid = numberCount > 5 || specialCount > 3;
        toggleError(emailInput, emailLiveError, isInvalid, 'Email allows maximum 5 numbers and 3 special characters before @.');
        return !isInvalid;
    }

    function validatePassword(force) {
        var value = passwordInput.value || '';

        if (force && value === '') {
            toggleError(passwordInput, passwordLiveError, true, 'Password is required.');
            return false;
        }

        var isInvalid = value.length > 0 && value.length < 8;
        toggleError(passwordInput, passwordLiveError, isInvalid, 'Password must be at least 8 characters.');
        return !isInvalid;
    }

    function validatePasswordConfirm(force) {
        var value = passwordConfirmInput.value || '';
        var passwordValue = passwordInput.value || '';

        if (force && value === '') {
            toggleError(passwordConfirmInput, passwordConfirmLiveError, true, 'Please confirm your password.');
            return false;
        }

        if (value.length > 0 && value !== passwordValue) {
            toggleError(passwordConfirmInput, passwordConfirmLiveError, true, 'Confirm password must match password.');
            return false;
        }

        toggleError(passwordConfirmInput, passwordConfirmLiveError, false);
        return true;
    }

    if (nameInput) {
        nameInput.addEventListener('input', function() {
            validateName(false);
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            validateEmailLimits(false);
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword(false);
            validatePasswordConfirm(false);
        });
    }

    if (passwordConfirmInput) {
        passwordConfirmInput.addEventListener('input', function() {
            validatePasswordConfirm(false);
        });
    }

    if (form) {
        form.addEventListener('submit', function(event) {
            var okName = validateName(true);
            var okEmail = validateEmailLimits(true);
            var okPassword = validatePassword(true);
            var okPasswordConfirm = validatePasswordConfirm(true);

            if (!okName || !okEmail || !okPassword || !okPasswordConfirm || !form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }
})();
</script>
</body>
</html>
