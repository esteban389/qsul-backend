<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => env('NATIONAL_COORDINATOR_NAME'),
            'email' => env('NATIONAL_COORDINATOR_EMAIL'),
            'password' => bcrypt(env('NATIONAL_COORDINATOR_PASSWORD')),
            'role'=> 'national_coordinator',
        ]);
    }
}
