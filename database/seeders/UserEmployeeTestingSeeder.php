<?php

namespace Database\Seeders;

use App\DTOs\Auth\UserRole;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\Process;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserEmployeeTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campuses = Campus::all();
        $processes = Process::all();

        foreach ($campuses as $campus) {
            // Create Campus Coordinator
            $this->createUserWithEmployee(UserRole::CampusCoordinator, $campus);

            foreach ($processes as $process) {
                // Create Process Leader per Campus & Process
                $this->createUserWithEmployee(UserRole::ProcessLeader, $campus, $process);
            }
        }
    }

    private function createEmployee(Campus $campus, ?Process $process = null): Employee
    {
        return Employee::factory()->create([
            'campus_id' => $campus->id,
            'process_id' => $process?->id,
        ]);
    }

    private function createUserWithEmployee(UserRole $role, Campus $campus, Process $process = null): void
    {
        $employee = $this->createEmployee($campus, $process);

        User::factory()->create([
            'name' => $employee->name,
            'email' => $employee->email,
            'password' => bcrypt('password'),
            'role' => $role,
            'employee_id' => $employee->id,
            'campus_id' => $campus->id,
        ]);
    }

}
