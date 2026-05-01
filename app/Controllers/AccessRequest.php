<?php

namespace App\Controllers;

use App\Models\AccessRequestModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class AccessRequest extends BaseController
{
    public function request()
    {
        if (! session()->get('isLoggedIn') || session('user_role') !== 'assistant_admin') {
            return redirect()->to('/dashboard');
        }

        $resource = trim((string) $this->request->getPost('resource'));
        $allowed  = ['patient_records', 'clinic_reports'];

        if (! in_array($resource, $allowed)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid resource.');
        }

        $userId = (int) (session('user_role') === 'assistant_admin' && session('assistant_user_id')
            ? session('assistant_user_id')
            : session('user_id'));
        $model  = new AccessRequestModel();

        // Check if already pending or approved
        if ($model->hasPending($userId, $resource)) {
            return redirect()->to('/dashboard')->with('error', 'You already have a pending request for this resource.');
        }

        if ($model->isApproved($userId, $resource)) {
            return redirect()->to('/dashboard')->with('error', 'You already have access to this resource.');
        }

        $model->insert([
            'user_id'        => $userId,
            'requested_role' => session('user_role'),
            'resource'       => $resource,
            'status'         => 'pending',
        ]);

        // Notify all admins (role = admin only, not assistant_admin)
        $userModel = new UserModel();
        $admins    = $userModel->where('role', 'admin')->findAll();
        $notif     = new NotificationModel();
        $name      = session('user_name') ?? 'Assistant Admin';
        $label     = $resource === 'patient_records' ? 'Patient Records' : 'Clinic Reports';

        foreach ($admins as $admin) {
            $notif->send(
                (int) $admin['id'],
                'Access Request',
                "{$name} is requesting access to {$label}.",
                'request'
            );
        }

        return redirect()->to('/dashboard')->with('success', "Access request for {$label} sent to Admin.");
    }

    public function approve()
    {
        if (! session()->get('isLoggedIn') || session('user_role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $id     = (int) $this->request->getPost('id');
        $action = (string) $this->request->getPost('action'); // approve or deny
        $model  = new AccessRequestModel();
        $req    = $model->find($id);

        if (! $req) {
            return redirect()->back()->with('error', 'Request not found.');
        }

        $status = $action === 'approve' ? 'approved' : 'denied';
        $model->update($id, ['status' => $status]);

        // Notify the assistant admin
        $notif = new NotificationModel();
        $label = $req['resource'] === 'patient_records' ? 'Patient Records' : 'Clinic Reports';
        $msg   = $status === 'approved'
            ? "Your request to access {$label} has been approved."
            : "Your request to access {$label} has been denied.";

        $notif->send((int) $req['user_id'], 'Access Request ' . ucfirst($status), $msg, $status === 'approved' ? 'info' : 'warning');

        return redirect()->back()->with('success', "Request {$status}.");
    }
}
