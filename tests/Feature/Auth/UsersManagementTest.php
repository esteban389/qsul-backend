<?php

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

//TODO add roles validation
test('user can get all users', function () {
    User::factory()->create();
    $usersCount = User::count();
    $this->assertTrue($usersCount > 0, "Failed asserting that the users count is greater than 0.");

    $response = $this->get('/api/users');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount($usersCount);
});

test('user can get a user by id', function () {
    $user = User::factory()->create();
    $this->assertNotNull($user);

    $response = $this->get("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ]);
});

test('user can disable a user', function () {
    $user = User::factory()->create();
    $this->assertNotNull($user);

    $response = $this->delete("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_OK);
    $user->refresh();
    $this->assertSoftDeleted($user);
});

test('user can activate a user', function () {
    $user = User::factory()->create();
    $this->assertNotNull($user);

    $user->delete();

    $response = $this->post("/api/users/{$user->id}/activate");

    $response->assertStatus(Response::HTTP_OK);
    $user->refresh();
    $this->assertNotSoftDeleted($user);
});
