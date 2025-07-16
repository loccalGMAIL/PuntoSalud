<?php

namespace Database\Seeders;

use App\Models\Professional;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class ProfessionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunos profesionales específicos
        $professionals = [
            [
                'first_name' => 'Dr. Juan',
                'last_name' => 'Pérez García',
                'email' => 'juan.perez@clinic.com',
                'phone' => '+54 11 4567-8901',
                'license_number' => 'MP-001234',
                'specialty_id' => 1,
                'commission_percentage' => 15.00,
                'is_active' => true,
            ],
            [
                'first_name' => 'Dra. María',
                'last_name' => 'González López',
                'email' => 'maria.gonzalez@clinic.com',
                'phone' => '+54 11 4567-8902',
                'license_number' => 'MP-001235',
                'specialty_id' => 2,
                'commission_percentage' => 20.00,
                'is_active' => true,
            ],
            [
                'first_name' => 'Dr. Carlos',
                'last_name' => 'Martínez Silva',
                'email' => 'carlos.martinez@clinic.com',
                'phone' => '+54 11 4567-8903',
                'license_number' => 'MP-001236',
                'specialty_id' => 3,
                'commission_percentage' => 18.50,
                'is_active' => true,
            ],
            [
                'first_name' => 'Dra. Ana',
                'last_name' => 'Rodríguez Méndez',
                'email' => 'ana.rodriguez@clinic.com',
                'phone' => '+54 11 4567-8904',
                'license_number' => 'MP-001237',
                'specialty_id' => 1,
                'commission_percentage' => 22.00,
                'is_active' => true,
            ],
            [
                'first_name' => 'Dr. Luis',
                'last_name' => 'Fernández Castro',
                'email' => 'luis.fernandez@clinic.com',
                'phone' => '+54 11 4567-8905',
                'license_number' => 'MP-001238',
                'specialty_id' => 2,
                'commission_percentage' => 17.50,
                'is_active' => false,
            ]
        ];

        foreach ($professionals as $professional) {
            Professional::create($professional);
        }
    }
}