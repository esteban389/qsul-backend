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
            $this->call(ProcessTestingSeeder::class);
            $this->call(ServiceTestingSeeder::class);
            $this->call(CampusTestingSeeder::class);
            $this->call(UserEmployeeTestingSeeder::class);
            $this->call(EmployeeServiceTestingSeeder::class);
        }
    }
}
