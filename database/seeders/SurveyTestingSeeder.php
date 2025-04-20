<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\RespondentType;
use App\Models\Survey;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SurveyTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $survey = Survey::factory()
            ->create([
                'version' => 1,
            ]);

        Question::factory()
            ->count(10)
            ->create([
                'survey_id' => $survey->id,
            ]);

        RespondentType::factory()->create();
    }
}
