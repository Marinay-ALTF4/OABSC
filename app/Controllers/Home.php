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

        // Real notifications for all roles
        $notifModel = new \App\Models\NotificationModel();
        $data['notifications']       = $notifModel->getAll((int) session('user_id'));
        $data['unread_notif_count']  = count($notifModel->getUnread((int) session('user_id')));

        if (session('user_role') === 'secretary') {
            $model = new AppointmentModel();
            $today = date('Y-m-d');

            $data['total_today']     = $model->where('appointment_date', $today)->countAllResults();
            $data['total_pending']   = $model->where('status', 'pending')->countAllResults();
            $data['total_completed'] = $model->where('appointment_date', $today)->where('status', 'completed')->countAllResults();
            $data['total_patients']  = $model->countAllResults();

            $data['recent_appointments'] = $model->orderBy('created_at', 'DESC')->findAll(10);
        }

        if (session('user_role') === 'doctor') {
            $doctorName = 'Dr. ' . session('user_name');
            $doctorId   = (int) session('user_id');
            $model      = new AppointmentModel();
            $today      = date('Y-m-d');

            $allAppts = $model->findAll();
            $myAppts  = array_filter($allAppts, fn($a) =>
                $a['doctor_name'] === $doctorName || (int)($a['doctor_id'] ?? 0) === $doctorId
            );

            $data['doc_today']        = count(array_filter($myAppts, fn($a) => $a['appointment_date'] === $today));
            $data['doc_upcoming']     = count(array_filter($myAppts, fn($a) => $a['appointment_date'] >= $today && in_array($a['status'], ['pending', 'approved'])));
            $data['doc_completed']    = count(array_filter($myAppts, fn($a) => $a['status'] === 'completed'));
            $data['doc_total']        = count($myAppts);
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
