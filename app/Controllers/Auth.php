<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoginEventModel;
use App\Models\NotificationModel;
use App\Models\AuthSessionModel;

class Auth extends BaseController
{
    private const PENDING_REGISTRATION_KEY = 'pending_registration';
    private const PENDING_MFA_KEY          = 'pending_mfa';
    private const VERIFICATION_TTL_SECONDS = 600;
    private const MFA_TTL_SECONDS          = 300;
    private const VERIFICATION_MAX_ATTEMPTS = 5;
    private const MFA_MAX_ATTEMPTS          = 5;

    // Seeded/system accounts that skip MFA
    private const MFA_BYPASS_DOMAIN = '@example.com';

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $validationRules = [
            'email'    => 'required',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your login details.');
        }

        $identifier = trim((string) $this->request->getPost('email'));
        $password   = (string) $this->request->getPost('password');

        $logModel = new LoginEventModel();
        $db       = \Config\Database::connect();

        // Use prepared statement to fetch user securely
        $user = $db->query(
                        'SELECT u.id,
                                        COALESCE(up.name, u.username, "") AS name,
                                        u.email,
                                        COALESCE(up.phone, "") AS phone,
                                        u.role,
                                        ua.password_hash AS password_hash,
                                        COALESCE(ua.failed_login_count, 0) AS failed_login_count,
                                        ua.lock_until AS lock_until,
                                        ua.last_login_at AS last_login_at,
                                        ua.user_id AS auth_user_id,
                                        u.deleted_at
                         FROM users u
                         LEFT JOIN user_profiles up ON up.user_id = u.id
                         LEFT JOIN user_auth ua ON ua.user_id = u.id
                         WHERE (u.email = ? OR up.phone = ?)
                             AND u.deleted_at IS NULL
                         LIMIT 1',
            [$identifier, $identifier]
        )->getRowArray();

        // User not found
        if (! $user) {
            $logModel->log(LoginEventModel::EVENT_LOGIN_FAILED, null, $identifier, 'user_not_found');
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        $userId = (int) $user['id'];

        // Check if account is locked
        if (! empty($user['lock_until']) && strtotime((string) $user['lock_until']) > time()) {
            $logModel->log(LoginEventModel::EVENT_LOGIN_LOCKED, $userId, $identifier, 'account_locked');
            return redirect()->back()->withInput()->with('error', 'Account locked. Try again later.');
        }

        // Wrong password — increment failed count
        if (! password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            $failCount = ((int) ($user['failed_login_count'] ?? 0)) + 1;
            $lockUntil = null;
            $authTable = ! empty($user['auth_user_id']) ? 'user_auth' : 'users';
            $authKey   = $authTable === 'user_auth' ? 'user_id' : 'id';

            if ($failCount >= 5) {
                $lockUntil = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                $logModel->log(LoginEventModel::EVENT_SUSPICIOUS, $userId, $identifier, 'too_many_failures');
                $this->alertAdminsOfSuspiciousActivity($user);
            }

            // Prepared statement update
            $db->query(
                "UPDATE {$authTable} SET failed_login_count = ?, lock_until = ? WHERE {$authKey} = ?",
                [$failCount, $lockUntil, $userId]
            );

            $logModel->log(LoginEventModel::EVENT_LOGIN_FAILED, $userId, $identifier, 'wrong_password');
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        // Successful login — reset lockout fields + update last_login_at
        $db->query(
            ! empty($user['auth_user_id'])
                ? 'UPDATE user_auth SET failed_login_count = 0, lock_until = NULL, last_login_at = ? WHERE user_id = ?'
                : 'UPDATE users SET failed_login_count = 0, lock_until = NULL, last_login_at = ? WHERE id = ?',
            [date('Y-m-d H:i:s'), $userId]
        );

        $logModel->log(LoginEventModel::EVENT_LOGIN_SUCCESS, $userId, $identifier);

        // Create auth session record for tracking
        $sessionToken = (new AuthSessionModel())->createSession($userId);
        session()->set('auth_session_token', $sessionToken);

        // Skip MFA for seeded/system accounts (@example.com)
        $isSeededUser = str_ends_with(strtolower((string) $user['email']), self::MFA_BYPASS_DOMAIN);

        if ($isSeededUser) {
            session()->set([
                'user_id'    => $user['id'],
                'user_name'  => $user['name'] ?? '',
                'user_email' => $user['email'] ?? '',
                'user_phone' => $user['phone'] ?? '',
                'user_role'  => $user['role'] ?? 'client',
                'isLoggedIn' => true,
            ]);

            return redirect()->to('/dashboard');
        }

        // Real registered users — send MFA OTP
        $mfaCode = (string) random_int(100000, 999999);

        $pendingMfa = [
            'user_id'        => $user['id'],
            'user_name'      => $user['name'] ?? '',
            'user_email'     => $user['email'] ?? '',
            'user_phone'     => $user['phone'] ?? '',
            'user_role'      => $user['role'] ?? 'client',
            'email'          => $user['email'],
            'mfa_code_hash'  => password_hash($mfaCode, PASSWORD_DEFAULT),
            'mfa_expires_at' => time() + self::MFA_TTL_SECONDS,
            'mfa_attempts'   => 0,
        ];

        if (! $this->sendMfaCode((string) $user['email'], (string) ($user['name'] ?? ''), $mfaCode)) {
            return redirect()->back()->withInput()->with('error', 'Unable to send verification code. Please check email settings and try again.');
        }

        session()->set(self::PENDING_MFA_KEY, $pendingMfa);

        return redirect()->to('/login/verify-mfa')->with('success', 'A 6-digit verification code has been sent to your email.');
    }

    public function verifyMfa()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $pendingMfa = $this->getPendingMfa();

        if (! $pendingMfa) {
            return redirect()->to('/login')->with('error', 'Session expired. Please log in again.');
        }

        if ($this->request->is('post')) {
            if (! $this->validate(['mfa_code' => 'required|exact_length[6]|numeric'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $pendingMfa = $this->getPendingMfa();

            if (! $pendingMfa) {
                return redirect()->to('/login')->with('error', 'Session expired. Please log in again.');
            }

            if (($pendingMfa['mfa_expires_at'] ?? 0) < time()) {
                session()->remove(self::PENDING_MFA_KEY);
                return redirect()->to('/login')->with('error', 'Verification code expired. Please log in again.');
            }

            $submittedCode = trim((string) $this->request->getPost('mfa_code'));

            if (! password_verify($submittedCode, (string) ($pendingMfa['mfa_code_hash'] ?? ''))) {
                $attempts = ((int) ($pendingMfa['mfa_attempts'] ?? 0)) + 1;

                if ($attempts >= self::MFA_MAX_ATTEMPTS) {
                    session()->remove(self::PENDING_MFA_KEY);
                    (new LoginEventModel())->log(LoginEventModel::EVENT_MFA_FAILED, (int) $pendingMfa['user_id'], null, 'max_attempts');
                    return redirect()->to('/login')->with('error', 'Too many incorrect attempts. Please log in again.');
                }

                $pendingMfa['mfa_attempts'] = $attempts;
                session()->set(self::PENDING_MFA_KEY, $pendingMfa);
                (new LoginEventModel())->log(LoginEventModel::EVENT_MFA_FAILED, (int) $pendingMfa['user_id'], null, 'wrong_code');
                return redirect()->back()->withInput()->with('errors', ['mfa_code' => 'Invalid verification code.']);
            }

            session()->remove(self::PENDING_MFA_KEY);
            (new LoginEventModel())->log(LoginEventModel::EVENT_MFA_SUCCESS, (int) $pendingMfa['user_id']);

            // Create auth session record
            $sessionToken = (new AuthSessionModel())->createSession((int) $pendingMfa['user_id']);
            session()->set('auth_session_token', $sessionToken);
            session()->set([
                'user_id'    => $pendingMfa['user_id'],
                'user_name'  => $pendingMfa['user_name'],
                'user_email' => $pendingMfa['user_email'] ?? '',
                'user_role'  => $pendingMfa['user_role'],
                'isLoggedIn' => true,
            ]);

            return redirect()->to('/dashboard');
        }

        return view('auth/verify_mfa', [
            'pendingEmail' => $pendingMfa['email'] ?? '',
            'expiresAt'    => (int) ($pendingMfa['mfa_expires_at'] ?? 0),
        ]);
    }

    public function resendMfaCode()
    {
        $pendingMfa = $this->getPendingMfa();

        if (! $pendingMfa) {
            return redirect()->to('/login')->with('error', 'Session expired. Please log in again.');
        }

        $mfaCode = (string) random_int(100000, 999999);
        $pendingMfa['mfa_code_hash']  = password_hash($mfaCode, PASSWORD_DEFAULT);
        $pendingMfa['mfa_expires_at'] = time() + self::MFA_TTL_SECONDS;
        $pendingMfa['mfa_attempts']   = 0;

        if (! $this->sendMfaCode((string) ($pendingMfa['email'] ?? ''), (string) ($pendingMfa['user_name'] ?? ''), $mfaCode)) {
            return redirect()->back()->with('error', 'Unable to resend code. Please try again.');
        }

        session()->set(self::PENDING_MFA_KEY, $pendingMfa);

        return redirect()->back()->with('success', 'A new verification code has been sent.');
    }

    public function apiLogin()
    {
        if (! $this->request->is('POST')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Method not allowed.',
            ]);
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (! $email || ! $password) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Email and password are required.',
            ]);
        }

        $db = \Config\Database::connect();
        $user      = $db->query(
            'SELECT u.id,
                    COALESCE(up.name, u.username, "") AS name,
                    u.email,
                    u.role,
                    ua.password_hash AS password_hash
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             LEFT JOIN user_auth ua ON ua.user_id = u.id
             WHERE u.email = ?
             LIMIT 1',
            [$email]
        )->getRowArray();

        if (! $user || ! password_verify($password, $user['password_hash'] ?? '')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid email or password.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'role'   => $user['role'] ?? 'client',
            'name'   => $user['name'] ?? '',
        ]);
    }

    public function logout()
    {
        $userId       = (int) session('user_id');
        $sessionToken = (string) session('auth_session_token');

        // Log logout event
        if (session()->get('isLoggedIn')) {
            (new LoginEventModel())->log(LoginEventModel::EVENT_LOGOUT, $userId);
        }

        // Revoke auth session — use token if available, otherwise revoke all for user
        $authSessionModel = new AuthSessionModel();
        if ($sessionToken !== '') {
            $authSessionModel->revokeSession($sessionToken, 'logout');
        } elseif ($userId > 0) {
            $authSessionModel->revokeAllForUser($userId, 'logout');
        }

        // Revoke access requests on logout for assistant_admin
        if (session('user_role') === 'assistant_admin') {
            $arModel = new \App\Models\AccessRequestModel();
            $uid = (int) (session('assistant_user_id') ?: $userId);
            $arModel->where('user_id', $uid)
                    ->whereIn('status', ['approved', 'pending'])
                    ->delete();
        }

        session()->destroy();
        return redirect()->to('/login');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        if ($this->hasPendingRegistration()) {
            return redirect()->to('/register/verify');
        }

        if ($this->request->is('post')) {
            $email = strtolower(trim((string) $this->request->getPost('email')));
            $phone = trim((string) $this->request->getPost('phone'));

            $rules = [
                'name'            => 'required|min_length[3]|regex_match[/^[\p{L}\s]+$/u]',
                'email'           => 'required|valid_email|is_unique[users.email]',
                'phone'           => 'required|regex_match[/^(\+63|0)[0-9\s\-\(\)]{9,12}$/]',
                'password'         => 'required|min_length[8]',
                'password_confirm' => 'required|matches[password]',
            ];

            $messages = [
                'email' => [
                    'is_unique'   => 'This email is already taken.',
                    'valid_email' => 'Please enter a valid email address.',
                ],
                'phone' => [
                    'regex_match' => 'Please enter a valid Philippine phone number (e.g., 09XX-XXX-XXXX or +63 9XX-XXX-XXXX).',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }


            $name     = trim((string) $this->request->getPost('name'));
            $password = (string) $this->request->getPost('password');

            // email was already extracted above as $email

            $emailLocalPart = explode('@', $email)[0] ?? '';
            $emailNumberCount = preg_match_all('/\d/', $emailLocalPart);
            $emailSpecialCount = preg_match_all('/[^a-z0-9]/i', $emailLocalPart);

            if ($emailNumberCount > 10 || $emailSpecialCount > 3) {
                return redirect()->back()->withInput()->with('errors', [
                    'email' => 'Email allows maximum 10 numbers and 3 special characters before @.',
                ]);
            }

            $verificationCode = (string) random_int(100000, 999999);
            $pendingRegistration = [
                'name'                   => $name,
                'email'                  => $email,
                'phone'                  => $phone,
                'password_hash'          => password_hash($password, PASSWORD_DEFAULT),
                'role'                   => 'client',
                'verification_code_hash' => password_hash($verificationCode, PASSWORD_DEFAULT),
                'verification_expires_at'=> time() + self::VERIFICATION_TTL_SECONDS,
                'verification_attempts'  => 0,
            ];

            if (! $this->sendVerificationCode($email, $name, $verificationCode)) {
                return redirect()->back()->withInput()->with('errors', [
                    '_form' => 'Unable to send verification code. Please check Gmail SMTP settings and try again.',
                ]);
            }

            session()->set(self::PENDING_REGISTRATION_KEY, $pendingRegistration);

            return redirect()->to('/register/verify')->with('success', 'We sent a 6-digit verification code to your email address.');
        }

        return view('auth/register');
    }

    public function verifyRegistration()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $pendingRegistration = $this->getPendingRegistration();

        if (! $pendingRegistration) {
            return redirect()->to('/register')->with('errors', [
                '_form' => 'Please register again to request a verification code.',
            ]);
        }

        if ($this->request->is('post')) {
            if (! $this->validate([
                'verification_code' => 'required|exact_length[6]|numeric',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $submittedCode = trim((string) $this->request->getPost('verification_code'));
            $pendingRegistration = $this->getPendingRegistration();

            if (! $pendingRegistration) {
                return redirect()->to('/register')->with('errors', [
                    '_form' => 'Your registration session expired. Please register again.',
                ]);
            }

            if (($pendingRegistration['verification_expires_at'] ?? 0) < time()) {
                session()->remove(self::PENDING_REGISTRATION_KEY);

                return redirect()->to('/register')->with('errors', [
                    '_form' => 'Your verification code expired. Please register again.',
                ]);
            }

            if (! password_verify($submittedCode, (string) ($pendingRegistration['verification_code_hash'] ?? ''))) {
                $attempts = ((int) ($pendingRegistration['verification_attempts'] ?? 0)) + 1;

                if ($attempts >= self::VERIFICATION_MAX_ATTEMPTS) {
                    session()->remove(self::PENDING_REGISTRATION_KEY);

                    return redirect()->to('/register')->with('errors', [
                        '_form' => 'Too many incorrect attempts. Please register again.',
                    ]);
                }

                $pendingRegistration['verification_attempts'] = $attempts;
                session()->set(self::PENDING_REGISTRATION_KEY, $pendingRegistration);

                return redirect()->back()->withInput()->with('errors', [
                    'verification_code' => 'Invalid verification code.',
                ]);
            }

            $userModel = new UserModel();
            $userId = $userModel->insert([
                'email'      => $pendingRegistration['email'] ?? '',
                'role'       => $pendingRegistration['role'] ?? 'client',
                'status'     => 'active',
                'public_id'  => bin2hex(random_bytes(16)),
                'username'   => null,
            ]);

            if (! $userId) {

                return redirect()->to('/register')->withInput()->with('errors', [
                    '_form' => $errors['email'] ?? 'Unable to create account right now. Please try again.',
                ]);
            }

            $db = \Config\Database::connect();
            $db->table('user_profiles')->insert([
                'user_id' => (int) $userId,
                'name'    => $pendingRegistration['name'] ?? '',
                'phone'   => $pendingRegistration['phone'] ?? null,
            ]);

            $db->table('user_auth')->insert([
                'user_id'            => (int) $userId,
                'password_hash'      => $pendingRegistration['password_hash'] ?? '',
                'role_password'      => null,
                'mfa_code_hash'      => null,
                'mfa_expires_at'     => null,
                'failed_login_count'  => 0,
                'cancel_attempts'    => 0,
                'cancel_reset_at'    => null,
                'lock_until'         => null,
                'last_login_at'      => null,
                'password_changed_at'=> null,
                'is_email_verified'   => 1,
            ]);

            // Apply deny overrides if this role has disabled features
            applyDenyOverridesForNewUser((int) $userId, $pendingRegistration['role'] ?? 'client');

            session()->remove(self::PENDING_REGISTRATION_KEY);

            return redirect()->to('/login')->with('success', 'Email verified. Your account has been created.');
        }

        return view('auth/verify_registration', [
            'pendingEmail'   => $pendingRegistration['email'] ?? '',
            'pendingContact' => $pendingRegistration['contact_value'] ?? ($pendingRegistration['email'] ?? ''),
            'contactMethod'  => $pendingRegistration['contact_method'] ?? 'email',
            'expiresAt'      => (int) ($pendingRegistration['verification_expires_at'] ?? 0),
        ]);
    }

    public function resendVerificationCode()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $pendingRegistration = $this->getPendingRegistration();

        if (! $pendingRegistration) {
            return redirect()->to('/register')->with('errors', [
                '_form' => 'Please register again to request a verification code.',
            ]);
        }

        $verificationCode = (string) random_int(100000, 999999);
        $pendingRegistration['verification_code_hash'] = password_hash($verificationCode, PASSWORD_DEFAULT);
        $pendingRegistration['verification_expires_at'] = time() + self::VERIFICATION_TTL_SECONDS;
        $pendingRegistration['verification_attempts'] = 0;

        if (! $this->sendVerificationCode((string) ($pendingRegistration['email'] ?? ''), (string) ($pendingRegistration['name'] ?? ''), $verificationCode)) {
            return redirect()->back()->with('errors', [
                '_form' => 'Unable to resend verification code. Please check Gmail SMTP settings and try again.',
            ]);
        }

        session()->set(self::PENDING_REGISTRATION_KEY, $pendingRegistration);

        return redirect()->back()->with('success', 'A new verification code has been sent.');
    }

    public function resetRegistration()
    {
        if (! session()->get('isLoggedIn')) {
            session()->remove(self::PENDING_REGISTRATION_KEY);
        }

        return redirect()->to('/register');
    }

    private function getPendingMfa(): ?array
    {
        $pendingMfa = session()->get(self::PENDING_MFA_KEY);
        return is_array($pendingMfa) ? $pendingMfa : null;
    }

    private function getPendingRegistration(): ?array
    {
        $pendingRegistration = session()->get(self::PENDING_REGISTRATION_KEY);

        return is_array($pendingRegistration) ? $pendingRegistration : null;
    }

    private function hasPendingRegistration(): bool
    {
        return $this->getPendingRegistration() !== null;
    }

    private function sendVerificationCode(string $email, string $name, string $verificationCode): bool
    {
        $emailService = service('email');
        $emailConfig = config('Email');
        $fromEmail = $emailConfig->fromEmail ?: $emailConfig->SMTPUser;
        $fromName = $emailConfig->fromName ?: 'Clinic Appointment Portal';

        if ($fromEmail === '' || $emailConfig->SMTPHost === '' || $emailConfig->SMTPUser === '' || $emailConfig->SMTPPass === '') {
            return false;
        }

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($email);
        $emailService->setSubject('Your verification code: ' . $verificationCode);
        $emailService->setMessage(view('emails/verification_code', [
            'name' => $name,
            'email' => $email,
            'verificationCode' => $verificationCode,
            'expiresMinutes' => (int) (self::VERIFICATION_TTL_SECONDS / 60),
        ]));
        $emailService->setAltMessage(
            'Hello ' . $name . ', your verification code is ' . $verificationCode
            . '. This code expires in ' . (int) (self::VERIFICATION_TTL_SECONDS / 60) . ' minutes.'
        );

        return (bool) $emailService->send(false);
    }

    private function alertAdminsOfSuspiciousActivity(array $user): void
    {
        $notifModel = new NotificationModel();
        $userModel  = new UserModel();
        $admins     = $userModel->whereIn('role', ['admin'])->where('deleted_at IS NULL')->findAll();

        foreach ($admins as $admin) {
            $notifModel->send(
                (int) $admin['id'],
                'Suspicious Login Activity',
                'Multiple failed login attempts detected for account: ' . ($user['email'] ?? 'unknown') . '. Account has been temporarily locked.',
                'warning'
            );
        }
    }

    private function sendMfaCode(string $email, string $name, string $mfaCode): bool
    {
        $emailService = service('email');
        $emailConfig  = config('Email');
        $fromEmail    = $emailConfig->fromEmail ?: $emailConfig->SMTPUser;
        $fromName     = $emailConfig->fromName ?: 'Clinic Appointment Portal';

        if ($fromEmail === '' || $emailConfig->SMTPHost === '' || $emailConfig->SMTPUser === '' || $emailConfig->SMTPPass === '') {
            return false;
        }

        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($email);
        $emailService->setSubject('Your login verification code: ' . $mfaCode);
        $emailService->setMessage(view('emails/verification_code', [
            'name'             => $name,
            'email'            => $email,
            'verificationCode' => $mfaCode,
            'expiresMinutes'   => (int) (self::MFA_TTL_SECONDS / 60),
        ]));
        $emailService->setAltMessage(
            'Hello ' . $name . ', your login verification code is ' . $mfaCode
            . '. This code expires in ' . (int) (self::MFA_TTL_SECONDS / 60) . ' minutes.'
        );

        return (bool) $emailService->send(false);
    }
}
