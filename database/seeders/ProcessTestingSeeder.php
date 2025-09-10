<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcessTestingSeeder extends Seeder
{
    private int $count = 14;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Process::factory()
            ->count($this->count)
            ->create();
    }

    public function setCount(int $count): self
    {
        $this->count = $count;
        return $this;
    }
}
