<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\IncomingRequest;

class Api extends BaseController
{
    use ResponseTrait;

    public function health()
    {
        return $this->respond([
            'status' => 'ok',
            'message' => 'API is running',
            'time' => date('Y-m-d H:i:s'),
        ]);
    }

    public function register()
    {
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $request = $this->request;

        $rules = [
            'name' => 'required|min_length[3]|regex_match[/^[A-Za-zÑñ\s]+$/u]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $name = trim((string) $request->getPost('name'));
        $email = strtolower(trim((string) $request->getPost('email')));
        $password = (string) $request->getPost('password');

        $emailLocalPart = explode('@', $email)[0] ?? '';
        $emailNumberCount = preg_match_all('/\d/', $emailLocalPart);
        $emailSpecialCount = preg_match_all('/[^a-z0-9]/i', $emailLocalPart);

        if ($emailNumberCount > 5 || $emailSpecialCount > 3) {
            return $this->failValidationErrors([
                'email' => 'Email allows maximum 5 numbers and 3 special characters before @.',
            ]);
        }

        $userModel = new UserModel();
        $userId = $userModel->insert([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'client',
        ]);

        if (! $userId) {
            return $this->failServerError('Unable to register account right now.');
        }

        return $this->respondCreated([
            'message' => 'Registration successful. Login your account',
            'user_id' => $userId,
        ]);
    }

    public function login()
    {
        if (! $this->request instanceof IncomingRequest) {
            return $this->failServerError('Invalid request object.');
        }

        $request = $this->request;

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $email = strtolower(trim((string) $request->getPost('email')));
        $password = (string) $request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (! $user || ! password_verify($password, $user['password_hash'] ?? '')) {
            return $this->failUnauthorized('Invalid email or password.');
        }

        return $this->respond([
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'] ?? null,
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'role' => $user['role'] ?? 'client',
            ],
        ]);
    }

    public function users()
    {
        $userModel = new UserModel();
        $users = $userModel
            ->select('id, name, email, role, created_at')
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->respond([
            'count' => count($users),
            'users' => $users,
        ]);
    }
}
