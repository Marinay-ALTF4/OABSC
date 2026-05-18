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

        // Check if announcements table exists
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

        $title   = trim((string) $this->request->getPost('title'));
        $content = trim((string) $this->request->getPost('content'));

        if (! $title || ! $content) {
            return redirect()->back()->with('error', 'Title and content are required.');
        }

        $db = \Config\Database::connect();

        // Create table if not exists
        if (! $db->tableExists('announcements')) {
            $db->query('CREATE TABLE announcements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                created_by INT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )');
        }

        $db->query(
            'INSERT INTO announcements (title, content, created_by) VALUES (?, ?, ?)',
            [$title, $content, (int) session('user_id')]
        );

        return redirect()->to('/admin/announcements')->with('success', 'Announcement posted successfully.');
    }

    public function delete(int $id)
    {
        if ($r = $this->ensureAdminAccess()) return $r;

        $db = \Config\Database::connect();
        if ($db->tableExists('announcements')) {
            $db->query('DELETE FROM announcements WHERE id = ?', [$id]);
        }

        return redirect()->to('/admin/announcements')->with('success', 'Announcement deleted.');
    }
}
