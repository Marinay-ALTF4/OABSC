<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'name',
        'email',
        'password_hash',
        'role',
        'profile_photo',
        'specialization',
        'experience',
        'degree',
        'bio',
        'deleted_at',
    ];
    
    protected $useTimestamps = true;
}

