<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialties = Specialty::orderBy('name')->get();
        
        return response()->json($specialties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specialties',
            'description' => 'required|string|max:255',
        ]);

        $specialty = Specialty::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'is_active' => true
        ]);

        return back()->with('success', 'Especialidad creada exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Specialty $specialty)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specialties,name,' . $specialty->id,
            'description' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $specialty->update($validated);

        return back()->with('success', 'Especialidad actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specialty $specialty)
    {
        // Verificar si hay profesionales usando esta especialidad
        if ($specialty->professionals()->exists()) {
            return back()->withErrors(['error' => 'No se puede eliminar la especialidad porque tiene profesionales asociados.']);
        }

        $specialty->delete();

        return back()->with('success', 'Especialidad eliminada exitosamente.');
    }
}