<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'title', 'body', 'type', 'is_read'];
    protected $useTimestamps = true;

    public function getUnread(int $userId): array
    {
        return $this->where('user_id', $userId)->where('is_read', 0)->orderBy('created_at', 'DESC')->findAll();
    }

    public function getAll(int $userId): array
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->limit(20)->findAll();
    }

    public function markAllRead(int $userId): void
    {
        $this->where('user_id', $userId)->set('is_read', 1)->update();
    }

    public function send(int $userId, string $title, string $body, string $type = 'info'): void
    {
        $this->insert([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'is_read' => 0,
        ]);
    }
}
