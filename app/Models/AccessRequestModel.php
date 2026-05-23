<?php

namespace App\Models;

use CodeIgniter\Model;

class AccessRequestModel extends Model
{
    protected $table         = 'access_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'requested_role', 'resource', 'status', 'permission_code'];
    protected $useTimestamps = true;

    public function getStatus(int $userId, string $resource): ?string
    {
        $row = $this->where('user_id', $userId)->where('resource', $resource)->orderBy('id', 'DESC')->first();
        return $row['status'] ?? null;
    }

    public function getStatusByPermission(int $userId, string $permissionCode): ?string
    {
        $row = $this->where('user_id', $userId)
                    ->where('permission_code', $permissionCode)
                    ->whereIn('status', ['pending', 'approved'])
                    ->orderBy('id', 'DESC')
                    ->first();
        return $row['status'] ?? null;
    }

    public function hasPending(int $userId, string $resource): bool
    {
        return $this->where('user_id', $userId)->where('resource', $resource)->where('status', 'pending')->countAllResults() > 0;
    }

    public function hasPendingPermission(int $userId, string $permissionCode): bool
    {
        return $this->where('user_id', $userId)
                    ->where('permission_code', $permissionCode)
                    ->where('status', 'pending')
                    ->countAllResults() > 0;
    }

    public function isApproved(int $userId, string $resource): bool
    {
        return $this->where('user_id', $userId)->where('resource', $resource)->where('status', 'approved')->countAllResults() > 0;
    }

    public function isPermissionApproved(int $userId, string $permissionCode): bool
    {
        return $this->where('user_id', $userId)
                    ->where('permission_code', $permissionCode)
                    ->where('status', 'approved')
                    ->countAllResults() > 0;
    }
}
