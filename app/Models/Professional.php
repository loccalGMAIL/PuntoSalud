<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'license_number',
        'specialty_id',
        'commission_percentage',
        'is_active',
        'schedule'
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'schedule' => 'array'
    ];

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}