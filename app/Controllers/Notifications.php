<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    public function fetch()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([]);
        }

        $uid = (session('user_role') === 'assistant_admin' && session('assistant_user_id'))
            ? (int) session('assistant_user_id')
            : (int) session('user_id');

        $model = new NotificationModel();
        $notifications = $model->getAll($uid);

        $typeMap = [
            'appointment'  => ['icon' => 'bi-calendar-check', 'color' => '#10b981', 'bg' => '#f0fdf4'],
            'info'         => ['icon' => 'bi-info-circle',    'color' => '#3b82f6', 'bg' => '#eff6ff'],
            'warning'      => ['icon' => 'bi-exclamation-triangle', 'color' => '#f59e0b', 'bg' => '#fffbeb'],
            'error'        => ['icon' => 'bi-x-circle',       'color' => '#ef4444', 'bg' => '#fff1f2'],
            'request'      => ['icon' => 'bi-key',            'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
            'announcement' => ['icon' => 'bi-megaphone',      'color' => '#2a6a7e', 'bg' => '#e0f0ff'],
        ];

        $result = array_map(function ($n) use ($typeMap) {
            $style = $typeMap[$n['type']] ?? $typeMap['info'];
            return [
                'id'    => $n['id'],
                'type'  => $n['type'],
                'icon'  => $style['icon'],
                'color' => $style['color'],
                'bg'    => $style['bg'],
                'title' => $n['title'],
                'body'  => $n['body'],
                'time'  => date('M j, g:i A', strtotime($n['created_at'])),
                'read'  => (bool) $n['is_read'],
            ];
        }, $notifications);

        return $this->response->setJSON($result);
    }

    public function markAllRead()
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401);
        }
        $model = new NotificationModel();
        $uid   = (session('user_role') === 'assistant_admin' && session('assistant_user_id'))
            ? (int) session('assistant_user_id')
            : (int) session('user_id');
        $model->markAllRead($uid);
        return $this->response->setJSON(['success' => true]);
    }

    public function deleteOne(int $id)
    {
        if (! session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401);
        }
        $model = new NotificationModel();
        $notif = $model->find($id);
        $uid   = (session('user_role') === 'assistant_admin' && session('assistant_user_id'))
            ? (int) session('assistant_user_id')
            : (int) session('user_id');
        if ($notif && (int) $notif['user_id'] === $uid) {
            $model->delete($id);
        }
        return $this->response->setJSON(['success' => true]);
    }
}
