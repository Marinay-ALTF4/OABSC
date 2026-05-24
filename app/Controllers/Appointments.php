<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;
use Config\Database;

class Appointments extends BaseController
{
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

    public function new()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'client') {
            return redirect()->to('/dashboard')->with('error', 'Only clients can create appointments.');
        }

        $model = new AppointmentModel();
        $selectFields = ['appointment_date', 'appointment_time', 'status'];
        if ($this->hasDoctorNameColumn()) {
            array_unshift($selectFields, 'doctor_name');
        }

        $bookedSlots = $model
            ->select(implode(', ', $selectFields))
            ->where('appointment_date >=', date('Y-m-d'))
            ->whereIn('status', ['pending', 'approved'])
            ->findAll();

        if (! $this->hasDoctorNameColumn()) {
            $bookedSlots = array_map(static function (array $row): array {
                $row['doctor_name'] = '';
                return $row;
            }, $bookedSlots);
        }

        $userModel    = new UserModel();
        $doctorUsers  = $userModel->where('role', 'doctor')->where('deleted_at IS NULL')->findAll();
        $doctorOptions = array_map(fn($d) => 'Dr. ' . $d['name'], $doctorUsers);
        $scheduleModel = new \App\Models\DoctorScheduleModel();

        // Use raw query to bypass encryption hooks for display fields
        $db = \Config\Database::connect();
        $rawDoctors = $db->query(
              "SELECT u.id, COALESCE(up.name, u.username, '') AS name, dp.specialization, dp.experience, dp.degree, up.bio, up.phone, up.profile_photo
               FROM users u
               LEFT JOIN user_profiles up ON up.user_id = u.id
               LEFT JOIN doctor_profiles dp ON dp.user_id = u.id
               WHERE u.role = 'doctor' AND u.deleted_at IS NULL"
        )->getResultArray();

        $doctorProfiles = [];
        foreach ($rawDoctors as $d) {
            $schedules = $scheduleModel->getScheduleByDoctor((int) $d['id']);

            // Strip enc: prefix if decryption failed
            $spec = $d['specialization'] ?? '';
            $exp  = $d['experience'] ?? '';
            $deg  = $d['degree'] ?? '';
            $bio  = $d['bio'] ?? '';
            $phone = $d['phone'] ?? '';

            if (str_starts_with((string)$spec, 'enc:')) $spec = 'Specialist';
            if (str_starts_with((string)$exp,  'enc:')) $exp  = 'N/A';
            if (str_starts_with((string)$deg,  'enc:')) $deg  = 'MD';
            if (str_starts_with((string)$bio,  'enc:')) $bio  = 'Experienced medical professional.';
            if (str_starts_with((string)$phone,'enc:')) $phone = null;

            $doctorProfiles['Dr. ' . $d['name']] = [
                'avatar'    => ! empty($d['profile_photo']) ? base_url($d['profile_photo']) : null,
                'spec'      => $spec ?: 'Specialist',
                'exp'       => $exp  ?: 'N/A',
                'degree'    => $deg  ?: 'MD',
                'bio'       => $bio  ?: 'Experienced medical professional.',
                'phone'     => $phone,
                'schedules' => $schedules,
            ];
        }

        return view('client/new_appointment', [
            'bookedSlots'    => $bookedSlots,
            'doctorOptions'  => $doctorOptions,
            'doctorProfiles' => $doctorProfiles,
        ]);
    }

    public function create()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'client') {
            return redirect()->to('/dashboard')->with('error', 'Only clients can create appointments.');
        }

        $rules = [
            'doctor_name' => 'required|min_length[3]|max_length[120]',
            'appointment_date' => 'required|valid_date[Y-m-d]',
            'appointment_time' => 'required|regex_match[/^([01]\d|2[0-3]):([0-5]\d)$/]',
            'reason' => 'required|min_length[5]|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $appointmentDate = (string) $this->request->getPost('appointment_date');
        if ($appointmentDate < date('Y-m-d')) {
            return redirect()->back()->withInput()->with('errors', [
                'appointment_date' => 'Appointment date cannot be in the past.',
            ]);
        }

        // ── Validate that the selected date falls on a schedule day for the doctor ──
        $doctorNamePost = trim((string) $this->request->getPost('doctor_name'));
        if ($doctorNamePost) {
            $nameOnly   = preg_replace('/^Dr\.\s*/i', '', $doctorNamePost);
            $doctorUser = Database::connect()->query(
                'SELECT u.id
                 FROM users u
                 INNER JOIN user_profiles up ON up.user_id = u.id
                 WHERE u.role = ?
                   AND up.name = ?
                 LIMIT 1',
                ['doctor', $nameOnly]
            )->getRowArray();
            if ($doctorUser) {
                $scheduleModel = new \App\Models\DoctorScheduleModel();
                $schedules     = $scheduleModel->getScheduleByDoctor((int) $doctorUser['id']);
                if (! empty($schedules)) {
                    $allowedDays = array_map(fn($s) => strtolower($s['day']), $schedules);
                    $dayNames    = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                    $selectedDay = $dayNames[(int) date('w', strtotime($appointmentDate))];
                    if (! in_array($selectedDay, $allowedDays, true)) {
                        $allowedLabels = implode(', ', array_map('ucfirst', $allowedDays));
                        return redirect()->back()->withInput()->with('errors', [
                            'appointment_date' => "Dr. {$nameOnly} is not available on " . ucfirst($selectedDay) . ". Available days: {$allowedLabels}.",
                        ]);
                    }
                }
            }
        }

        $ownerColumn = $this->ownerColumn();
        if ($ownerColumn === null) {
            return redirect()->back()->withInput()->with('errors', [
                '_form' => 'Appointments table is missing owner column (client_id or user_id).',
            ]);
        }

        $ownerId = (int) session('user_id');

        // Ensure the session user exists to avoid foreign key constraint failures
        $userModelCheck = new UserModel();
        if (! $userModelCheck->find($ownerId)) {
            log_message('error', 'Appointment create failed: session user_id not found - ' . $ownerId);

            return redirect()->to('/login')->with('error', 'Your session is invalid. Please log in again.');
        }

        $insertData = [
            $ownerColumn => $ownerId,
            'appointment_date' => $appointmentDate,
            'appointment_time' => (string) $this->request->getPost('appointment_time'),
            'reason' => trim((string) $this->request->getPost('reason')),
            'status' => 'pending',
        ];

        if ($this->hasDoctorNameColumn()) {
            $doctorNamePost = trim((string) $this->request->getPost('doctor_name'));
            $insertData['doctor_name'] = $doctorNamePost;

            // Save doctor_id - strip "Dr. " prefix to find by name
            $nameOnly    = preg_replace('/^Dr\.\s*/i', '', $doctorNamePost);
                        $doctorUser  = Database::connect()->query(
                                'SELECT u.id
                                 FROM users u
                                 INNER JOIN user_profiles up ON up.user_id = u.id
                                 WHERE u.role = ?
                                     AND up.name = ?
                                 LIMIT 1',
                                ['doctor', $nameOnly]
                        )->getRowArray();
            if ($doctorUser) {
                $insertData['doctor_id'] = $doctorUser['id'];
            }
        }

        $model = new AppointmentModel();
        $db    = \Config\Database::connect();
        $db->transStart();

        try {
            $saved = $model->insert($insertData);

            // Send notification to doctor inside transaction
            if (! empty($insertData['doctor_id'])) {
                $notifModel  = new \App\Models\NotificationModel();
                $patientName = session('user_name') ?? 'A patient';
                $notifModel->send(
                    (int) $insertData['doctor_id'],
                    'New Appointment Booked',
                    "{$patientName} booked an appointment on {$appointmentDate} at " . substr((string) $insertData['appointment_time'], 0, 5) . '.',
                    'appointment'
                );
            }

            // Notify admins and secretaries about the new booking
            try {
                $userModel = new \App\Models\UserModel();
                $adminRecipients = $userModel->whereIn('role', ['admin', 'secretary', 'assistant_admin'])->where('deleted_at IS NULL')->findAll();
                if (! empty($adminRecipients)) {
                    $notifModel = $notifModel ?? new \App\Models\NotificationModel();
                    $patientName = session('user_name') ?? 'A patient';
                    $msgBody = "{$patientName} booked an appointment on {$appointmentDate} at " . substr((string) $insertData['appointment_time'], 0, 5) . '.';
                    foreach ($adminRecipients as $recip) {
                        $notifModel->send((int) $recip['id'], 'New Appointment Booked', $msgBody, 'appointment');
                    }
                }
            } catch (\Throwable $e) {
                // don't break the appointment creation if notification fails
                log_message('error', 'Failed to notify admins on appointment create: ' . $e->getMessage());
            }

            if (! $saved || ! $db->transStatus()) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('errors', [
                    '_form' => 'Unable to create appointment. Transaction rolled back.',
                ]);
            }
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Appointment create transaction failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('errors', [
                '_form' => 'An unexpected error occurred. Changes rolled back.',
            ]);
        }

        return redirect()->to('/appointments/my')->with('success', 'Appointment request submitted successfully.');
    }

    public function my()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'client') {
            return redirect()->to('/dashboard');
        }

        $ownerColumn = $this->ownerColumn();
        if ($ownerColumn === null) {
            return view('client/appointment', [
                'appointments'      => [],
                'cancel_attempts'   => 0,
                'cancel_remaining'  => 3,
                'cancel_reset_date' => null,
            ]);
        }

        $clientId  = (int) session('user_id');
        $userModel = new UserModel();
        $client    = $userModel->find($clientId);

        // Calculate remaining attempts (auto-reset if week has passed)
        $attempts = (int) ($client['cancel_attempts'] ?? 0);
        $resetAt  = $client['cancel_reset_at'] ?? null;

        if ($resetAt !== null) {
            $resetDate = new \DateTime($resetAt);
            $resetDate->modify('+7 days');
            if (new \DateTime() >= $resetDate) {
                $attempts = 0;
                $userModel->update($clientId, ['cancel_attempts' => 0, 'cancel_reset_at' => null]);
                $resetAt = null;
            }
        }

        $resetLabel = null;
        if ($resetAt !== null && $attempts >= 3) {
            $rd = new \DateTime($resetAt);
            $rd->modify('+7 days');
            $resetLabel = $rd->format('M j, Y \a\t g:i A');
        }

        $model        = new AppointmentModel();
        $appointments = $model
            ->where($ownerColumn, $clientId)
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('appointment_time', 'DESC')
            ->findAll();

        return view('client/appointment', [
            'appointments'      => $appointments,
            'cancel_attempts'   => $attempts,
            'cancel_remaining'  => max(0, 3 - $attempts),
            'cancel_reset_date' => $resetLabel,
        ]);
    }

    public function cancel(int $id = 0)
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'client') {
            return redirect()->to('/dashboard')->with('error', 'Only clients can cancel their appointments.');
        }

        $ownerColumn = $this->ownerColumn();
        if ($ownerColumn === null) {
            return redirect()->back()->with('error', 'Unable to cancel appointment.');
        }

        $model       = new AppointmentModel();
        $appointment = $model->find($id);

        if (! $appointment) {
            return redirect()->to('/appointments/my')->with('error', 'Appointment not found.');
        }

        $clientId           = (int) session('user_id');
        $appointmentOwnerId = (int) ($appointment[$ownerColumn] ?? 0);

        if ($appointmentOwnerId !== $clientId) {
            return redirect()->to('/appointments/my')->with('error', 'You cannot cancel this appointment.');
        }

        // ── Cancel attempt limit: 3 per week ──
        $userModel = new UserModel();
        $client    = $userModel->find($clientId);

        $attempts  = (int) ($client['cancel_attempts'] ?? 0);
        $resetAt   = $client['cancel_reset_at'] ?? null;
        $now       = new \DateTime();

        // Check if a week has passed since the reset timestamp — if so, reset counter
        if ($resetAt !== null) {
            $resetDate = new \DateTime($resetAt);
            $resetDate->modify('+7 days');
            if ($now >= $resetDate) {
                $attempts = 0;
                $userModel->update($clientId, [
                    'cancel_attempts' => 0,
                    'cancel_reset_at' => null,
                ]);
            }
        }

        // Block if already used 3 attempts
        if ($attempts >= 3) {
            $resetDate  = new \DateTime($client['cancel_reset_at']);
            $resetDate->modify('+7 days');
            $resetLabel = $resetDate->format('M j, Y \a\t g:i A');
            return redirect()->to('/appointments/my')->with(
                'error',
                "You have used all 3 cancellation attempts for this week. Your attempts will reset on {$resetLabel}."
            );
        }

        // Proceed with cancellation
        $updated = $model->update($id, [
            'status'      => 'cancelled',
            'archived_at' => date('Y-m-d H:i:s'),
        ]);

        if (! $updated) {
            return redirect()->back()->with('error', 'Unable to cancel appointment. Please try again.');
        }

        // Increment attempt counter; set reset timestamp on first use
        $newAttempts = $attempts + 1;
        $updateData  = ['cancel_attempts' => $newAttempts];
        if ($attempts === 0) {
            $updateData['cancel_reset_at'] = date('Y-m-d H:i:s');
        }
        $userModel->update($clientId, $updateData);

        $remaining       = 3 - $newAttempts;
        $clientName      = session('user_name') ?? 'A patient';
        $appointmentDate = $appointment['appointment_date'] ?? 'scheduled date';
        $appointmentTime = substr((string) ($appointment['appointment_time'] ?? ''), 0, 5);

        // Notify doctor
        if (! empty($appointment['doctor_id'])) {
            $notifModel = new \App\Models\NotificationModel();
            $notifModel->send(
                (int) $appointment['doctor_id'],
                'Appointment Cancelled',
                "{$clientName} has cancelled their appointment scheduled for {$appointmentDate} at {$appointmentTime}.",
                'appointment'
            );
        }

        // Notify admins and secretaries
        try {
            $notifModel      = $notifModel ?? new \App\Models\NotificationModel();
            $adminRecipients = (new UserModel())
                ->whereIn('role', ['admin', 'secretary', 'assistant_admin'])
                ->where('deleted_at IS NULL')
                ->findAll();
            $msgBody = "{$clientName} cancelled their appointment scheduled for {$appointmentDate} at {$appointmentTime}.";
            foreach ($adminRecipients as $recip) {
                $notifModel->send((int) $recip['id'], 'Appointment Cancelled', $msgBody, 'appointment');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Failed to notify admins on appointment cancel: ' . $e->getMessage());
        }

        $msg = $remaining > 0
            ? "Appointment cancelled. You have {$remaining} cancellation attempt(s) remaining this week."
            : "Appointment cancelled. You have used all 3 cancellation attempts for this week.";

        return redirect()->to('/appointments/my')->with('success', $msg);
    }
}
