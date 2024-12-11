<?php

test('example', function () {
    $user = \App\Models\User::factory()->withRole(\App\DTOs\Auth\UserRole::NationalCoordinator)->create();
    $this->actingAs($user);
    $response = $this->post('/api/survey',);

    $response->assertStatus(200);
});
