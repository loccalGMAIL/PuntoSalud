<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_id',
        'patient_id',
        'office_id',
        'appointment_date',
        'duration',
        'status',
        'notes',
        'amount',
        'created_by',
        'confirmed_at'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'duration' => 'string',
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime'
    ];

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }
}