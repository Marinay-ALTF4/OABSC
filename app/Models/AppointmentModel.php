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
        'archived_at',
    ];

    protected $useTimestamps = true;

    public function getPending(): array
    {
        return $this->where('status', 'pending')->where('archived_at IS NULL')->orderBy('appointment_date', 'ASC')->findAll();
    }

    public function getConfirmed(): array
    {
        return $this->where('status', 'confirmed')->where('archived_at IS NULL')->orderBy('appointment_date', 'ASC')->findAll();
    }

    public function getArchived(): array
    {
        return $this->where('archived_at IS NOT NULL')->orderBy('archived_at', 'DESC')->findAll();
    }

    public function getActive(): array
    {
        return $this->where('archived_at IS NULL')->orderBy('appointment_date', 'DESC')->findAll();
    }
}
