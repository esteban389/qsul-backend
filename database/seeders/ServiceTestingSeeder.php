<?php

namespace Database\Seeders;

use App\Models\Process;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceTestingSeeder extends Seeder
{
    private int $count = 5;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Process::all()->each(function (Process $process) {
            Service::factory()
                ->count($this->count)
                ->create([
                    'process_id' => $process->id,
                ]);
        });
    }

    public function setCount(int $count): self
    {
        $this->count = $count;
        return $this;
    }
}
