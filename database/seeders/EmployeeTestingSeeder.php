<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Employee;
use App\Models\Process;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campuses = Campus::all();

        $processes = Process::all();

        foreach ($campuses as $campus) {

            foreach ($processes as $process) {
                Employee::factory()
                    ->count(5)
                    ->create([
                        'campus_id' => $campus->id,
                        'process_id' => $process->id,
                    ]);
            }
        }
    }
}
