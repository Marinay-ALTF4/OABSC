<?php

namespace App\Controllers;

class Announcements extends BaseController
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

        $db            = \Config\Database::connect();
        $announcements = [];

        if ($db->tableExists('announcements')) {
            $announcements = $db->query(
                'SELECT * FROM announcements ORDER BY created_at DESC LIMIT 50'
            )->getResultArray();
        }

        return view('admin/announcements', ['announcements' => $announcements]);
    }

    public function create()
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        if (! $this->request->is('post')) {
            return redirect()->to('/admin/announcements');
        }

        $title           = trim((string) $this->request->getPost('title'));
        $body            = trim((string) ($this->request->getPost('body') ?: $this->request->getPost('content')));
        $type            = (string) $this->request->getPost('type') ?: 'info';
        $targetDashboard = (string) $this->request->getPost('target_dashboard') ?: 'all';

        if (! $title || ! $body) {
            return redirect()->back()->with('error', 'Title and message/content are required.');
        }

        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $db->query(
            "INSERT INTO announcements (title, body, type, target_dashboard, created_by, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$title, $body, $type, $targetDashboard, (int) session('user_id'), $now, $now]
        );
        $announcementId = $db->insertID();

        // Send notifications to matched users
        $userModel = new \App\Models\UserModel();
        $builder = $userModel->where('deleted_at IS NULL');
        
        if ($targetDashboard === 'admin') {
            $builder->whereIn('role', ['admin', 'assistant_admin']);
        } elseif ($targetDashboard !== 'all') {
            $builder->where('role', $targetDashboard);
        }

        $users = $builder->findAll();

        $notifModel = new \App\Models\NotificationModel();
        foreach ($users as $user) {
            $notificationId = $notifModel->insert([
                'user_id'         => (int) $user['id'],
                'title'           => 'New Announcement: ' . $title,
                'body'            => $body,
                'type'            => 'announcement',
                'is_read'         => 0,
            ], true);

            if ($notificationId) {
                $db->table('notification_announcement_links')->insert([
                    'notification_id' => (int) $notificationId,
                    'announcement_id' => (int) $announcementId,
                ]);
            }
        }

        return redirect()->to('/admin/announcements')->with('success', 'Announcement posted successfully.');
    }

    public function delete(int $id)
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        $db = \Config\Database::connect();
        if ($db->tableExists('announcements')) {
            $db->query('DELETE FROM announcements WHERE id = ?', [$id]);
        }
        $db->query(
            'DELETE n FROM notifications n INNER JOIN notification_announcement_links l ON l.notification_id = n.id WHERE l.announcement_id = ?',
            [$id]
        );

        return redirect()->to('/admin/announcements')->with('success', 'Announcement deleted.');
    }
}
