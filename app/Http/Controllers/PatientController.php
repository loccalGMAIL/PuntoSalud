<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = Patient::orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return Inertia::render('patients/index', [
            'patients' => $patients
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:patients',
            'birth_date' => 'required|date|before:today',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'health_insurance' => 'nullable|string|max:255',
            'health_insurance_number' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string|max:1000',
        ]);

        Patient::create($validated);

        return back()->with('success', 'Paciente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        // Cargar citas y consultas del paciente
        $patient->load(['appointments.professional', 'consultations.professional']);
        
        return Inertia::render('Patients/Show', [
            'patient' => $patient
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        // Detectar si es solo toggle de estado o actualización completa
        if ($request->has('is_active') && count($request->all()) === 1) {
            // Solo cambiar el estado
            $patient->update([
                'is_active' => $request->boolean('is_active')
            ]);

            return back()->with('success', 'Estado del paciente actualizado.');
        }

        // Actualización completa del paciente
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:patients,dni,' . $patient->id,
            'birth_date' => 'required|date|before:today',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'health_insurance' => 'nullable|string|max:255',
            'health_insurance_number' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $patient->update($validated);

        return back()->with('success', 'Paciente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        // Verificar si tiene citas programadas
        if ($patient->appointments()->where('status', 'scheduled')->exists()) {
            return back()->withErrors(['error' => 'No se puede desactivar el paciente porque tiene turnos programados.']);
        }

        // Marcar como inactivo
        $patient->update(['is_active' => false]);

        return back()->with('success', 'Paciente desactivado exitosamente.');
    }
}