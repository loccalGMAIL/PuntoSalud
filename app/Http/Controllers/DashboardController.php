<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Professional;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // Métricas principales
        $consultasHoy = $this->getConsultasDelDia($today);
        $ingresosHoy = $this->getIngresosDelDia($today);
        $profesionalesActivos = $this->getProfesionalesActivos();
        
        // Datos para el dashboard
        $consultasDetalle = $this->getConsultasDetalle($today);
        $resumenCaja = $this->getResumenCaja($today);

        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'consultasHoy' => $consultasHoy,
                'ingresosHoy' => $ingresosHoy,
                'profesionalesActivos' => $profesionalesActivos,
                'consultasDetalle' => $consultasDetalle,
                'resumenCaja' => $resumenCaja,
                'fecha' => $today->format('d/m/Y')
            ]
        ]);
    }

    private function getConsultasDelDia($fecha)
    {
        $turnos = Appointment::whereDate('appointment_date', $fecha)->get();
        
        return [
            'total' => $turnos->count(),
            'completadas' => $turnos->where('status', 'attended')->count(),
            'pendientes' => $turnos->whereIn('status', ['scheduled'])->count(),
            'canceladas' => $turnos->where('status', 'cancelled')->count()
        ];
    }

    private function getIngresosDelDia($fecha)
    {
        $consultas = Consultation::whereDate('consultation_date', $fecha)
            ->where('payment_status', 'paid')
            ->get();

        $total = $consultas->sum('amount_charged');
        
        // Por ahora asumimos 70% efectivo y 30% transferencia
        // Esto se puede mejorar cuando tengan una tabla de payments más detallada
        return [
            'total' => $total,
            'efectivo' => $total * 0.7,
            'transferencia' => $total * 0.3
        ];
    }

    private function getProfesionalesActivos()
    {
        $profesionales = Professional::where('is_active', true)->get();
        
        // Profesionales que tienen turnos hoy
        $conTurnosHoy = Professional::where('is_active', true)
            ->whereHas('appointments', function($query) {
                $query->whereDate('appointment_date', Carbon::today())
                      ->whereIn('status', ['scheduled', 'attended']);
            })
            ->count();

        // Profesionales actualmente en consulta (status = attended)
        $enConsulta = Professional::where('is_active', true)
            ->whereHas('appointments', function($query) {
                $query->whereDate('appointment_date', Carbon::today())
                      ->where('status', 'attended');
            })
            ->count();

        return [
            'total' => $conTurnosHoy,
            'enConsulta' => $enConsulta,
            'disponibles' => $conTurnosHoy - $enConsulta
        ];
    }

    private function getConsultasDetalle($fecha)
    {
        return Appointment::with(['professional', 'patient'])
            ->whereDate('appointment_date', $fecha)
            ->orderBy('appointment_date')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'paciente' => $appointment->patient->last_name . ', ' . $appointment->patient->first_name,
                    'profesional' => 'Dr. ' . $appointment->professional->first_name . ' ' . $appointment->professional->last_name,
                    'hora' => Carbon::parse($appointment->appointment_date)->format('H:i'),
                    'monto' => $appointment->amount ?? 0,
                    'status' => $appointment->status,
                    'statusLabel' => $this->getStatusLabel($appointment->status)
                ];
            });
    }

    private function getResumenCaja($fecha)
    {
        // Totales por profesional
        $porProfesional = Consultation::with('professional')
            ->whereDate('consultation_date', $fecha)
            ->where('payment_status', 'paid')
            ->get()
            ->groupBy('professional_id')
            ->map(function ($consultas, $professionalId) {
                $professional = $consultas->first()->professional;
                return [
                    'nombre' => 'Dr. ' . $professional->first_name . ' ' . $professional->last_name,
                    'total' => $consultas->sum('clinic_amount') // Monto que queda para la clínica
                ];
            })
            ->values();

        // Total general
        $totalGeneral = Consultation::whereDate('consultation_date', $fecha)
            ->where('payment_status', 'paid')
            ->sum('amount_charged');

        return [
            'porProfesional' => $porProfesional,
            'totalGeneral' => $totalGeneral,
            'formasPago' => [
                'efectivo' => $totalGeneral * 0.7,
                'transferencia' => $totalGeneral * 0.3
            ]
        ];
    }

    private function getStatusLabel($status)
    {
        return match($status) {
            'scheduled' => 'Programada',
            'attended' => 'Completada',
            'cancelled' => 'Cancelada',
            'absent' => 'Ausente',
            default => 'Desconocido'
        };
    }
}