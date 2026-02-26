<?php

namespace App\Controllers;

use App\Models\UserModel;

class Admin extends BaseController
{
    public function patients()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        return view('admin/patients');
    }

    public function patientList()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $userModel = new UserModel();
        $users = $userModel->orderBy('id', 'DESC')->findAll();

        return view('admin/patients_list', [
            'users' => $users,
        ]);
    }
}

