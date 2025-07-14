<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfessionalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $professionals = Professional::with('specialty')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $specialties = Specialty::active()->orderBy('name')->get(); // ⭐ Agregado

        return Inertia::render('professionals/index', [
            'professionals' => $professionals,
            'specialties' => $specialties  // ⭐ Agregado
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $specialties = Specialty::active()->orderBy('name')->get();

        return Inertia::render('Professionals/Create', [
            'specialties' => $specialties
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
            'email' => 'required|string|email|max:255|unique:professionals',
            'phone' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:255|unique:professionals',
            'specialty_id' => 'required|exists:specialties,id',
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        Professional::create($validated);

        return redirect()->route('professionals.index')
            ->with('success', 'Profesional creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Professional $professional)
    {
        $professional->load('specialty');
        
        return Inertia::render('Professionals/Show', [
            'professional' => $professional
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Professional $professional)
    {
        $specialties = Specialty::active()->orderBy('name')->get();

        return Inertia::render('Professionals/Edit', [
            'professional' => $professional,
            'specialties' => $specialties
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Professional $professional)
    {
        // ⭐ ARREGLO: Detectar si es solo toggle de estado o actualización completa
        if ($request->has('is_active') && count($request->all()) === 1) {
            // Solo cambiar el estado
            $professional->update([
                'is_active' => $request->boolean('is_active')
            ]);

            return back()->with('success', 'Estado del profesional actualizado.');
        }

        // Actualización completa del profesional
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:professionals,email,' . $professional->id,
            'phone' => 'nullable|string|max:255',
            'license_number' => 'required|string|max:255|unique:professionals,license_number,' . $professional->id,
            'specialty_id' => 'required|exists:specialties,id',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $professional->update($validated);

        return redirect()->route('professionals.index')
            ->with('success', 'Profesional actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professional $professional)
    {
        // En lugar de eliminar, marcamos como inactivo
        $professional->update(['is_active' => false]);

        return redirect()->route('professionals.index')
            ->with('success', 'Profesional desactivado exitosamente.');
    }
}