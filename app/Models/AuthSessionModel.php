<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthSessionModel extends Model
{
    protected $table         = 'auth_sessions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id', 'refresh_token_hash', 'device_fingerprint_hash',
        'ip_hash', 'ip_address', 'user_agent', 'issued_at', 'last_active_at', 'expires_at',
        'revoked_at', 'revoke_reason',
    ];

    public function __construct()
    {
        parent::__construct();
        $db = \Config\Database::connect();
        if (! $db->fieldExists('ip_address', 'auth_sessions')) {
            $db->query('ALTER TABLE auth_sessions ADD COLUMN ip_address VARCHAR(45) NULL AFTER ip_hash');
        }
        if (! $db->fieldExists('last_active_at', 'auth_sessions')) {
            $db->query('ALTER TABLE auth_sessions ADD COLUMN last_active_at DATETIME NULL AFTER issued_at');
        }
    }

    /**
     * Create a new session record on login.
     * Returns the session token (plain) to store in PHP session.
     */
    public function createSession(int $userId): string
    {
        $request = service('request');

        // Generate a secure random token
        $token     = bin2hex(random_bytes(32));
        $tokenHash = hex2bin(hash('sha256', $token));

        // Hash IP address
        $ip     = $request->getIPAddress();
        $ipHash = hex2bin(hash('sha256', $ip));

        // Device fingerprint: hash of user-agent + accept-language
        $ua          = $request->getUserAgent()->getAgentString();
        $acceptLang  = $request->getHeaderLine('Accept-Language');
        $fingerprint = hex2bin(hash('sha256', $ua . '|' . $acceptLang));

        $now     = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', strtotime('+8 hours'));

        $db = \Config\Database::connect();
        $db->query(
            'INSERT INTO auth_sessions
             (user_id, refresh_token_hash, device_fingerprint_hash, ip_hash, ip_address, user_agent, issued_at, last_active_at, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$userId, $tokenHash, $fingerprint, $ipHash, $ip, substr($ua, 0, 255), $now, $now, $expires]
        );

        return $token;
    }

    /**
     * Revoke session on logout.
     */
    public function revokeSession(string $token, string $reason = 'logout'): void
    {
        $tokenHash = hex2bin(hash('sha256', $token));
        $db        = \Config\Database::connect();
        $db->query(
            'UPDATE auth_sessions SET revoked_at = ?, revoke_reason = ?
             WHERE refresh_token_hash = ? AND revoked_at IS NULL',
            [date('Y-m-d H:i:s'), $reason, $tokenHash]
        );
    }

    /**
     * Revoke all active sessions for a user.
     */
    public function revokeAllForUser(int $userId, string $reason = 'logout'): void
    {
        $db = \Config\Database::connect();
        $db->query(
            'UPDATE auth_sessions SET revoked_at = ?, revoke_reason = ?
             WHERE user_id = ? AND revoked_at IS NULL',
            [date('Y-m-d H:i:s'), $reason, $userId]
        );
    }

    /**
     * Get active sessions (not revoked, not expired).
     */
    public function getActiveSessions(): array
    {
        $db = \Config\Database::connect();
        return $db->query(
                        'SELECT s.id, s.user_id, COALESCE(up.name, u.username, "") AS name, u.email, u.role,
                    s.user_agent, s.issued_at, s.expires_at
             FROM auth_sessions s
             INNER JOIN users u ON u.id = s.user_id
                         LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE s.revoked_at IS NULL
               AND s.expires_at > NOW()
             ORDER BY s.issued_at DESC
             LIMIT 50'
        )->getResultArray();
    }

    /**
     * Get session stats for audit reports.
     */
    public function getSessionStats(string $since): array
    {
        $db = \Config\Database::connect();

        $total = (int) $db->query(
            'SELECT COUNT(*) as c FROM auth_sessions WHERE issued_at >= ?', [$since]
        )->getRowArray()['c'];

        $active = (int) $db->query(
            'SELECT COUNT(*) as c FROM auth_sessions
             WHERE revoked_at IS NULL AND expires_at > NOW() AND issued_at >= ?', [$since]
        )->getRowArray()['c'];

        $revoked = (int) $db->query(
            'SELECT COUNT(*) as c FROM auth_sessions
             WHERE revoked_at IS NOT NULL AND issued_at >= ?', [$since]
        )->getRowArray()['c'];

        return [
            'total'   => $total,
            'active'  => $active,
            'revoked' => $revoked,
        ];
    }
    /**
     * Get recent sessions (both active and revoked)
     */
    public function getRecentSessions(int $limit = 50): array
    {
        $db = \Config\Database::connect();
        return $db->query(
            'SELECT s.id, s.user_id, COALESCE(up.name, u.username, "") AS name, u.email, u.role,
                    s.user_agent, s.ip_address, s.issued_at, s.last_active_at, s.expires_at, s.revoked_at, s.revoke_reason
             FROM auth_sessions s
             INNER JOIN users u ON u.id = s.user_id
             LEFT JOIN user_profiles up ON up.user_id = u.id
             ORDER BY s.issued_at DESC
             LIMIT ?',
            [$limit]
        )->getResultArray();
    }
}
