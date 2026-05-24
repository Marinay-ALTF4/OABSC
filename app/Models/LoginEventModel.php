<?php

namespace App\Models;

use App\Libraries\LoginEventCrypt;
use CodeIgniter\Model;

class LoginEventModel extends Model
{
    protected $table         = 'login_events';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id', 'email_attempted', 'event_type',
        'reason_code', 'ip_hash', 'ip_address', 'user_agent', 'created_at',
    ];
    protected $useTimestamps  = false;

    public function __construct()
    {
        parent::__construct();
        $db = \Config\Database::connect();
        if (! $db->fieldExists('ip_address', 'login_events')) {
            $db->query('ALTER TABLE login_events ADD COLUMN ip_address VARCHAR(45) NULL AFTER ip_hash');
        }
    }
    
    protected $beforeInsert = ['encryptLogFields'];
    protected $afterFind    = ['decryptLogFields'];

    private array $encryptedFields = [
        'email_attempted',
        'user_agent',
    ];

    protected function encryptLogFields(array $data): array
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        $crypt = new LoginEventCrypt();
        $data['data'] = $crypt->encryptFields($data['data'], $this->encryptedFields);

        return $data;
    }

    protected function decryptLogFields(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        $crypt = new LoginEventCrypt();

        if ($data['data'] === null) {
            return $data;
        }

        if (isset($data['data'][0]) && is_array($data['data'][0])) {
            foreach ($data['data'] as $index => $row) {
                $data['data'][$index] = $crypt->decryptFields($row, $this->encryptedFields);
            }

            return $data;
        }

        if (is_array($data['data'])) {
            $data['data'] = $crypt->decryptFields($data['data'], $this->encryptedFields);
        }

        return $data;
    }

    // Event types
    public const EVENT_LOGIN_SUCCESS   = 'login_success';
    public const EVENT_LOGIN_FAILED    = 'login_failed';
    public const EVENT_LOGIN_LOCKED    = 'login_locked';
    public const EVENT_LOGOUT          = 'logout';
    public const EVENT_MFA_SUCCESS     = 'mfa_success';
    public const EVENT_MFA_FAILED      = 'mfa_failed';
    public const EVENT_ACCOUNT_MODIFIED = 'account_modified';
    public const EVENT_ACCOUNT_DELETED  = 'account_deleted';
    public const EVENT_ACCOUNT_RESTORED = 'account_restored';
    public const EVENT_SUSPICIOUS       = 'suspicious_activity';

    public function log(
        string $eventType,
        ?int $userId = null,
        ?string $emailAttempted = null,
        ?string $reasonCode = null
    ): void {
        $request   = service('request');
        $ipAddress = $request->getIPAddress();
        $userAgent = $request->getUserAgent()->getAgentString();

        $this->insert([
            'user_id'          => $userId,
            'email_attempted'  => $emailAttempted,
            'event_type'       => $eventType,
            'reason_code'      => $reasonCode,
            'ip_hash'          => hex2bin(hash('sha256', $ipAddress)),
            'ip_address'       => $ipAddress,
            'user_agent'       => substr($userAgent, 0, 255),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
    }

    public function getRecentEvents(int $limit = 100): array
    {
        return $this->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }

    public function getFailedLoginsLast24h(): int
    {
        return $this->where('event_type', self::EVENT_LOGIN_FAILED)
                    ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                    ->countAllResults();
    }

    public function getSuspiciousCount(): int
    {
        return $this->where('event_type', self::EVENT_SUSPICIOUS)
                    ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                    ->countAllResults();
    }

    public function getAuditSummary(): array
    {
        $since = date('Y-m-d H:i:s', strtotime('-7 days'));
        $db    = \Config\Database::connect();

        $counts = $db->query(
            "SELECT event_type, COUNT(*) as count
             FROM login_events
             WHERE created_at >= ?
             GROUP BY event_type
             ORDER BY count DESC",
            [$since]
        )->getResultArray();

        return $counts;
    }

    public function getActiveSessions(): array
    {
        $db = \Config\Database::connect();
        return $db->query(
            'SELECT u.id, u.name, u.email, u.role, u.last_login_at,
                    s.issued_at, s.expires_at, s.user_agent
             FROM auth_sessions s
             INNER JOIN users u ON u.id = s.user_id
             WHERE s.revoked_at IS NULL
               AND s.expires_at > NOW()
             ORDER BY s.issued_at DESC
             LIMIT 50'
        )->getResultArray();
    }
}
