<?php

use App\DTOs\Auth\UserRole;
use App\Models\Process;
use App\Models\Question;
use App\Models\Service;
use App\Models\Survey;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->process = Process::factory()->create();
    $this->service = Service::factory()->create(['process_id' => $this->process->id]);
    Service::factory()->create(['process_id' => $this->process->id]);
    $this->survey = Survey::factory()->create();
    $this->question = Question::factory()->create(['survey_id' => $this->survey->id, 'service_id' => $this->service->id]);
    $this->question2 = Question::factory()->create(['survey_id' => $this->survey->id]);
});


test('Anyone can get current survey', function () {
    $response = $this->get('/api/survey');
    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson([
        'version' => $this->survey->version,
        'questions' => [
            [
                'text' => $this->question->text,
                'type' => $this->question->type,
                'order' => $this->question->order,
            ],
            [
                'text' => $this->question2->text,
                'type' => $this->question2->type,
                'order' => $this->question2->order,
            ],
        ]
    ]);
});

test('National coordinator can create a new version of the survey', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $question = [
        'text' => 'What is your name?',
        'type' => 'radio',
        'order' => '1',
    ];
    $response = $this->post('/api/survey', [
        'questions' => [$question],
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('surveys', ['version' => $this->survey->version + 1]);
    $this->assertDatabaseHas('questions', $question);
});

test('Non national coordinator cannot create a new version of the survey', function () {
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create();
    $this->actingAs($user);
    $question = [
        'text' => 'What is your name?',
        'type' => 'radio',
        'order' => '1',
    ];
    $response = $this->post('/api/survey', [
        'questions' => [$question],
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('Service based questions can be keep', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $question = [
        'text' => 'What is your name?',
        'type' => 'radio',
        'order' => '1',
    ];
    $response = $this->post('/api/survey', [
        'questions' => [$question],
        'keep_service_questions' => true,
    ]);

    $response->assertStatus(Response::HTTP_CREATED);

    // Assert version
    $response->assertJsonPath('version', $this->survey->version + 1);

    // Assert each question exists in response regardless of order
    $response->assertJsonFragment([
        'text' => $this->question->text,
        'type' => $this->question->type,
        'order' => $this->question->order,
    ]);

    $response->assertJsonFragment([
        'text' => $question['text'],
        'type' => $question['type'],
        'order' => 'B'.$question['order'],
    ]);
});


test('Service based questions can be removed', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->delete('/api/survey/questions/' . $this->question->id);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertSoftDeleted($this->question);
});

test('Non service based questions cannot be removed', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $response = $this->delete('/api/survey/questions/' . $this->question2->id);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('Service based questions can be updated', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $question = [
        'text' => 'What is your name?',
        'type' => 'radio',
        'order' => '1',
    ];
    $response = $this->post('/api/survey/questions/' . $this->question->id, $question);

    $response->assertStatus(Response::HTTP_OK);
    $this->assertDatabaseHas('questions', $question);
});

test('Non service based questions cannot be updated', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $question = [
        'text' => 'What is your name?',
        'type' => 'radio',
        'order' => '1',
    ];
    $response = $this->post('/api/survey/questions/' . $this->question2->id, $question);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('National coordinator can create service based questions', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $question = [
        'text' => 'What is your name?',
        'type' => 'radio',
        'order' => '1',
        'service_id' => $this->service->id,
    ];
    $response = $this->post('/api/survey/questions/service', $question);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('questions', $question);
});

test('National coordinator can get all survey versions', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $response = $this->get('/api/survey/versions');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson([
        [
            'version' => $this->survey->version,
            'questions' => [
                [
                    'text' => $this->question->text,
                    'type' => $this->question->type,
                    'order' => $this->question->order,
                ],
                [
                    'text' => $this->question2->text,
                    'type' => $this->question2->type,
                    'order' => $this->question2->order,
                ],
            ]
        ]
    ]);
});

test('National coordinator can get a survey version by version number', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $response = $this->get('/api/survey/versions/' . $this->survey->version);

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson([
        'version' => $this->survey->version,
        'questions' => [
            [
                'text' => $this->question->text,
                'type' => $this->question->type,
                'order' => $this->question->order,
            ],
            [
                'text' => $this->question2->text,
                'type' => $this->question2->type,
                'order' => $this->question2->order,
            ],
        ]
    ]);
});
