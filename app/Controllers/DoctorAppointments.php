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

    public function index()
    {
        $access = $this->ensureDoctor();
        if ($access !== null) return $access;

        $doctorName = 'Dr. ' . session('user_name');
        $doctorId   = (int) session('user_id');
        $model      = new AppointmentModel();
        $userModel  = new UserModel();

        $filter = $this->request->getGet('filter') ?? 'all';
        $today  = date('Y-m-d');

        // Get ALL appointments - no filter first
        $allAppts = $model->findAll();

        // Filter by doctor
        $appointments = array_filter($allAppts, function($appt) use ($doctorName, $doctorId) {
            return ($appt['doctor_name'] === $doctorName) ||
                   ((int)($appt['doctor_id'] ?? 0) === $doctorId && $doctorId > 0);
        }); 
        $appointments = array_values($appointments);

        // Apply date filter
        if ($filter === 'today') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] === $today);
        } elseif ($filter === 'upcoming') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] >= $today && in_array($a['status'], ['pending', 'approved']));
        } elseif ($filter === 'past') {
            $appointments = array_filter($appointments, fn($a) => $a['appointment_date'] < $today);
        }
        $appointments = array_values($appointments);

        // Sort by date and time
        usort($appointments, fn($a, $b) => strcmp($a['appointment_date'].$a['appointment_time'], $b['appointment_date'].$b['appointment_time']));

        // Attach patient info
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

        return view('doctor/appointments', [
            'appointments' => $appointments,
            'filter'       => $filter,
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
