<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $validationRules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your login details.');
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        if (! $user || ! password_verify($password, $user['password_hash'] ?? '')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid email or password.');
        }

        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'] ?? '',
            'user_role' => $user['role'] ?? 'client',
            'isLoggedIn'=> true,
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'              => 'required|min_length[3]',
                'email'             => 'required|valid_email|is_unique[users.email]',
                'password'          => 'required|min_length[6]',
                'password_confirm'  => 'required|matches[password]',
            ];

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $userId    = $userModel->insert([
                'name'          => $this->request->getPost('name'),
                'email'         => $this->request->getPost('email'),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'role'          => 'client',
            ]);

            session()->set([
                'user_id'   => $userId,
                'user_name' => $this->request->getPost('name'),
                'user_role' => 'client',
                'isLoggedIn'=> true,
            ]);

            return redirect()->to('/dashboard');
        }

        return view('auth/register');
    }
}

