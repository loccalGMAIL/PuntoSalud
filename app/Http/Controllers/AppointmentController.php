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
     * Lista de turnos con filtros
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['professional.specialty', 'patient', 'office']);

        // Filtros de fecha (por defecto: hoy y próximos 7 días)
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

        // Búsqueda por paciente
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        $appointments = $query->orderBy('appointment_date', 'asc')->get();

        // Datos para filtros y formularios
        $professionals = Professional::where('is_active', true)->with('specialty')->orderBy('last_name')->get();
        $patients = Patient::where('is_active', true)->orderBy('last_name')->get();
        $offices = Office::where('is_active', true)->orderBy('number')->get();

        return Inertia::render('appointments/index', [
            'appointments' => $appointments,
            'professionals' => $professionals,
            'patients' => $patients,
            'offices' => $offices,
            'filters' => $request->only(['start_date', 'end_date', 'professional_id', 'status', 'search']),
            'stats' => $this->getStats($appointments)
        ]);
    }

    /**
     * Crear nuevo turno
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'duration' => 'required|integer|min:15|max:120',
            'office_id' => 'nullable|exists:offices,id',
            'notes' => 'nullable|string|max:500',
            'amount' => 'nullable|numeric|min:0',
        ]);

        // Crear fecha y hora completa
        $appointmentDateTime = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);

        // Validación básica de disponibilidad
        $existingAppointment = Appointment::where('professional_id', $validated['professional_id'])
            ->where('appointment_date', $appointmentDateTime)
            ->where('status', 'scheduled')
            ->first();

        if ($existingAppointment) {
            return redirect()->back()->withErrors(['appointment_time' => 'El profesional ya tiene un turno en ese horario.']);
        }

        // Crear turno
        Appointment::create([
            'professional_id' => $validated['professional_id'],
            'patient_id' => $validated['patient_id'],
            'appointment_date' => $appointmentDateTime,
            'duration' => $this->formatDuration($validated['duration']),
            'office_id' => $validated['office_id'],
            'notes' => $validated['notes'],
            'amount' => $validated['amount'],
            'status' => 'scheduled',
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Turno creado exitosamente.');
    }

    /**
     * Mostrar detalle del turno
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['professional.specialty', 'patient', 'office']);

        return Inertia::render('appointments/show', [
            'appointment' => $appointment
        ]);
    }

    /**
     * Actualizar turno existente
     */
    public function update(Request $request, Appointment $appointment)
    {
        // Si es solo cambio de estado
        if ($request->has('status') && count($request->only(['status', '_token', '_method'])) <= 3) {
            $appointment->update([
                'status' => $request->status,
                'confirmed_at' => $request->status === 'attended' ? now() : null
            ]);

            return redirect()->back()->with('success', 'Estado del turno actualizado.');
        }

        // Actualización completa
        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|string',
            'duration' => 'required|integer|min:15|max:120',
            'office_id' => 'nullable|exists:offices,id',
            'notes' => 'nullable|string|max:500',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:scheduled,attended,cancelled,absent',
        ]);

        $appointmentDateTime = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);

        // Validación básica de disponibilidad (si cambió la fecha/hora)
        if ($appointment->appointment_date->format('Y-m-d H:i') !== $appointmentDateTime->format('Y-m-d H:i')) {
            $existingAppointment = Appointment::where('professional_id', $validated['professional_id'])
                ->where('appointment_date', $appointmentDateTime)
                ->where('status', 'scheduled')
                ->where('id', '!=', $appointment->id)
                ->first();

            if ($existingAppointment) {
                return redirect()->back()->withErrors(['appointment_time' => 'El profesional ya tiene un turno en ese horario.']);
            }
        }

        $appointment->update([
            'professional_id' => $validated['professional_id'],
            'patient_id' => $validated['patient_id'],
            'appointment_date' => $appointmentDateTime,
            'duration' => $this->formatDuration($validated['duration']),
            'office_id' => $validated['office_id'],
            'notes' => $validated['notes'],
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'confirmed_at' => $validated['status'] === 'attended' ? now() : $appointment->confirmed_at,
        ]);

        return redirect()->back()->with('success', 'Turno actualizado exitosamente.');
    }

    /**
     * Cancelar turno
     */
    public function destroy(Appointment $appointment)
    {
        if ($appointment->status !== 'scheduled') {
            return redirect()->back()->withErrors(['error' => 'Solo se pueden cancelar turnos programados.']);
        }

        $appointment->update([
            'status' => 'cancelled',
            'confirmed_at' => null
        ]);

        return redirect()->back()->with('success', 'Turno cancelado exitosamente.');
    }

    /**
     * Obtener horarios disponibles para un profesional
     */
    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'professional_id' => 'required|exists:professionals,id',
            'date' => 'required|date',
            'duration' => 'required|integer|min:15|max:120'
        ]);

        $slots = [];
        $date = Carbon::parse($validated['date']);
        
        // No generar slots para fechas pasadas o fines de semana
        if ($date->isPast() || $date->isWeekend()) {
            return response()->json($slots);
        }

        // Obtener turnos existentes del día
        $existingAppointments = Appointment::where('professional_id', $validated['professional_id'])
            ->whereDate('appointment_date', $date)
            ->where('status', 'scheduled')
            ->get();

        // Generar slots de 8:00 a 18:00 cada 30 minutos
        $currentTime = $date->copy()->setTime(8, 0);
        $endTime = $date->copy()->setTime(18, 0);
        $duration = $validated['duration'];

        while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);
            
            // Verificar si el slot está libre
            $isAvailable = true;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->appointment_date);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($this->durationToMinutes($appointment->duration));
                
                if ($currentTime->lt($appointmentEnd) && $slotEnd->gt($appointmentStart)) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($isAvailable) {
                $slots[] = $currentTime->format('H:i');
            }
            
            $currentTime->addMinutes(30);
        }

        return response()->json($slots);
    }

    /**
     * Obtener estadísticas
     */
    private function getStats($appointments)
    {
        return [
            'total' => $appointments->count(),
            'scheduled' => $appointments->where('status', 'scheduled')->count(),
            'attended' => $appointments->where('status', 'attended')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'absent' => $appointments->where('status', 'absent')->count(),
        ];
    }

    /**
     * Convertir duración a minutos
     */
    private function durationToMinutes($duration)
    {
        if (is_numeric($duration)) {
            return (int) $duration;
        }

        $parts = explode(':', $duration);
        return ((int) $parts[0] * 60) + ((int) $parts[1]);
    }

    /**
     * Formatear duración para la base de datos
     */
    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d:00', $hours, $mins);
    }
}