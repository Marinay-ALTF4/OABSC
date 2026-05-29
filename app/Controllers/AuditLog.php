<?php

namespace App\Controllers;

use App\Models\LoginEventModel;
use App\Models\UserModel;

class AuditLog extends BaseController
{
    private array $userCache = [];

    private function ensureAdminAccess()
    {
        if (! session()->get('isLoggedIn') || ! in_array(session('user_role'), ['admin', 'assistant_admin'], true)) {
            return redirect()->to('/dashboard');
        }
        return null;
    }

    private function formatDuration(?int $seconds): string
    {
        if ($seconds === null || $seconds <= 0) {
            return '—';
        }

        $minutes = (int) floor($seconds / 60);
        $hours   = (int) floor($minutes / 60);
        $days    = (int) floor($hours / 24);

        if ($days > 0) {
            return $days . ' day' . ($days === 1 ? '' : 's') . ' ' . ($hours % 24) . ' hr' . ((($hours % 24) === 1) ? '' : 's');
        }

        if ($hours > 0) {
            return $hours . ' hr' . ($hours === 1 ? '' : 's') . ' ' . ($minutes % 60) . ' min' . ((($minutes % 60) === 1) ? '' : 's');
        }

        return $minutes . ' min' . ($minutes === 1 ? '' : 's');
    }

    private function getUserDetails(UserModel $userModel, int $userId): array
    {
        if ($userId <= 0) {
            return ['name' => '', 'email' => '', 'role' => ''];
        }

        if (! isset($this->userCache[$userId])) {
            $row = $userModel->select('users.id, users.email, users.role, COALESCE(up.name, users.username, "") AS name')
                ->join('user_profiles up', 'up.user_id = users.id', 'left')
                ->where('users.id', $userId)
                ->first();

            $this->userCache[$userId] = [
                'name'  => trim((string) ($row['name'] ?? '')),
                'email' => trim((string) ($row['email'] ?? '')),
                'role'  => trim((string) ($row['role'] ?? '')),
            ];
        }

        return $this->userCache[$userId];
    }

    private function enrichEvents(array $events, LoginEventModel $logModel, UserModel $userModel): array
    {
        $db = \Config\Database::connect();

        foreach ($events as &$event) {
            $userId = (int) ($event['user_id'] ?? 0);
            $user   = $this->getUserDetails($userModel, $userId);

            $event['display_role'] = $user['role'] !== '' ? $user['role'] : '—';
            $event['display_email'] = $user['email'] !== ''
                ? $user['email']
                : (string) ($event['email_attempted'] ?? '—');

            $ipAddress = trim((string) ($event['ip_address'] ?? ''));
            if ($ipAddress === '') {
                $ipAddress = '—';
            } elseif ($ipAddress === '::1') {
                $ipAddress = 'Localhost (::1)';
            }
            $event['display_location'] = $ipAddress;

            $event['display_device'] = $event['user_agent'] ?? '—';

            $event['time_active'] = '—';
            if (($event['event_type'] ?? '') === LoginEventModel::EVENT_LOGOUT && $userId > 0) {
                $logoutTime = strtotime((string) ($event['created_at'] ?? '')) ?: null;
                if ($logoutTime !== null) {
                    $session = $db->table('auth_sessions')
                        ->select('issued_at, revoked_at')
                        ->where('user_id', $userId)
                        ->where('revoked_at IS NOT NULL', null, false)
                        ->where('revoked_at <=', date('Y-m-d H:i:s', $logoutTime))
                        ->orderBy('revoked_at', 'DESC')
                        ->limit(1)
                        ->get()
                        ->getRowArray();

                    if ($session) {
                        $issuedAt = strtotime((string) ($session['issued_at'] ?? '')) ?: null;
                        $revokedAt = strtotime((string) ($session['revoked_at'] ?? '')) ?: null;
                        if ($issuedAt !== null && $revokedAt !== null) {
                            $event['time_active'] = $this->formatDuration($revokedAt - $issuedAt);
                        }
                    }
                }
            }

            $event['reason_display'] = $this->formatReasonCode((string) ($event['reason_code'] ?? ''));
        }
        unset($event);

        return $events;
    }

    private function formatReasonCode(string $reasonCode): string
    {
        $reasonCode = trim($reasonCode);
        if ($reasonCode === '') {
            return '—';
        }

        $parts = array_values(array_filter(array_map('trim', explode('|', $reasonCode)), static fn ($part) => $part !== ''));
        if ($parts === []) {
            return '—';
        }

        $formatted = [];
        foreach ($parts as $part) {
            [$key, $value] = array_pad(explode(':', $part, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            $formatted[] = match ($key) {
                'patient_registered' => 'Patient Registered: ' . $value,
                'user_added'         => 'User Added: ' . $value,
                'user_edited'        => 'User Edited: ' . $value,
                'by_admin'           => 'By: ' . ($value !== '' ? $value : 'Admin'),
                'by_secretary'       => 'By: ' . ($value !== '' ? $value : 'Secretary'),
                'role'               => 'Role: ' . $this->prettyLabel($value),
                default              => $this->prettyLabel($part),
            };
        }

        return implode(' | ', $formatted);
    }

    private function prettyLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '—') {
            return '—';
        }

        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return ucwords($value);
    }

    public function index()
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        $logModel = new LoginEventModel();
        $userModel = new UserModel();
        $sessionModel = new \App\Models\AuthSessionModel();

        $events   = $this->enrichEvents($logModel->getRecentEvents(200), $logModel, $userModel);
        $summary  = $logModel->getAuditSummary();
        
        $sessions = $sessionModel->getRecentSessions(50);
        $activeCount = count(array_filter($sessions, function ($s) {
            return is_null($s['revoked_at']) && strtotime((string)$s['expires_at']) > time();
        }));

        $failed24 = $logModel->getFailedLoginsLast24h();
        $suspicious = $logModel->getSuspiciousCount();

        return view('admin/audit_log', [
            'events'      => $events,
            'summary'     => $summary,
            'sessions'    => $sessions,
            'activeCount' => $activeCount,
            'failed24'    => $failed24,
            'suspicious'  => $suspicious,
        ]);
    }
}
