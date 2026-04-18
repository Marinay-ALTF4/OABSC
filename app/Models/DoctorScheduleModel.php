<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorScheduleModel extends Model
{
    protected $table         = 'doctor_schedules';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['doctor_id', 'day', 'start_time', 'end_time', 'is_available'];
    protected $useTimestamps = true;

    public function getScheduleByDoctor(int $doctorId): array
    {
        return $this->where('doctor_id', $doctorId)->orderBy('FIELD(day, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday")', '', false)->findAll();
    }
}
