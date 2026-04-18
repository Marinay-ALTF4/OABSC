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
        $model->markAllRead((int) session('user_id'));
        return $this->response->setJSON(['success' => true]);
    }
}
