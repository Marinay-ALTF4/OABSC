<?php

/**
 * Apply deny overrides to a newly created user based on current role permission state.
 * Call this after inserting any new user.
 *
 * Logic: If ANY existing user of the same role has a deny override for a permission,
 * it means that feature is "disabled" for the role — apply to new user too.
 *
 * @param int    $userId   The new user's ID
 * @param string $role     The user's role (client, doctor, secretary, etc.)
 */
function applyDenyOverridesForNewUser(int $userId, string $role): void
{
    try {
        $db = \Config\Database::connect();

        // Find all permissions for this role
        $roleRow = $db->table('roles')->where('name', $role)->get()->getRowArray();
        if (! $roleRow) return;

        // Get all permissions in role_permissions for this role
        $rolePerms = $db->query(
            'SELECT p.code FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = ?',
            [$roleRow['id']]
        )->getResultArray();

        if (empty($rolePerms)) return;

        $now = date('Y-m-d H:i:s');

        foreach ($rolePerms as $perm) {
            $permCode = $perm['code'];

            // Check if ANY existing user of this role has a deny override for this permission
            // If yes, the feature is considered "disabled" — apply to new user too
            $hasDenyInRole = (int) $db->query(
                "SELECT COUNT(*) as c FROM user_permission_overrides upo
                 JOIN users u ON u.id = upo.user_id
                 WHERE u.role = ? AND u.deleted_at IS NULL AND u.id != ?
                   AND upo.permission_code = ? AND upo.type = 'deny'",
                [$role, $userId, $permCode]
            )->getRowArray()['c'];

            if ($hasDenyInRole > 0) {
                // Feature is disabled for this role — apply deny to new user
                $exists = $db->query(
                    "SELECT 1 FROM user_permission_overrides WHERE user_id = ? AND permission_code = ? AND type = 'deny'",
                    [$userId, $permCode]
                )->getRowArray();

                if (! $exists) {
                    $db->query(
                        "INSERT INTO user_permission_overrides (user_id, permission_code, type, created_at, updated_at) VALUES (?, ?, 'deny', ?, ?)",
                        [$userId, $permCode, $now, $now]
                    );
                }
            }
        }
    } catch (\Throwable $e) {
        log_message('error', 'applyDenyOverridesForNewUser failed: ' . $e->getMessage());
    }
}
