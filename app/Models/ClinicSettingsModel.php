<?php

namespace App\Models;

use CodeIgniter\Model;

class ClinicSettingsModel extends Model
{
    protected $table      = 'clinic_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['key', 'value'];
    protected $useTimestamps = true;

    public function getValue(string $key): ?string
    {
        $row = $this->where('key', $key)->first();
        return $row['value'] ?? null;
    }

    public function setValue(string $key, string $value): void
    {
        $existing = $this->where('key', $key)->first();
        if ($existing) {
            $this->where('key', $key)->set('value', $value)->update();
        } else {
            $this->insert(['key' => $key, 'value' => $value]);
        }
    }
}
