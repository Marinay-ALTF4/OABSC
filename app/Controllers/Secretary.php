<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;

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

        $model = new UserModel();
        $search = $this->request->getGet('search');
        $query = $model->where('role', 'client')->where('deleted_at IS NULL');
        if ($search) {
            $query = $query->groupStart()
                           ->like('name', $search)
                           ->orLike('email', $search)
                           ->groupEnd();
        }
        $patients = $query->findAll();

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

            $model = new UserModel();
            $model->insert([
                'name'          => trim($this->request->getPost('name')),
                'email'         => strtolower(trim($this->request->getPost('email'))),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'role'          => 'client',
                'phone'         => trim($this->request->getPost('phone') ?? ''),
            ]);

            return redirect()->to('/secretary/register')->with('success', 'Patient registered successfully.');
        }

        return view('secretary/register');
    }

    public function schedules()
    {
        if ($r = $this->checkAccess()) return $r;

        $model = new UserModel();
        $doctors = $model->where('role', 'doctor')->where('deleted_at IS NULL')->findAll();

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

        $id     = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        if (in_array($status, ['confirmed', 'cancelled'])) {
            $model = new AppointmentModel();
            $model->update($id, ['status' => $status]);
        }

        return redirect()->back()->with('success', 'Appointment status updated.');
    }
}
