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
    Service::factory()->create(['process_id' => $this->process->id]);
    $this->survey = Survey::factory()->create();
    $question = Question::factory()->create(['survey_id' => $this->survey->id, 'service_id' => $this->service->id]);
    $question2 = Question::factory()->create(['survey_id' => $this->survey->id]);

    $campus = Campus::factory()->create();
    $campus2 = Campus::factory()->create();
    $employee = Employee::factory()->create(['campus_id' => $campus->id]);
    $employee2 = Employee::factory()->create(['campus_id' => $campus2->id]);
    $service = Service::factory()->create(['process_id' => $this->process->id]);
    $employee->services()->attach($service->id);
    $employee2->services()->attach($service->id);
    $employeeService = $employee->services()->first();
    $employeeService2 = $employee2->services()->first();

    $respondentType = RespondentType::factory()->create();

    $answer = Answer::factory()->create(['employee_service_id' => $employeeService->id, 'survey_id' => $this->survey->id, 'respondent_type_id' => $respondentType->id]);
    $questionAnswer = AnswerQuestion::factory()->create(['question_id' => $question->id, 'answer_id' => $answer->id]);
    $questionAnswer2 = AnswerQuestion::factory()->create(['question_id' => $question2->id, 'answer_id' => $answer->id]);

    $answer2 = Answer::factory()->create(['employee_service_id' => $employeeService2->id, 'survey_id' => $this->survey->id, 'respondent_type_id' => $respondentType->id]);
    $questionAnswerCampus2 = AnswerQuestion::factory()->create(['question_id' => $question->id, 'answer_id' => $answer2->id]);
    $questionAnswerCampus22 = AnswerQuestion::factory()->create(['question_id' => $question2->id, 'answer_id' => $answer2->id]);
});

test('National coordinator can get all results', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
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
            'answers' => [
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
            'answers' => [
                [
                    'question_id' => $this->question->id,
                    'answer' => $this->questionAnswerCampus2->answer,
                ],
                [
                    'question_id' => $this->question2->id,
                    'answer' => $this->questionAnswerCampus22->answer,
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
            'answers' => [
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
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus->id, 'process_id' => $this->process->id]);
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
            'answers' => [
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

test('National coordinator can add observation to answers', function (){
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->post('/api/answers/'.$this->answer->id. '/observation', [
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('observations', [
        'answer_id' => $this->answer->id,
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
        'user_id' => $user->id,
    ]);
});

test('Campus coordinator can add observation to answers', function (){
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create(['campus_id' => $this->campus->id]);
    $this->actingAs($user);

    $response = $this->post('/api/answers/'.$this->answer->id. '/observation', [
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('observations', [
        'answer_id' => $this->answer->id,
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
        'user_id' => $user->id,
    ]);
});

test('Campus coordinator cannot add observation to answers from other campus', function (){
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create(['campus_id' => $this->campus2->id]);
    $this->actingAs($user);

    $response = $this->post('/api/answers/'.$this->answer->id. '/observation', [
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('Process leader can add observation to answers', function (){
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus->id, 'process_id' => $this->process->id]);
    $this->actingAs($user);

    $response = $this->post('/api/answers/'.$this->answer->id. '/observation', [
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('observations', [
        'answer_id' => $this->answer->id,
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
        'user_id' => $user->id,
    ]);
});

test('Process leader cannot add observation to answers from other process', function (){
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus->id, 'process_id' => $this->process2->id]);
    $this->actingAs($user);

    $response = $this->post('/api/answers/'.$this->answer->id. '/observation', [
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('National coordinator can ignore a result', function (){
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->post('/api/answers/'.$this->answer->id. '/ignore', [
        'answer_id' => $this->answer->id,
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
        'user_id' => $user->id,
    ]);

    $response->assertStatus(Response::HTTP_OK);
    $this->assertSoftDeleted('answers', [
        'id' => $this->answer->id,
    ]);
    $this->assertDatabaseHas('answers', [
        'answer_id' => $this->answer->id,
        'text' => 'This is an observation',
        'type' => 'RESOLVED',
        'user_id' => $user->id,
    ]);
});
