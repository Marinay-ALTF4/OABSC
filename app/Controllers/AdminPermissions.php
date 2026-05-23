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

        $roles       = $db->query('SELECT * FROM roles ORDER BY id ASC')->getResultArray();
        $permissions = $db->query('SELECT * FROM permissions ORDER BY code ASC')->getResultArray();

        // Build mapping: role_id → [permission_id, ...] that are ENABLED
        // A permission is "enabled" if it's in role_permissions AND no deny overrides exist for ANY user of that role
        $rolePerms = $db->query('SELECT role_id, permission_id FROM role_permissions')->getResultArray();
        $rpMap = [];
        foreach ($rolePerms as $rp) {
            $rpMap[$rp['role_id']][] = $rp['permission_id'];
        }

        // Get deny override counts per role per permission
        $denyRows = $db->query(
            'SELECT r.id as role_id, p.id as perm_id, COUNT(upo.id) as deny_count
             FROM roles r
             JOIN users u ON u.role = r.name AND u.deleted_at IS NULL
             JOIN user_permission_overrides upo ON upo.user_id = u.id AND upo.type = \'deny\'
             JOIN permissions p ON p.code = upo.permission_code
             GROUP BY r.id, p.id'
        )->getResultArray();

        $denyMap = [];
        foreach ($denyRows as $row) {
            $denyMap[$row['role_id']][$row['perm_id']] = (int) $row['deny_count'];
        }

        // A permission toggle is ON if: in role_permissions AND no deny overrides for this role
        $mapping = [];
        foreach ($rpMap as $roleId => $permIds) {
            foreach ($permIds as $permId) {
                $denyCount = $denyMap[$roleId][$permId] ?? 0;
                if ($denyCount === 0) {
                    $mapping[$roleId][] = $permId;
                }
            }
        }

        $userModel  = new UserModel();
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

        $db      = \Config\Database::connect();
        $permRow = $db->query('SELECT code FROM permissions WHERE id = ?', [$permissionId])->getRowArray();
        $roleRow = $db->query('SELECT name FROM roles WHERE id = ?', [$roleId])->getRowArray();

        if (! $permRow || ! $roleRow) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => 'Invalid permission or role.']);
            return redirect()->back()->with('error', 'Invalid permission or role.');
        }

        $permCode = $permRow['code'];
        $roleName = $roleRow['name'];
        $now      = date('Y-m-d H:i:s');

        // Get all users of this role
        $usersInRole = (new \App\Models\UserModel())
            ->where('role', $roleName)
            ->where('deleted_at IS NULL')
            ->findAll();

        if ($action === 'assign') {
            // Ensure role_permissions entry exists (restore if missing)
            $rpExists = $db->query(
                'SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                [$roleId, $permissionId]
            )->getRowArray();
            if (! $rpExists) {
                $db->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)', [$roleId, $permissionId]);
            }

            // Remove deny overrides for ALL users of this role
            foreach ($usersInRole as $u) {
                $db->query(
                    "DELETE FROM user_permission_overrides WHERE user_id = ? AND permission_code = ? AND type = 'deny'",
                    [(int) $u['id'], $permCode]
                );
            }

            if ($isAjax) {
                return $this->response->setJSON(['success' => true, 'action' => 'assigned', 'csrf_token' => csrf_hash()]);
            }
            return redirect()->back()->with('success', 'Permission enabled for all users.');
        }

        if ($action === 'revoke') {
            // Ensure role_permissions entry exists (keep it — we use deny overrides instead)
            $rpExists = $db->query(
                'SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                [$roleId, $permissionId]
            )->getRowArray();
            if (! $rpExists) {
                $db->query('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)', [$roleId, $permissionId]);
            }

            // Add deny overrides for ALL users of this role
            foreach ($usersInRole as $u) {
                $uid    = (int) $u['id'];
                $exists = $db->query(
                    "SELECT 1 FROM user_permission_overrides WHERE user_id = ? AND permission_code = ? AND type = 'deny'",
                    [$uid, $permCode]
                )->getRowArray();
                if (! $exists) {
                    $db->query(
                        "INSERT INTO user_permission_overrides (user_id, permission_code, type, created_at, updated_at) VALUES (?, ?, 'deny', ?, ?)",
                        [$uid, $permCode, $now, $now]
                    );
                }
            }

            if ($isAjax) {
                return $this->response->setJSON(['success' => true, 'action' => 'revoked', 'csrf_token' => csrf_hash()]);
            }
            return redirect()->back()->with('success', 'Permission disabled for all users.');
        }

        if ($isAjax) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid action.']);
        }
        return redirect()->back()->with('error', 'Invalid action.');
    }
}
