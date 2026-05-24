<?php

namespace App\Controllers;

use App\Models\LoginEventModel;
use App\Models\UserModel;

class AuditLog extends BaseController
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

        $logModel = new LoginEventModel();
        $sessionModel = new \App\Models\AuthSessionModel();

        $events   = $logModel->getRecentEvents(200);
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
