<?php

namespace App\Controllers;

use App\Models\DoctorScheduleModel;

class DoctorSchedule extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn') || session('user_role') !== 'doctor') {
            return redirect()->to('/dashboard');
        }

        $model     = new DoctorScheduleModel();
        $schedules = $model->getScheduleByDoctor((int) session('user_id'));

        return view('doctor/schedule_settings', ['schedules' => $schedules]);
    }

    public function save()
    {
        if (! session()->get('isLoggedIn') || session('user_role') !== 'doctor') {
            return redirect()->to('/dashboard');
        }

        $doctorId = (int) session('user_id');
        $model    = new DoctorScheduleModel();
        $days     = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Delete existing schedules
        $model->where('doctor_id', $doctorId)->delete();

        foreach ($days as $day) {
            $available = $this->request->getPost("available_{$day}");
            if ($available) {
                $start = $this->request->getPost("start_{$day}");
                $end   = $this->request->getPost("end_{$day}");
                if ($start && $end) {
                    $model->insert([
                        'doctor_id'    => $doctorId,
                        'day'          => $day,
                        'start_time'   => $start,
                        'end_time'     => $end,
                        'is_available' => 1,
                    ]);
                }
            }
        }

        return redirect()->to('/doctor/schedule')->with('success', 'Schedule updated successfully.');
    }

    // API endpoint for getting doctor schedule (used in booking modal)
    public function getByDoctor(int $doctorId)
    {
        $model     = new DoctorScheduleModel();
        $schedules = $model->getScheduleByDoctor($doctorId);
        return $this->response->setJSON($schedules);
    }
}
