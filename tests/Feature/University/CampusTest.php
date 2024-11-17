<?php

use App\DTOs\Auth\UserRole;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

test('anyone can get all campuses', function () {
    $campuses = Campus::factory()->count(3)->create();
    $response = $this->get('/api/campuses');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(3);
});

test('anyone can get a campus', function () {
    $campus = Campus::factory()->create();

    $response = $this->get("/api/campuses/{$campus->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'id' => $campus->id,
        'name' => $campus->name,
        'address' => $campus->address,
    ]);
});

test('National coordinator can create a campus', function () {
    Storage::fake('public');
    $campusCoordinator = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $icon = UploadedFile::fake()->image('icon.png');

    $response = $this->post('/api/campuses', [
        'name' => 'Campus Name',
        'address' => 'Campus Location',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    Storage::disk('public')->assertExists('icons/' . $icon->hashName());
});

test('No one else can create a campus', function () {
    Storage::fake('public');
    $campusCoordinator = User::factory()->withRole(UserRole::CampusCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $icon = UploadedFile::fake()->image('icon.png');

    $response = $this->post('/api/campuses', [
        'name' => 'Campus Name',
        'address' => 'Campus Location',
        'icon' => $icon,
    ]);
    Storage::disk('public')->assertMissing('icons/' . $icon->hashName());
    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('Unauthorized user cannot create a campus', function () {
    Storage::fake('public');
    $icon = UploadedFile::fake()->image('icon.png');

    $response = $this->post('/api/campuses', [
        'name' => 'Campus Name',
        'address' => 'Campus Location',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_FOUND);
    Storage::disk('public')->assertMissing('icons/' . $icon->hashName());
});

test('National coordinator can update a campus', function () {
    Storage::fake('public');
    $campusCoordinator = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $icon = UploadedFile::fake()->image('icon.png');
    $campus = Campus::factory()->create();

    $response = $this->put("/api/campuses/{$campus->id}", [
        'name' => 'Campus Name',
        'address' => 'Campus Location',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertDatabaseHas('campuses', [
        'id' => $campus->id,
        'name' => 'Campus Name',
        'address' => 'Campus Location',
    ]);
});

test('Old campus icon is deleted when updating a campus\'s icon', function () {
    Storage::fake('public');
    $campusCoordinator = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $icon = UploadedFile::fake()->image('icon.png');
    $oldIcon = UploadedFile::fake()->image('old_icon.png');
    $campus = Campus::factory()->create(['icon' => $oldIcon->hashName()]);

    $response = $this->put("/api/campuses/{$campus->id}", [
        'name' => 'Campus Name',
        'address' => 'Campus Location',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertDatabaseHas('campuses', [
        'id' => $campus->id,
        'name' => 'Campus Name',
        'address' => 'Campus Location',
    ]);
    Storage::disk('public')->assertExists('icons/' . $icon->hashName());
    Storage::disk('public')->assertMissing('icons/' . $oldIcon->hashName());
});

test('No one else can update a campus', function () {
    Storage::fake('public');
    $campusCoordinator = User::factory()->withRole(UserRole::CampusCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $icon = UploadedFile::fake()->image('icon.png');
    $campus = Campus::factory()->create();

    $response = $this->put("/api/campuses/{$campus->id}", [
        'name' => 'Campus Name',
        'address' => 'Campus Location',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('Unauthorized user cannot update a campus', function () {
    Storage::fake('public');
    $icon = UploadedFile::fake()->image('icon.png');
    $campus = Campus::factory()->create();

    $response = $this->put("/api/campuses/{$campus->id}", [
        'name' => 'Campus Name',
        'address' => 'Campus Location',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_FOUND);
});

test('National coordinator can delete a campus', function () {
    $campusCoordinator = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $campus = Campus::factory()->create();

    $response = $this->delete("/api/campuses/{$campus->id}");

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertSoftDeleted($campus);
});

test('No one else can delete a campus', function () {
    $campusCoordinator = User::factory()->withRole(UserRole::CampusCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $campus = Campus::factory()->create();

    $response = $this->delete("/api/campuses/{$campus->id}");

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertNotSoftDeleted($campus);
});

test('Unauthorized user cannot delete a campus', function () {
    $campus = Campus::factory()->create();

    $response = $this->delete("/api/campuses/{$campus->id}");

    $response->assertStatus(Response::HTTP_FOUND);
    $this->assertNotSoftDeleted($campus);
});

test('National coordinator can restore a campus', function () {
    $campusCoordinator = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $campus = Campus::factory()->create();
    $campus->delete();

    $response = $this->patch("/api/campuses/{$campus->id}");

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertNotSoftDeleted($campus);
});

test('No one else can restore a campus', function () {
    $campusCoordinator = User::factory()->withRole(UserRole::CampusCoordinator)->create();
    $this->actingAs($campusCoordinator);
    $campus = Campus::factory()->create();
    $campus->delete();

    $response = $this->patch("/api/campuses/{$campus->id}");

    $response->assertStatus(Response::HTTP_FORBIDDEN);
    $this->assertSoftDeleted($campus);
});
