<?php

namespace Database\Seeders;

use App\Models\Process;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Process::all()->each(function (Process $process) {
            Service::factory()
                ->count(5)
                ->create([
                    'process_id' => $process->id,
                ]);
        });
    }
}
