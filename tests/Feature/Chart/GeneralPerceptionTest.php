<?php

use App\DTOs\Auth\UserRole;
use App\Models\Campus;
use App\Models\Survey;
use App\Models\User;
use Database\Seeders\AnswerTestingSeeder;
use Database\Seeders\CampusTestingSeeder;
use Database\Seeders\EmployeeServiceTestingSeeder;
use Database\Seeders\ProcessTestingSeeder;
use Database\Seeders\ServiceTestingSeeder;
use Database\Seeders\SurveyTestingSeeder;
use Database\Seeders\UserEmployeeTestingSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setUpSeeders(): void
{
    // This will run before each test
    (new ProcessTestingSeeder())->setCount(2)->run();
    (new ServiceTestingSeeder())->setCount(1)->run();
    (new CampusTestingSeeder())->setCount(2)->run();
    (new UserEmployeeTestingSeeder())->run();
    (new EmployeeServiceTestingSeeder())->run();
    (new SurveyTestingSeeder())->run();
}

function setUpSurvey(): Collection|Model
{
    // This will run before each test
    return Survey::factory()->create([
        'version' => 1,
    ]);
}

beforeEach(function () {
    setUpSeeders();
    $this->survey = setUpSurvey();
    $this->nationalCoordinator = User::factory()
        ->withRole(UserRole::NationalCoordinator)
        ->create();

    $this->campusCoordinator = User::query()->where('role', UserRole::CampusCoordinator)
        ->where('campus_id', Campus::first('id')->id)
        ->first();

    $this->processLeader = User::query()->where('role', UserRole::ProcessLeader)
        ->where('campus_id', Campus::first('id')->id)
        ->first();
});

it('should return the correct data structure', function () {
    $this->actingAs($this->campusCoordinator);

    $response = $this->get('/api/chart/general-perception');

    $response->assertStatus(200);
    // I expect the response structure to be: data: [{period: date-string, perception: number}]
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'period',
                'perception',
            ],
        ],
    ]);
});
describe('Campus coordinator behavior for General perception chart', function () {
});
