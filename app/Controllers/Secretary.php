<?php

namespace App\Controllers;

use App\Libraries\UserDataCrypt;
use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Models\DoctorScheduleModel;

class Secretary extends BaseController
{
    private function checkAccess()
    {
        if (! session()->get('isLoggedIn') || session('user_role') !== 'secretary') {
            return redirect()->to('/dashboard');
        }
        return null;
    }

    public function appointments()
    {
        if ($r = $this->checkAccess()) return $r;

        $model = new AppointmentModel();
        $appointments = $model->orderBy('appointment_date', 'DESC')->findAll();

        return view('secretary/appointments', ['appointments' => $appointments]);
    }

    public function queue()
    {
        if ($r = $this->checkAccess()) return $r;

        $model = new AppointmentModel();
        $today = date('Y-m-d');
        $queue = $model->where('appointment_date', $today)
                       ->orderBy('appointment_time', 'ASC')
                       ->findAll();

        return view('secretary/queue', ['queue' => $queue]);
    }

    public function records()
    {
        if ($r = $this->checkAccess()) return $r;

        $search = $this->request->getGet('search');
        $db = \Config\Database::connect();

        $builder = $db->table('users u')
            ->select('u.id, COALESCE(up.name, u.username, "") AS name, u.email, up.phone, u.created_at')
            ->join('user_profiles up', 'up.user_id = u.id', 'left')
            ->where('u.role', 'client')
            ->where('u.deleted_at IS NULL', null, false);

        if ($search) {
            $builder->groupStart()
                ->like('up.name', $search)
                ->orLike('u.email', $search)
                ->groupEnd();
        }

        $patients = $builder->orderBy('u.created_at', 'DESC')->get()->getResultArray();

        return view('secretary/records', ['patients' => $patients, 'search' => $search]);
    }

    public function register()
    {
        if ($r = $this->checkAccess()) return $r;

        if ($this->request->is('post')) {
            $rules = [
                'name'     => 'required|min_length[3]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[8]',
            ];

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $model   = new UserModel();
                $created = $model->insert([
                    'name'          => trim($this->request->getPost('name')),
                    'email'         => strtolower(trim($this->request->getPost('email'))),
                    'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'          => 'client',
                    'phone'         => trim($this->request->getPost('phone') ?? ''),
                ]);

                if (! $created || ! $db->transStatus()) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('errors', ['_form' => 'Unable to register patient. Transaction rolled back.']);
                }

                applyDenyOverridesForNewUser((int) $created, 'client');

                $db->transComplete();
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Secretary register transaction failed: ' . $e->getMessage());
                return redirect()->back()->withInput()->with('errors', ['_form' => 'An unexpected error occurred. Changes rolled back.']);
            }

            return redirect()->to('/secretary/register')->with('success', 'Patient registered successfully.');
        }

        return view('secretary/register');
    }

    public function schedules()
    {
        if ($r = $this->checkAccess()) return $r;

        $db = \Config\Database::connect();
        $doctors = $db->query(
            'SELECT u.id, COALESCE(up.name, u.username, "") AS name, u.email, up.phone, up.profile_photo, dp.specialization, dp.experience, dp.degree
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             LEFT JOIN doctor_profiles dp ON dp.user_id = u.id
             WHERE u.role = ?
               AND u.deleted_at IS NULL
             ORDER BY COALESCE(up.name, u.username, "") ASC',
            ['doctor']
        )->getResultArray();

        // Load schedules for all doctors in one query and index by doctor_id
        $scheduleModel = new DoctorScheduleModel();
        $allSchedules = $scheduleModel->findAll();
        $schedByDoctor = [];
        foreach ($allSchedules as $row) {
            $schedByDoctor[(int)$row['doctor_id']][ucfirst(strtolower($row['day']))] = $row;
        }

        // This endpoint uses a raw SQL query, so model-level afterFind decryption does not run.
        // Decrypt any encrypted profile fields before rendering and attach schedules.
        try {
            $crypt = new UserDataCrypt();
            foreach ($doctors as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $decrypted = $crypt->decryptFields($row, ['phone', 'specialization', 'experience', 'degree']);
                // Attach schedules for this doctor (may be empty)
                $decrypted['schedules'] = $schedByDoctor[(int) ($row['id'] ?? 0)] ?? [];
                $doctors[$i] = $decrypted;
            }
        } catch (\Throwable) {
            // If encryption service is not available, keep raw values and attach schedules.
            foreach ($doctors as $i => $row) {
                if (! is_array($row)) continue;
                $row['schedules'] = $schedByDoctor[(int) ($row['id'] ?? 0)] ?? [];
                $doctors[$i] = $row;
            }
        }

        return view('secretary/schedules', ['doctors' => $doctors]);
    }

    public function approvals()
    {
        if ($r = $this->checkAccess()) return $r;

        $model = new AppointmentModel();
        $pending = $model->where('status', 'pending')->orderBy('appointment_date', 'ASC')->findAll();

        return view('secretary/approvals', ['pending' => $pending]);
    }

    public function updateStatus()
    {
        if ($r = $this->checkAccess()) return $r;

        $id     = (int) $this->request->getPost('id');
        $status = (string) $this->request->getPost('status');

        if (! in_array($status, ['confirmed', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $model = new AppointmentModel();
            $model->update($id, ['status' => $status]);

            if (! $db->transStatus()) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Unable to update status. Transaction rolled back.');
            }
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'updateStatus transaction failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Changes rolled back.');
        }

        return redirect()->back()->with('success', 'Appointment status updated.');
    }
}
