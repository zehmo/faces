<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFee extends Model
{
    protected $fillable = [
        'student_id',
        'academic_session_id',
        'school_fees_paid',
        'school_fees_date_paid',
        'departmental_dues_paid',
        'faculty_dues_paid',
    ];

    protected $casts = [
        'school_fees_paid' => 'boolean',
        'school_fees_date_paid' => 'date',
        'departmental_dues_paid' => 'boolean',
        'faculty_dues_paid' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }
}
