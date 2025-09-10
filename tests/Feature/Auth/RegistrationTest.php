<?php

use App\DTOs\Auth\UserRole;
use App\Models\Campus;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\UserCreatedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

test('Unauthenticated user can\'t create new users', function () {

    Storage::fake('public');
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'avatar' => $avatar,
    ]);
    $this->assertGuest();
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test('Process leader user can\'t create new users', function () {

    Storage::fake('public');
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $user = User::factory()->withRole(UserRole::from('process_leader'))->create();
    $this->actingAs($user);

    $this->assertAuthenticated();
    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'test-user@example.com',
        'avatar' => $avatar,
    ]);

    $response
        ->assertStatus(Response::HTTP_FORBIDDEN);
});

test('User created by National Coordinator has campus coordinator role', function () {

    Storage::fake('public');
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);

    $campus = Campus::factory()->create();
    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'campus-coordinator-role-user@example.com',
        'avatar' => $avatar,
        'campus_id' => $campus->id,
    ]);
    $response
        ->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseHas('users', [
        'email' => 'campus-coordinator-role-user@example.com',
        'role' => UserRole::from('campus_coordinator')->value,
    ]);
});

test('User created by Campus Coordinator has process leader role and same campus', function () {

    Storage::fake('public');
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $campus = Campus::factory()->create();
    $user = User::factory()->withRole(UserRole::from('campus_coordinator'))->create([
        'campus_id' => $campus->id,
    ]);
    $this->actingAs($user);

    $this->assertAuthenticated();
    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'process-leader@example.com',
        'avatar' => $avatar,
    ]);
    $response
        ->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseHas('users', [
        'email' => 'process-leader@example.com',
        'role' => UserRole::from('process_leader')->value,
        'campus_id' => $user->campus->id,
    ]);
    $this->assertDatabaseHas('employees', [
        'email' => 'process-leader@example.com',
        'campus_id' => $user->campus->id,
    ]);
});

test('Employee is created when a user is created', function () {

    Storage::fake('public');
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    $campus = Campus::factory()->create();
    $user = User::factory()->withRole(UserRole::from('campus_coordinator'))->create([
        'campus_id' => $campus->id,
    ]);

    $this->actingAs($user);
    $this->assertAuthenticated();

    $email = 'employee@example.com';
    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => $email,
        'avatar' => $avatar,
    ]);
    $response->assertStatus(Response::HTTP_CREATED);

    $this->assertDatabaseHas('employees', [
        'email' => $email,
    ]);

    $user = User::where('email', $email)->first();

    $this->assertNotNull($user->employee, 'Employee was not created or associated with user');

    $employee = Employee::where('email', $email)->first();

    $this->assertEquals($user->employee->id, $employee->id);
    $this->assertEquals($user->employee->avatar, $employee->avatar);
});

test('User creation stores an icon sets it on the employee ', function () {

    Storage::fake('public');
    $avatar = UploadedFile::fake()->image('avatar.jpg');

     $campus = Campus::factory()->create();
    $user = User::factory()->withRole(UserRole::from('campus_coordinator'))->create([
        'campus_id' => $campus->id,
    ]);
    $this->actingAs($user);
    $this->assertAuthenticated();

    $email = 'storage@example.com';
    $response = $this->postJson('/register', [
        'name' => 'Test User',
        'email' => $email,
        'avatar' => $avatar,
    ]);
    $response->assertStatus(Response::HTTP_CREATED);

    Storage::disk('public')->assertExists('avatars/' . $avatar->hashName());
});

test('User creation sends notification', function () {

    Storage::fake('public');
    $avatar = UploadedFile::fake()->image('avatar.jpg');
    Notification::fake();

    $campus = Campus::factory()->create();
    $user = User::factory()->withRole(UserRole::from('campus_coordinator'))->create([
        'campus_id' => $campus->id,
    ]);

    $this->actingAs($user);

    $this->assertAuthenticated();
    $this->postJson('/register', [
        'name' => 'Test User',
        'email' => 'test-user2@example.com',
        'avatar' => $avatar,
    ]);

    $user = User::where('email', 'test-user2@example.com')->first();

    $this->assertNotNull($user, 'User was not created');
    Notification::assertSentTo(
        [$user], UserCreatedNotification::class
    );
});
