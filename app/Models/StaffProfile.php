<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    protected $fillable = [
        'staff_type', 'employment_status', 'name_with_initials', 'nic_number', 'grade', 'designation',
        'permanent_address', 'contact_number', 'mobile_fixed', 'official_email',
        'date_of_birth', 'gender', 'w_op_number', 'highest_education_qualifications',
        'personal_file_number', 'first_appointment_date', 'photo_path', 'photo_name',
        'appointment_letter_path', 'appointment_letter_name', 'cv_path', 'cv_name', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'first_appointment_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
