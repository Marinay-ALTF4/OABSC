<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    public function index()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find((int) session('user_id'));

        return view('client/profile', ['user' => $user]);
    }

    public function save()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userId = (int) session('user_id');
        $userModel = new UserModel();
        $data = [];

        $name  = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $phone = trim((string) $this->request->getPost('phone'));
        $city  = trim((string) $this->request->getPost('city'));
        $addr  = trim((string) $this->request->getPost('address'));

        if ($name !== '')  $data['name']    = $name;
        if ($email !== '') $data['email']   = $email;
        if ($phone !== '') $data['phone']   = $phone;
        if ($city !== '')  $data['city']    = $city;
        if ($addr !== '')  $data['address'] = $addr;

        // Doctor-specific fields
        if (session('user_role') === 'doctor') {
            $spec   = trim((string) $this->request->getPost('specialization'));
            $exp    = trim((string) $this->request->getPost('experience'));
            $degree = trim((string) $this->request->getPost('degree'));
            $bio    = trim((string) $this->request->getPost('bio'));

            if ($spec !== '')   $data['specialization'] = $spec;
            if ($exp !== '')    $data['experience']      = $exp;
            if ($degree !== '') $data['degree']          = $degree;
            if ($bio !== '')    $data['bio']             = $bio;
        }

        // Handle photo upload
        $photo = $this->request->getFile('profile_photo');
        if ($photo && $photo->isValid() && ! $photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/profiles/', $newName);
            $data['profile_photo'] = 'uploads/profiles/' . $newName;
        }

        if (! empty($data)) {
            $userModel->update($userId, $data);

            // Update session if name changed
            if (isset($data['name'])) {
                session()->set('user_name', $data['name']);
            }
        }

        return redirect()->to('/profile')->with('success', 'Profile updated successfully.');
    }
}
