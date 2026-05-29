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

    /**
     * Count pending appointments assigned to a doctor (by id and/or display name).
     */
    public function countPendingForDoctor(int $doctorId, string $doctorName = ''): int
    {
        if ($doctorId <= 0 && $doctorName === '') {
            return 0;
        }

        $db            = \Config\Database::connect();
        $hasDoctorId   = $db->fieldExists('doctor_id', 'appointments');
        $hasDoctorName = $db->fieldExists('doctor_name', 'appointments');
        $hasArchived   = $db->fieldExists('archived_at', 'appointments');

        $builder = $this->where('status', 'pending');
        if ($hasArchived) {
            $builder = $builder->where('archived_at IS NULL');
        }

        if ($hasDoctorId && $doctorId > 0 && $hasDoctorName && $doctorName !== '') {
            return $builder->groupStart()
                ->where('doctor_id', $doctorId)
                ->orWhere('doctor_name', $doctorName)
                ->groupEnd()
                ->countAllResults();
        }

        if ($hasDoctorId && $doctorId > 0) {
            return $builder->where('doctor_id', $doctorId)->countAllResults();
        }

        if ($hasDoctorName && $doctorName !== '') {
            return $builder->where('doctor_name', $doctorName)->countAllResults();
        }

        return 0;
    }

    /**
     * Statuses that reserve a doctor/date/time slot (cancelled/completed do not).
     */
    public function slotBlockingStatuses(): array
    {
        return ['pending', 'approved', 'confirmed'];
    }

    /**
     * Slots currently booked for the booking calendar (excludes cancelled/completed).
     */
    public function getBookedSlotsForCalendar(): array
    {
        $db            = \Config\Database::connect();
        $hasDoctorId   = $db->fieldExists('doctor_id', 'appointments');
        $hasDoctorName = $db->fieldExists('doctor_name', 'appointments');
        $hasArchived   = $db->fieldExists('archived_at', 'appointments');

        $select = ['appointment_date', 'appointment_time', 'status'];
        if ($hasDoctorId) {
            $select[] = 'doctor_id';
        }
        if ($hasDoctorName) {
            $select[] = 'doctor_name';
        }

        $builder = $this->select(implode(', ', $select))
            ->where('appointment_date >=', date('Y-m-d'))
            ->whereIn('status', $this->slotBlockingStatuses());

        if ($hasArchived) {
            $builder->where('archived_at IS NULL');
        }

        $rows = $builder->findAll();

        if ($hasDoctorId && ! $hasDoctorName) {
            $nameMap = $this->doctorDisplayNameMap(
                array_values(array_unique(array_filter(array_map(
                    static fn ($r) => (int) ($r['doctor_id'] ?? 0),
                    $rows
                ))))
            );

            foreach ($rows as &$row) {
                $row['doctor_name'] = $nameMap[(int) ($row['doctor_id'] ?? 0)] ?? '';
            }
            unset($row);
        }

        foreach ($rows as &$row) {
            $row['appointment_time'] = substr((string) ($row['appointment_time'] ?? ''), 0, 5);
        }
        unset($row);

        return $rows;
    }

    /**
     * Whether an active appointment already occupies this doctor slot.
     */
    public function isSlotTaken(int $doctorId, string $date, string $time, string $doctorName = ''): bool
    {
        return $this->countActiveBookingsForSlot($doctorId, $date, $time, $doctorName) > 0;
    }

    public function countActiveBookingsForSlot(int $doctorId, string $date, string $time, string $doctorName = ''): int
    {
        $db            = \Config\Database::connect();
        $hasDoctorId   = $db->fieldExists('doctor_id', 'appointments');
        $hasDoctorName = $db->fieldExists('doctor_name', 'appointments');
        $hasArchived   = $db->fieldExists('archived_at', 'appointments');
        $timeHHMM      = substr((string) $time, 0, 5);

        $builder = $this->where('appointment_date', $date)
            ->whereIn('status', $this->slotBlockingStatuses())
            ->groupStart()
                ->where('appointment_time', $timeHHMM)
                ->orWhere('appointment_time', $timeHHMM . ':00')
            ->groupEnd();

        if ($hasArchived) {
            $builder->where('archived_at IS NULL');
        }

        if ($hasDoctorId && $doctorId > 0 && $hasDoctorName && $doctorName !== '') {
            return $builder->groupStart()
                ->where('doctor_id', $doctorId)
                ->orWhere('doctor_name', $doctorName)
                ->groupEnd()
                ->countAllResults();
        }

        if ($hasDoctorId && $doctorId > 0) {
            return $builder->where('doctor_id', $doctorId)->countAllResults();
        }

        if ($hasDoctorName && $doctorName !== '') {
            return $builder->where('doctor_name', $doctorName)->countAllResults();
        }

        return 0;
    }

    /**
     * @param list<int> $doctorIds
     *
     * @return array<int, string>
     */
    private function doctorDisplayNameMap(array $doctorIds): array
    {
        $doctorIds = array_values(array_unique(array_filter(array_map('intval', $doctorIds))));
        if ($doctorIds === []) {
            return [];
        }

        $db           = \Config\Database::connect();
        $placeholders = implode(',', array_fill(0, count($doctorIds), '?'));
        $result       = $db->query(
            "SELECT u.id, COALESCE(up.name, u.username, '') AS name
             FROM users u
             LEFT JOIN user_profiles up ON up.user_id = u.id
             WHERE u.id IN ({$placeholders})",
            $doctorIds
        )->getResultArray();

        $map = [];
        foreach ($result as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $map[(int) $row['id']] = 'Dr. ' . $name;
            }
        }

        return $map;
    }
}
