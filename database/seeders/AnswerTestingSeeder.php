<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\AnswerQuestion;
use App\Models\EmployeeService;
use App\Models\RespondentType;
use App\Models\Survey;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnswerTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $survey = Survey::query()->with('questions')->latest()->first();
        if (!$survey) {
            $this->command->error('No survey found. Please create a survey first.');
            return;
        }

        $questions = $survey->questions;
        if ($questions->isEmpty()) {
            $this->command->error('The survey has no questions. Please add questions to the survey first.');
            return;
        }

        $employeeServices = EmployeeService::all();
        if ($employeeServices->isEmpty()) {
            $this->command->error('No employee services found. Please create employee services first.');
            return;
        }

        $respondentType = RespondentType::first();
        if (!$respondentType) {
            $this->command->error('No respondent type found. Please create a respondent type first.');
            return;
        }

        // Creating answers in chunks for better performance
        $this->command->info('Creating answers...');

        $createdAnswers = new Collection();

        DB::transaction(function () use ($questions, $employeeServices, $survey, $respondentType, &$createdAnswers) {

            foreach ($employeeServices as $employeeService) {
                // Create and save answers properly to get IDs
                $answers = Answer::factory()
                    ->count(2)
                    ->create([
                        'employee_service_id' => $employeeService->id,
                        'survey_id' => $survey->id,
                        'respondent_type_id' => $respondentType->id,
                    ]);

                $createdAnswers = $createdAnswers->merge($answers);
            }

            $this->command->info("Created {$createdAnswers->count()} answers.");

            // Create answer questions in chunks for better memory usage
            $this->command->info('Creating answer questions...');

            $totalAnswerQuestions = 0;

            // Process in smaller batches to avoid memory issues
            $createdAnswers->chunk(50)->each(function ($answerChunk) use ($questions, $survey, &$totalAnswerQuestions) {
                $answerQuestionData = [];

                foreach ($answerChunk as $answer) {
                    foreach ($questions as $question) {
                        // Generate the data for factory but don't create objects yet
                        $factoryData = AnswerQuestion::factory()->makeOne([
                            'answer_id' => $answer->id,
                            'question_id' => $question->id,
                        ])->toArray();

                        $answerQuestionData[] = $factoryData;
                    }
                }

                // Insert in chunks for better performance
                foreach (array_chunk($answerQuestionData, 100) as $chunk) {
                    AnswerQuestion::insert($chunk);
                    $totalAnswerQuestions += count($chunk);
                }
            });

            $this->command->info("Created {$totalAnswerQuestions} answer questions.");
        });
    }
}
