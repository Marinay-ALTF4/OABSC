<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;

class Home extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [];

        $userModel = new UserModel();
        $data['currentUser'] = $userModel->find((int) session('user_id'));

        if (session('user_role') === 'secretary') {
            $model = new AppointmentModel();
            $today = date('Y-m-d');

            $data['total_today']     = $model->where('appointment_date', $today)->countAllResults();
            $data['total_pending']   = $model->where('status', 'pending')->countAllResults();
            $data['total_completed'] = $model->where('appointment_date', $today)->where('status', 'completed')->countAllResults();
            $data['total_patients']  = $model->countAllResults();

            $data['recent_appointments'] = $model->orderBy('created_at', 'DESC')->findAll(10);
        }

        return view('auth/dashboard', $data);
    }

    public function profile()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('client/profile');
    }
}
