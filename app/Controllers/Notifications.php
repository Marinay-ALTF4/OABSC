<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
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
