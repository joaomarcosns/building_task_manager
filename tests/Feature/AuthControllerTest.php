<?php

use App\Models\User;
use App\Models\Client;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;


uses(RefreshDatabase::class);

/**
 * Testa o registro de um novo usuário.
 */
// it('permite registrar um usuário', function () {
//     $client = Client::factory()->create();
//     $team = Team::factory()->create(['client_id' => $client->id]);

//     $response = $this->postJson('/api/register', [
//         'name' => 'João Marcos',
//         'email' => 'joao.marcos@example.com',
//         'password' => 'senha123',
//         'team_id' => $team->id,
//         'client_id' => $client->id,
//     ]);

//     $response->assertStatus(201)
//         ->assertJsonStructure(['token']);
// });

/**
 * Tests user login.
 */
it('can login a user', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token']);
});

/**
 * Tests login attempt with invalid credentials.
 */
it('prevents login with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
});

/**
 * Tests user logout.
 */
it('can logout a user', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->actingAs($user)->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Logged out successfully']);
});
