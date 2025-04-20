<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampusTestingSeeder extends Seeder
{
    private int $count = 4;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Campus::factory()
            ->count($this->count)
            ->create();
    }
    public function setCount(int $count): CampusTestingSeeder
    {
        $this->count = $count;
        return $this;
    }
}
