<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Consultation;
use App\Models\Appointment;

class ConsultationsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los turnos que fueron atendidos
        $attendedAppointments = Appointment::where('status', 'attended')->get();

        foreach ($attendedAppointments as $appointment) {
            // Calcular comisiones
            $amountCharged = $appointment->amount;
            $professionalCommissionRate = $appointment->professional->commission_percentage / 100;
            $professionalCommission = $amountCharged * $professionalCommissionRate;
            $clinicAmount = $amountCharged - $professionalCommission;

            // Determinar estado de pago (80% pagado, 20% pendiente)
            $paymentStatus = rand(1, 10) <= 8 ? 'paid' : 'pending';

            Consultation::create([
                'appointment_id' => $appointment->id,
                'professional_id' => $appointment->professional_id,
                'patient_id' => $appointment->patient_id,
                'consultation_date' => $appointment->appointment_date,
                'diagnosis' => $this->generateDiagnosis($appointment->professional->specialty->name),
                'treatment' => $this->generateTreatment($appointment->professional->specialty->name),
                'notes' => $this->generateConsultationNotes(),