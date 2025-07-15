<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Patient;
use App\Models\Office;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['professional.specialty', 'patient', 'office', 'createdBy']);

        // Filtro por fecha (por defecto hoy y próximos 7 días)
        $startDate = $request->get('start_date', today()->format('Y-m-d'));
        $endDate = $request->get('end_date', today()->addDays(7)->format('Y-m-d'));
        
        $query->whereBetween('appointment_date', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);

        // Filtro por profesional
        if ($request->filled('professional_id')) {
            $query->where('professional_id', $request->professional_id);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date')->get();

        // Cargar datos para filtros
        $professionals = Professional::active()->with('specialty')->orderBy('last_name')->get();
        $patients = Patient::active()->orderBy('last_name')->get();
        $offices = Office::active()->orderBy('number')->get();

        return Inertia::render('appointments/index', [
            'appointments' => $appointments,
            'professionals' => $professionals,
            'patients' => $patients,
            'offices' => $offices,
            'filters' => $request->only(['start_date', 'end_date', 'professional_id', 'status'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date|after:now',
            'duration' => 'required|string|in:00:15:00,00:30:00,00:45:00,01:00:00,01:30:00,02:00:00',
            'office_id' => 'nullable|exists:offices,id',
            'notes' => 'nullable|string|max:500',
            'amount' => 'nullable|numeric|min:0',
        ]);

        // Verificar disponibilidad del profesional
        $appointmentStart = Carbon::parse($validated['appointment_date']);
        $durationParts = explode(':', $validated['duration']);
        $appointmentEnd = $appointmentStart->copy()
            ->addHours((int)$durationParts[0])
            ->addMinutes((int)$durationParts[1]);

        $conflict = Appointment::where('professional_id', $validated['professional_id'])
            ->where('status', 'scheduled')
            ->where(function ($query) use ($appointmentStart, $appointmentEnd) {
                $query->whereBetween('appointment_date', [$appointmentStart, $appointmentEnd])
                    ->orWhere(function ($subQuery) use ($appointmentStart) {
                        $subQuery->where('appointment_date', '<=', $appointmentStart)
                            ->whereRaw('DATE_ADD(appointment_date, INTERVAL TIME_TO_SEC(duration) SECOND) > ?', [$appointmentStart]);
                    });
            })->exists();

        if ($conflict) {
            return back()->withErrors(['appointment_date' => 'El profesional ya tiene un turno en ese horario.']);
        }

        $validated['created_by'] = auth()->id();

        Appointment::create($validated);

        return back()->with('success', 'Turno creado exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        // Detectar si es solo cambio de estado
        if ($request->has('status') && count($request->all()) === 1) {
            $appointment->update([
                'status' => $request->status,
                'confirmed_at' => $request->status === 'attended' ? now() : null
            ]);

            return back()->with('success', 'Estado del turno actualizado.');
        }

        // Actualización completa
        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date',
            'duration' => 'required|string|in:00:15:00,00:30:00,00:45:00,01:00:00,01:30:00,02:00:00',
            'office_id' => 'nullable|exists:offices,id',
            'notes' => 'nullable|string|max:500',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:scheduled,attended,cancelled,absent',
        ]);

        // Verificar disponibilidad solo si cambió la fecha/hora o profesional
        if ($appointment->appointment_date != $validated['appointment_date'] || 
            $appointment->professional_id != $validated['professional_id']) {
            
            $appointmentStart = Carbon::parse($validated['appointment_date']);
            $durationParts = explode(':', $validated['duration']);
            $appointmentEnd = $appointmentStart->copy()
                ->addHours((int)$durationParts[0])
                ->addMinutes((int)$durationParts[1]);

            $conflict = Appointment::where('professional_id', $validated['professional_id'])
                ->where('id', '!=', $appointment->id)
                ->where('status', 'scheduled')
                ->where(function ($query) use ($appointmentStart, $appointmentEnd) {
                    $query->whereBetween('appointment_date', [$appointmentStart, $appointmentEnd])
                        ->orWhere(function ($subQuery) use ($appointmentStart) {
                            $subQuery->where('appointment_date', '<=', $appointmentStart)
                                ->whereRaw('DATE_ADD(appointment_date, INTERVAL TIME_TO_SEC(duration) SECOND) > ?', [$appointmentStart]);
                        });
                })->exists();

            if ($conflict) {
                return back()->withErrors(['appointment_date' => 'El profesional ya tiene un turno en ese horario.']);
            }
        }

        $appointment->update($validated);

        return back()->with('success', 'Turno actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        // Solo permitir cancelar turnos programados
        if ($appointment->status !== 'scheduled') {
            return back()->withErrors(['error' => 'Solo se pueden cancelar turnos programados.']);
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Turno cancelado exitosamente.');
    }

    /**
     * Get available time slots for a professional on a specific date
     */
    public function availableSlots(Request $request)
    {
        $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'date' => 'required|date',
            'duration' => 'required|string'
        ]);

        $professional = Professional::findOrFail($request->professional_id);
        $date = Carbon::parse($request->date);
        
        // Horarios de trabajo básicos (esto después se puede mejorar con el schedule del profesional)
        $workStart = $date->copy()->setTime(8, 0); // 8:00 AM
        $workEnd = $date->copy()->setTime(18, 0);  // 6:00 PM
        
        $durationParts = explode(':', $request->duration);
        $durationMinutes = ((int)$durationParts[0] * 60) + (int)$durationParts[1];
        
        // Obtener turnos existentes del día
        $existingAppointments = Appointment::where('professional_id', $request->professional_id)
            ->whereDate('appointment_date', $date)
            ->where('status', 'scheduled')
            ->orderBy('appointment_date')
            ->get();
        
        $availableSlots = [];
        $currentTime = $workStart->copy();
        
        while ($currentTime->copy()->addMinutes($durationMinutes)->lte($workEnd)) {
            $slotEnd = $currentTime->copy()->addMinutes($durationMinutes);
            
            // Verificar si el slot está libre
            $isAvailable = true;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentDuration = explode(':', $appointment->duration);
                $appointmentEnd = $appointmentStart->copy()
                    ->addHours((int)$appointmentDuration[0])
                    ->addMinutes((int)$appointmentDuration[1]);
                
                if ($currentTime->lt($appointmentEnd) && $slotEnd->gt($appointmentStart)) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($isAvailable) {
                $availableSlots[] = $currentTime->format('H:i');
            }
            
            $currentTime->addMinutes(30); // Slots cada 30 minutos
        }
        
        return response()->json($availableSlots);
    }
}