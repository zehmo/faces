<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reg_number',
        'jamb_reg_number',
        'full_name',
        'date_of_birth',
        'sex',
        'marital_status',
        'state_id',
        'lga_id',
        'town',
        'phone_number',
        'email',
        'department_id',
        'level',
        'photo_filename',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function currentFee(): ?StudentFee
    {
        $session = AcademicSession::current();
        if (!$session) return null;
        return $this->fees()->where('academic_session_id', $session->id)->first();
    }
}
