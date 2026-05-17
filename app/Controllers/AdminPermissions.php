<?php

namespace App\Controllers;

use App\Models\UserModel;

class AdminPermissions extends BaseController
{
    private function ensureAdminAccess()
    {
        if (! session()->get('isLoggedIn') || session('user_role') !== 'admin') {
            return redirect()->to('/dashboard');
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        $db = \Config\Database::connect();

        // Get all roles with their permissions
        $roles = $db->query('SELECT * FROM roles ORDER BY id ASC')->getResultArray();
        $permissions = $db->query('SELECT * FROM permissions ORDER BY code ASC')->getResultArray();

        // Get role_permissions mapping
        $rolePerms = $db->query('SELECT role_id, permission_id FROM role_permissions')->getResultArray();
        $mapping = [];
        foreach ($rolePerms as $rp) {
            $mapping[$rp['role_id']][] = $rp['permission_id'];
        }

        // Get user counts per role
        $userModel = new UserModel();
        $roleCounts = [];
        foreach ($roles as $role) {
            $roleCounts[$role['name']] = $userModel->where('role', $role['name'])->where('deleted_at IS NULL')->countAllResults();
        }

        return view('admin/permissions', [
            'roles'       => $roles,
            'permissions' => $permissions,
            'mapping'     => $mapping,
            'roleCounts'  => $roleCounts,
        ]);
    }

    public function addPermission()
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        $code = trim((string) $this->request->getPost('code'));
        $desc = trim((string) $this->request->getPost('description'));

        if (! $code) {
            return redirect()->back()->with('error', 'Permission code is required.');
        }

        $db = \Config\Database::connect();
        $exists = $db->query('SELECT id FROM permissions WHERE code = ?', [$code])->getRowArray();

        if ($exists) {
            return redirect()->back()->with('error', 'Permission code already exists.');
        }

        $db->query('INSERT INTO permissions (code, description) VALUES (?, ?)', [$code, $desc]);

        return redirect()->back()->with('success', "Permission '{$code}' added.");
    }

    public function assignPermission()
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        $roleId       = (int) $this->request->getPost('role_id');
        $permissionId = (int) $this->request->getPost('permission_id');
        $action       = (string) $this->request->getPost('action');
        $isAjax       = $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        $db = \Config\Database::connect();

        if ($action === 'assign') {
            $exists = $db->query(
                'SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                [$roleId, $permissionId]
            )->getRowArray();

            if (! $exists) {
                $db->query(
                    'INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)',
                    [$roleId, $permissionId]
                );
            }

            if ($isAjax) {
                return $this->response->setJSON(['success' => true, 'action' => 'assigned', 'csrf_token' => csrf_hash()]);
            }
            return redirect()->back()->with('success', 'Permission assigned.');
        }

        if ($action === 'revoke') {
            $db->query(
                'DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                [$roleId, $permissionId]
            );

            if ($isAjax) {
                return $this->response->setJSON(['success' => true, 'action' => 'revoked', 'csrf_token' => csrf_hash()]);
            }
            return redirect()->back()->with('success', 'Permission revoked.');
        }

        if ($isAjax) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid action.']);
        }
        return redirect()->back()->with('error', 'Invalid action.');
    }
}
