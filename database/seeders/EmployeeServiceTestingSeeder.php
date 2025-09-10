<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeServiceTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $services = Service::query()
                ->where('process_id', $employee->process_id)
                ->get();
            foreach ($services as $service) {
                $employee->services()->attach($service->id);
            }
        }
    }
}
