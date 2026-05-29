<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoginEventModel;

class Admin extends BaseController
{
    private function sendAppointmentApprovedNotification(array $appointment): void
    {
        $doctorId   = (int) ($appointment['doctor_id'] ?? 0);
        $doctorName = trim((string) ($appointment['doctor_name'] ?? ''));

        if ($doctorId <= 0 && $doctorName === '') {
            return;
        }

        $db = \Config\Database::connect();
        $doctor = null;

        if ($doctorId > 0) {
            $doctor = $db->query(
                'SELECT u.id, COALESCE(up.name, u.username, "") AS name
                 FROM users u
                 LEFT JOIN user_profiles up ON up.user_id = u.id
                 WHERE u.id = ?
                   AND u.role = ?
                 LIMIT 1',
                [$doctorId, 'doctor']
            )->getRowArray();
        }

        if (! $doctor && $doctorName !== '') {
            $doctor = $db->query(
                'SELECT u.id, COALESCE(up.name, u.username, "") AS name
                 FROM users u
                 LEFT JOIN user_profiles up ON up.user_id = u.id
                 WHERE u.role = ?
                   AND COALESCE(up.name, u.username, "") = ?
                 LIMIT 1',
                ['doctor', $doctorName]
            )->getRowArray();
        }

        if (! $doctor || empty($doctor['id'])) {
            return;
        }

        $approverName = trim((string) (session('user_name') ?? ''));
        $approverRole  = session('user_role') === 'assistant_admin' ? 'Assistant Admin' : 'Admin';
        $approvedBy    = $approverName !== '' ? "{$approverRole} {$approverName}" : strtolower($approverRole);

        $date = (string) ($appointment['appointment_date'] ?? 'a scheduled date');
        $time = substr((string) ($appointment['appointment_time'] ?? ''), 0, 5);

        $notifModel = new \App\Models\NotificationModel();
        $notifModel->send(
            (int) $doctor['id'],
            'Appointment Confirmed',
            "Your client appointment on {$date} at {$time} has been confirmed by {$approvedBy}.",
            'appointment'
        );
    }

    public function announcements()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $db = \Config\Database::connect();
        $announcements = [];
        if ($db->tableExists('announcements')) {
            $announcements = $db->query(
                'SELECT a.*, COALESCE(up.name, u.username, "") AS created_by_name
                 FROM announcements a
                 LEFT JOIN users u ON u.id = a.created_by
                 LEFT JOIN user_profiles up ON up.user_id = u.id
                 ORDER BY a.created_at DESC
                 LIMIT 50'
            )->getResultArray();
        }

        return view('admin/announcements', ['announcements' => $announcements]);
    }

    public function addAnnouncement()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $title           = trim((string) $this->request->getPost('title'));
        $body            = trim((string) $this->request->getPost('body'));
        $type            = (string) $this->request->getPost('type') ?: 'info';
        $targetDashboard = (string) $this->request->getPost('target_dashboard') ?: 'all';

        if (! $title || ! $body) {
            return redirect()->back()->with('error', 'Title and message are required.');
        }

        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $db->query(
            "INSERT INTO announcements (title, body, type, target_dashboard, created_by, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$title, $body, $type, $targetDashboard, (int) session('user_id'), $now, $now]
        );
        $announcementId = $db->insertID();

        // Send notifications to matched users
        $userModel = new UserModel();
        $builder = $userModel->where('deleted_at IS NULL');
        
        if ($targetDashboard === 'admin') {
            $builder->whereIn('role', ['admin', 'assistant_admin']);
        } elseif ($targetDashboard !== 'all') {
            $builder->where('role', $targetDashboard);
        }

        $users = $builder->findAll();

        $notifModel = new \App\Models\NotificationModel();
        foreach ($users as $user) {
            $notificationId = $notifModel->insert([
                'user_id'         => (int) $user['id'],
                'title'           => 'New Announcement: ' . $title,
                'body'            => $body,
                'type'            => 'announcement',
                'is_read'         => 0,
            ], true);

            if ($notificationId) {
                $db->table('notification_announcement_links')->insert([
                    'notification_id' => (int) $notificationId,
                    'announcement_id' => (int) $announcementId,
                ]);
            }
        }

        return redirect()->to('/admin/announcements')->with('success', 'Announcement posted successfully.');
    }

    public function deleteAnnouncement(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $db = \Config\Database::connect();
        if ($db->tableExists('announcements')) {
            $db->query('DELETE FROM announcements WHERE id = ?', [$id]);
        }
        $db->query(
            'DELETE n FROM notifications n INNER JOIN notification_announcement_links l ON l.notification_id = n.id WHERE l.announcement_id = ?',
            [$id]
        );

        return redirect()->to('/admin/announcements')->with('success', 'Announcement deleted.');
    }

    public function appointments()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $db = \Config\Database::connect();
        $hasDoctorId = $db->fieldExists('doctor_id', 'appointments');
        $hasDoctorName = $db->fieldExists('doctor_name', 'appointments');

        $all = $db->query(
              'SELECT a.*, COALESCE(up.name, u.username, "—") as patient_name
               FROM appointments a
               LEFT JOIN users u ON u.id = a.user_id
               LEFT JOIN user_profiles up ON up.user_id = u.id
             ORDER BY a.appointment_date DESC'
        )->getResultArray();

        if ($hasDoctorId && ! empty($all)) {
            $doctorIds = [];
            foreach ($all as $row) {
                $doctorId = (int) ($row['doctor_id'] ?? 0);
                if ($doctorId > 0) {
                    $doctorIds[$doctorId] = true;
                }
            }

            if ($doctorIds !== []) {
                $doctorRows = $db->query(
                    'SELECT u.id, COALESCE(up.name, u.username, "—") AS doctor_display_name
                     FROM users u
                     LEFT JOIN user_profiles up ON up.user_id = u.id
                     WHERE u.id IN (' . implode(',', array_fill(0, count($doctorIds), '?')) . ')',
                    array_keys($doctorIds)
                )->getResultArray();

                $doctorMap = [];
                foreach ($doctorRows as $doctorRow) {
                    $doctorMap[(int) $doctorRow['id']] = trim((string) ($doctorRow['doctor_display_name'] ?? '')) ?: '—';
                }

                foreach ($all as &$row) {
                    $doctorName = trim((string) ($row['doctor_name'] ?? ''));
                    if ($doctorName === '' && $hasDoctorId) {
                        $doctorId = (int) ($row['doctor_id'] ?? 0);
                        if ($doctorId > 0 && isset($doctorMap[$doctorId])) {
                            $row['doctor_name'] = $doctorMap[$doctorId];
                        }
                    }
                }
                unset($row);
            }
        }

        $pending   = array_filter($all, fn($a) => ($a['status'] ?? '') === 'pending');
        $confirmed = array_filter($all, fn($a) => ($a['status'] ?? '') === 'confirmed');
        $archived  = array_filter($all, fn($a) => in_array($a['status'] ?? '', ['cancelled', 'completed'], true));

        return view('admin/appointments', [
            'appointments' => array_values($all),
            'pending'      => array_values($pending),
            'confirmed'    => array_values($confirmed),
            'archived'     => array_values($archived),
        ]);
    }

    public function archiveAppointment(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $model = new \App\Models\AppointmentModel();
        $model->update($id, ['archived_at' => date('Y-m-d H:i:s')]);

        return redirect()->back()->with('success', 'Appointment moved to archive.');
    }

    public function restoreAppointment(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $model = new \App\Models\AppointmentModel();
        $appt  = $model->find($id);

        if (! $appt) {
            return redirect()->back()->with('error', 'Appointment not found.');
        }

        // Auto-delete if the appointment date has already passed
        if (! empty($appt['appointment_date']) && $appt['appointment_date'] < date('Y-m-d')) {
            $model->delete($id);
            return redirect()->back()->with('success', 'Appointment was past its date and has been permanently deleted.');
        }

        $model->update($id, ['archived_at' => null, 'status' => 'pending']);
        return redirect()->back()->with('success', 'Appointment restored to Pending.');
    }

    public function deleteArchivedAppointment(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $model = new \App\Models\AppointmentModel();
        $model->delete($id);

        return redirect()->back()->with('success', 'Appointment permanently deleted.');
    }

    public function updateAppointmentStatus()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $id     = (int) $this->request->getPost('id');
        $status = (string) $this->request->getPost('status');

        if (! in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'Invalid appointment status.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $model      = new \App\Models\AppointmentModel();
            $appointment = $model->find($id);
            $updateData = ['status' => $status];

            // Auto-archive when admin sets status to cancelled
            if ($status === 'cancelled') {
                $updateData['archived_at'] = date('Y-m-d H:i:s');
            }

            $model->update($id, $updateData);

            if ($status === 'confirmed' && $appointment) {
                $this->sendAppointmentApprovedNotification($appointment);
            }

            if (! $db->transStatus()) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Unable to update status. Transaction rolled back.');
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Admin updateAppointmentStatus transaction failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Changes rolled back.');
        }

        return redirect()->back()->with('success', 'Appointment status updated.');
    }

    public function accessRequests()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $arModel    = new \App\Models\AccessRequestModel();
        $userModel  = new UserModel();

        $roleTabs = [
            'assistant_admin'     => 'Assistant Admin',
            'secretary'           => 'Secretary',
            'doctor'              => 'Doctor',
            'client'              => 'Client / Patient',
        ];

        $pending = $arModel->where('status', 'pending')->orderBy('id', 'DESC')->findAll();
        $all     = $arModel->orderBy('id', 'DESC')->limit(100)->findAll();

        $pendingByRole = [];
        $historyByRole = [];
        foreach (array_keys($roleTabs) as $roleKey) {
            $pendingByRole[$roleKey] = [];
            $historyByRole[$roleKey] = [];
        }

        // Attach user info to all requests
        foreach ($all as &$req) {
            $u = $userModel->find($req['user_id']);
            $req['user_name']  = $u['name']  ?? '—';
            $req['user_email'] = $u['email'] ?? '—';
            $reqRole = $req['requested_role'] ?? 'client';
            if (! isset($historyByRole[$reqRole])) {
                $historyByRole[$reqRole] = [];
            }
            $historyByRole[$reqRole][] = $req;
        }

        foreach ($pending as $req) {
            $reqRole = $req['requested_role'] ?? 'client';
            if (! isset($pendingByRole[$reqRole])) {
                $pendingByRole[$reqRole] = [];
            }
            $pendingByRole[$reqRole][] = $req;
        }

        $activeRole = (string) $this->request->getGet('role');
        if (! isset($roleTabs[$activeRole])) {
            $activeRole = array_key_first($roleTabs) ?: 'client';
        }

        return view('admin/access_requests', [
            'pending'        => $pending,
            'all'            => $all,
            'pendingByRole'  => $pendingByRole,
            'historyByRole'  => $historyByRole,
            'roleTabs'       => $roleTabs,
            'activeRole'     => $activeRole,
        ]);
    }

    public function ensureAdminAccess()
    {
        $role = (string) session()->get('user_role');
        if ($role !== 'admin' && $role !== 'assistant_admin') {
            return redirect()->to('/dashboard');
        }
        return null;
    }

    private function ensureMainAdminOnly()
    {
        $role = (string) session()->get('user_role');
        if ($role !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Main Admin only.');
        }
        return null;
    }

    public function addRole()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        if ($this->request->is('post')) {
            $rules = [
                'name'  => 'required|min_length[3]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'role'  => 'required|in_list[assistant_admin,assistant_secretary]',
            ];

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $created = $userModel->insert([
                'name'          => trim((string) $this->request->getPost('name')),
                'email'         => strtolower(trim((string) $this->request->getPost('email'))),
                'role'          => (string) $this->request->getPost('role'),
                'password_hash' => password_hash('unused_' . bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
            ]);

            if (! $created) {
                return redirect()->back()->withInput()->with('error', 'Unable to add role.');
            }

            return redirect()->to('/admin/patients/list')->with('success', 'Role added successfully.');
        }

        return view('admin/add_role');
    }

    public function patients()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) {
            return $access;
        }

        // Redirect directly to the patient list — skip the hub page
        return redirect()->to('/admin/patients/clients');
    }

    public function clientList()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) {
            return $access;
        }

        $db = \Config\Database::connect();
        $users = $db->query(
            'SELECT u.id, COALESCE(up.name, u.username, "") AS name, u.email, up.phone, u.deleted_at, u.created_at
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE u.role = ?
             ORDER BY u.id DESC',
            ['client']
        )->getResultArray();

        // Attach appointment count per patient
        $apptModel = new \App\Models\AppointmentModel();
        $ownerCol = $db->fieldExists('client_id', 'appointments') ? 'client_id' : 'user_id';

        foreach ($users as &$user) {
            $user['appointment_count'] = $apptModel
                ->where($ownerCol, (int) $user['id'])
                ->countAllResults(false);
        }

        return view('admin/client_list', [
            'users'    => $users,
            'ownerCol' => $ownerCol,
        ]);
    }

    public function doctorSchedule()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $userModel = new UserModel();
        $doctors   = $userModel->where('role', 'doctor')->where('deleted_at IS NULL')->findAll();

        return view('admin/doctor_schedule', ['doctors' => $doctors]);
    }

    public function doctorSpecialization()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $userModel = new UserModel();
        $doctors   = $userModel->where('role', 'doctor')->where('deleted_at IS NULL')->orderBy('specialization', 'ASC')->findAll();

        $grouped = [];
        foreach ($doctors as $doc) {
            $spec = $doc['specialization'] ?? 'General';
            $grouped[$spec][] = $doc;
        }

        return view('admin/doctor_specialization', ['grouped' => $grouped]);
    }

    public function doctorList()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $userModel = new UserModel();
        $doctors   = $userModel->where('role', 'doctor')->where('deleted_at IS NULL')->orderBy('name', 'ASC')->findAll();

        return view('admin/doctor_list', ['doctors' => $doctors]);
    }

    public function patientHistory(int $id = 0)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $userModel = new UserModel();
        $appointmentModel = new \App\Models\AppointmentModel();

        // If a specific patient ID is given, show their history
        if ($id > 0) {
            $patient = $userModel->find($id);
            if (! $patient) {
                return redirect()->to('/admin/patients/history')->with('error', 'Patient not found.');
            }

            $db       = \Config\Database::connect();
            $ownerCol = $db->fieldExists('client_id', 'appointments') ? 'client_id' : 'user_id';

            $appointments = $appointmentModel
                ->where($ownerCol, $id)
                ->orderBy('appointment_date', 'DESC')
                ->findAll();

            return view('admin/patient_history', [
                'patient'      => $patient,
                'appointments' => $appointments,
            ]);
        }

        // No ID — show list of all clients to pick from
        $patients = $userModel->where('role', 'client')->orderBy('name', 'ASC')->findAll();

        return view('admin/patient_history', [
            'patient'      => null,
            'patients'     => $patients,
            'appointments' => [],
        ]);
    }

    public function patientList()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $db = \Config\Database::connect();
        $selectedRole = strtolower(trim((string) $this->request->getGet('role')));
        $allowedRoles = ['admin', 'assistant_admin', 'doctor', 'secretary', 'client'];
        $roleTabs = [
            'all'             => 'All',
            'admin'           => 'Admin',
            'assistant_admin' => 'Assistant Admin',
            'doctor'          => 'Doctor',
            'secretary'       => 'Secretary',
            'client'          => 'Client / Patient',
        ];

        $counts = [
            'all' => (int) $db->table('users')->where('deleted_at IS NULL', null, false)->countAllResults(),
        ];

        foreach ($allowedRoles as $role) {
            $counts[$role] = (int) $db->table('users')
                ->where('role', $role)
                ->where('deleted_at IS NULL', null, false)
                ->countAllResults();
        }

        $sql = 'SELECT u.id, COALESCE(up.name, u.username, "") AS name, u.email, u.role, u.deleted_at, u.created_at
                FROM users u
                LEFT JOIN user_profiles up ON up.user_id = u.id';
        $params = [];

        if ($selectedRole !== '' && in_array($selectedRole, $allowedRoles, true)) {
            $sql .= ' WHERE u.role = ?';
            $params[] = $selectedRole;
        }

        $sql .= ' ORDER BY u.id DESC';

        $users = $db->query($sql, $params)->getResultArray();

        return view('admin/patients_list', [
            'users'        => $users,
            'selectedRole' => $selectedRole,
            'roleTabs'     => $roleTabs,
            'roleCounts'   => $counts,
        ]);
    }

    public function addUser()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        if ($this->hasPendingRegistration()) {
            return redirect()->to('/register/verify');
        }

        if ($this->request->is('post')) {
            $rules = [
                'name' => 'required|min_length[3]|regex_match[/^[A-Za-zÑñ\s]+$/u]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'role' => 'required|in_list[admin,assistant_admin,client,secretary,doctor]',
                'password' => 'required|min_length[8]',
                'password_confirm' => 'required|matches[password]',
            ];

            $messages = [
                'email' => [
                    'is_unique' => 'This email is already taken.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $name = trim((string) $this->request->getPost('name'));
            $email = strtolower(trim((string) $this->request->getPost('email')));
            $role = (string) $this->request->getPost('role');
            $password = (string) $this->request->getPost('password');

            $emailLocalPart = explode('@', $email)[0] ?? '';
            $emailNumberCount = preg_match_all('/\d/', $emailLocalPart);
            $emailSpecialCount = preg_match_all('/[^a-z0-9]/i', $emailLocalPart);

            if ($emailNumberCount > 5 || $emailSpecialCount > 3) {
                return redirect()->back()->withInput()->with('errors', [
                    'email' => 'Email allows maximum 5 numbers and 3 special characters before @.',
                ]);
            }

            $verificationCode = (string) random_int(100000, 999999);
            $adminName = trim((string) (session('user_name') ?? 'Admin'));
            $pendingRegistration = [
                'name'                   => $name,
                'email'                  => $email,
                'phone'                  => $role === 'client' ? trim((string) $this->request->getPost('phone')) : null,
                'password_hash'          => password_hash($password, PASSWORD_DEFAULT),
                'role'                   => $role,
                'verification_code_hash' => password_hash($verificationCode, PASSWORD_DEFAULT),
                'verification_expires_at'=> time() + 600,
                'verification_attempts'  => 0,
                'redirect_to'            => '/admin/patients/list',
                'reset_url'              => '/admin/patients/add',
                'created_by'             => (int) session('user_id'),
                'audit_note'             => 'user_added:' . $email . ' | by_admin:' . $adminName . ' | role:' . $role,
            ];

            if (! $this->sendVerificationCode($email, $name, $verificationCode)) {
                return redirect()->back()->withInput()->with('error', 'Unable to send verification code. Please check email settings and try again.');
            }

            session()->set('pending_registration', $pendingRegistration);

            return redirect()->to('/register/verify')->with('success', 'We sent a 6-digit verification code to the provided email. The user will be created after verification.');
        }

        return view('admin/add_user');
    }

    public function editUser(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (! $user) {
            return redirect()->to('/admin/patients/list')->with('error', 'User not found.');
        }

        if ($this->request->is('post')) {
            $rules = [
                'name' => 'required|min_length[3]|regex_match[/^[A-Za-zÑñ\s]+$/u]',
                'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
                'role' => 'required|in_list[admin,assistant_admin,client,secretary,doctor]',
                'password' => 'permit_empty|min_length[8]',
                'password_confirm' => 'permit_empty|matches[password]',
            ];

            $messages = [
                'email' => [
                    'is_unique' => 'This email is already taken.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $updateData = [
                'name'  => trim((string) $this->request->getPost('name')),
                'email' => strtolower(trim((string) $this->request->getPost('email'))),
                'role'  => (string) $this->request->getPost('role'),
            ];

            $newPassword = (string) $this->request->getPost('password');
            if ($newPassword !== '') {
                $updateData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            // Role-selection password handling removed for web

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $updated = $userModel->update($id, $updateData);

                // Audit log — record what changed (old vs new values)
                $changes = [];
                if (($user['name'] ?? '') !== ($updateData['name'] ?? '')) {
                    $changes[] = 'name: "' . ($user['name'] ?? '') . '" → "' . ($updateData['name'] ?? '') . '"';
                }
                if (($user['email'] ?? '') !== ($updateData['email'] ?? '')) {
                    $changes[] = 'email: "' . ($user['email'] ?? '') . '" → "' . ($updateData['email'] ?? '') . '"';
                }
                if (($user['role'] ?? '') !== ($updateData['role'] ?? '')) {
                    $changes[] = 'role: "' . ($user['role'] ?? '') . '" → "' . ($updateData['role'] ?? '') . '"';
                }
                if (isset($updateData['password_hash'])) {
                    $changes[] = 'password: changed';
                }

                $changeLog = ! empty($changes) ? implode(', ', $changes) : 'no_changes';

                (new LoginEventModel())->log(
                    LoginEventModel::EVENT_ACCOUNT_MODIFIED,
                    (int) $id,
                    $updateData['email'] ?? null,
                    'user_edited:' . $id . ' | by_admin:' . session('user_id') . ' | changes:' . $changeLog
                );

                if (! $db->transStatus()) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Unable to update user. Transaction rolled back.');
                }
                $db->transComplete();
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'editUser transaction failed: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'An unexpected error occurred. Changes rolled back.');
            }

            if (! $updated) {
                return redirect()->back()->withInput()->with('error', 'Unable to update user.');
            }

            if ((int) session()->get('user_id') === $id) {
                session()->set('user_name', trim((string) $this->request->getPost('name')));
                session()->set('user_role', (string) $this->request->getPost('role'));
            }

            return redirect()->to('/admin/patients/list')->with('success', 'User updated successfully.');
        }

        return view('admin/edit_user', [
            'user' => $user,
        ]);
    }

    public function deleteUser(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $currentAdminId = (int) session()->get('user_id');
        if ($id === $currentAdminId) {
            return redirect()->to('/admin/patients/list')->with('error', 'Current admin cannot delete their own account.');
        }

        $userModel = new UserModel();
        $user      = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to('/admin/patients/list')->with('error', 'User not found.');
        }

        if (! empty($user['deleted_at'])) {
            return redirect()->to('/admin/patients/list')->with('error', 'User is already deleted.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $userModel->delete($id);
            (new LoginEventModel())->log(LoginEventModel::EVENT_ACCOUNT_DELETED, (int) session('user_id'), null, 'user_id:' . $id);

            if (! $db->transStatus()) {
                $db->transRollback();
                return redirect()->to('/admin/patients/list')->with('error', 'Unable to delete user. Transaction rolled back.');
            }
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'deleteUser transaction failed: ' . $e->getMessage());
            return redirect()->to('/admin/patients/list')->with('error', 'An unexpected error occurred. Changes rolled back.');
        }

        return redirect()->to('/admin/patients/list')->with('success', 'User deleted successfully. You can restore this user anytime.');
    }

    public function restoreUser(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $userModel = new UserModel();
        $user      = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to('/admin/patients/list')->with('error', 'User not found.');
        }

        if (empty($user['deleted_at'])) {
            return redirect()->to('/admin/patients/list')->with('error', 'User is not deleted.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $userModel->withDeleted()->update($id, ['deleted_at' => null]);
            (new LoginEventModel())->log(LoginEventModel::EVENT_ACCOUNT_RESTORED, (int) session('user_id'), null, 'user_id:' . $id);

            if (! $db->transStatus()) {
                $db->transRollback();
                return redirect()->to('/admin/patients/list')->with('error', 'Unable to restore user. Transaction rolled back.');
            }
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'restoreUser transaction failed: ' . $e->getMessage());
            return redirect()->to('/admin/patients/list')->with('error', 'An unexpected error occurred. Changes rolled back.');
        }

        return redirect()->to('/admin/patients/list')->with('success', 'User restored successfully.');
    }

    private function hasPendingRegistration(): bool
    {
        $pending = session()->get('pending_registration');

        return is_array($pending) && ! empty($pending['verification_code_hash']) && ! empty($pending['email']);
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
            'expiresMinutes' => 10,
        ]));
        $emailService->setAltMessage(
            'Hello ' . $name . ', your verification code is ' . $verificationCode .
            '. This code expires in 10 minutes.'
        );

        return (bool) $emailService->send(false);
    }
}

