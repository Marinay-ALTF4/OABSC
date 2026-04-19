<?php

namespace App\Controllers;

use App\Models\UserModel;

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
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your login details.');
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        if (! password_verify($password, $user['password_hash'] ?? '')) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        // Skip MFA for seeded/system accounts (@example.com)
        $isSeededUser = str_ends_with(strtolower((string) $user['email']), self::MFA_BYPASS_DOMAIN);

        if ($isSeededUser) {
            session()->set([
                'user_id'    => $user['id'],
                'user_name'  => $user['name'] ?? '',
                'user_email' => $user['email'] ?? '',
                'user_role'  => $user['role'] ?? 'client',
                'isLoggedIn' => true,
            ]);

            if (in_array($user['role'], ['admin', 'assistant_admin'], true)) {
                session()->set([
                    'isLoggedIn'             => false,
                    'pending_role_selection' => true,
                    'pending_user_id'        => $user['id'],
                    'pending_user_role'      => $user['role'],
                ]);
                return redirect()->to('/role-selection');
            }

            return redirect()->to('/dashboard');
        }

        // Real registered users — send MFA OTP
        $mfaCode = (string) random_int(100000, 999999);

        $pendingMfa = [
            'user_id'        => $user['id'],
            'user_name'      => $user['name'] ?? '',
            'user_email'     => $user['email'] ?? '',
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
                    return redirect()->to('/login')->with('error', 'Too many incorrect attempts. Please log in again.');
                }

                $pendingMfa['mfa_attempts'] = $attempts;
                session()->set(self::PENDING_MFA_KEY, $pendingMfa);

                return redirect()->back()->withInput()->with('errors', ['mfa_code' => 'Invalid verification code.']);
            }

            session()->remove(self::PENDING_MFA_KEY);
            session()->set([
                'user_id'    => $pendingMfa['user_id'],
                'user_name'  => $pendingMfa['user_name'],
                'user_email' => $pendingMfa['user_email'] ?? '',
                'user_role'  => $pendingMfa['user_role'],
                'isLoggedIn' => true,
            ]);

            if (in_array($pendingMfa['user_role'], ['admin', 'assistant_admin'], true)) {
                session()->set([
                    'isLoggedIn'             => false,
                    'pending_role_selection' => true,
                    'pending_user_id'        => $pendingMfa['user_id'],
                    'pending_user_role'      => $pendingMfa['user_role'],
                ]);
                return redirect()->to('/role-selection');
            }

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

        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

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
            $rules = [
                'name'              => 'required|min_length[3]|regex_match[/^[\p{L}\s]+$/u]',
                'email'             => 'required|valid_email|is_unique[users.email]',
                'password'          => 'required|min_length[8]',
                'password_confirm'  => 'required|matches[password]',
            ];

            $messages = [
                'email' => [
                    'is_unique' => 'This email is already taken.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $name     = trim((string) $this->request->getPost('name'));
            $email    = strtolower(trim((string) $this->request->getPost('email')));
            $password = (string) $this->request->getPost('password');

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

            return redirect()->to('/register/verify')->with('success', 'We sent a 6-digit verification code to your Gmail address.');
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
                'name'          => $pendingRegistration['name'] ?? '',
                'email'         => $pendingRegistration['email'] ?? '',
                'password_hash' => $pendingRegistration['password_hash'] ?? '',
                'role'          => $pendingRegistration['role'] ?? 'client',
            ]);

            if (! $userId) {
                $errors = $userModel->errors();

                return redirect()->to('/register')->withInput()->with('errors', [
                    '_form' => $errors['email'] ?? 'Unable to create account right now. Please try again.',
                ]);
            }

            session()->remove(self::PENDING_REGISTRATION_KEY);

            return redirect()->to('/login')->with('success', 'Email verified. Your account has been created.');
        }

        return view('auth/verify_registration', [
            'pendingEmail' => $pendingRegistration['email'] ?? '',
            'expiresAt'    => (int) ($pendingRegistration['verification_expires_at'] ?? 0),
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
