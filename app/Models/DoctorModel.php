<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorModel extends Model
{
    protected $table      = 'doctors';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'specialization',
        'email',
        'phone',
        'available',
    ];

    protected $useTimestamps = true;
}
