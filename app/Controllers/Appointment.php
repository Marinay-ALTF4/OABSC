<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\DoctorModel;

class Appointment extends BaseController
{
    public function book()
    {
        if (!session()->get('isLoggedIn') || session()->get('user_role') !== 'client') {
            return redirect()->to('/login')->with('error', 'Please login as a client.');
        }

        $doctorModel = new DoctorModel();
        $data['doctors'] = $doctorModel->getAvailableDoctors();

        return view('appointments/book', $data);
    }

    public function store()
    {
        if (!session()->get('isLoggedIn') || session()->get('user_role') !== 'client') {
            return redirect()->to('/login')->with('error', 'Please login as a client.');
        }

        $rules = [
            'doctor_id'        => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'reason'           => 'required|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $appointmentModel = new AppointmentModel();
        
        $data = [
            'user_id'          => session()->get('user_id'),
            'doctor_id'        => $this->request->getPost('doctor_id'),
            'appointment_date' => $this->request->getPost('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'reason'           => $this->request->getPost('reason'),
            'notes'            => $this->request->getPost('notes'),
            'status'           => 'pending',
        ];

        if ($appointmentModel->insert($data)) {
            return redirect()->to('/dashboard')->with('success', 'Appointment booked successfully! Waiting for confirmation.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to book appointment. Please try again.');
    }

    public function myAppointments()
    {
        if (!session()->get('isLoggedIn') || session()->get('user_role') !== 'client') {
            return redirect()->to('/login')->with('error', 'Please login as a client.');
        }

        $appointmentModel = new AppointmentModel();
        $userId = session()->get('user_id');
        
        $appointments = $appointmentModel->getAppointmentsWithDetails($userId);
        
        // Separate upcoming and past appointments
        $today = date('Y-m-d');
        $data['upcoming'] = [];
        $data['past'] = [];
        
        foreach ($appointments as $appointment) {
            if ($appointment['appointment_date'] >= $today && $appointment['status'] !== 'cancelled') {
                $data['upcoming'][] = $appointment;
            } else {
                $data['past'][] = $appointment;
            }
        }

        return view('appointments/my_appointments', $data);
    }

    public function cancel($id)
    {
        if (!session()->get('isLoggedIn') || session()->get('user_role') !== 'client') {
            return redirect()->to('/login')->with('error', 'Please login as a client.');
        }

        $appointmentModel = new AppointmentModel();
        $appointment = $appointmentModel->find($id);

        if (!$appointment || $appointment['user_id'] != session()->get('user_id')) {
            return redirect()->to('/appointments/my')->with('error', 'Appointment not found.');
        }

        if ($appointmentModel->update($id, ['status' => 'cancelled'])) {
            return redirect()->to('/appointments/my')->with('success', 'Appointment cancelled successfully.');
        }

        return redirect()->to('/appointments/my')->with('error', 'Failed to cancel appointment.');
    }
}
