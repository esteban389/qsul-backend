<?php

use App\DTOs\Auth\UserRole;
use App\Models\Answer;
use App\Models\AnswerQuestion;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\Process;
use App\Models\Question;
use App\Models\RespondentType;
use App\Models\Service;
use App\Models\Survey;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->process = Process::factory()->create();
    $this->service = Service::factory()->create(['process_id' => $this->process->id]);
    $this->survey = Survey::factory()->create();
    $this->question = Question::factory()->create(['survey_id' => $this->survey->id, 'service_id' => $this->service->id]);
    $this->question2 = Question::factory()->create(['survey_id' => $this->survey->id]);

    $this->campus = Campus::factory()->create();
    $this->campus2 = Campus::factory()->create();
    $this->employee = Employee::factory()->create(['campus_id' => $this->campus->id, 'process_id' => $this->process->id]);
    $this->employee2 = Employee::factory()->create(['campus_id' => $this->campus2->id, 'process_id' => $this->process->id]);
    $this->employee->services()->attach($this->service->id);
    $this->employee2->services()->attach($this->service->id);
    $this->employeeService = $this->employee->services()->withPivot("id")->first()->pivot;
    $this->employeeService2 = $this->employee2->services()->withPivot('id')->first()->pivot;

    $this->respondentType = RespondentType::factory()->create();

    $this->answer = Answer::factory()->create(['employee_service_id' => $this->employeeService->id, 'survey_id' => $this->survey->id, 'respondent_type_id' => $this->respondentType->id]);

    $this->questionAnswer = AnswerQuestion::factory()->create(['question_id' => $this->question->id, 'answer_id' => $this->answer->id]);
    $this->questionAnswer2 = AnswerQuestion::factory()->create(['question_id' => $this->question2->id, 'answer_id' => $this->answer->id]);

    $this->answer2 = Answer::factory()->create(['employee_service_id' => $this->employeeService2->id, 'survey_id' => $this->survey->id, 'respondent_type_id' => $this->respondentType->id]);
    $this->questionAnswerCampus2 = AnswerQuestion::factory()->create(['question_id' => $this->question->id, 'answer_id' => $this->answer2->id]);
    $this->questionAnswer2Campus2 = AnswerQuestion::factory()->create(['question_id' => $this->question2->id, 'answer_id' => $this->answer2->id]);
});

test('National coordinator can get all results', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->get('/api/answers');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(2);
    $response->assertJson([
        [
            'survey_id' => $this->survey->id,
            'respondent_type_id' => $this->respondentType->id,
            'average' => $this->answer->average,
            'email' => $this->answer->email,
            'answer_questions' => [
                [
                    'question_id' => $this->question->id,
                    'answer' => $this->questionAnswer->answer,
                ],
                [
                    'question_id' => $this->question2->id,
                    'answer' => $this->questionAnswer2->answer,
                ],
            ]
        ],
        [
            'survey_id' => $this->survey->id,
            'respondent_type_id' => $this->respondentType->id,
            'average' => $this->answer2->average,
            'email' => $this->answer2->email,
            'answer_questions' => [
                [
                    'question_id' => $this->question->id,
                    'answer' => $this->questionAnswerCampus2->answer,
                ],
                [
                    'question_id' => $this->question2->id,
                    'answer' => $this->questionAnswer2Campus2->answer,
                ],
            ]
        ]
    ]);
});

test('Campus coordinator can only get the results of their campus', function () {
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create(['campus_id' => $this->campus->id]);
    $this->actingAs($user);

    $response = $this->get('/api/answers');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(1);
    $response->assertJson([
        [
            'survey_id' => $this->survey->id,
            'respondent_type_id' => $this->respondentType->id,
            'average' => $this->answer->average,
            'email' => $this->answer->email,
            'answer_questions' => [
                [
                    'question_id' => $this->question->id,
                    'answer' => $this->questionAnswer->answer,
                ],
                [
                    'question_id' => $this->question2->id,
                    'answer' => $this->questionAnswer2->answer,
                ],
            ]
        ]
    ]);
});

test('Process Leader can only get the results of their process inside their campus', function () {
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus->id, 'employee_id' => $this->employee->id]);
    $this->actingAs($user);

    $response = $this->get('/api/answers');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(1);
    $response->assertJson([
        [
            'survey_id' => $this->survey->id,
            'respondent_type_id' => $this->respondentType->id,
            'average' => $this->answer->average,
            'email' => $this->answer->email,
            'answer_questions' => [
                [
                    'question_id' => $this->question->id,
                    'answer' => $this->questionAnswer->answer,
                ],
                [
                    'question_id' => $this->question2->id,
                    'answer' => $this->questionAnswer2->answer,
                ],
            ]
        ]
    ]);
});

test('Results can be exported to csv', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->get('/api/answers/csv');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertHeader('Content-Type', 'text/csv');
    $now = \Carbon\Carbon::now()->format('Y-m-d');
    $response->assertHeader('Content-Disposition', 'attachment; filename=' . $now . '.csv');

    $csvContent = $response->streamedContent();
    $rows = array_map('str_getcsv', explode("\n", trim($csvContent)));
    $this->assertNotEmpty($rows);
    $this->assertCount(2, $rows);
});

test('National coordinator can add observation to answers', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->post('/api/answers/' . $this->answer->id . '/observations', [
        'description' => 'This is an observation',
        'type' => 'positive',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('observations', [
        'answer_id' => $this->answer->id,
        'description' => 'This is an observation',
        'type' => 'positive',
        'user_id' => $user->id,
    ]);
});

test('Campus coordinator can add observation to answers', function () {
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create(['campus_id' => $this->campus->id]);
    $this->actingAs($user);

    $response = $this->post('/api/answers/' . $this->answer->id . '/observations', [
        'description' => 'This is an observation',
        'type' => 'positive',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('observations', [
        'answer_id' => $this->answer->id,
        'description' => 'This is an observation',
        'type' => 'positive',
        'user_id' => $user->id,
    ]);
});

test('Campus coordinator cannot add observation to answers from other campus', function () {
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create(['campus_id' => $this->campus2->id]);
    $this->actingAs($user);

    $response = $this->post('/api/answers/' . $this->answer->id . '/observations', [
        'description' => 'This is an observation',
        'type' => 'positive',
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('Process leader can add observation to answers', function () {
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus->id, 'employee_id' => $this->employee->id]);
    $this->actingAs($user);

    $response = $this->post('/api/answers/' . $this->answer->id . '/observations', [
        'description' => 'This is an observation',
        'type' => 'positive',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('observations', [
        'answer_id' => $this->answer->id,
        'description' => 'This is an observation',
        'type' => 'positive',
        'user_id' => $user->id,
    ]);
});

test('Process leader cannot add observation to answers from other process', function () {
    $process2 = Process::factory()->create();
    $employee3 = Employee::factory()->create(['campus_id' => $this->campus->id, 'process_id' => $process2->id]);
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus->id, 'employee_id' => $employee3->id]);
    $this->actingAs($user);

    $response = $this->post('/api/answers/' . $this->answer->id . '/observations', [
        'description' => 'This is an observation',
        'type' => 'positive',
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('National coordinator can ignore a result', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->post('/api/answers/' . $this->answer->id . '/ignore', [
        'description' => 'This is an observation',
        'type' => 'negative',
    ]);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertSoftDeleted('answers', [
        'id' => $this->answer->id,
    ]);

    $this->assertDatabaseHas('observations', [
        'answer_id' => $this->answer->id,
        'description' => 'This is an observation',
        'type' => 'negative',
        'user_id' => $user->id,
    ]);
});

test('Anyone can answer the survey', function () {
    $nationalCoordinator = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $campusCoordinator = User::factory()->withRole(UserRole::CampusCoordinator)->create(['campus_id' => $this->campus->id]);
    $processLeader = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus->id, 'employee_id' => $this->employee->id]);

    $response = $this->post('/api/answers', [
        'version' => $this->survey->version,
        'respondent_type_id' => $this->respondentType->id,
        'email' => 'email@mail.com',
        'employee_service_id' => $this->employeeService->id,
        'answers' => [
            [
                'question_id' => $this->question->id,
                'answer' => 5,
            ],
            [
                'question_id' => $this->question2->id,
                'answer' => 4,
            ],
        ],
        'observation' => 'This is an observation',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('answers', [
        'respondent_type_id' => $this->respondentType->id,
        'employee_service_id' => $this->employeeService->id,
        'email' => 'email@mail.com',
        'average' => 4.5,
    ]);

    $this->assertDatabaseHas('answer_question', [
        'question_id' => $this->question->id,
        'answer' => 5,
    ]);

    $this->assertDatabaseHas('answer_question', [
        'question_id' => $this->question2->id,
        'answer' => 4,
    ]);

    $this->assertDatabaseHas('answer_observations', [
        'observation' => 'This is an observation',
    ]);
});

test('National coordinator can create respondent types', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->post('/api/respondent-types', [
        'name' => 'New respondent type',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('respondent_types', [
        'name' => 'New respondent type',
    ]);
});

test('National coordinator can delete respondent types', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->delete('/api/respondent-types/' . $this->respondentType->id);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertSoftDeleted('respondent_types', [
        'id' => $this->respondentType->id,
    ]);
});

test('Anyone can get all respondent types', function () {
    $response = $this->get('/api/respondent-types');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(1);
    $response->assertJson([
        [
            'id' => $this->respondentType->id,
            'name' => $this->respondentType->name,
        ]
    ]);
});
