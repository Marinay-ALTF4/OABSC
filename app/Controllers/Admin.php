<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function patients()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        return view('admin/patients');
    }
}

