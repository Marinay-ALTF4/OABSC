<?php

namespace App\Controllers;

use App\Models\LoginEventModel;

class AuditReport extends BaseController
{
    private function ensureAdminAccess()
    {
        if (! session()->get('isLoggedIn') || ! in_array(session('user_role'), ['admin', 'assistant_admin'], true)) {
            return redirect()->to('/dashboard');
        }
        return null;
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
                $e['email_attempted'] ?? '',
                $e['reason_code'] ?? '',
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
