<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ClinicSettingsModel;

class RoleSelection extends BaseController
{
    public function index()
    {
        if (! session()->get('pending_role_selection')) {
            return redirect()->to('/login');
        }

        return view('auth/role_selection');
    }

    public function verify()
    {
        if (! session()->get('pending_role_selection')) {
            return redirect()->to('/login');
        }

        $clinicCode   = trim((string) $this->request->getPost('clinic_code'));
        $selectedRole = trim((string) $this->request->getPost('role'));
        $rolePassword = trim((string) $this->request->getPost('role_password'));

        // Validate clinic access code
        $settingsModel = new ClinicSettingsModel();
        $storedCode = $settingsModel->getValue('clinic_access_code');

        if (! $storedCode || ! password_verify($clinicCode, $storedCode)) {
            return redirect()->back()->with('error', 'Invalid clinic access code.');
        }

        $userId    = session()->get('pending_user_id');
        $userModel = new UserModel();
        $user      = $userModel->find($userId);

        if (! $user) {
            return redirect()->to('/login')->with('error', 'User not found.');
        }

        // Validate role password
        if ($selectedRole === 'admin') {
            // Admin uses role_password if set, otherwise password_hash
            $hashToCheck = ! empty($user['role_password']) ? $user['role_password'] : $user['password_hash'];
            if (! password_verify($rolePassword, $hashToCheck)) {
                return redirect()->back()->with('error', 'Incorrect password for Admin role.');
            }
            $finalRole = 'admin';
        } elseif ($selectedRole === 'assistant_admin') {
            // Find the matching assistant_admin user by role_password
            $allAssistants = $userModel->where('role', 'assistant_admin')->where('deleted_at IS NULL')->findAll();
            $assistantAdmin = null;
            foreach ($allAssistants as $a) {
                if (! empty($a['role_password']) && password_verify($rolePassword, $a['role_password'])) {
                    $assistantAdmin = $a;
                    break;
                }
            }
            if (! $assistantAdmin) {
                return redirect()->back()->with('error', 'Incorrect password for Assistant Admin role.');
            }
            $finalRole = 'assistant_admin';
        } else {
            return redirect()->back()->with('error', 'Invalid role selected.');
        }

        // Clear pending session and set full session
        session()->remove('pending_role_selection');
        session()->remove('pending_user_id');

        session()->set([
            'isLoggedIn'         => true,
            'user_id'            => $user['id'],
            'user_name'          => $finalRole === 'assistant_admin' ? ($assistantAdmin['name'] ?? $user['name']) : $user['name'],
            'user_email'         => $user['email'],
            'user_role'          => $finalRole,
            'assistant_user_id'  => $finalRole === 'assistant_admin' ? $assistantAdmin['id'] : null,
        ]);

        return redirect()->to('/dashboard');
    }
}
