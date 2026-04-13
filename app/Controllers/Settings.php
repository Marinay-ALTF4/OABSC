<?php

namespace App\Controllers;

use App\Models\UserModel;

class Settings extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model = new UserModel();
        $user  = $model->find(session('user_id'));

        return view('auth/settings', ['user' => $user]);
    }

    public function update()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $model  = new UserModel();
        $userId = session('user_id');

        $rules = [
            'name'  => 'required|min_length[3]',
            'phone' => 'permit_empty|max_length[20]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'  => trim($this->request->getPost('name')),
            'phone' => trim($this->request->getPost('phone') ?? ''),
        ];

        // Change password if provided
        $newPassword = $this->request->getPost('new_password');
        $currentPassword = $this->request->getPost('current_password');

        if ($newPassword) {
            $user = $model->find($userId);
            if (! password_verify($currentPassword, $user['password_hash'])) {
                return redirect()->back()->withInput()->with('errors', ['current_password' => 'Current password is incorrect.']);
            }
            if (strlen($newPassword) < 8) {
                return redirect()->back()->withInput()->with('errors', ['new_password' => 'New password must be at least 8 characters.']);
            }
            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $model->update($userId, $data);

        // Update session name
        session()->set('user_name', $data['name']);

        return redirect()->to('/settings')->with('success', 'Settings updated successfully.');
    }
}
