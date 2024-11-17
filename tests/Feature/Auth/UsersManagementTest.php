<?php

use App\DTOs\Auth\UserRole;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->campus1 = Campus::factory()->create();
    $this->campus2 = Campus::factory()->create();
    // Create a user with each role for each campus
    $this->nationalCoordinator = User::factory()->withRole(UserRole::NationalCoordinator)->create();
    $this->campus1Coordinator = User::factory()->withRole(UserRole::CampusCoordinator)->create(['campus_id' => $this->campus1->id]);
    $this->campus1Leader = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus1->id]);
    $this->campus2Coordinator = User::factory()->withRole(UserRole::CampusCoordinator)->create(['campus_id' => $this->campus2->id]);
    $this->campus2Leader = User::factory()->withRole(UserRole::ProcessLeader)->create(['campus_id' => $this->campus2->id]);
});
test('National coordinator can see all users', function () {
    $this->actingAs($this->nationalCoordinator);

    $usersCount = User::count();
    $this->assertEquals(5, $usersCount);

    $response = $this->get('/api/users');

    $response->assertStatus(Response::HTTP_OK);
    //Total users is 5 but the national coordinator is not included
    $response->assertJsonCount($usersCount - 1);
});

test('Campus coordinator can see users in his campus', function () {
    $this->actingAs($this->campus1Coordinator);

    $usersCount = User::where('campus_id', $this->campus1->id)->count();
    // Total users in campus 1 is 2 but the campus coordinator is not included
    $this->assertEquals(2, $usersCount);

    $response = $this->get('/api/users');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(fn(AssertableJson $json) => $json->has($usersCount - 1)
        ->first(
            fn(AssertableJson $json) => $json->where('campus_id', $this->campus1->id)->etc()
        )
    );
});

test('campus coordinator can\'t see users in other campuses', function () {
    $this->actingAs($this->campus1Coordinator);

    $usersCount = User::where('campus_id', $this->campus1->id)->count();
    $this->assertEquals(2, $usersCount);

    $response = $this->get('/api/users');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(fn(AssertableJson $json) => $json->has($usersCount - 1)
        ->first(
            fn(AssertableJson $json) => $json->where('campus_id', $this->campus1->id)->etc()
        )
    );
});

test('Process leader can\'t see users', function () {
    $this->actingAs($this->campus1Leader);

    $response = $this->get('/api/users');

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('Can filter users', function () {
    $this->actingAs($this->nationalCoordinator);

    $user = User::factory()->create(['name' => 'John Doe']);
    $this->assertNotNull($user);

    $response = $this->get('/api/users?filter[name]=John Doe');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonCount(1);
    $response->assertJson([
        ['name' => 'John Doe']
    ]);
});

test('Can sort users', function () {
    $this->actingAs($this->nationalCoordinator);

    $user1 = User::factory()->create(['name' => 'Axl Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Doe']);
    $this->assertNotNull($user1);
    $this->assertNotNull($user2);

    $response = $this->get('/api/users?sort=name');

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson(fn(AssertableJson $json) => $json->has(6)
        ->first(fn(AssertableJson $json) => $json->where('name', fn(string $name)=> str($name)->startsWith('A'))->etc())
    );
});

test('National coordinator can get any user by id', function () {

    $this->actingAs($this->nationalCoordinator);

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

test('Campus coordinator can get any user in his campus by id', function () {

    $this->actingAs($this->campus1Coordinator);

    $user = User::factory()->create(['campus_id' => $this->campus1->id]);
    $this->assertNotNull($user);

    $response = $this->get("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ]);
});

test('Campus coordinator can\'t get any user outside his campus by id', function () {

    $this->actingAs($this->campus1Coordinator);
    $user = User::factory()->create(['campus_id' => $this->campus2->id]);
    $this->assertNotNull($user);

    $response = $this->get("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('Process leader can\'t get any user by id', function () {

    $this->actingAs($this->campus1Leader);

    $user = User::factory()->create(['campus_id' => $this->campus1->id]);
    $this->assertNotNull($user);

    $response = $this->get("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('National coordinator can disable any user', function () {

    $this->actingAs($this->nationalCoordinator);

    $user = User::factory()->create();
    $this->assertNotNull($user);

    $response = $this->delete("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NO_CONTENT);
});

test('Campus coordinator can disable any user in his campus', function () {

    $this->actingAs($this->campus1Coordinator);

    $user = User::factory()->create(['campus_id' => $this->campus1->id]);
    $this->assertNotNull($user);

    $response = $this->delete("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NO_CONTENT);
});

test('Campus coordinator can\'t disable any user outside his campus', function () {

    $this->actingAs($this->campus1Coordinator);

    $user = User::factory()->create(['campus_id' => $this->campus2->id]);
    $this->assertNotNull($user);

    $response = $this->delete("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('Process leader can\'t disable any user', function () {

    $this->actingAs($this->campus1Leader);

    $user = User::factory()->create(['campus_id' => $this->campus1->id]);
    $this->assertNotNull($user);

    $response = $this->put("/api/users/{$user->id}/disable");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('National coordinator can enable any user', function () {

    $this->actingAs($this->nationalCoordinator);

    $user = User::factory()->create();
    $user->delete();
    $this->assertNotNull($user);

    $response = $this->patch("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NO_CONTENT);
});

test('Campus coordinator can enable any user in his campus', function () {

    $this->actingAs($this->campus1Coordinator);

    $user = User::factory()->create(['campus_id' => $this->campus1->id]);
    $user->delete();
    $this->assertNotNull($user);

    $response = $this->patch("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NO_CONTENT);
});

test('Campus coordinator can\'t enable any user outside his campus', function () {

    $this->actingAs($this->campus1Coordinator);

    $user = User::factory()->create(['campus_id' => $this->campus2->id]);
    $user->delete();
    $this->assertNotNull($user);

    $response = $this->patch("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

test('Process leader can\'t enable any user', function () {

    $this->actingAs($this->campus1Leader);

    $user = User::factory()->create(['campus_id' => $this->campus1->id]);
    $user->delete();
    $this->assertNotNull($user);

    $response = $this->patch("/api/users/{$user->id}");

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});
