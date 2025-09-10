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
        Process::query()->firstOrCreate([
            'name' => 'DOCENCIA',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'PROYECCIÓN SOCIAL',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'INTERNACIONALIZACIÓN',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'INVESTIGACIÓN',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'BIENESTAR UNIVERSITARIO',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'DIRECCIÓN ESTRATÉGICA',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'ASEGURAMIENTO DE LA CALIDAD',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN HUMANA',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN FINANCIERA',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN DE LA BIBLIOTECA',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN DOCUMENTAL',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN INFORMÁTICA',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN DE LA AUDITORÍA INTERNA',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN DE SERVICIOS GENERALES',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN DE ADMINISIÓN Y REGISTROS',
        ],[
            'icon' => '',
        ]);
        Process::query()->firstOrCreate([
            'name' => 'GESTIÓN DE ADQUISICIONES Y SUMINISTROS',
        ],[
            'icon' => '',
        ]);
    }
}
