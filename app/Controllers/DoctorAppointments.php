<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;

class DoctorAppointments extends BaseController
{
    private function ensureDoctor()
    {
        if (! session()->get('isLoggedIn') || session('user_role') !== 'doctor') {
            return redirect()->to('/dashboard');
        }
        return null;
    }

    private function currentDoctorName(): string
    {
        $baseName = preg_replace('/^Dr\.\s*/i', '', trim((string) session('user_name')));

        return $baseName !== '' ? 'Dr. ' . $baseName : 'Dr. Doctor';
    }

    private function loadDoctorAppointments(string $filter = 'all'): array
    {
        $doctorName = $this->currentDoctorName();
        $doctorId   = (int) session('user_id');
        $model      = new AppointmentModel();
        $userModel  = new UserModel();
        $today      = date('Y-m-d');

        $allAppts = $model->findAll();

        $appointments = array_filter($allAppts, function ($appt) use ($doctorName, $doctorId) {
            return (($appt['doctor_name'] ?? '') === $doctorName) ||
                   ((int) ($appt['doctor_id'] ?? 0) === $doctorId && $doctorId > 0);
        });
        $appointments = array_values($appointments);

        if ($filter === 'today') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] === $today);
        } elseif ($filter === 'upcoming') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] > $today && in_array($a['status'], ['pending', 'approved'], true));
        } elseif ($filter === 'past') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] < $today);
        } elseif ($filter === 'approved') {
            $appointments = array_filter($appointments, fn($a) => in_array($a['status'] ?? '', ['approved', 'confirmed'], true));
        } elseif ($filter === 'cancelled') {
            $appointments = array_filter($appointments, fn($a) => ($a['status'] ?? '') === 'cancelled');
        } elseif ($filter === 'all') {
            $appointments = array_filter($appointments, fn($a) => ! in_array($a['status'] ?? '', ['approved', 'confirmed', 'cancelled'], true));
        }

        $appointments = array_values($appointments);

        $sortDesc = $filter === 'cancelled';
        usort($appointments, function ($a, $b) use ($sortDesc) {
            $cmp = strcmp($a['appointment_date'] . $a['appointment_time'], $b['appointment_date'] . $b['appointment_time']);

            return $sortDesc ? -$cmp : $cmp;
        });

        foreach ($appointments as &$appt) {
            $clientId = $appt['client_id'] ?? $appt['user_id'] ?? null;
            if ($clientId) {
                $patient = $userModel->find((int) $clientId);
                $appt['patient_name']  = $patient['name'] ?? 'Unknown';
                $appt['patient_email'] = $patient['email'] ?? '';
                $appt['patient_phone'] = $patient['phone'] ?? '';
            } else {
                $appt['patient_name']  = 'Unknown';
                $appt['patient_email'] = '';
                $appt['patient_phone'] = '';
            }
        }
        unset($appt);

        return $appointments;
    }

    private function notesStoragePath(int $doctorId): string
    {
        return WRITEPATH . 'doctor_notes_' . $doctorId . '.json';
    }

    private function loadDoctorNotes(int $doctorId): array
    {
        $path = $this->notesStoragePath($doctorId);
        if (! is_file($path)) {
            return [];
        }

        $json = @file_get_contents($path);
        if ($json === false || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private function saveDoctorNotes(int $doctorId, array $notes): void
    {
        $path = $this->notesStoragePath($doctorId);
        @file_put_contents($path, json_encode(array_values($notes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function prescriptionsStoragePath(int $doctorId): string
    {
        return WRITEPATH . 'doctor_prescriptions_' . $doctorId . '.json';
    }

    private function loadDoctorPrescriptions(int $doctorId): array
    {
        $path = $this->prescriptionsStoragePath($doctorId);
        if (! is_file($path)) {
            return [];
        }

        $json = @file_get_contents($path);
        if ($json === false || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    private function saveDoctorPrescriptions(int $doctorId, array $prescriptions): void
    {
        $path = $this->prescriptionsStoragePath($doctorId);
        @file_put_contents($path, json_encode(array_values($prescriptions), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function index()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $filter = $this->request->getGet('filter') ?? 'upcoming';
        $allowedFilters = ['upcoming', 'today', 'past', 'approved', 'cancelled', 'all'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'upcoming';
        }
        $appointments = $this->loadDoctorAppointments($filter);

        return view('doctor/appointments', [
            'appointments' => $appointments,
            'filter'       => $filter,
        ]);
    }

    public function queue()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $today = date('Y-m-d');
        $appointments = $this->loadDoctorAppointments('all');

        $todayQueue = array_values(array_filter($appointments, function (array $appt) use ($today): bool {
            return $appt['appointment_date'] === $today && in_array($appt['status'], ['pending', 'approved']);
        }));

        $upcomingQueue = array_values(array_filter($appointments, function (array $appt) use ($today): bool {
            return $appt['appointment_date'] > $today && in_array($appt['status'], ['pending', 'approved']);
        }));

        return view('doctor/queue', [
            'todayQueue'    => $todayQueue,
            'upcomingQueue' => $upcomingQueue,
            'today'         => $today,
        ]);
    }

    public function records(int $patientId = 0)
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $doctorName = $this->currentDoctorName();
        $doctorId   = (int) session('user_id');
        $model      = new AppointmentModel();
        $userModel  = new UserModel();

        $allAppts = $model->findAll();
        $doctorAppts = array_values(array_filter($allAppts, function (array $appt) use ($doctorName, $doctorId): bool {
            return (($appt['doctor_name'] ?? '') === $doctorName) || ((int) ($appt['doctor_id'] ?? 0) === $doctorId && $doctorId > 0);
        }));

        if ($patientId > 0) {
            $patient = $userModel->withDeleted()->find($patientId);

            if (! $patient || (string) ($patient['role'] ?? '') !== 'client') {
                return redirect()->to('/doctor/records')->with('error', 'Patient not found.');
            }

            $appointments = array_values(array_filter($doctorAppts, function (array $appt) use ($patientId): bool {
                $clientId = (int) ($appt['client_id'] ?? $appt['user_id'] ?? 0);
                return $clientId === $patientId;
            }));

            usort($appointments, fn($a, $b) => strcmp(($b['appointment_date'] ?? '') . ($b['appointment_time'] ?? ''), ($a['appointment_date'] ?? '') . ($a['appointment_time'] ?? '')));

            return view('doctor/records', [
                'patient'      => $patient,
                'appointments' => $appointments,
                'patients'     => [],
                'search'       => '',
            ]);
        }

        $search = trim((string) $this->request->getGet('search'));
        $patients = [];

        foreach ($doctorAppts as $appt) {
            $clientId = (int) ($appt['client_id'] ?? $appt['user_id'] ?? 0);
            if ($clientId <= 0) {
                continue;
            }

            $patient = $userModel->withDeleted()->find($clientId);
            if (! $patient || (string) ($patient['role'] ?? '') !== 'client') {
                continue;
            }

            if ($search !== '') {
                $needle = mb_strtolower($search);
                $haystack = mb_strtolower(($patient['name'] ?? '') . ' ' . ($patient['email'] ?? '') . ' ' . ($patient['phone'] ?? ''));
                if (mb_strpos($haystack, $needle) === false) {
                    continue;
                }
            }

            if (! isset($patients[$clientId])) {
                $patients[$clientId] = [
                    'id'             => $clientId,
                    'name'           => $patient['name'] ?? 'Unknown',
                    'email'          => $patient['email'] ?? '',
                    'phone'          => $patient['phone'] ?? '',
                    'appointment_count' => 0,
                    'latest_date'    => '',
                    'latest_time'    => '',
                    'latest_status'  => '',
                ];
            }

            $patients[$clientId]['appointment_count']++;

            $currentStamp = ($appt['appointment_date'] ?? '') . ' ' . ($appt['appointment_time'] ?? '');
            $latestStamp  = $patients[$clientId]['latest_date'] . ' ' . $patients[$clientId]['latest_time'];
            if ($currentStamp >= $latestStamp) {
                $patients[$clientId]['latest_date']   = (string) ($appt['appointment_date'] ?? '');
                $patients[$clientId]['latest_time']   = (string) ($appt['appointment_time'] ?? '');
                $patients[$clientId]['latest_status'] = (string) ($appt['status'] ?? '');
            }
        }

        $patients = array_values($patients);
        usort($patients, fn($a, $b) => strcmp(($b['latest_date'] ?? '') . ($b['latest_time'] ?? ''), ($a['latest_date'] ?? '') . ($a['latest_time'] ?? '')));

        $today = date('Y-m-d');
        $stats = [
            'patients'    => count($patients),
            'appointments'=> count($doctorAppts),
            'today'       => count(array_filter($doctorAppts, fn($appt) => ($appt['appointment_date'] ?? '') === $today)),
        ];

        return view('doctor/records', [
            'patient'      => null,
            'appointments' => [],
            'patients'     => $patients,
            'search'       => $search,
            'stats'        => $stats,
        ]);
    }

    public function notes()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $doctorId = (int) session('user_id');
        $notes = $this->loadDoctorNotes($doctorId);

        $userModel = new UserModel();
        $patients = $userModel->where('role', 'client')->findAll();
        usort($patients, function (array $a, array $b): int {
            $nameA = (string) ($a['name'] ?? $a['username'] ?? '');
            $nameB = (string) ($b['name'] ?? $b['username'] ?? '');

            return strcmp($nameA, $nameB);
        });

        usort($notes, fn($a, $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return view('doctor/notes', [
            'notes' => $notes,
            'patients' => $patients,
        ]);
    }

    public function saveNote()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $doctorId = (int) session('user_id');
        $action = (string) $this->request->getPost('action');
        $notes = $this->loadDoctorNotes($doctorId);

        if ($action === 'delete') {
            $noteId = (string) $this->request->getPost('note_id');
            $notes = array_values(array_filter($notes, fn($n) => (string) ($n['id'] ?? '') !== $noteId));
            $this->saveDoctorNotes($doctorId, $notes);
            return redirect()->to('/doctor/notes')->with('success', 'Note deleted.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $body = trim((string) $this->request->getPost('body'));
        $patientId = (int) $this->request->getPost('patient_id');
        $patientName = trim((string) $this->request->getPost('patient_name'));

        if ($title === '' || $body === '') {
            return redirect()->back()->with('error', 'Title and note content are required.');
        }

        $notes[] = [
            'id' => uniqid('note_', true),
            'title' => mb_substr($title, 0, 120),
            'body' => mb_substr($body, 0, 3000),
            'patient_id' => $patientId > 0 ? $patientId : null,
            'patient_name' => $patientName !== '' ? mb_substr($patientName, 0, 120) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'author' => $this->currentDoctorName(),
        ];

        $this->saveDoctorNotes($doctorId, $notes);

        // Notify the specific patient
        if ($patientId > 0) {
            $notifModel = new \App\Models\NotificationModel();
            $doctorName = $this->currentDoctorName();
            $notifModel->send(
                $patientId,
                'New Consultation Note',
                "{$doctorName} has added a consultation note for you: \"{$title}\".",
                'info'
            );
        }

        return redirect()->to('/doctor/notes')->with('success', 'Note saved successfully.');
    }

    public function prescriptions()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $doctorId = (int) session('user_id');
        $prescriptions = $this->loadDoctorPrescriptions($doctorId);

        $userModel = new UserModel();
        $patients = $userModel->where('role', 'client')->findAll();
        usort($patients, function (array $a, array $b): int {
            $nameA = (string) ($a['name'] ?? $a['username'] ?? '');
            $nameB = (string) ($b['name'] ?? $b['username'] ?? '');

            return strcmp($nameA, $nameB);
        });

        usort($prescriptions, fn($a, $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return view('doctor/prescriptions', [
            'prescriptions' => $prescriptions,
            'patients' => $patients,
        ]);
    }

    public function savePrescription()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $doctorId = (int) session('user_id');
        $action = (string) $this->request->getPost('action');
        $prescriptions = $this->loadDoctorPrescriptions($doctorId);

        if ($action === 'delete') {
            $prescriptionId = (string) $this->request->getPost('prescription_id');
            $prescriptions = array_values(array_filter($prescriptions, fn($p) => (string) ($p['id'] ?? '') !== $prescriptionId));
            $this->saveDoctorPrescriptions($doctorId, $prescriptions);
            return redirect()->to('/doctor/prescriptions')->with('success', 'Prescription deleted.');
        }

        $patientId = (int) $this->request->getPost('patient_id');
        $patientName = trim((string) $this->request->getPost('patient_name'));
        $medicine = trim((string) $this->request->getPost('medicine'));
        $dosage = trim((string) $this->request->getPost('dosage'));
        $frequency = trim((string) $this->request->getPost('frequency'));
        $duration = trim((string) $this->request->getPost('duration'));
        $instructions = trim((string) $this->request->getPost('instructions'));

        if ($patientName === '' || $medicine === '' || $dosage === '' || $frequency === '' || $duration === '') {
            return redirect()->back()->with('error', 'Patient, medicine, dosage, frequency, and duration are required.');
        }

        $prescriptions[] = [
            'id' => uniqid('rx_', true),
            'patient_id' => $patientId > 0 ? $patientId : null,
            'patient_name' => mb_substr($patientName, 0, 120),
            'medicine' => mb_substr($medicine, 0, 200),
            'dosage' => mb_substr($dosage, 0, 120),
            'frequency' => mb_substr($frequency, 0, 120),
            'duration' => mb_substr($duration, 0, 120),
            'instructions' => mb_substr($instructions, 0, 2000),
            'created_at' => date('Y-m-d H:i:s'),
            'doctor_name' => $this->currentDoctorName(),
        ];

        $this->saveDoctorPrescriptions($doctorId, $prescriptions);

        // Notify the specific patient
        if ($patientId > 0) {
            $notifModel = new \App\Models\NotificationModel();
            $doctorName = $this->currentDoctorName();
            $msg = "{$doctorName} has added a prescription for you: \"{$medicine}\"\n"
                 . "• Dosage: {$dosage}\n"
                 . "• Frequency: {$frequency}\n"
                 . "• Duration: {$duration}";
            if ($instructions !== '') {
                $msg .= "\n• Instructions: {$instructions}";
            }
            $notifModel->send(
                $patientId,
                'New Prescription Added',
                $msg,
                'info'
            );
        }

        return redirect()->to('/doctor/prescriptions')->with('success', 'Prescription saved successfully.');
    }

    public function updateStatus()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $id     = (int) $this->request->getPost('id');
        $status = (string) $this->request->getPost('status');

        if (! in_array($status, ['approved', 'completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $model = new AppointmentModel();
        $appt  = $model->find($id);

        if (! $appt) {
            return redirect()->back()->with('error', 'Appointment not found.');
        }

        // Make sure this appointment belongs to this doctor.
        // Allow matches by doctor_id or by a normalized doctor_name.
        $doctorId   = (int) session('user_id');
        $sessionName = (string) session('user_name');

        $apptDoctorName = (string) ($appt['doctor_name'] ?? '');
        $apptDoctorId   = (int) ($appt['doctor_id'] ?? 0);

        $normalizeName = function (string $name): string {
            $n = preg_replace('/^Dr\.\s*/i', '', $name);
            return mb_strtolower(trim($n));
        };

        $authorized = false;

        // Exact id match is the strongest signal
        if ($apptDoctorId > 0 && $apptDoctorId === $doctorId) {
            $authorized = true;
        }

        // Normalize and compare names (case-insensitive, strip leading "Dr.")
        if (! $authorized && $apptDoctorName !== '' && $sessionName !== '') {
            if ($normalizeName($apptDoctorName) === $normalizeName($sessionName)) {
                $authorized = true;
            }
        }

        // If still not authorized but appointment has a doctor_name and no doctor_id,
        // attempt to resolve the user id from profiles/users and persist it.
        if (! $authorized && $apptDoctorId === 0 && $apptDoctorName !== '') {
            $nameOnly = preg_replace('/^Dr\.\s*/i', '', $apptDoctorName);
            $db = \Config\Database::connect();
            $found = $db->query(
                'SELECT u.id FROM users u INNER JOIN user_profiles up ON up.user_id = u.id WHERE u.role = ? AND TRIM(up.name) = TRIM(?) LIMIT 1',
                ['doctor', $nameOnly]
            )->getRowArray();
            if ($found && isset($found['id'])) {
                $foundId = (int) $found['id'];
                try {
                    $model->update($id, ['doctor_id' => $foundId]);
                    if ($foundId === $doctorId) {
                        $authorized = true;
                    }
                } catch (\Throwable $e) {
                    // ignore update failures here; authorization will fail below if not matched
                }
            }
        }

        if (! $authorized) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $updateData = ['status' => $status];
        if ($status === 'cancelled' && \Config\Database::connect()->fieldExists('archived_at', 'appointments')) {
            $updateData['archived_at'] = date('Y-m-d H:i:s');
        }

        $model->update($id, $updateData);
        return redirect()->back()->with('success', 'Appointment status updated.');
    }
}
