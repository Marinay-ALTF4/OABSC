<?php

namespace App\Controllers;

use App\Models\UserModel;

class Admin extends BaseController
{
    private function ensureAdminAccess()
    {
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        return null;
    }

    public function patients()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) {
            return $access;
        }

        return view('admin/patients');
    }

    public function patientList()
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) {
            return $access;
        }

        $userModel = new UserModel();
        $users = $userModel->withDeleted()->orderBy('id', 'DESC')->findAll();

        return view('admin/patients_list', [
            'users' => $users,
        ]);
    }

    public function editUser(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) {
            return $access;
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (! $user) {
            return redirect()->to('/admin/patients/list')->with('error', 'User not found.');
        }

        if ($this->request->is('post')) {
            $rules = [
                'name' => 'required|min_length[3]|regex_match[/^[A-Za-zÑñ\s]+$/u]',
                'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
                'role' => 'required|in_list[admin,client]',
                'password' => 'permit_empty|min_length[8]',
                'password_confirm' => 'permit_empty|matches[password]',
            ];

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $updateData = [
                'name' => trim((string) $this->request->getPost('name')),
                'email' => strtolower(trim((string) $this->request->getPost('email'))),
                'role' => (string) $this->request->getPost('role'),
            ];

            $newPassword = (string) $this->request->getPost('password');
            if ($newPassword !== '') {
                $updateData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $updated = $userModel->update($id, $updateData);

            if (! $updated) {
                return redirect()->back()->withInput()->with('error', 'Unable to update user.');
            }

            if ((int) session()->get('user_id') === $id) {
                session()->set('user_name', trim((string) $this->request->getPost('name')));
                session()->set('user_role', (string) $this->request->getPost('role'));
            }

            return redirect()->to('/admin/patients/list')->with('success', 'User updated successfully.');
        }

        return view('admin/edit_user', [
            'user' => $user,
        ]);
    }

    public function deleteUser(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) {
            return $access;
        }

        $currentAdminId = (int) session()->get('user_id');
        if ($id === $currentAdminId) {
            return redirect()->to('/admin/patients/list')->with('error', 'Current admin cannot delete their own account.');
        }

        $userModel = new UserModel();
        $user = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to('/admin/patients/list')->with('error', 'User not found.');
        }

        if (! empty($user['deleted_at'])) {
            return redirect()->to('/admin/patients/list')->with('error', 'User is already deleted.');
        }

        if (! $userModel->delete($id)) {
            return redirect()->to('/admin/patients/list')->with('error', 'Unable to delete user.');
        }

        return redirect()->to('/admin/patients/list')->with('success', 'User deleted successfully. You can restore this user anytime.');
    }

    public function restoreUser(int $id)
    {
        $access = $this->ensureAdminAccess();
        if ($access !== null) {
            return $access;
        }

        $userModel = new UserModel();
        $user = $userModel->withDeleted()->find($id);

        if (! $user) {
            return redirect()->to('/admin/patients/list')->with('error', 'User not found.');
        }

        if (empty($user['deleted_at'])) {
            return redirect()->to('/admin/patients/list')->with('error', 'User is not deleted.');
        }

        if (! $userModel->withDeleted()->update($id, ['deleted_at' => null])) {
            return redirect()->to('/admin/patients/list')->with('error', 'Unable to restore user.');
        }

        return redirect()->to('/admin/patients/list')->with('success', 'User restored successfully.');
    }
}

