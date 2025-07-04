<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            ['number' => '1', 'name' => 'Consultorio 1'],
            ['number' => '2', 'name' => 'Consultorio 2'],
            ['number' => '3', 'name' => 'Consultorio 3'],
            ['number' => '4', 'name' => 'Consultorio 4'],
        ];

        foreach ($offices as $office) {
            Office::create($office);
        }
    }
}