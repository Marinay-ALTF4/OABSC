<?php

namespace App\Controllers;

use App\Models\UserModel;

class Admin extends BaseController
{
    private function ensureAdminAccess()
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
                'name'          => 'required|min_length[3]',
                'email'         => 'required|valid_email|is_unique[users.email]',
                'role'          => 'required|in_list[assistant_admin,assistant_secretary]',
                'role_password' => 'required|min_length[8]',
                'role_password_confirm' => 'required|matches[role_password]',
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
                'role_password' => password_hash((string) $this->request->getPost('role_password'), PASSWORD_DEFAULT),
            ]);

            if (! $created) {
                return redirect()->back()->withInput()->with('error', 'Unable to add role.');
            }

            return redirect()->to('/admin/patients/list')->with('success', 'Role added successfully.');
        }

        return view('admin/add_role');
    }

    public function clinicSettings()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $settingsModel = new \App\Models\ClinicSettingsModel();

        if ($this->request->is('post')) {
            $newCode = trim((string) $this->request->getPost('clinic_access_code'));
            if ($newCode !== '') {
                $settingsModel->setValue('clinic_access_code', password_hash($newCode, PASSWORD_DEFAULT));
                return redirect()->back()->with('success', 'Clinic access code updated.');
            }
            return redirect()->back()->with('error', 'Please enter a new access code.');
        }

        return view('admin/clinic_settings');
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

    public function doctorSpecialization()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $doctorModel = new \App\Models\DoctorModel();
        $doctors = $doctorModel->orderBy('specialization', 'ASC')->findAll();

        // Group by specialization
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

        $doctorModel = new \App\Models\DoctorModel();
        $doctors = $doctorModel->orderBy('name', 'ASC')->findAll();

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
            $created = $userModel->insert([
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

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
                'name' => trim((string) $this->request->getPost('name')),
                'email' => strtolower(trim((string) $this->request->getPost('email'))),
                'role' => (string) $this->request->getPost('role'),
            ];

            $newPassword = (string) $this->request->getPost('password');
            if ($newPassword !== '') {
                $updateData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $newRolePassword = (string) $this->request->getPost('role_password');
            if ($newRolePassword !== '') {
                $updateData['role_password'] = password_hash($newRolePassword, PASSWORD_DEFAULT);
            }

            $updated = $userModel->update($id, $updateData);

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
        $user = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to('/admin/patients/list')->with('error', 'User not found.');
        }

        if (! empty($user['deleted_at'])) {
            return redirect()->to('/admin/patients/list')->with('error', 'User is already deleted.');
        }

        if (! $userModel->delete($id)) {
            return redirect()->to('/admin/patients/list')->with('error', 'Unable to delete user.');
        }

        return redirect()->to('/admin/patients/list')->with('success', 'User deleted successfully. You can restore this user anytime.');
    }

    public function restoreUser(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) return $access;

        $userModel = new UserModel();
        $user = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to('/admin/patients/list')->with('error', 'User not found.');
        }

        if (empty($user['deleted_at'])) {
            return redirect()->to('/admin/patients/list')->with('error', 'User is not deleted.');
        }

        if (! $userModel->withDeleted()->update($id, ['deleted_at' => null])) {
            return redirect()->to('/admin/patients/list')->with('error', 'Unable to restore user.');
        }

        return redirect()->to('/admin/patients/list')->with('success', 'User restored successfully.');
    }
}

