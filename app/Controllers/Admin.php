<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoginEventModel;

class Admin extends BaseController
{
    public function announcements()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $db = \Config\Database::connect();

        // Only show admin-posted announcements (stored with a special marker)
        $announcements = $db->query(
            "SELECT * FROM notifications 
             WHERE title LIKE '[ANN]%'
             ORDER BY created_at DESC 
             LIMIT 50"
        )->getResultArray();

        // Strip the [ANN] prefix for display
        foreach ($announcements as &$a) {
            $a['title'] = ltrim(str_replace('[ANN]', '', $a['title']));
        }

        return view('admin/announcements', ['announcements' => $announcements]);
    }

    public function addAnnouncement()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $title = trim((string) $this->request->getPost('title'));
        $body  = trim((string) $this->request->getPost('body'));
        $type  = (string) $this->request->getPost('type') ?: 'info';

        if (! $title || ! $body) {
            return redirect()->back()->with('error', 'Title and message are required.');
        }

        // Send to all users with [ANN] prefix to distinguish from regular notifications
        $notifModel = new \App\Models\NotificationModel();
        $userModel  = new UserModel();
        $users      = $userModel->where('deleted_at IS NULL')->findAll();

        foreach ($users as $user) {
            $notifModel->send((int) $user['id'], '[ANN]' . $title, $body, $type);
        }

        return redirect()->to('/admin/announcements')->with('success', 'Announcement posted to all users.');
    }

    public function deleteAnnouncement(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $db = \Config\Database::connect();
        $db->query('DELETE FROM notifications WHERE id = ?', [$id]);

        return redirect()->to('/admin/announcements')->with('success', 'Announcement deleted.');
    }

    public function appointments()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $db = \Config\Database::connect();

        $all = $db->query(
            'SELECT a.*, COALESCE(u.name, "—") as patient_name
             FROM appointments a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.appointment_date DESC'
        )->getResultArray();

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
            $updateData = ['status' => $status];

            // Auto-archive when admin sets status to cancelled
            if ($status === 'cancelled') {
                $updateData['archived_at'] = date('Y-m-d H:i:s');
            }

            $model->update($id, $updateData);

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
        $userModel2 = new UserModel();

        $pending  = $arModel->where('status', 'pending')->orderBy('id', 'DESC')->findAll();
        $all      = $arModel->orderBy('id', 'DESC')->limit(50)->findAll();

        // Attach user info
        foreach ($all as &$req) {
            $u = $userModel2->find($req['user_id']);
            $req['user_name']  = $u['name']  ?? '—';
            $req['user_email'] = $u['email'] ?? '—';
        }

        return view('admin/access_requests', [
            'pending' => $pending,
            'all'     => $all,
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

        return view('admin/patients');
    }

    public function clientList()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) {
            return $access;
        }

        $userModel = new UserModel();
        $users = $userModel->withDeleted()->where('role', 'client')->orderBy('id', 'DESC')->findAll();

        return view('admin/client_list', [
            'users' => $users,
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
            $appointments = $appointmentModel
                ->where('client_id', $id)
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

        $userModel = new UserModel();
        $users = $userModel->withDeleted()->orderBy('id', 'DESC')->findAll();

        return view('admin/patients_list', [
            'users' => $users,
        ]);
    }

    public function addUser()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

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

            $userModel = new UserModel();
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $created = $userModel->insert([
                    'name'          => $name,
                    'email'         => $email,
                    'role'          => $role,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);

                (new LoginEventModel())->log(LoginEventModel::EVENT_ACCOUNT_MODIFIED, (int) session('user_id'), null, 'user_added:' . $email);

                if (! $db->transStatus()) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Unable to add user. Transaction rolled back.');
                }
                $db->transComplete();
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'addUser transaction failed: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'An unexpected error occurred. Changes rolled back.');
            }

            if (! $created) {
                return redirect()->back()->withInput()->with('error', 'Unable to add user.');
            }

            return redirect()->to('/admin/patients/list')->with('success', 'New user added successfully.');
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

                (new LoginEventModel())->log(
                    LoginEventModel::EVENT_ACCOUNT_MODIFIED,
                    (int) $id,
                    $updateData['email'] ?? null,
                    'user_edited:' . $id
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
}

