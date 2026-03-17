<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'client_id',
        'user_id',
        'doctor_id',
        'doctor_name',
        'appointment_date',
        'appointment_time',
        'reason',
        'status',
    ];

    protected $useTimestamps = true;
}
