<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AppointmentModel;
use App\Models\ClinicSettingsModel;
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
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $request = $this->request;

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

        return $this->respondCreated([
            'message' => 'Registration successful. Please login.',
            'user_id' => $userId,
        ]);
    }

    // ──────────────────── Role Selection ─────────────────────

    /**
     * POST /api/role-selection
     * Verify clinic access code + role password for admin / assistant_admin.
     */
    public function roleSelection()
    {
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $json = $this->request->getJSON(true);
        $userId       = $json['user_id']           ?? $this->request->getPost('user_id');
        $clinicCode   = $json['clinic_access_code'] ?? $this->request->getPost('clinic_access_code');
        $selectedRole = $json['role']               ?? $this->request->getPost('role');
        $rolePassword = $json['role_password']      ?? $this->request->getPost('role_password');

        if (! $userId || ! $clinicCode || ! $selectedRole || ! $rolePassword) {
            return $this->failValidationErrors(['_form' => 'All fields are required.']);
        }

        // Validate clinic access code
        $settingsModel = new ClinicSettingsModel();
        $storedCode = $settingsModel->getValue('clinic_access_code');

        if (! $storedCode || ! password_verify((string) $clinicCode, $storedCode)) {
            return $this->failUnauthorized('Invalid clinic access code.');
        }

        $userModel = new UserModel();
        $user = $userModel->find((int) $userId);

        if (! $user) {
            return $this->failNotFound('User not found.');
        }

        if ($selectedRole === 'admin') {
            $hashToCheck = ! empty($user['role_password']) ? $user['role_password'] : $user['password_hash'];
            if (! password_verify((string) $rolePassword, $hashToCheck)) {
                return $this->failUnauthorized('Incorrect password for Admin role.');
            }
        } elseif ($selectedRole === 'assistant_admin') {
            $allAssistants = $userModel->where('role', 'assistant_admin')->where('deleted_at IS NULL')->findAll();
            $matched = false;
            foreach ($allAssistants as $a) {
                if (! empty($a['role_password']) && password_verify((string) $rolePassword, $a['role_password'])) {
                    $matched = true;
                    $user = $a; // Use the assistant's data
                    break;
                }
            }
            if (! $matched) {
                return $this->failUnauthorized('Incorrect password for Assistant Admin role.');
            }
        } else {
            return $this->failValidationErrors(['role' => 'Invalid role selected.']);
        }

        $token = bin2hex(random_bytes(32));

        return $this->respond([
            'message' => 'Role verified',
            'token'   => $token,
            'user'    => [
                'id'    => $user['id'],
                'name'  => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'role'  => $selectedRole,
            ],
        ]);
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
        $userId = $this->request->getGet('user_id');

        if (! $userId) {
            return $this->failValidationErrors(['user_id' => 'user_id is required.']);
        }

        $ownerCol = $this->ownerColumn();
        if ($ownerCol === null) {
            return $this->respond(['appointments' => []]);
        }

        $model = new AppointmentModel();
        $appointments = $model
            ->where($ownerCol, (int) $userId)
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('appointment_time', 'DESC')
            ->findAll();

        // Normalize field names for the Flutter model
        $result = array_map(function ($a) {
            return [
                'id'           => $a['id'] ?? null,
                'patient_name' => '', // will be filled by Flutter from session
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
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $json = $this->request->getJSON(true);
        $userId          = $json['user_id']          ?? $this->request->getPost('user_id');
        $doctorName      = $json['doctor_name']      ?? $this->request->getPost('doctor_name');
        $appointmentDate = $json['appointment_date'] ?? $this->request->getPost('appointment_date');
        $appointmentTime = $json['appointment_time'] ?? $this->request->getPost('appointment_time');
        $reason          = $json['reason']           ?? $this->request->getPost('reason');

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

            // Resolve doctor_id from name
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

        // Notify doctor
        if (! empty($insertData['doctor_id'])) {
            $notifModel = new NotificationModel();
            $patientUser = (new UserModel())->find((int) $userId);
            $patientName = $patientUser['name'] ?? 'A patient';
            $notifModel->send(
                (int) $insertData['doctor_id'],
                'New Appointment Booked',
                "{$patientName} booked an appointment on {$appointmentDate} at " . substr((string) $appointmentTime, 0, 5) . '.',
                'appointment'
            );
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
        $userId = $this->request->getGet('user_id');
        $role   = $this->request->getGet('role') ?? 'client';

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

        // Admin / Secretary / Doctor — show all counts
        $total    = $appointmentModel->countAllResults(false);
        $pending  = $appointmentModel->where('status', 'pending')->countAllResults(false);
        $approved = $appointmentModel->where('status', 'approved')->countAllResults(false);

        $userModel   = new UserModel();
        $totalUsers  = $userModel->where('deleted_at IS NULL')->countAllResults(false);
        $totalDoctors = $userModel->where('role', 'doctor')->where('deleted_at IS NULL')->countAllResults(false);

        return $this->respond([
            'total_appointments' => $total,
            'pending'            => $pending,
            'approved'           => $approved,
            'total_users'        => $totalUsers,
            'total_doctors'      => $totalDoctors,
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
        $userId = $this->request->getGet('user_id');

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
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $json = $this->request->getJSON(true);
        $userId  = $json['user_id'] ?? $this->request->getPost('user_id');

        if (! $userId) {
            return $this->failValidationErrors(['user_id' => 'user_id is required.']);
        }

        $userModel = new UserModel();
        $user = $userModel->find((int) $userId);

        if (! $user) {
            return $this->failNotFound('User not found.');
        }

        $updateData = [];
        $allowedFields = ['name', 'phone', 'city', 'address'];
        foreach ($allowedFields as $field) {
            if (isset($json[$field])) {
                $updateData[$field] = trim((string) $json[$field]);
            }
        }

        if (empty($updateData)) {
            return $this->failValidationErrors(['_form' => 'No fields to update.']);
        }

        $userModel->update((int) $userId, $updateData);

        return $this->respond([
            'message' => 'Profile updated successfully.',
        ]);
    }

    // ──────────────────── Notifications ──────────────────────

    /**
     * GET /api/notifications?user_id=X
     * Get notifications for a user.
     */
    public function notifications()
    {
        $userId = $this->request->getGet('user_id');

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
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $json = $this->request->getJSON(true);
        $name             = $json['name']             ?? $this->request->getPost('name');
        $email            = $json['email']            ?? $this->request->getPost('email');
        $phone            = $json['phone']            ?? $this->request->getPost('phone');
        $role             = $json['role']             ?? $this->request->getPost('role');
        $password         = $json['password']         ?? $this->request->getPost('password');
        $passwordConfirm  = $json['password_confirm'] ?? $this->request->getPost('password_confirm');

        $this->request->setGlobal('post', [
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
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $json = $this->request->getJSON(true);
        $name                = $json['name']                ?? $this->request->getPost('name');
        $email               = $json['email']               ?? $this->request->getPost('email');
        $role                = $json['role']                ?? $this->request->getPost('role');
        $rolePassword        = $json['role_password']       ?? $this->request->getPost('role_password');
        $rolePasswordConfirm = $json['role_password_confirm'] ?? $this->request->getPost('role_password_confirm');

        $this->request->setGlobal('post', [
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
}
