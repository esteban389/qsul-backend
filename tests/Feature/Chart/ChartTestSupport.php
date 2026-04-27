<?php

use App\DTOs\Auth\UserRole;
use App\Models\Answer;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\EmployeeService;
use App\Models\Process;
use App\Models\RespondentType;
use App\Models\Service;
use App\Models\Survey;
use App\Models\User;
use Carbon\Carbon;

function createInclusiveChartFixture(): array
{
    $campus = Campus::factory()->create([
        'name' => 'Campus QA',
    ]);

    $process = Process::factory()->create([
        'name' => 'Process QA',
    ]);

    $service = Service::factory()->create([
        'name' => 'Service QA',
        'process_id' => $process->id,
    ]);

    $employee = Employee::factory()->create([
        'name' => 'Employee QA',
        'campus_id' => $campus->id,
        'process_id' => $process->id,
    ]);

    $employeeService = EmployeeService::query()->create([
        'employee_id' => $employee->id,
        'service_id' => $service->id,
    ]);

    $survey = Survey::factory()->create([
        'version' => 99,
    ]);

    $respondentType = RespondentType::factory()->create([
        'name' => 'Student QA',
    ]);

    $user = User::factory()
        ->withRole(UserRole::NationalCoordinator)
        ->create();

    $selectedDay = Carbon::parse('2026-04-10');
    $includedAt = $selectedDay->copy()->endOfDay();
    $excludedAt = $selectedDay->copy()->addDay()->startOfDay();

    $includedAnswer = Answer::factory()->create([
        'survey_id' => $survey->id,
        'respondent_type_id' => $respondentType->id,
        'employee_service_id' => $employeeService->id,
        'average' => 4.75,
        'email' => 'included@example.com',
        'created_at' => $includedAt,
        'updated_at' => $includedAt,
    ]);

    $excludedAnswer = Answer::factory()->create([
        'survey_id' => $survey->id,
        'respondent_type_id' => $respondentType->id,
        'employee_service_id' => $employeeService->id,
        'average' => 1.25,
        'email' => 'excluded@example.com',
        'created_at' => $excludedAt,
        'updated_at' => $excludedAt,
    ]);

    return [
        'campus' => $campus,
        'process' => $process,
        'service' => $service,
        'employee' => $employee,
        'employee_service' => $employeeService,
        'survey' => $survey,
        'respondent_type' => $respondentType,
        'user' => $user,
        'selected_date' => '2026-04-10',
        'included_answer' => $includedAnswer,
        'excluded_answer' => $excludedAnswer,
    ];
}
