<?php

namespace App\Controllers;

use App\Libraries\LoginEventCrypt;
use App\Models\LoginEventModel;
use App\Models\UserModel;

class AuditReport extends BaseController
{
    private array $userNameCache = [];
    private array $userEmailCache = [];

    private function decryptEmailAttempted(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '—';
        }

        return (new LoginEventCrypt())->decrypt($value) ?? $value;
    }

    private function ensureAdminAccess()
    {
        if (! session()->get('isLoggedIn') || ! in_array(session('user_role'), ['admin', 'assistant_admin'], true)) {
            return redirect()->to('/dashboard');
        }
        return null;
    }

    private function resolveUserEmail(
        UserModel $userModel,
        int $userId
    ): string {
        if ($userId <= 0) {
            return '';
        }

        if (! isset($this->userEmailCache[$userId])) {
            $row = $userModel->select('users.id, users.email')
                ->where('users.id', $userId)
                ->first();

            $this->userEmailCache[$userId] = trim((string) ($row['email'] ?? ''));
        }

        return $this->userEmailCache[$userId];
    }

    private function resolveUserName(
        \CodeIgniter\Database\BaseConnection $db,
        string $roleLabel,
        string $value
    ): string {
        $trimmedValue = trim($value);
        if ($trimmedValue === '') {
            return $roleLabel;
        }

        if (ctype_digit($trimmedValue)) {
            $userId = (int) $trimmedValue;
            if (! isset($this->userNameCache[$userId])) {
                $row = $db->query(
                    'SELECT COALESCE(up.name, u.username, "") AS name
                     FROM users u
                     LEFT JOIN user_profiles up ON up.user_id = u.id
                     WHERE u.id = ?
                     LIMIT 1',
                    [$userId]
                )->getRowArray();
                $this->userNameCache[$userId] = trim((string) ($row['name'] ?? ''));
            }

            return $this->userNameCache[$userId] !== ''
                ? $this->userNameCache[$userId]
                : $roleLabel . ' #' . $trimmedValue;
        }

        return $trimmedValue;
    }

    private function prettyAuditCode(string $value): string
    {
        $value = trim($value);
        if ($value === '' || $value === '—') {
            return '—';
        }

        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return ucwords($value);
    }

    private function formatReasonCode(\CodeIgniter\Database\BaseConnection $db, ?string $reasonCode): string
    {
        $reasonCode = trim((string) $reasonCode);
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
                'by_admin'           => 'By: Admin ' . $this->resolveUserName($db, 'Admin', $value),
                'by_secretary'       => 'By: Secretary ' . $this->resolveUserName($db, 'Secretary', $value),
                'role'               => 'Role: ' . $this->prettyAuditCode($value),
                default              => $this->prettyAuditCode($part),
            };
        }

        return implode(' | ', $formatted);
    }

    public function index()
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        $filter = $this->request->getGet('filter') ?? 'weekly';
        $data   = $this->buildReport($filter);

        return view('admin/audit_reports', array_merge($data, ['filter' => $filter]));
    }

    public function exportCsv()
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        $filter = $this->request->getGet('filter') ?? 'weekly';
        $data   = $this->buildReport($filter);

        $filename = 'audit_report_' . $filter . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        // Summary section
        fputcsv($out, ['AUDIT REPORT - ' . strtoupper($filter)]);
        fputcsv($out, ['Generated', date('Y-m-d H:i:s')]);
        fputcsv($out, []);
        fputcsv($out, ['SUMMARY']);
        fputcsv($out, ['Metric', 'Count']);
        fputcsv($out, ['Successful Logins',    $data['total_success']]);
        fputcsv($out, ['Failed Logins',        $data['total_failed']]);
        fputcsv($out, ['Locked Accounts',      $data['total_locked']]);
        fputcsv($out, ['Suspicious Activity',  $data['total_suspicious']]);
        fputcsv($out, ['MFA Successes',        $data['total_mfa_success']]);
        fputcsv($out, ['MFA Failures',         $data['total_mfa_failed']]);
        fputcsv($out, ['Active Sessions',      $data['active_sessions']]);
        fputcsv($out, []);

        // Event log
        fputcsv($out, ['EVENT LOG']);
        fputcsv($out, ['#', 'Timestamp', 'Event Type', 'User ID', 'Email Attempted', 'Reason']);
        foreach ($data['events'] as $i => $e) {
            fputcsv($out, [
                $i + 1,
                $e['created_at'] ?? '',
                $e['event_type'] ?? '',
                $e['user_id'] ?? '',
                $e['email_attempted_display'] ?? '',
                $e['reason_display'] ?? $e['reason_code'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    private function buildReport(string $filter): array
    {
        $db = \Config\Database::connect();

        $since = match($filter) {
            'daily'   => date('Y-m-d H:i:s', strtotime('-1 day')),
            'monthly' => date('Y-m-d H:i:s', strtotime('-30 days')),
            default   => date('Y-m-d H:i:s', strtotime('-7 days')),
        };

        // Aggregate counts per event type
        $rows = $db->query(
            'SELECT event_type, COUNT(*) as count
             FROM login_events
             WHERE created_at >= ?
             GROUP BY event_type',
            [$since]
        )->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['event_type']] = (int) $row['count'];
        }

        // Daily breakdown for chart (last 7 or 30 days)
        $days = $filter === 'monthly' ? 30 : ($filter === 'daily' ? 1 : 7);
        $chartLabels  = [];
        $chartSuccess = [];
        $chartFailed  = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day   = date('Y-m-d', strtotime("-{$i} days"));
            $label = date('M j', strtotime($day));
            $chartLabels[] = $label;

            $s = (int) $db->query(
                "SELECT COUNT(*) as c FROM login_events
                 WHERE event_type = 'login_success' AND DATE(created_at) = ?",
                [$day]
            )->getRowArray()['c'];

            $f = (int) $db->query(
                "SELECT COUNT(*) as c FROM login_events
                 WHERE event_type = 'login_failed' AND DATE(created_at) = ?",
                [$day]
            )->getRowArray()['c'];

            $chartSuccess[] = $s;
            $chartFailed[]  = $f;
        }

        // Active sessions from auth_sessions table
        $authSessionModel = new \App\Models\AuthSessionModel();
        $activeSessions   = count($authSessionModel->getActiveSessions());
        $sessionStats     = $authSessionModel->getSessionStats($since);

        // Recent events list
        $events = $db->query(
            'SELECT id, user_id, email_attempted, event_type, reason_code, created_at
             FROM login_events
             WHERE created_at >= ?
             ORDER BY created_at DESC
             LIMIT 100',
            [$since]
        )->getResultArray();

        $userModel = new UserModel();

        foreach ($events as &$event) {
            $userId = (int) ($event['user_id'] ?? 0);

            $event['email_attempted_display'] = $this->decryptEmailAttempted($event['email_attempted'] ?? null);
            if (($event['event_type'] ?? '') === LoginEventModel::EVENT_LOGOUT && trim((string) $event['email_attempted_display']) === '—') {
                $email = $this->resolveUserEmail($userModel, $userId);
                $event['email_attempted_display'] = $email !== '' ? $email : '—';
            }
            $event['reason_display'] = $this->formatReasonCode($db, $event['reason_code'] ?? null);
        }
        unset($event);

        // Notifications/alerts count
        $alertCount = (int) $db->query(
            "SELECT COUNT(*) as c FROM notifications
             WHERE type = 'warning' AND created_at >= ?",
            [$since]
        )->getRowArray()['c'];

        return [
            'total_success'    => $counts['login_success']  ?? 0,
            'total_failed'     => $counts['login_failed']   ?? 0,
            'total_locked'     => $counts['login_locked']   ?? 0,
            'total_suspicious' => $counts['suspicious_activity'] ?? 0,
            'total_mfa_success'=> $counts['mfa_success']    ?? 0,
            'total_mfa_failed' => $counts['mfa_failed']     ?? 0,
            'total_logout'     => $counts['logout']         ?? 0,
            'active_sessions'  => $activeSessions,
            'sessions_total'   => $sessionStats['total']    ?? 0,
            'sessions_revoked' => $sessionStats['revoked']  ?? 0,
            'alert_count'      => $alertCount,
            'events'           => $events,
            'chart_labels'     => $chartLabels,
            'chart_success'    => $chartSuccess,
            'chart_failed'     => $chartFailed,
            'since'            => $since,
        ];
    }
}
