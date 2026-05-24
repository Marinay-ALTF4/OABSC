<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AppointmentModel;
use App\Models\DoctorScheduleModel;
use App\Models\NotificationModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;
use Config\Database;

/**
 * API Controller — JSON endpoints for the Flutter mobile app.
 *
 * Every method returns JSON. The Flutter app calls these via
 * HTTP using the ApiService class.
 */
class Api extends BaseController
{
    use ResponseTrait;

    private function httpRequest(): IncomingRequest
    {
        return $this->request instanceof IncomingRequest ? $this->request : service('request');
    }

    // ───────────────────────── Health ─────────────────────────

    /**
     * GET /api/health
     * Quick connectivity check for the Flutter app.
     */
    public function health()
    {
        return $this->respond([
            'status'  => 'ok',
            'message' => 'API is running',
            'time'    => date('Y-m-d H:i:s'),
        ]);
    }

    // ───────────────────────── Auth ──────────────────────────

    /**
     * POST /api/login
     * Authenticate a user with email + password.
     * Returns user info and a simple token.
     */
    public function login()
    {
        $request = $this->httpRequest();

        // Accept both JSON body and form POST
        $json = $request->getJSON(true);
        $email    = $json['email']    ?? $request->getPost('email');
        $password = $json['password'] ?? $request->getPost('password');

        if (! $email || ! $password) {
            return $this->failValidationErrors(['email' => 'Email and password are required.']);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', strtolower(trim((string) $email)))->first();

        if (! $user || ! password_verify((string) $password, $user['password_hash'] ?? '')) {
            return $this->failUnauthorized('Invalid email or password.');
        }

        // Generate a simple token (in production, use JWT)
        $token = bin2hex(random_bytes(32));

        return $this->respond([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user['id'] ?? null,
                'name'  => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'role'  => $user['role'] ?? 'client',
            ],
        ]);
    }

    /**
     * POST /api/register
     * Register a new client account.
     */
    public function register()
    {
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $request = $this->request;

        $json = $request->getJSON(true);
        $name             = $json['name']             ?? $request->getPost('name');
        $email            = $json['email']            ?? $request->getPost('email');
        $password         = $json['password']         ?? $request->getPost('password');
        $passwordConfirm  = $json['password_confirm'] ?? $request->getPost('password_confirm');

        $this->request->setGlobal('post', [
            'name'             => $name,
            'email'            => $email,
            'password'         => $password,
            'password_confirm' => $passwordConfirm,
        ]);

        $rules = [
            'name'             => 'required|min_length[3]|regex_match[/^[\p{L}\s]+$/u]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userModel = new UserModel();
        $userId = $userModel->insert([
            'name'          => trim((string) $name),
            'email'         => strtolower(trim((string) $email)),
            'password_hash' => password_hash((string) $password, PASSWORD_DEFAULT),
            'role'          => 'client',
        ]);

        if (! $userId) {
            return $this->failServerError('Unable to register account right now.');
        }

        applyDenyOverridesForNewUser((int) $userId, 'client');

        return $this->respondCreated([
            'message' => 'Registration successful. Please login.',
            'user_id' => $userId,
        ]);
    }

    /**
     * POST /api/register/send-code
     */
    public function registerSendCode()
    {
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $request = $this->request;
        $json = $request->getJSON(true);
        $name             = $json['name']             ?? $request->getPost('name');
        $email            = $json['email']            ?? $request->getPost('email');
        $phone            = $json['phone']            ?? $request->getPost('phone') ?? '';
        $password         = $json['password']         ?? $request->getPost('password');
        $passwordConfirm  = $json['password_confirm'] ?? $request->getPost('password_confirm');

        $this->request->setGlobal('post', [
            'name'             => $name,
            'email'            => $email,
            'phone'            => $phone,
            'password'         => $password,
            'password_confirm' => $passwordConfirm,
        ]);

        $rules = [
            'name'             => 'required|min_length[3]|regex_match[/^[\p{L}\s]+$/u]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'phone'            => 'required|regex_match[/^(\+63|0)[0-9\s\-\(\)]{9,12}$/]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'email' => [
                'is_unique'   => 'This email is already taken.',
                'valid_email' => 'Please enter a valid email address.',
            ],
            'phone' => [
                'regex_match' => 'Please enter a valid Philippine phone number (e.g., 09XX-XXX-XXXX).',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('pending_registrations')) {
            $db->query("CREATE TABLE pending_registrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(191) NOT NULL UNIQUE,
                phone VARCHAR(50) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                verification_code VARCHAR(255) NOT NULL,
                expires_at INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = time() + 600; // 10 minutes

        // Send OTP code via email
        if (! $this->sendRegisterCodeEmail($email, $name, $code)) {
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                // SMTP Bypass for development mode - log code and allow test
                log_message('error', "DEVELOPMENT SMTP BYPASS: Verification code for $email is $code");
                
                // Still register the pending code in the DB so they can type it!
                $db->query('DELETE FROM pending_registrations WHERE email = ?', [$email]);
                $db->query(
                    'INSERT INTO pending_registrations (name, email, phone, password_hash, verification_code, expires_at) VALUES (?, ?, ?, ?, ?, ?)',
                    [trim((string) $name), strtolower(trim((string) $email)), trim((string) $phone), password_hash((string) $password, PASSWORD_DEFAULT), password_hash((string) $code, PASSWORD_DEFAULT), $expiresAt]
                );

                return $this->respond([
                    'success' => true,
                    'message' => "SMTP is not configured. (Dev Bypass Code: $code)",
                ]);
            }

            return $this->respond([
                'success' => false,
                'message' => 'Unable to send verification code. Please configure SMTP settings in your .env file.',
            ], 400);
        }

        // Delete old pending registration for this email
        $db->query('DELETE FROM pending_registrations WHERE email = ?', [$email]);

        // Insert new pending registration
        $db->query(
            'INSERT INTO pending_registrations (name, email, phone, password_hash, verification_code, expires_at) VALUES (?, ?, ?, ?, ?, ?)',
            [trim((string) $name), strtolower(trim((string) $email)), trim((string) $phone), password_hash((string) $password, PASSWORD_DEFAULT), password_hash((string) $code, PASSWORD_DEFAULT), $expiresAt]
        );

        return $this->respond([
            'success' => true,
            'message' => 'Verification code sent to your email.',
        ]);
    }

    /**
     * POST /api/register/verify-code
     */
    public function registerVerifyCode()
    {
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $request = $this->request;
        $json = $request->getJSON(true);
        $email = strtolower(trim((string) ($json['email'] ?? $request->getPost('email'))));
        $code  = trim((string) ($json['code'] ?? $request->getPost('code')));

        if (! $email || ! $code) {
            return $this->failValidationErrors(['_form' => 'Email and verification code are required.']);
        }

        $db = \Config\Database::connect();
        if (! $db->tableExists('pending_registrations')) {
            return $this->failNotFound('No pending registration found for this email.');
        }

        $pending = $db->query('SELECT * FROM pending_registrations WHERE email = ? LIMIT 1', [$email])->getRowArray();

        if (! $pending) {
            return $this->failNotFound('Registration session expired or not found. Please register again.');
        }

        if ((int) $pending['expires_at'] < time()) {
            $db->query('DELETE FROM pending_registrations WHERE email = ?', [$email]);
            return $this->failValidationErrors(['_form' => 'Verification code expired. Please register again.']);
        }

        if (! password_verify($code, $pending['verification_code'])) {
            return $this->failValidationErrors(['code' => 'Invalid verification code.']);
        }

        // Successfully verified! Create main user row
        $userModel = new UserModel();
        $userId = $userModel->insert([
            'name'          => $pending['name'],
            'email'         => $pending['email'],
            'phone'         => $pending['phone'],
            'password_hash' => $pending['password_hash'],
            'role'          => 'client',
        ]);

        if (! $userId) {
            return $this->failServerError('Unable to create account right now.');
        }

        applyDenyOverridesForNewUser((int) $userId, 'client');

        // Clean up pending registration
        $db->query('DELETE FROM pending_registrations WHERE email = ?', [$email]);

        return $this->respondCreated([
            'success' => true,
            'message' => 'Email verified and account successfully registered! Please login.',
            'user_id' => $userId,
        ]);
    }

    private function sendRegisterCodeEmail(string $email, string $name, string $code): bool
    {
        $emailService = service('email');
        $emailConfig = config('Email');
        $fromEmail = $emailConfig->fromEmail ?: $emailConfig->SMTPUser;
        $fromName = $emailConfig->fromName ?: 'Clinic Appointment Portal';

        if ($fromEmail === '' || $emailConfig->SMTPHost === '' || $emailConfig->SMTPUser === '' || $emailConfig->SMTPPass === '') {
            return false;
        }

        $emailConfig->CRLF = "\r\n";
        $emailConfig->newline = "\r\n";
        $emailService->initialize($emailConfig);
        $emailService->setMailType('html');
        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($email);
        $emailService->setSubject('Your verification code: ' . $code);
        $emailService->setMessage(view('emails/verification_code', [
            'name' => $name,
            'email' => $email,
            'verificationCode' => $code,
            'expiresMinutes' => 10,
        ]));
        $emailService->setAltMessage(
            'Hello ' . $name . ', your verification code is ' . $code
            . '. This code expires in 10 minutes.'
        );

        $sent = (bool) $emailService->send(false);
        if (! $sent) {
            $debug = $emailService->printDebugger(['headers', 'subject', 'body']);
            log_message('error', 'SMTP SENDING FAILED DEBUGGER: ' . $debug);
        }
        return $sent;
    }

    // ──────────────────── Appointments ───────────────────────

    /**
     * Helper: detect whether the appointments table uses client_id or user_id.
     */
    private function ownerColumn(): ?string
    {
        $db = Database::connect();

        if ($db->fieldExists('client_id', 'appointments')) {
            return 'client_id';
        }
        if ($db->fieldExists('user_id', 'appointments')) {
            return 'user_id';
        }

        return null;
    }

    private function hasDoctorNameColumn(): bool
    {
        return Database::connect()->fieldExists('doctor_name', 'appointments');
    }

    /**
     * GET /api/appointments?user_id=X
     * Get appointments for a specific user.
     */
    public function appointments()
    {
        $request = $this->httpRequest();
        $userId = $request->getGet('user_id');
        $role   = $request->getGet('role') ?? 'client';

        if (! $userId) {
            return $this->failValidationErrors(['user_id' => 'user_id is required.']);
        }

        $ownerCol = $this->ownerColumn();
        if ($ownerCol === null) {
            return $this->respond(['appointments' => []]);
        }

        $model = new AppointmentModel();
        $query = $model->select('appointments.*, users.name as patient_name')
                       ->join('users', 'users.id = appointments.' . $ownerCol, 'left');

        if ($role === 'doctor') {
            $query->where('doctor_id', (int) $userId);
        } else {
            $query->where($ownerCol, (int) $userId);
        }

        $appointments = $query->orderBy('appointment_date', 'DESC')
                             ->orderBy('appointment_time', 'DESC')
                             ->findAll();

        // Normalize field names for the Flutter model
        $result = array_map(function ($a) {
            return [
                'id'           => $a['id'] ?? null,
                'patient_name' => $a['patient_name'] ?? ('Patient #' . $a['id']),
                'doctor_name'  => $a['doctor_name'] ?? '',
                'date'         => $a['appointment_date'] ?? '',
                'time'         => $a['appointment_time'] ?? '',
                'status'       => $a['status'] ?? '',
                'notes'        => $a['reason'] ?? '',
            ];
        }, $appointments);

        return $this->respond([
            'count'        => count($result),
            'appointments' => $result,
        ]);
    }

    /**
     * POST /api/appointments
     * Create a new appointment.
     */
    public function createAppointment()
    {
        $request = $this->httpRequest();

        $json = $request->getJSON(true);
        $userId          = $json['user_id']          ?? $request->getPost('user_id');
        $doctorName      = $json['doctor_name']      ?? $request->getPost('doctor_name');
        $appointmentDate = $json['appointment_date'] ?? $request->getPost('appointment_date');
        $appointmentTime = $json['appointment_time'] ?? $request->getPost('appointment_time');
        $reason          = $json['reason']           ?? $request->getPost('reason');

        if (! $userId || ! $doctorName || ! $appointmentDate || ! $appointmentTime || ! $reason) {
            return $this->failValidationErrors(['_form' => 'All fields are required.']);
        }

        if ($appointmentDate < date('Y-m-d')) {
            return $this->failValidationErrors(['appointment_date' => 'Appointment date cannot be in the past.']);
        }

        $ownerCol = $this->ownerColumn();
        if ($ownerCol === null) {
            return $this->failServerError('Appointments table is missing owner column.');
        }

        $insertData = [
            $ownerCol          => (int) $userId,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'reason'           => trim((string) $reason),
            'status'           => 'pending',
        ];

        if ($this->hasDoctorNameColumn()) {
            $insertData['doctor_name'] = trim((string) $doctorName);
        }

        $doctorId = $json['doctor_id'] ?? $request->getPost('doctor_id');
        if ($doctorId) {
            $insertData['doctor_id'] = (int)$doctorId;
        } else {
            // Resolve doctor_id from name if not provided
            $nameOnly   = preg_replace('/^Dr\.\s*/i', '', (string) $doctorName);
            $userModel  = new UserModel();
            $doctorUser = $userModel->where('role', 'doctor')->where('name', $nameOnly)->first();
            if ($doctorUser) {
                $insertData['doctor_id'] = $doctorUser['id'];
            }
        }

        $model = new AppointmentModel();
        $saved = $model->insert($insertData);

        if (! $saved) {
            return $this->failServerError('Unable to create appointment right now.');
        }

        $notifModel  = new NotificationModel();
        $patientUser = (new UserModel())->find((int) $userId);
        $patientName = $patientUser['name'] ?? 'A patient';
        $msgBody     = "{$patientName} booked an appointment on {$appointmentDate} at " . substr((string) $appointmentTime, 0, 5) . '.';

        // Notify doctor
        if (! empty($insertData['doctor_id'])) {
            $notifModel->send(
                (int) $insertData['doctor_id'],
                'New Appointment Booked',
                $msgBody,
                'appointment'
            );
        }

        // Notify admins, secretaries, and assistant admins
        try {
            $adminRecipients = (new UserModel())
                ->whereIn('role', ['admin', 'secretary', 'assistant_admin'])
                ->where('deleted_at IS NULL')
                ->findAll();
            foreach ($adminRecipients as $recip) {
                $notifModel->send((int) $recip['id'], 'New Appointment Booked', $msgBody, 'appointment');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to notify admins on API appointment create: ' . $e->getMessage());
        }

        return $this->respondCreated([
            'message'        => 'Appointment created successfully.',
            'appointment_id' => $saved,
        ]);
    }

    // ──────────────────── Dashboard Stats ────────────────────

    /**
     * GET /api/dashboard?user_id=X&role=Y
     * Get dashboard statistics depending on user role.
     */
    public function dashboard()
    {
        $request = $this->httpRequest();
        $userId = $request->getGet('user_id');
        $role   = $request->getGet('role') ?? 'client';

        if (! $userId) {
            return $this->failValidationErrors(['user_id' => 'user_id is required.']);
        }

        $appointmentModel = new AppointmentModel();
        $ownerCol = $this->ownerColumn();

        if ($role === 'client') {
            $total    = $ownerCol ? $appointmentModel->where($ownerCol, (int) $userId)->countAllResults(false) : 0;
            $pending  = $ownerCol ? $appointmentModel->where($ownerCol, (int) $userId)->where('status', 'pending')->countAllResults(false) : 0;
            $approved = $ownerCol ? $appointmentModel->where($ownerCol, (int) $userId)->where('status', 'approved')->countAllResults(false) : 0;

            return $this->respond([
                'total_appointments' => $total,
                'pending'            => $pending,
                'approved'           => $approved,
            ]);
        }

        if ($role === 'doctor') {
            $total     = $appointmentModel->where('doctor_id', (int) $userId)->countAllResults(false);
            $upcoming  = $appointmentModel->where('doctor_id', (int) $userId)->whereIn('status', ['pending', 'approved'])->where('appointment_date >=', date('Y-m-d'))->countAllResults(false);
            $completed = $appointmentModel->where('doctor_id', (int) $userId)->where('status', 'completed')->countAllResults(false);
            $today     = $appointmentModel->where('doctor_id', (int) $userId)->where('appointment_date', date('Y-m-d'))->countAllResults(false);

            return $this->respond([
                'total_consultations' => $total,
                'upcoming'            => $upcoming,
                'completed'           => $completed,
                'today_patients'      => $today,
            ]);
        }

        // Admin / Secretary — show all counts
        $total    = $appointmentModel->countAllResults(false);
        $pending  = $appointmentModel->where('status', 'pending')->countAllResults(false);
        $approved = $appointmentModel->where('status', 'approved')->countAllResults(false);
        $today    = $appointmentModel->where('appointment_date', date('Y-m-d'))->countAllResults(false);

        $userModel   = new UserModel();
        $totalUsers  = $userModel->where('deleted_at IS NULL')->countAllResults(false);
        $totalPatients = $userModel->where('role', 'client')->where('deleted_at IS NULL')->countAllResults(false);
        $totalDoctors = $userModel->where('role', 'doctor')->where('deleted_at IS NULL')->countAllResults(false);
        $totalSecretaries = $userModel->where('role', 'secretary')->where('deleted_at IS NULL')->countAllResults(false);

        return $this->respond([
            'success'            => true,
            'total_appointments' => $total,
            'pending'            => $pending,
            'approved'           => $approved,
            'today_appointments' => $today,
            'total_users'        => $totalUsers,
            'total_patients'     => $totalPatients,
            'total_doctors'      => $totalDoctors,
            'secretaries'        => $totalSecretaries,
        ]);
    }

    // ──────────────────── Doctors ────────────────────────────

    /**
     * GET /api/doctors
     * Get list of all doctors with their schedules.
     */
    public function doctors()
    {
        $userModel     = new UserModel();
        $scheduleModel = new DoctorScheduleModel();

        $doctorUsers = $userModel->where('role', 'doctor')->where('deleted_at IS NULL')->findAll();

        $doctors = [];
        foreach ($doctorUsers as $d) {
            $schedules = $scheduleModel->getScheduleByDoctor((int) $d['id']);
            $doctors[] = [
                'id'             => $d['id'],
                'name'           => 'Dr. ' . $d['name'],
                'specialization' => $d['specialization'] ?? 'Specialist',
                'experience'     => $d['experience'] ?? 'N/A',
                'degree'         => $d['degree'] ?? 'MD',
                'bio'            => $d['bio'] ?? '',
                'phone'          => $d['phone'] ?? '',
                'profile_photo'  => ! empty($d['profile_photo']) ? base_url($d['profile_photo']) : null,
                'schedules'      => $schedules,
            ];
        }

        return $this->respond([
            'count'   => count($doctors),
            'doctors' => $doctors,
        ]);
    }

    // ──────────────────── User Profile ───────────────────────

    /**
     * GET /api/profile?user_id=X
     * Get user profile details.
     */
    public function profile()
    {
        $request = $this->httpRequest();
        $userId = $request->getGet('user_id');

        if (! $userId) {
            return $this->failValidationErrors(['user_id' => 'user_id is required.']);
        }

        $userModel = new UserModel();
        $user = $userModel->find((int) $userId);

        if (! $user) {
            return $this->failNotFound('User not found.');
        }

        return $this->respond([
            'user' => [
                'id'             => $user['id'],
                'name'           => $user['name'] ?? '',
                'email'          => $user['email'] ?? '',
                'role'           => $user['role'] ?? '',
                'phone'          => $user['phone'] ?? '',
                'city'           => $user['city'] ?? '',
                'address'        => $user['address'] ?? '',
                'profile_photo'  => ! empty($user['profile_photo']) ? base_url($user['profile_photo']) : null,
                'specialization' => $user['specialization'] ?? '',
                'experience'     => $user['experience'] ?? '',
                'degree'         => $user['degree'] ?? '',
                'bio'            => $user['bio'] ?? '',
                'created_at'     => $user['created_at'] ?? '',
            ],
        ]);
    }

    /**
     * POST /api/profile/update
     * Update user profile.
     */
    public function updateProfile()
    {
        $request = $this->httpRequest();

        $json = $request->getJSON(true);
        $userId  = $json['user_id'] ?? $request->getPost('user_id');

        if (! $userId) {
            return $this->failValidationErrors(['user_id' => 'user_id is required.']);
        }

        $userModel = new UserModel();
        $user = $userModel->find((int) $userId);

        if (! $user) {
            return $this->failNotFound('User not found.');
        }

        $updateData = [];
        $allowedFields = ['name', 'phone', 'city', 'address', 'specialization', 'experience', 'degree', 'bio'];
        foreach ($allowedFields as $field) {
            if (isset($json[$field])) {
                $updateData[$field] = trim((string) $json[$field]);
            }
        }

        // Handle password update
        if (isset($json['current_password']) && isset($json['new_password'])) {
            if (! password_verify((string) $json['current_password'], $user['password_hash'] ?? '')) {
                return $this->failValidationErrors(['current_password' => 'Incorrect current password.']);
            }
            $updateData['password_hash'] = password_hash((string) $json['new_password'], PASSWORD_DEFAULT);
        }

        if (empty($updateData)) {
            return $this->failValidationErrors(['_form' => 'No fields to update.']);
        }

        $userModel->update((int) $userId, $updateData);

        return $this->respond([
            'message' => 'Profile updated successfully.',
        ]);
    }

    /**
     * POST /api/appointments/cancel
     * Cancel an appointment.
     */
    public function cancelAppointment()
    {
        $request = $this->httpRequest();
        $json = $request->getJSON(true);
        $appointmentId = $json['id'] ?? $request->getPost('id');

        if (! $appointmentId) {
            return $this->failValidationErrors(['id' => 'Appointment ID is required.']);
        }

        $model       = new AppointmentModel();
        $appointment = $model->find((int) $appointmentId);

        if (! $appointment) {
            return $this->failNotFound('Appointment not found.');
        }

        $model->update((int) $appointmentId, ['status' => 'cancelled']);

        // Notify doctor
        $patientUser = (new UserModel())->find((int) ($appointment[$this->ownerColumn()] ?? 0));
        $patientName = $patientUser['name'] ?? 'A patient';
        $apptDate    = $appointment['appointment_date'] ?? 'scheduled date';
        $apptTime    = substr((string) ($appointment['appointment_time'] ?? ''), 0, 5);
        $msgBody     = "{$patientName} cancelled their appointment scheduled for {$apptDate} at {$apptTime}.";

        if (! empty($appointment['doctor_id'])) {
            $notifModel = new NotificationModel();
            $notifModel->send(
                (int) $appointment['doctor_id'],
                'Appointment Cancelled',
                $msgBody,
                'appointment'
            );
        }

        // Notify admins, secretaries, and assistant admins
        try {
            $notifModel      = $notifModel ?? new NotificationModel();
            $adminRecipients = (new UserModel())
                ->whereIn('role', ['admin', 'secretary', 'assistant_admin'])
                ->where('deleted_at IS NULL')
                ->findAll();
            foreach ($adminRecipients as $recip) {
                $notifModel->send((int) $recip['id'], 'Appointment Cancelled', $msgBody, 'appointment');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to notify admins on API appointment cancel: ' . $e->getMessage());
        }

        return $this->respond([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
        ]);
    }

    /**
     * POST /api/appointments/update-status
     */
    public function updateAppointmentStatus()
    {
        $request = $this->httpRequest();
        $json = $request->getJSON(true);
        $id = $json['id'];
        $status = $json['status'];
        
        $model = new \App\Models\AppointmentModel();
        $appointment = $model->find($id);
        
        // Prevent duplicate updates and notifications
        if ($appointment && $appointment['status'] === $status) {
            return $this->respond(['success' => true]);
        }

        $model->update($id, ['status' => $status]);
        
        // Notify client about the status change
        if ($appointment) {
            $ownerCol = $this->ownerColumn();
            $patientId = $ownerCol ? (int) ($appointment[$ownerCol] ?? 0) : 0;
            if ($patientId > 0) {
                $notifModel = new \App\Models\NotificationModel();
                $date = $appointment['appointment_date'] ?? 'a scheduled date';
                $title = 'Appointment ' . ucfirst($status);
                $body = "Your appointment on {$date} has been marked as {$status}.";
                $notifModel->send($patientId, $title, $body, 'appointment');
            }
        }
        
        return $this->respond(['success' => true]);
    }

    // ──────────────────── Notes ──────────────────────────

    /**
     * GET /api/notes
     */
    public function getNotes()
    {
        $request = $this->httpRequest();
        $userId = $request->getGet('user_id');
        $model = new \App\Models\NoteModel();
        $notes = $model->where('doctor_id', (int)$userId)->orderBy('created_at', 'DESC')->findAll();
        return $this->respond(['success' => true, 'notes' => $notes]);
    }

    /**
     * POST /api/notes
     */
    public function saveNote()
    {
        $request = $this->httpRequest();
        $json = $request->getJSON(true);
        $model = new \App\Models\NoteModel();
        
        if (isset($json['id']) && $json['id']) {
            $model->update($json['id'], $json);
        } else {
            $model->insert($json);
        }
        
        return $this->respond(['success' => true]);
    }

    /**
     * DELETE /api/notes/$id
     */
    public function deleteNote($id)
    {
        $model = new \App\Models\NoteModel();
        $model->delete($id);
        return $this->respond(['success' => true]);
    }

    // ──────────────────── Prescriptions ────────────────────

    /**
     * GET /api/prescriptions
     */
    private function ensurePrescriptionsTable()
    {
        $db = \Config\Database::connect();
        if (! $db->tableExists('prescriptions')) {
            $db->query("CREATE TABLE prescriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                doctor_id INT NOT NULL,
                patient_name VARCHAR(255) NOT NULL,
                medication VARCHAR(255) NOT NULL,
                dosage VARCHAR(255) NOT NULL,
                instructions TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
        }
    }

    public function getPrescriptions()
    {
        $this->ensurePrescriptionsTable();
        $request = $this->httpRequest();
        $userId = $request->getGet('user_id');
        $model = new \App\Models\PrescriptionModel();
        $items = $model->where('doctor_id', (int)$userId)->orderBy('created_at', 'DESC')->findAll();
        return $this->respond(['success' => true, 'prescriptions' => $items]);
    }

    /**
     * POST /api/prescriptions
     */
    public function savePrescription()
    {
        $this->ensurePrescriptionsTable();
        $request = $this->httpRequest();
        $json = $request->getJSON(true);
        $model = new \App\Models\PrescriptionModel();
        
        if (isset($json['id']) && $json['id']) {
            $model->update($json['id'], $json);
        } else {
            $model->insert($json);
            
            // Send notification to the patient
            $userModel = new \App\Models\UserModel();
            $patient = $userModel->where('name', $json['patient_name'])->where('role', 'client')->first();
            if ($patient) {
                $notifModel = new \App\Models\NotificationModel();
                $notifModel->send(
                    (int) $patient['id'],
                    'New Prescription',
                    'Your doctor has prescribed a new medication for you: ' . $json['medication'] . '. Dosage: ' . $json['dosage'],
                    'prescription'
                );
            }
        }
        
        return $this->respond(['success' => true]);
    }

    /**
     * DELETE /api/prescriptions/$id
     */
    public function deletePrescription($id)
    {
        $model = new \App\Models\PrescriptionModel();
        $model->delete($id);
        return $this->respond(['success' => true]);
    }

    // ──────────────────── Notifications ──────────────────────

    /**
     * GET /api/notifications?user_id=X
     * Get notifications for a user.
     */
    public function notifications()
    {
        $request = $this->httpRequest();
        $userId = $request->getGet('user_id');

        if (! $userId) {
            return $this->failValidationErrors(['user_id' => 'user_id is required.']);
        }

        $notifModel = new NotificationModel();
        $notifications = $notifModel
            ->where('user_id', (int) $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll(50); // Limit to 50

        return $this->respond([
            'count'         => count($notifications),
            'notifications' => $notifications,
        ]);
    }

    /**
     * POST /api/notifications/mark-read
     * Mark all notifications as read for a user.
     */
    public function markNotificationsRead()
    {
        $request = $this->httpRequest();
        $json = $request->getJSON(true);
        $userId = (int) ($json['user_id'] ?? 0);

        if (! $userId) {
            return $this->failValidationErrors(['user_id' => 'user_id is required.']);
        }

        $notifModel = new NotificationModel();
        // The NotificationModel has a markAllRead method
        $notifModel->markAllRead($userId);

        return $this->respond(['success' => true]);
    }

    /**
     * DELETE /api/notifications/:id
     * Delete a notification.
     */
    public function deleteNotification($id)
    {
        $model = new NotificationModel();
        $model->delete($id);
        return $this->respond(['success' => true]);
    }

    // ──────────────────── Users (admin) ──────────────────────

    /**
     * GET /api/users
     * List all users (for admin/testing).
     */
    public function users()
    {
        $userModel = new UserModel();
        $users = $userModel
            ->select('id, name, email, role, created_at')
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->respond([
            'count' => count($users),
            'users' => $users,
        ]);
    }

    /**
     * GET /api/patients
     * List all registered patients (clients).
     */
    public function patients()
    {
        $userModel = new UserModel();
        $patients = $userModel
            ->select('id, name, email, phone, role, created_at')
            ->where('role', 'client')
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->respond([
            'count' => count($patients),
            'patients' => $patients,
        ]);
    }

    /**
     * POST /api/admin/users/add
     * Add a new user (for admin).
     */
    public function addUser()
    {
        $request = $this->httpRequest();

        $json = $request->getJSON(true);
        $name             = $json['name']             ?? $request->getPost('name');
        $email            = $json['email']            ?? $request->getPost('email');
        $phone            = $json['phone']            ?? $request->getPost('phone');
        $role             = $json['role']             ?? $request->getPost('role');
        $password         = $json['password']         ?? $request->getPost('password');
        $passwordConfirm  = $json['password_confirm'] ?? $request->getPost('password_confirm');

        $request->setGlobal('post', [
            'name'             => $name,
            'email'            => $email,
            'phone'            => $phone,
            'role'             => $role,
            'password'         => $password,
            'password_confirm' => $passwordConfirm,
        ]);

        $rules = [
            'name'             => 'required|min_length[3]|regex_match[/^[A-Za-zÑñ\s]+$/u]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'phone'            => 'required|regex_match[/^(09|\+639)\d{9}$/]',
            'role'             => 'required|in_list[admin,assistant_admin,client,secretary,doctor]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userModel = new UserModel();
        $userId = $userModel->insert([
            'name'          => trim((string) $name),
            'email'         => strtolower(trim((string) $email)),
            'phone'         => trim((string) $phone),
            'role'          => (string) $role,
            'password_hash' => password_hash((string) $password, PASSWORD_DEFAULT),
        ]);

        if (! $userId) {
            return $this->failServerError('Unable to add user.');
        }

        applyDenyOverridesForNewUser((int) $userId, (string) $role);

        return $this->respondCreated([
            'message' => 'User added successfully.',
            'user_id' => $userId,
        ]);
    }

    /**
     * POST /api/admin/roles/add
     * Add a new role (assistant_admin/assistant_secretary).
     */
    public function addRole()
    {
        $request = $this->httpRequest();

        $json = $request->getJSON(true);
        $name                = $json['name']                ?? $request->getPost('name');
        $email               = $json['email']               ?? $request->getPost('email');
        $role                = $json['role']                ?? $request->getPost('role');
        $rolePassword        = $json['role_password']       ?? $request->getPost('role_password');
        $rolePasswordConfirm = $json['role_password_confirm'] ?? $request->getPost('role_password_confirm');

        $request->setGlobal('post', [
            'name'                  => $name,
            'email'                 => $email,
            'role'                  => $role,
            'role_password'         => $rolePassword,
            'role_password_confirm' => $rolePasswordConfirm,
        ]);

        $rules = [
            'name'                  => 'required|min_length[3]',
            'email'                 => 'required|valid_email|is_unique[users.email]',
            'role'                  => 'required|in_list[assistant_admin,assistant_secretary]',
            'role_password'         => 'required|min_length[8]',
            'role_password_confirm' => 'required|matches[role_password]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userModel = new UserModel();
        $userId = $userModel->insert([
            'name'          => trim((string) $name),
            'email'         => strtolower(trim((string) $email)),
            'role'          => (string) $role,
            'password_hash' => password_hash('unused_' . bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
            'role_password' => password_hash((string) $rolePassword, PASSWORD_DEFAULT),
        ]);

        if (! $userId) {
            return $this->failServerError('Unable to add role.');
        }

        return $this->respondCreated([
            'message' => 'Role added successfully.',
            'user_id' => $userId,
        ]);
    }

    /**
     * POST /api/doctor/schedule/save
     * Save the doctor's schedule via JSON request.
     */
    public function saveDoctorSchedule($doctorId = null)
    {
        $request = $this->httpRequest();

        if ($doctorId === null) {
            $doctorId = (int) $request->getPost('doctor_id');
        }
        $doctorId = (int) $doctorId;
        if (!$doctorId) {
            $json = $request->getJSON(true);
            $doctorId = (int) ($json['doctor_id'] ?? 0);
        }

        if ($doctorId <= 0) {
            return $this->failValidationErrors(['doctor_id' => 'Doctor ID is required']);
        }

        $json = $request->getJSON(true);
        $schedule = $json['schedule'] ?? [];

        if (empty($schedule) || !is_array($schedule)) {
            return $this->failValidationErrors(['schedule' => 'Schedule data is required']);
        }

        $model = new \App\Models\DoctorScheduleModel();
        
        // Delete existing
        $model->where('doctor_id', $doctorId)->delete();

        // Insert new
        foreach ($schedule as $dayData) {
            if (!empty($dayData['enabled'])) {
                $model->insert([
                    'doctor_id'    => $doctorId,
                    'day'          => $dayData['day'],
                    'start_time'   => $dayData['startTime'],
                    'end_time'     => $dayData['endTime'],
                    'is_available' => 1,
                ]);
            }
        }

        return $this->respondUpdated([
            'success' => true,
            'message' => 'Schedule saved successfully'
        ]);
    }

    // ──────────────────── Admin: Appointments ─────────────────

    /**
     * GET /api/admin/appointments
     * All appointments with patient name, grouped by status.
     */
    public function adminAppointments()
    {
        $db = \Config\Database::connect();

        $ownerCol = $this->ownerColumn() ?? 'user_id';

        $rows = $db->query(
            "SELECT a.*, COALESCE(u.name, '—') AS patient_name
             FROM appointments a
             LEFT JOIN users u ON u.id = a.{$ownerCol}
             ORDER BY a.appointment_date DESC
             LIMIT 200"
        )->getResultArray();

        $pending   = array_values(array_filter($rows, fn($r) => ($r['status'] ?? '') === 'pending'));
        $confirmed = array_values(array_filter($rows, fn($r) => ($r['status'] ?? '') === 'confirmed'));
        $archived  = array_values(array_filter($rows, fn($r) => in_array($r['status'] ?? '', ['cancelled', 'completed'], true)));

        return $this->respond([
            'success'   => true,
            'all'       => array_values($rows),
            'pending'   => $pending,
            'confirmed' => $confirmed,
            'archived'  => $archived,
            'total'     => count($rows),
        ]);
    }

    /**
     * POST /api/admin/appointments/update-status
     * Update appointment status (pending|confirmed|cancelled).
     */
    public function adminUpdateAppointmentStatus()
    {
        $request = $this->httpRequest();
        $json    = $request->getJSON(true);
        $id      = (int) ($json['id'] ?? 0);
        $status  = (string) ($json['status'] ?? '');

        if (! $id || ! in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
            return $this->failValidationErrors(['_form' => 'Invalid id or status.']);
        }

        $model      = new AppointmentModel();
        $appointment = $model->find($id);

        // Prevent duplicate updates and notifications
        if ($appointment && $appointment['status'] === $status) {
            return $this->respond(['success' => true, 'message' => 'Status unchanged.']);
        }

        $updateData = ['status' => $status];
        if ($status === 'cancelled') {
            $updateData['archived_at'] = date('Y-m-d H:i:s');
        }
        $model->update($id, $updateData);

        // Notify client about the status change
        if ($appointment) {
            $ownerCol = $this->ownerColumn();
            $patientId = $ownerCol ? (int) ($appointment[$ownerCol] ?? 0) : 0;
            if ($patientId > 0) {
                $notifModel = new \App\Models\NotificationModel();
                $date = $appointment['appointment_date'] ?? 'a scheduled date';
                $title = 'Appointment ' . ucfirst($status);
                $body = "Your appointment on {$date} has been marked as {$status}.";
                $notifModel->send($patientId, $title, $body, 'appointment');
            }
        }

        return $this->respond(['success' => true, 'message' => 'Status updated.']);
    }

    // ──────────────────── Admin: Doctor Schedules ─────────────

    /**
     * GET /api/admin/doctor-schedules
     * All doctors with their weekly schedules.
     */
    public function adminDoctorSchedules()
    {
        $userModel     = new UserModel();
        $scheduleModel = new DoctorScheduleModel();

        $doctors = $userModel->where('role', 'doctor')->where('deleted_at IS NULL')->findAll();

        $result = [];
        foreach ($doctors as $doc) {
            $schedules = $scheduleModel->getScheduleByDoctor((int) $doc['id']);
            $result[]  = [
                'id'             => $doc['id'],
                'name'           => $doc['name'],
                'specialization' => $doc['specialization'] ?? 'Doctor',
                'phone'          => $doc['phone'] ?? '',
                'schedules'      => $schedules,
            ];
        }

        return $this->respond([
            'success' => true,
            'doctors' => $result,
            'count'   => count($result),
        ]);
    }

    // ──────────────────── Admin: Access Requests ──────────────

    /**
     * GET /api/admin/access-requests
     * Pending + full history of access requests.
     */
    public function adminAccessRequests()
    {
        $arModel   = new \App\Models\AccessRequestModel();
        $userModel = new UserModel();

        $pending = $arModel->where('status', 'pending')->orderBy('id', 'DESC')->findAll();
        $all     = $arModel->orderBy('id', 'DESC')->limit(100)->findAll();

        // Attach user info
        foreach ($all as &$req) {
            $u = $userModel->find((int) ($req['user_id'] ?? 0));
            $req['user_name']  = $u['name']  ?? '—';
            $req['user_email'] = $u['email'] ?? '—';
        }
        foreach ($pending as &$req) {
            $u = $userModel->find((int) ($req['user_id'] ?? 0));
            $req['user_name']  = $u['name']  ?? '—';
            $req['user_email'] = $u['email'] ?? '—';
        }

        return $this->respond([
            'success' => true,
            'pending' => array_values($pending),
            'all'     => array_values($all),
        ]);
    }

    /**
     * POST /api/admin/access-requests/approve
     * Approve or reject an access request.
     */
    public function adminApproveAccessRequest()
    {
        $request = $this->httpRequest();
        $json    = $request->getJSON(true);
        $id      = (int) ($json['id'] ?? 0);
        $action  = (string) ($json['action'] ?? ''); // 'approve' | 'reject'

        if (! $id || ! in_array($action, ['approve', 'reject'], true)) {
            return $this->failValidationErrors(['_form' => 'Invalid id or action.']);
        }

        $arModel = new \App\Models\AccessRequestModel();
        $arModel->update($id, ['status' => $action === 'approve' ? 'approved' : 'rejected']);

        return $this->respond(['success' => true, 'message' => 'Request ' . $action . 'd.']);
    }

    // ──────────────────── Admin: Announcements ────────────────

    /**
     * GET /api/admin/announcements
     * Fetch all announcements.
     */
    public function adminAnnouncements()
    {
        $db = \Config\Database::connect();

        $rows = [];
        if ($db->tableExists('announcements')) {
            $rows = $db->query(
                'SELECT a.*, u.name AS created_by_name
                 FROM announcements a
                 LEFT JOIN users u ON u.id = a.created_by
                 ORDER BY a.created_at DESC
                 LIMIT 50'
            )->getResultArray();
        }

        return $this->respond([
            'success'       => true,
            'announcements' => $rows,
            'count'         => count($rows),
        ]);
    }

    /**
     * POST /api/admin/announcements
     * Create a new announcement.
     */
    public function adminCreateAnnouncement()
    {
        $request         = $this->httpRequest();
        $json            = $request->getJSON(true);
        $title           = trim((string) ($json['title'] ?? ''));
        $content         = trim((string) ($json['content'] ?? ''));
        $userId          = (int) ($json['user_id'] ?? 0);
        $targetDashboard = trim((string) ($json['target_dashboard'] ?? 'all'));

        if (! $title || ! $content) {
            return $this->failValidationErrors(['_form' => 'Title and content are required.']);
        }

        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $db->query(
            "INSERT INTO announcements (title, body, content, type, target_dashboard, created_by, created_at, updated_at) 
             VALUES (?, ?, ?, 'info', ?, ?, ?, ?)",
            [$title, $content, $content, $targetDashboard, $userId, $now, $now]
        );
        $announcementId = $db->insertID();

        // Notify matched active users
        try {
            $userModel = new \App\Models\UserModel();
            $notifModel = new \App\Models\NotificationModel();
            
            $builder = $userModel->where('deleted_at IS NULL');
            if ($targetDashboard === 'admin') {
                $builder->whereIn('role', ['admin', 'assistant_admin']);
            } elseif ($targetDashboard !== 'all') {
                $builder->where('role', $targetDashboard);
            }

            $users = $builder->findAll();
            foreach ($users as $u) {
                $notifModel->save([
                    'user_id'         => (int) $u['id'],
                    'title'           => 'New Announcement: ' . $title,
                    'body'            => $content,
                    'type'            => 'announcement',
                    'announcement_id' => $announcementId,
                    'is_read'         => 0,
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to notify users on announcement: ' . $e->getMessage());
        }

        return $this->respondCreated(['success' => true, 'message' => 'Announcement posted.']);
    }

    /**
     * DELETE /api/admin/announcements/(:num)
     * Delete an announcement.
     */
    public function adminDeleteAnnouncement(int $id)
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('announcements')) {
            $db->query('DELETE FROM announcements WHERE id = ?', [$id]);
        }
        $db->query('DELETE FROM notifications WHERE announcement_id = ?', [$id]);
        return $this->respond(['success' => true, 'message' => 'Announcement deleted.']);
    }

    private function sanitizeUtf8($data)
    {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        } elseif (is_array($data)) {
            $ret = [];
            foreach ($data as $k => $v) {
                $ret[$k] = $this->sanitizeUtf8($v);
            }
            return $ret;
        } elseif (is_object($data)) {
            $ret = new \stdClass();
            foreach (get_object_vars($data) as $k => $v) {
                $ret->$k = $this->sanitizeUtf8($v);
            }
            return $ret;
        }
        return $data;
    }

    // ──────────────────── Admin: Audit Reports ────────────────

    /**
     * GET /api/admin/audit-reports
     * Login event stats + event log.
     */
    public function adminAuditReports()
    {
        $request = $this->httpRequest();
        $filter  = $request->getGet('filter') ?? 'weekly';

        $data = $this->buildAuditReport($filter);

        return $this->respond($this->sanitizeUtf8(array_merge(['success' => true, 'filter' => $filter], $data)));
    }

    /**
     * GET /api/admin/audit-reports/export?filter=weekly
     * Returns CSV content as a downloadable file.
     */
    public function adminAuditExportCsv()
    {
        $request = $this->httpRequest();
        $filter  = $request->getGet('filter') ?? 'weekly';
        $data    = $this->buildAuditReport($filter);

        $filename = 'audit_report_' . $filter . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['AUDIT REPORT - ' . strtoupper($filter)]);
        fputcsv($out, ['Generated', date('Y-m-d H:i:s')]);
        fputcsv($out, []);
        fputcsv($out, ['SUMMARY']);
        fputcsv($out, ['Metric', 'Count']);
        fputcsv($out, ['Successful Logins',   $data['total_success']]);
        fputcsv($out, ['Failed Logins',        $data['total_failed']]);
        fputcsv($out, ['Locked Accounts',      $data['total_locked']]);
        fputcsv($out, ['Suspicious Activity',  $data['total_suspicious']]);
        fputcsv($out, ['MFA Successes',        $data['total_mfa_success']]);
        fputcsv($out, ['MFA Failures',         $data['total_mfa_failed']]);
        fputcsv($out, ['Logouts',              $data['total_logout']]);
        fputcsv($out, ['Active Sessions',      $data['active_sessions']]);
        fputcsv($out, ['Security Alerts Sent', $data['alert_count']]);
        fputcsv($out, []);
        fputcsv($out, ['EVENT LOG']);
        fputcsv($out, ['#', 'Timestamp', 'Event Type', 'User ID', 'Email Attempted', 'Reason']);
        foreach ($data['events'] as $i => $e) {
            fputcsv($out, [
                $i + 1,
                $e['created_at']     ?? '',
                $e['event_type']     ?? '',
                $e['user_id']        ?? '',
                $e['email_attempted']?? '',
                $e['reason_code']    ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    private function buildAuditReport(string $filter): array
    {
        $db = \Config\Database::connect();

        $since = match($filter) {
            'daily'   => date('Y-m-d H:i:s', strtotime('-1 day')),
            'monthly' => date('Y-m-d H:i:s', strtotime('-30 days')),
            default   => date('Y-m-d H:i:s', strtotime('-7 days')),
        };

        // Aggregate counts
        $rows = $db->query(
            'SELECT event_type, COUNT(*) as cnt FROM login_events WHERE created_at >= ? GROUP BY event_type',
            [$since]
        )->getResultArray();
        $counts = [];
        foreach ($rows as $r) {
            $counts[$r['event_type']] = (int) $r['cnt'];
        }

        // Chart breakdown
        $days         = $filter === 'monthly' ? 30 : ($filter === 'daily' ? 1 : 7);
        $chartLabels  = [];
        $chartSuccess = [];
        $chartFailed  = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day           = date('Y-m-d', strtotime("-{$i} days"));
            $chartLabels[] = date('M j', strtotime($day));
            $chartSuccess[] = (int) ($db->query(
                "SELECT COUNT(*) as c FROM login_events WHERE event_type='login_success' AND DATE(created_at)=?", [$day]
            )->getRowArray()['c'] ?? 0);
            $chartFailed[] = (int) ($db->query(
                "SELECT COUNT(*) as c FROM login_events WHERE event_type='login_failed' AND DATE(created_at)=?", [$day]
            )->getRowArray()['c'] ?? 0);
        }

        // Active sessions
        $activeSessions = 0;
        if ($db->tableExists('auth_sessions')) {
            $sesRow = $db->query(
                "SELECT COUNT(*) as cnt FROM auth_sessions WHERE revoked_at IS NULL AND expires_at > NOW()"
            )->getRowArray();
            $activeSessions = (int) ($sesRow['cnt'] ?? 0);
        }

        // Alert count from notifications
        $alertCount = (int) ($db->query(
            "SELECT COUNT(*) as c FROM notifications WHERE type='warning' AND created_at >= ?", [$since]
        )->getRowArray()['c'] ?? 0);

        // Events list
        $eventModel = new \App\Models\LoginEventModel();
        $raw = $db->query(
            'SELECT id, user_id, email_attempted, event_type, reason_code, created_at
             FROM login_events WHERE created_at >= ? ORDER BY created_at DESC LIMIT 100',
            [$since]
        )->getResultArray();

        // Decrypt email via model's afterFind hook
        $events = $eventModel->find(array_column($raw, 'id')) ?: $raw;
        // Flatten & sanitize
        foreach ($events as &$e) {
            $e['email_attempted'] = is_string($e['email_attempted'] ?? null) ? $e['email_attempted'] : '—';
            unset($e['ip_hash'], $e['user_agent']);
        }

        return [
            'total_success'     => $counts['login_success']       ?? 0,
            'total_failed'      => $counts['login_failed']        ?? 0,
            'total_locked'      => $counts['login_locked']        ?? 0,
            'total_suspicious'  => $counts['suspicious_activity'] ?? 0,
            'total_mfa_success' => $counts['mfa_success']         ?? 0,
            'total_mfa_failed'  => $counts['mfa_failed']          ?? 0,
            'total_logout'      => $counts['logout']              ?? 0,
            'active_sessions'   => $activeSessions,
            'alert_count'       => $alertCount,
            'events'            => array_values($raw),   // use raw (not decrypted) for display
            'chart_labels'      => $chartLabels,
            'chart_success'     => $chartSuccess,
            'chart_failed'      => $chartFailed,
            'since'             => $since,
        ];
    }

    // ── SYSTEM AUDIT LOG ──────────────────────────────────────────────
    public function adminSystemAuditLog()
    {
        $logModel = new \App\Models\LoginEventModel();
        
        $events   = $logModel->getRecentEvents(200);
        $summary  = $logModel->getAuditSummary();
        $sessions = $logModel->getActiveSessions();
        $failed24 = $logModel->getFailedLoginsLast24h();
        $suspicious = $logModel->getSuspiciousCount();
        
        return $this->respond($this->sanitizeUtf8([
            'success' => true,
            'events' => array_values($events),
            'summary' => $summary,
            'sessions' => $sessions,
            'failed24' => $failed24,
            'suspicious' => $suspicious,
        ]));
    }

    // ── MANAGE PERMISSIONS ────────────────────────────────────────────
    public function adminPermissions()
    {
        $db = \Config\Database::connect();

        $roles = $db->query('SELECT * FROM roles ORDER BY id ASC')->getResultArray();
        $rawPermissions = $db->query('SELECT * FROM permissions ORDER BY code ASC')->getResultArray();

        // Normalize permission codes to human-friendly labels (no underscores)
        // and deduplicate by code if the DB contains duplicates.
        $permissions = [];
        $seenCodes = [];
        foreach ($rawPermissions as $p) {
            $code = $p['code'] ?? '';
            if ($code === '' || in_array($code, $seenCodes, true)) {
                continue;
            }
            $seenCodes[] = $code;
            $label = preg_replace('/\s+/', ' ', trim(str_replace('_', ' ', $code)));
            $label = ucwords($label);
            $p['display_name'] = $label;
            $permissions[] = $p;
        }

        $rolePerms = $db->query('SELECT role_id, permission_id FROM role_permissions')->getResultArray();
        $mapping = [];
        foreach ($rolePerms as $rp) {
            $rid = $rp['role_id'];
            $pid = $rp['permission_id'];
            $mapping[$rid] = $mapping[$rid] ?? [];
            if (! in_array($pid, $mapping[$rid], true)) {
                $mapping[$rid][] = $pid;
            }
        }

        $userModel = new \App\Models\UserModel();
        $roleCounts = [];
        foreach ($roles as $role) {
            $roleCounts[$role['name']] = $userModel->where('role', $role['name'])->where('deleted_at IS NULL')->countAllResults();
        }

        return $this->respond([
            'success' => true,
            'roles' => $roles,
            'permissions' => $permissions,
            'mapping' => $mapping,
            'roleCounts' => $roleCounts,
        ]);
    }

    public function adminTogglePermission()
    {
        $json = $this->httpRequest()->getJSON(true);
        if (!$json) {
            return $this->fail('Invalid JSON');
        }

        $roleId = (int) ($json->role_id ?? 0);
        $permissionId = (int) ($json->permission_id ?? 0);
        $action = (string) ($json->action ?? '');

        if (!$roleId || !$permissionId || !in_array($action, ['assign', 'revoke'])) {
            return $this->fail('Invalid parameters');
        }

        $db = \Config\Database::connect();

        if ($action === 'assign') {
            $exists = $db->query('SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?', [$roleId, $permissionId])->getRowArray();
            if (!$exists) {
                $db->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)', [$roleId, $permissionId]);
            }
        } else {
            $db->query('DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?', [$roleId, $permissionId]);
        }

        return $this->respond(['success' => true, 'action' => $action]);
    }

    public function adminAddPermission()
    {
        $json = $this->httpRequest()->getJSON(true);
        if (!$json) {
            return $this->fail('Invalid JSON');
        }

        $code = trim((string) ($json->code ?? ''));
        $desc = trim((string) ($json->description ?? ''));

        if (!$code) {
            return $this->respond(['success' => false, 'message' => 'Permission code is required.']);
        }

        $db = \Config\Database::connect();
        $exists = $db->query('SELECT id FROM permissions WHERE code = ?', [$code])->getRowArray();

        if ($exists) {
            return $this->respond(['success' => false, 'message' => 'Permission code already exists.']);
        }

        $db->query('INSERT INTO permissions (code, description) VALUES (?, ?)', [$code, $desc]);

        return $this->respond(['success' => true, 'message' => "Permission '{$code}' added."]);
    }
}

