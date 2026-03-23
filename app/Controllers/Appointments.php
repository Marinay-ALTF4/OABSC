<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
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

        $doctorOptions = [
            'Dr. Santos',
            'Dr. Reyes',
            'Dr. Cruz',
            'Dr. Garcia',
        ];

        return view('client/new_appointment', [
            'bookedSlots' => $bookedSlots,
            'doctorOptions' => $doctorOptions,
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

        $ownerColumn = $this->ownerColumn();
        if ($ownerColumn === null) {
            return redirect()->back()->withInput()->with('errors', [
                '_form' => 'Appointments table is missing owner column (client_id or user_id).',
            ]);
        }

        $insertData = [
            $ownerColumn => (int) session('user_id'),
            'appointment_date' => $appointmentDate,
            'appointment_time' => (string) $this->request->getPost('appointment_time'),
            'reason' => trim((string) $this->request->getPost('reason')),
            'status' => 'pending',
        ];

        if ($this->hasDoctorNameColumn()) {
            $insertData['doctor_name'] = trim((string) $this->request->getPost('doctor_name'));
        }

        $model = new AppointmentModel();
        $saved = $model->insert($insertData);

        if (! $saved) {
            return redirect()->back()->withInput()->with('errors', [
                '_form' => 'Unable to create appointment right now. Please try again.',
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
                'appointments' => [],
            ]);
        }

        $model = new AppointmentModel();
        $appointments = $model
            ->where($ownerColumn, (int) session('user_id'))
            ->orderBy('appointment_date', 'DESC')
            ->orderBy('appointment_time', 'DESC')
            ->findAll();

        return view('client/appointment', [
            'appointments' => $appointments,
        ]);
    }
}
