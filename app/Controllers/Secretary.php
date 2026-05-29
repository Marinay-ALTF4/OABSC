<?php

namespace App\Controllers;

use App\Libraries\UserDataCrypt;
use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Models\DoctorScheduleModel;

class Secretary extends BaseController
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
        $approverRole  = 'Secretary';
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

    public function patientHistory(int $id = 0)
    {
        if ($r = $this->checkAccess()) return $r;

        $userModel = new UserModel();
        $appointmentModel = new AppointmentModel();

        if ($id > 0) {
            $patient = $userModel->where('role', 'client')->find($id);
            if (! $patient) {
                return redirect()->to('/secretary/records')->with('error', 'Patient not found.');
            }

            $db = \Config\Database::connect();
            $ownerCol = $db->fieldExists('client_id', 'appointments') ? 'client_id' : 'user_id';

            $appointments = $appointmentModel
                ->where($ownerCol, $id)
                ->orderBy('appointment_date', 'DESC')
                ->findAll();

            return view('secretary/patient_history', [
                'patient'      => $patient,
                'appointments' => $appointments,
            ]);
        }

        $patients = $userModel->where('role', 'client')->orderBy('name', 'ASC')->findAll();

        return view('secretary/patient_history', [
            'patient'      => null,
            'patients'     => $patients,
            'appointments' => [],
        ]);
    }

    public function register()
    {
        if ($r = $this->checkAccess()) return $r;

        if ($this->hasPendingRegistration()) {
            return redirect()->to('/register/verify');
        }

        if ($this->request->is('post')) {
            $rules = [
                'name'     => 'required|min_length[3]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[8]',
            ];

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $name = trim((string) $this->request->getPost('name'));
            $email = strtolower(trim((string) $this->request->getPost('email')));
            $phone = trim((string) $this->request->getPost('phone') ?? '');
            $password = (string) $this->request->getPost('password');
            $verificationCode = (string) random_int(100000, 999999);

            $pendingRegistration = [
                'name'                   => $name,
                'email'                  => $email,
                'phone'                  => $phone,
                'password_hash'          => password_hash($password, PASSWORD_DEFAULT),
                'role'                   => 'client',
                'verification_code_hash' => password_hash($verificationCode, PASSWORD_DEFAULT),
                'verification_expires_at'=> time() + 600,
                'verification_attempts'  => 0,
                'redirect_to'            => '/secretary/register',
                'reset_url'              => '/secretary/register',
                'created_by'             => (int) session('user_id'),
                'audit_note'             => 'patient_registered:' . $email . ' | by_secretary:' . (int) session('user_id'),
            ];

            if (! $this->sendVerificationCode($email, $name, $verificationCode)) {
                return redirect()->back()->withInput()->with('errors', ['_form' => 'Unable to send verification code. Please check email settings and try again.']);
            }

            session()->set('pending_registration', $pendingRegistration);

            return redirect()->to('/register/verify')->with('success', 'We sent a 6-digit verification code to the provided email. The patient will be created after verification.');
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
            $appointment = $model->find($id);
            $model->update($id, ['status' => $status]);

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
            log_message('error', 'updateStatus transaction failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Changes rolled back.');
        }

        return redirect()->back()->with('success', 'Appointment status updated.');
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
