<?php

use App\DTOs\Auth\UserRole;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

test('anyone can get all services', closure: function () {
    Service::factory()->count(3)->create();

    $response = $this->get('/api/services');

    $response->assertStatus(Response::HTTP_OK);
    $this->assertJsonCount(3);
});

test('anyone can get a service by id', closure: function () {
    $service = Service::factory()->create();

    $response = $this->get("/api/services/$service->id");

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson($service->toArray());
});

test('National coordinator can create a service', closure: function () {
    Storage::fake('public');
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $icon = UploadedFile::fake()->image('icon.png');

    $response = $this->post('/api/services', [
        'name' => 'Service Name',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseHas(Service::class, [
        'name' => 'Service Name',
    ]);
    Storage::disk('public')->assertExists('icons/' . $icon->hashName());
});

test('Other users cannot create a service', closure: function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $icon = UploadedFile::fake()->image('icon.png');

    $response = $this->post('/api/services', [
        'name' => 'Service Name',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('National coordinator can update a service', closure: function () {
    Storage::fake('public');
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $service = Service::factory()->create();
    $icon = UploadedFile::fake()->image('icon.png');

    $response = $this->put("/api/services/$service->id", [
        'name' => 'Updated Service Name',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertDatabaseHas(Service::class, [
        'name' => 'Updated Service Name',
    ]);
    Storage::disk('public')->assertExists('icons/' . $icon->hashName());
});

test('Old icon is deleted when updating a service', closure: function () {
    Storage::fake('public');
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $service = Service::factory()->create();
    $icon = UploadedFile::fake()->image('icon.png');

    $this->put("/api/services/$service->id", [
        'name' => 'Updated Service Name',
        'icon' => $icon,
    ]);

    $oldIcon = $service->icon;
    $this->assertDatabaseHas(Service::class, [
        'icon' => $icon->hashName(),
    ]);
    Storage::disk('public')->assertExists('icons/' . $icon->hashName());
    Storage::disk('public')->assertMissing('icons/' . $oldIcon);
});

test('Other users cannot update a service', closure: function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $service = Service::factory()->create();
    $icon = UploadedFile::fake()->image('icon.png');

    $response = $this->put("/api/services/$service->id", [
        'name' => 'Updated Service Name',
        'icon' => $icon,
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('National coordinator can delete a service', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $service = Service::factory()->create();

    $response = $this->delete("/api/services/$service->id");

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertSoftDeleted($service);
});

test('Other users cannot delete a service', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $service = Service::factory()->create();

    $response = $this->delete("/api/services/$service->id");

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('National Coordinator can restore a service', function () {
    $user = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $service = Service::factory()->softDeleted()->create();

    $response = $this->put("/api/services/$service->id/restore");

    $response->assertStatus(Response::HTTP_NO_CONTENT);
    $this->assertNotSoftDeleted($service);
});
