<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Process;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SurveyProductionSeeder::class);
        if (App::environment('prod')) {
            $this->call(UserProductionSeeder::class);
            $this->call(ProcessProductionSeeder::class);
        }
        if (App::environment('local') || App::environment('dev')) {
            User::factory()->create([
                'name' => 'Esteban Andrés Murcia Acuña',
                'email' => 'estebana.murciaa@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'national_coordinator',
            ]);
            Campus::factory()->create([
                'id' => 1,
                'name' => 'Bogotá',
                'address' => 'Carrera 1 # 1-1'
            ]);
            Campus::factory()->create([
                'id' => 2,
                'name' => 'Cúcuta',
                'address' => 'Calle 1 # 1-1'
            ]);
            $this->call(ProcessTestingSeeder::class);
            $processes = Process::all();
            // Create 6 services for each process
            foreach ($processes as $process) {
                Service::factory()->count(6)->create([
                    'process_id' => $process->id,
                ]);
            }
        }
    }
}
