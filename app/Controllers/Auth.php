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

        if ($this->request->is('post')) {
            $rules = [
                'name'              => 'required|min_length[3]|regex_match[/^[A-Za-zÑñ\s]+$/u]',
                'email'             => 'required|valid_email|is_unique[users.email]',
                'password'          => 'required|min_length[8]',
                'password_confirm'  => 'required|matches[password]',
            ];

            $messages = [
                'email' => [
                    'is_unique' => 'This email is already taken.',
                ],
            ];

            if (! $this->validate($rules, $messages)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $name     = trim((string) $this->request->getPost('name'));
            $email    = strtolower(trim((string) $this->request->getPost('email')));
            $password = (string) $this->request->getPost('password');

            $emailLocalPart = explode('@', $email)[0] ?? '';
            $emailNumberCount = preg_match_all('/\d/', $emailLocalPart);
            $emailSpecialCount = preg_match_all('/[^a-z0-9]/i', $emailLocalPart);

            if ($emailNumberCount > 5 || $emailSpecialCount > 3) {
                $message = 'Email allows maximum 5 numbers and 3 special characters before @.';

                return redirect()->back()->withInput()->with('errors', [
                    'email' => $message,
                ]);
            }

            $userModel = new UserModel();
            $userId    = $userModel->insert([
                'name'          => $name,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role'          => 'client',
            ]);

            if (! $userId) {
                $errors = $userModel->errors();

                return redirect()->back()->withInput()->with('errors', [
                    '_form' => $errors['email'] ?? 'Unable to register account right now. Please try again.',
                ]);
            }

            return redirect()->to('/login')->with('success', 'Login your account');
        }

        return view('auth/register');
    }
}

