<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcessProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Process::query()->create([
            'name' => 'DOCENCIA',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'PROYECCIÓN SOCIAL',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'INTERNACIONALIZACIÓN',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'INVESTIGACIÓN',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'BIENESTAR UNIVERSITARIO',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'DIRECCIÓN ESTRATÉGICA',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'ASEGURAMIENTO DE LA CALIDAD',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN HUMANA',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN FINANCIERA',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN DE LA BIBLIOTECA',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN DOCUMENTAL',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN INFORMÁTICA',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN DE LA AUDITORÍA INTERNA',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN DE SERVICIOS GENERALES',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN DE ADMINISIÓN Y REGISTROS',
            'icon' => '',
        ]);
        Process::query()->create([
            'name' => 'GESTIÓN DE ADQUISICIONES Y SUMINISTROS',
            'icon' => '',
        ]);
    }
}
