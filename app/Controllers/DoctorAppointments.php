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

    private function loadDoctorAppointments(string $filter = 'all'): array
    {
        $doctorName = 'Dr. ' . session('user_name');
        $doctorId   = (int) session('user_id');
        $model      = new AppointmentModel();
        $userModel  = new UserModel();
        $today      = date('Y-m-d');

        $allAppts = $model->findAll();

        $appointments = array_filter($allAppts, function ($appt) use ($doctorName, $doctorId) {
            return ($appt['doctor_name'] === $doctorName) ||
                   ((int) ($appt['doctor_id'] ?? 0) === $doctorId && $doctorId > 0);
        });
        $appointments = array_values($appointments);

        if ($filter === 'today') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] === $today);
        } elseif ($filter === 'upcoming') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] > $today && in_array($a['status'], ['pending', 'approved']));
        } elseif ($filter === 'past') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] < $today);
        }

        $appointments = array_values($appointments);

        usort($appointments, fn($a, $b) => strcmp($a['appointment_date'] . $a['appointment_time'], $b['appointment_date'] . $b['appointment_time']));

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

    public function index()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $filter = $this->request->getGet('filter') ?? 'all';
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

        $doctorName = 'Dr. ' . session('user_name');
        $doctorId   = (int) session('user_id');
        $model      = new AppointmentModel();
        $userModel  = new UserModel();

        $allAppts = $model->findAll();
        $doctorAppts = array_values(array_filter($allAppts, function (array $appt) use ($doctorName, $doctorId): bool {
            return ($appt['doctor_name'] === $doctorName) || ((int) ($appt['doctor_id'] ?? 0) === $doctorId && $doctorId > 0);
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

        // Make sure this appointment belongs to this doctor
        $doctorName = 'Dr. ' . session('user_name');
        $doctorId   = (int) session('user_id');
        if ($appt['doctor_name'] !== $doctorName && ($appt['doctor_id'] ?? 0) !== $doctorId) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $model->update($id, ['status' => $status]);
        return redirect()->back()->with('success', 'Appointment status updated.');
    }
}
