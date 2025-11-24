<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create([
            'nombre' => 'Activo',
            'descripcion' => 'Estatus activo en el sistema.',
        ]);

        Status::create([
            'nombre' => 'Baja',
            'descripcion' => 'Estatus dado de baja en el sistema.',
        ]);
    }
}
