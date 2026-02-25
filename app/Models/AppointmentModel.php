<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table            = 'appointments';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'user_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'reason',
        'notes',
        'status'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getAppointmentsWithDetails($userId)
    {
        return $this->select('appointments.*, doctors.name as doctor_name, doctors.specialization')
            ->join('doctors', 'doctors.id = appointments.doctor_id')
            ->where('appointments.user_id', $userId)
            ->orderBy('appointments.appointment_date', 'DESC')
            ->orderBy('appointments.appointment_time', 'DESC')
            ->findAll();
    }
}
