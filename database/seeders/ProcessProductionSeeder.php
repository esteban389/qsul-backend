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
        Process::factory()->create([
            'name' => 'Docencia',
            'icon' => '',
        ]);
        Process::factory()->create([
            'name' => 'Proyección Social',
            'icon' => '',
        ]);
        Process::factory()->create([
            'name' => 'Internacionalización',
            'icon' => '',
        ]);
        Process::factory()->create([
            'name' => 'Investigación',
            'icon' => '',
        ]);
        Process::factory()->create([
            'name' => 'Bienestar Universitario',
            'icon' => '',
        ]);
    }
}
