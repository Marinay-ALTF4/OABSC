<?php

namespace App\Controllers;

use App\Models\AccessRequestModel;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Libraries\PermissionManager;

class AccessRequest extends BaseController
{
    /**
     * GET /access-denied?from=/appointments/my
     * Shows the access denied page with request access button.
     */
    public function denied()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('errors/access_denied');
    }
    public function request()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $permissionCode = trim((string) $this->request->getPost('permission_code'));
        $userId         = (int) session('user_id');
        $userRole       = (string) session('user_role');

        // Validate the permission code exists in definitions
        if (! isset(PermissionManager::$definitions[$permissionCode])) {
            return redirect()->back()->with('error', 'Invalid permission.');
        }

        $model = new AccessRequestModel();

        // Block if already pending
        if ($model->hasPendingPermission($userId, $permissionCode)) {
            return redirect()->to('/access-denied?from=' . urlencode($permissionCode))
                ->with('error', 'You already have a pending request for this feature.');
        }

        // Block if already approved — but check if they still have a deny override
        // If they have a deny override, the old approval is stale — allow new request
        $db = \Config\Database::connect();
        $hasDeny = $db->query(
            "SELECT 1 FROM user_permission_overrides WHERE user_id = ? AND permission_code = ? AND type = 'deny'",
            [$userId, $permissionCode]
        )->getRowArray();

        if (! $hasDeny && $model->isPermissionApproved($userId, $permissionCode)) {
            return redirect()->to('/dashboard')->with('success', 'You already have access to this feature.');
        }

        $permLabel = PermissionManager::$definitions[$permissionCode]['label'] ?? $permissionCode;

        // Clear any old approved/denied records for this user+permission so new request is clean
        $model->where('user_id', $userId)
              ->where('permission_code', $permissionCode)
              ->whereIn('status', ['approved', 'denied'])
              ->delete();

        $model->insert([
            'user_id'         => $userId,
            'requested_role'  => $userRole,
            'resource'        => $permissionCode, // keep for backward compat
            'permission_code' => $permissionCode,
            'status'          => 'pending',
        ]);

        // Notify all admins
        $userModel = new UserModel();
        $admins    = $userModel->where('role', 'admin')->where('deleted_at IS NULL')->findAll();
        $notif     = new NotificationModel();
        $name      = session('user_name') ?? 'A user';

        foreach ($admins as $admin) {
            $notif->send(
                (int) $admin['id'],
                'Access Request',
                "{$name} ({$userRole}) is requesting access to: {$permLabel}.",
                'request'
            );
        }

        // Find a route for this permission to use as the `from` param
        $permRoutes = PermissionManager::$definitions[$permissionCode]['routes'] ?? [];
        $fromUri    = ! empty($permRoutes) ? $permRoutes[0] : '/dashboard';

        return redirect()->to('/access-denied?from=' . urlencode($fromUri))
            ->with('success', "Access request for \"{$permLabel}\" sent to Admin.");
    }

    /**
     * POST /access-request/approve
     * Admin approves or denies a request.
     * On approve: grants the permission to the role (role-level) OR just notifies the user.
     */
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

        $permCode  = $req['permission_code'] ?? $req['resource'];
        $permLabel = PermissionManager::$definitions[$permCode]['label'] ?? $permCode;

        // If approved, remove ALL deny overrides for this specific user
        // This restores full access for THIS user only
        if ($status === 'approved' && $permCode) {
            $db     = \Config\Database::connect();
            $userId = (int) $req['user_id'];

            // Remove ALL deny overrides for this user (full restore)
            $db->query(
                "DELETE FROM user_permission_overrides WHERE user_id = ? AND type = 'deny'",
                [$userId]
            );
        }

        // Notify the requesting user
        $notif = new NotificationModel();
        $msg   = $status === 'approved'
            ? "Your request to access \"{$permLabel}\" has been approved. All features for your role have been restored."
            : "Your request to access \"{$permLabel}\" has been denied.";

        $notif->send(
            (int) $req['user_id'],
            'Access Request ' . ucfirst($status),
            $msg,
            $status === 'approved' ? 'info' : 'warning'
        );

        return redirect()->back()->with('success', "Request {$status}.");
    }
}
