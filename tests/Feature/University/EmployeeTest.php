<?php

use App\DTOs\Auth\UserRole;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->process = Process::factory()->create();
    $this->process2 = Process::factory()->create();
    $this->campus = Campus::factory()->create();
    $this->campus2 = Campus::factory()->create();
    $this->employee = Employee::factory()->count(3)->create([
        'campus_id' => $this->campus->id,
        'process_id' => $this->process->id
    ]);
});

test('anyone can get all employees', function () {
    $response = $this->get('/api/employees');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(3);
});

test('anyone can get an employee by id', function () {

    $response = $this->get('/api/employees/' . $this->employee[0]->token);

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonFragment([
        'id' => $this->employee[0]->id,
        'name' => $this->employee[0]->name,
        'email' => $this->employee[0]->email,
        'campus_id' => $this->campus->id,
    ]);
});

test('Campus coordinator can create an employee inside their campus', function () {

    Storage::fake('public');
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);
    $employeeImage = UploadedFile::fake()->image('employee.jpg');

    $response = $this->actingAs($user)->post('/api/employees', [
        'name' => 'Employee Name',
        'email' => 'employee@example.com',
        'avatar' => $employeeImage,
        'process_id' => $this->process->id
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('employees', [
        'name' => 'Employee Name',
        'email' => 'employee@example.com',
        'campus_id' => $this->campus->id,
        'process_id' => $this->process->id
    ]);
    Storage::disk('public')->assertExists('avatars/' . $employeeImage->hashName());
});

test('Process leader can create an employee inside their process', function () {

    Storage::fake('public');
    $employee = Employee::factory()->create([
        'campus_id' => $this->campus->id,
        'process_id' => $this->process2->id
    ]);
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
        'campus_id' => $this->campus->id,
        'employee_id' => $employee->id,
    ]);
    $employeeImage = UploadedFile::fake()->image('employee.jpg');

    $response = $this->actingAs($user)->post('/api/employees', [
        'name' => 'Employee Name',
        'email' => 'employee@example.com',
        'avatar' => $employeeImage,
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas('employees', [
        'name' => 'Employee Name',
        'email' => 'employee@example.com',
        'campus_id' => $this->campus->id,
        'process_id' => $this->process2->id
    ]);
    Storage::disk('public')->assertExists('avatars/' . $employeeImage->hashName());
});

test('Process leader can\'t create an employee outside their process', function () {

    Storage::fake('public');
    $employee = Employee::factory()->create([
        'campus_id' => $this->campus->id,
        'process_id' => $this->process2->id
    ]);
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
        'campus_id' => $this->campus->id,
        'employee_id' => $employee->id,
    ]);
    $employeeImage = UploadedFile::fake()->image('employee.jpg');

    $response = $this->actingAs($user)->post('/api/employees', [
        'name' => 'Employee Name',
        'email' => 'employee@example.com',
        'avatar' => $employeeImage,
        'process_id' => $this->process2->id
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertDatabaseMissing('employees', [
        'name' => 'Employee Name',
        'email' => 'employee@example.com',
        'campus_id' => $this->campus->id,
        'process_id' => $this->process2->id
    ]);

    Storage::disk('public')->assertMissing('avatars/' . $employeeImage->hashName());
});

test('Campus coordinator can update an employee inside their campus', function () {

    Storage::fake('public');
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);
    $employeeImage = UploadedFile::fake()->image('updated-employee.jpg');

    $response = $this->actingAs($user)->put('/api/employees/' . $this->employee[0]->token, [
        'name' => 'Employee Name Updated',
        'email' => 'updated-employee@example.com',
        'avatar' => $employeeImage,
        'process_id' => $this->process->id
    ]);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertDatabaseHas('employees', [
        'id' => $this->employee[0]->id,
        'name' => 'Employee Name Updated',
        'email' => 'updated-employee@example.com',
        'campus_id' => $this->campus->id,
    ]);
    Storage::disk('public')->assertExists('avatars/' . $employeeImage->hashName());
});

test('Process leader can update an employee inside their process', function () {

    Storage::fake('public');
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
        'campus_id' => $this->campus->id,
        'employee_id' => $this->employee[0]->id
    ]);
    $employeeImage = UploadedFile::fake()->image('updated-employee.jpg');

    $response = $this->actingAs($user)->put('/api/employees/' . $this->employee[0]->token, [
        'name' => 'Employee Name Updated',
        'email' => 'updated-employee@example.com',
        'avatar' => $employeeImage,
    ]);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertDatabaseHas('employees', [
        'id' => $this->employee[0]->id,
        'name' => 'Employee Name Updated',
        'email' => 'updated-employee@example.com',
        'campus_id' => $this->campus->id,
        'process_id' => $this->process->id
    ]);
    Storage::disk('public')->assertExists('avatars/' . $employeeImage->hashName());
});

test('Campus coordinator can\'t update an employee outside their campus', function () {

    Storage::fake('public');
    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);
    $employeeImage = UploadedFile::fake()->image('updated-employee.jpg');

    $this->employee[1]->update(['campus_id' => $this->campus2->id]);

    $response = $this->actingAs($user)->put('/api/employees/' . $this->employee[1]->token, [
        'name' => 'Employee Name Updated',
        'email' => 'updated-employee@example.com',
        'avatar' => $employeeImage,
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertDatabaseMissing('employees', [
        'id' => $this->employee[1]->id,
        'name' => 'Employee Name Updated',
        'email' => 'updated-employee@example.com',
        'campus_id' => $this->campus2->id
    ]);
    Storage::disk('public')->assertMissing('avatars/' . $employeeImage->hashName());
});

test('Process leader can\'t update an employee outside their process', function () {

    Storage::fake('public');
    $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
        'campus_id' => $this->campus->id,
        'employee_id' => $this->employee[0]->id
    ]);
    $employeeImage = UploadedFile::fake()->image('updated-employee.jpg');

    $this->employee[1]->update(['process_id' => $this->process2->id]);

    $response = $this->actingAs($user)->put('/api/employees/' . $this->employee[1]->token, [
        'name' => 'Employee Name Updated',
        'email' => 'updated-employee@example.com',
        'avatar' => $employeeImage,
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertDatabaseMissing('employees', [
        'id' => $this->employee[1]->id,
        'name' => 'Employee Name Updated',
        'email' => 'updated-employee@example.com',
        'campus_id' => $this->campus->id,
        'process_id' => $this->process2->id
    ]);
    Storage::disk('public')->assertMissing('avatars/' . $employeeImage->hashName());
});

test('Process leader can\'t change employee\'s process', function () {

        Storage::fake('public');
        $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
            'campus_id' => $this->campus->id,
            'employee_id' => $this->employee[0]->id
        ]);
        $employeeImage = UploadedFile::fake()->image('updated-employee.jpg');

        $response = $this->actingAs($user)->put('/api/employees/' . $this->employee[0]->token, [
            'name' => 'Employee Name Updated',
            'email' => 'example@example.com',
            'avatar' => $employeeImage,
            'process_id' => $this->process2->id
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing('employees', [
            'id' => $this->employee[0]->id,
            'name' => 'Employee Name Updated',
            'email' => 'example@example.com',
            'campus_id' => $this->campus->id,
            'process_id' => $this->process2->id
        ]);
        Storage::disk('public')->assertMissing('avatars/' . $employeeImage->hashName());
});

test('Campus coordinator can delete an employee inside their campus', function () {

    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);

    $response = $this->actingAs($user)->delete('/api/employees/' . $this->employee[0]->token);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertSoftDeleted($this->employee[0]);
});

test('User associated with employee is deleted when employee is deleted', function (){
    $user = User::factory()->create([
        'employee_id' => $this->employee[0]->id
    ]);
    $authenticatingUser = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);
    $this->actingAs($authenticatingUser)->delete('/api/employees/' . $this->employee[0]->token);

    $this->assertSoftDeleted($this->employee[0]);
    $this->assertSoftDeleted($user);
});

test('Campus coordinator can\'t delete an employee outside their campus', function () {

    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);

    $this->employee[1]->update(['campus_id' => $this->campus2->id]);
    $response = $this->actingAs($user)->delete('/api/employees/' . $this->employee[1]->token);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertNotSoftDeleted($this->employee[1]);
});

test('Process leader can delete an employee inside their process', function () {

    $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
        'campus_id' => $this->campus->id,
        'employee_id' => $this->employee[0]->id
    ]);

    $response = $this->actingAs($user)->delete('/api/employees/' . $this->employee[0]->token);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertSoftDeleted($this->employee[0]);
});

test('Process leader can\'t delete an employee outside their process', function () {

    $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
        'campus_id' => $this->campus->id,
        'employee_id' => $this->employee[0]->id
    ]);

    $this->employee[1]->update(['process_id' => $this->process2->id]);
    $response = $this->actingAs($user)->delete('/api/employees/' . $this->employee[1]->token);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertNotSoftDeleted($this->employee[1]);
});

test('Campus coordinator can restore an employee inside their campus', function () {

    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);

    $this->employee[0]->delete();

    $response = $this->actingAs($user)->patch('/api/employees/' . $this->employee[0]->token);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertNotSoftDeleted($this->employee[0]);
});

test('User associated with employee is restored when employee is restored', function (){
    $user = User::factory()->create([
        'employee_id' => $this->employee[0]->id
    ]);
    $authenticatingUser = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);
    $user->delete();
    $this->employee[0]->delete();

    $this->actingAs($authenticatingUser)->patch('/api/employees/' . $this->employee[0]->token);

    $this->assertNotSoftDeleted($this->employee[0]);
    $this->assertNotSoftDeleted($user);
});

test('Campus coordinator can\'t restore an employee outside their campus', function () {

    $user = User::factory()->withRole(UserRole::CampusCoordinator)->create([
        'campus_id' => $this->campus->id
    ]);

    $this->employee[1]->update(['campus_id' => $this->campus2->id]);
    $this->employee[1]->delete();

    $response = $this->actingAs($user)->patch('/api/employees/' . $this->employee[1]->token);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertSoftDeleted($this->employee[1]);
});

test('Process leader can restore an employee inside their process', function () {

    $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
        'campus_id' => $this->campus->id,
        'employee_id' => $this->employee[1]->id
    ]);

    $this->employee[0]->delete();

    $response = $this->actingAs($user)->patch('/api/employees/' . $this->employee[0]->token);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertNotSoftDeleted($this->employee[0]);
});

test('Process leader can\'t restore an employee outside their process', function () {

    $user = User::factory()->withRole(UserRole::ProcessLeader)->create([
        'campus_id' => $this->campus->id,
        'employee_id' => $this->employee[0]->id
    ]);

    $this->employee[1]->update(['process_id' => $this->process2->id]);
    $this->employee[1]->delete();

    $response = $this->actingAs($user)->patch('/api/employees/' . $this->employee[1]->token);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertSoftDeleted($this->employee[1]);
});
