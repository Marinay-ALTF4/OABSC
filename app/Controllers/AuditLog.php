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

        $events   = $logModel->getRecentEvents(200);
        $summary  = $logModel->getAuditSummary();
        $sessions = $logModel->getActiveSessions();
        $failed24 = $logModel->getFailedLoginsLast24h();
        $suspicious = $logModel->getSuspiciousCount();

        return view('admin/audit_log', [
            'events'     => $events,
            'summary'    => $summary,
            'sessions'   => $sessions,
            'failed24'   => $failed24,
            'suspicious' => $suspicious,
        ]);
    }
}
