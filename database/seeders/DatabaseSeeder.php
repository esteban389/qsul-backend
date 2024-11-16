<?php

namespace Database\Seeders;

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
        if(App::environment('prod')) {
            $this->call(UserProductionSeeder::class);
        }
        if (App::environment('local')){
            User::factory()->create([
                'name' => 'Esteban Andrés Murcia Acuña',
                'email' => 'estebana.murciaa@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'national_coordinator',
            ]);
        }
    }
}
