<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'professional_id',
        'patient_id',
        'consultation_date',
        'diagnosis',
        'treatment',
        'notes',
        'amount_charged',
        'professional_commission',
        'clinic_amount',
        'payment_status'
    ];

    protected $casts = [
        'consultation_date' => 'datetime',
        'amount_charged' => 'decimal:2',
        'professional_commission' => 'decimal:2',
        'clinic_amount' => 'decimal:2'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}