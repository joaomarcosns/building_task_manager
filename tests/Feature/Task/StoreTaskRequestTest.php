<?php

declare(strict_types=1);

use App\Enums\BuildingStatusEnum;
use App\Models\Building;
use App\Models\Team;
use App\Models\User;
use App\Models\Client;
use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Set up the test environment before each test.
 */
beforeEach(function () {
    // Create two clients
    $this->client = Client::factory()->create();
    $this->otherClient = Client::factory()->create();

    // Create two teams
    $this->team = Team::factory()->create(['client_id' => $this->client->id]);
    $this->otherTeam = Team::factory()->create(['client_id' => $this->otherClient->id]);

    // Create users for each client
    $this->user = User::factory()->create([
        'client_id' => $this->client->id,
        'team_id' => $this->team->id,
        'role' => UserRoleEnum::OWNER,
    ]);

    $this->userSamaTeam = User::factory()->create([
        'client_id' => $this->client->id,
        'team_id' => $this->team->id,
        'role' => UserRoleEnum::EMPLOYEE,
    ]);

    $this->otherUser = User::factory()->create([
        'client_id' => $this->otherClient->id,
        'team_id' => $this->otherTeam->id,
    ]);

    // Authenticate as the first client user
    $this->actingAs($this->user);

    // Create buildings and teams for both clients
    $this->building = Building::factory()->create(['client_id' => $this->client->id]);

    $this->otherBuilding = Building::factory()->create(['client_id' => $this->otherClient->id]);
});

/**
 * Test that only a user with the 'OWNER' role can create a task.
 */
it('fails to create a task if the user is not an owner', function () {
    // Authenticate as a user with the EMPLOYEE role
    $this->user->update([
        'role' => UserRoleEnum::EMPLOYEE,
    ]);
    // Try to create a task with a user who does not have the 'OWNER' role
    $response = $this->postJson(route('tasks.store'), [
        'title' => 'Task with Inactive Building',
        'description' => 'Building is inactive, so task should not be created.',
        'building_id' => $this->building->id,
        'team_id' => $this->team->id,
        'priority' => TaskPriorityEnum::HIGH,
    ]);

    // Verify the response is an error (forbidden) because the user does not have permission to create the task
    $response->assertStatus(403);
});

/**
 * Test validation failure when required fields are missing.
 */
it('fails to create a task without required fields', function () {

    $response = $this->postJson(route('tasks.store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'description', 'building_id', 'team_id', 'priority', 'responsible_id']);
});

/**
 * Test validation failure when the building ID is invalid.
 */
it('fails to create a task with an invalid building ID', function () {

    $data = [
        'title' => 'Invalid Task',
        'description' => 'Test',
        'building_id' => 999, // Non-existent ID
        'team_id' => $this->team->id,
        'priority' => TaskPriorityEnum::HIGH,
        'due_date' => now()->addDays(3)->toDateString(),
    ];

    $response = $this->postJson(route('tasks.store'), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['building_id']);
});

/**
 * Test validation failure when the team ID is invalid.
 */
it('fails to create a task with an invalid team ID', function () {
    $data = [
        'title' => 'Invalid Task',
        'description' => 'Test',
        'building_id' => $this->building->id,
        'team_id' => 999, // Non-existent ID
        'due_date' => now()->addDays(3)->toDateString(),
    ];

    $response = $this->postJson(route('tasks.store'), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_id']);
});

/**
 * Test if a task cannot be created with an invalid priority value.
 */
it('fails to create a task with an invalid priority value', function () {
    $data = [
        'title' => 'Task with Invalid Priority',
        'description' => 'This task has an invalid priority.',
        'building_id' => $this->building->id,
        'team_id' => $this->team->id,
        'status' => TaskStatusEnum::OPEN,
        'priority' => 999, // Invalid priority
    ];

    $response = $this->postJson(route('tasks.store'), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['priority']);
});

/**
 * Test if a task cannot be created with a building_id from another client.
 */
it('fails to create a task with a building_id from another client', function () {
    $data = [
        'title' => 'Task with Wrong Building',
        'description' => 'Building does not belong to the authenticated user client.',
        'building_id' => $this->otherBuilding->id, // Belongs to another client
        'team_id' => $this->team->id,
        'priority' => TaskPriorityEnum::HIGH,
    ];

    $response = $this->postJson(route('tasks.store'), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['building_id']);
});

/**
 * Test if a task cannot be created with a team_id from another client.
 */
it('fails to create a task with a team_id from another client', function () {
    $data = [
        'title' => 'Task with Wrong Team',
        'description' => 'Team does not belong to the authenticated user client.',
        'building_id' => $this->building->id,
        'team_id' => $this->otherTeam->id, // Belongs to another client
        'priority' => TaskPriorityEnum::HIGH,
    ];

    $response = $this->postJson(route('tasks.store'), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_id']);
});

/**
 * Test if a task cannot be created if the building is not active.
 */
it('fails to create a task with an inactive building', function () {
    // Mark the building as inactive
    $this->building->update(['status' => BuildingStatusEnum::INACTIVE]);

    $data = [
        'title' => 'Task with Inactive Building',
        'description' => 'Building is inactive, so task should not be created.',
        'building_id' => $this->building->id,
        'team_id' => $this->team->id,
        'priority' => TaskPriorityEnum::HIGH,
    ];

    $response = $this->postJson(route('tasks.store'), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['building_id']); // Expecting a validation error for building_id
});

/**
 * Test validation for responsible_id field.
 */
it('fails to create a task if responsible_id is invalid', function () {
    // Test with responsible_id missing (required validation)
    $response = $this->postJson(route('tasks.store'), [
        'title' => 'Task with Invalid Responsible ID',
        'description' => 'This task does not have a responsible ID.',
        'building_id' => $this->building->id,
        'team_id' => $this->team->id,
        'priority' => TaskPriorityEnum::HIGH,
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['responsible_id']);

    // Test with responsible_id being a non-existent user for the authenticated client's team
    $nonExistentUserId = 999; // Assuming user with ID 999 doesn't exist in the authenticated user's client and team
    $response = $this->postJson(route('tasks.store'), [
        'title' => 'Task with Invalid Responsible User',
        'description' => 'This task has an invalid responsible user.',
        'building_id' => $this->building->id,
        'team_id' => $this->team->id,
        'priority' => TaskPriorityEnum::HIGH,
        'responsible_id' => $nonExistentUserId,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['responsible_id']);
});

/**
 * Test validation to ensure the responsible_id cannot be the same as the authenticated user.
 */
it('fails to create a task if responsible_id is the same as the authenticated user', function () {
    // Mark the building as inactive
    $this->building->update(['status' => BuildingStatusEnum::ACTIVE]);

    $data = [
        'title' => 'New Task',
        'description' => 'Task description',
        'building_id' => $this->building->id,
        'team_id' => $this->team->id,
        'status' => TaskStatusEnum::OPEN,
        'priority' => TaskPriorityEnum::HIGH,
        'responsible_id' => $this->user->id,
    ];

    // Send a POST request to create a task
    $response = $this->postJson(route('tasks.store'), $data);

    // Assert that the response is an error (422 Unprocessable Entity)
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['responsible_id']); // Expecting a validation error for responsible_id
});

/**
 * Test if a task can be successfully created.
 */
it('creates a task successfully', function () {
    // Mark the building as inactive
    $this->building->update(['status' => BuildingStatusEnum::ACTIVE]);

    $data = [
        'title' => 'New Task',
        'description' => 'Task description',
        'building_id' => $this->building->id,
        'team_id' => $this->team->id,
        'status' => TaskStatusEnum::OPEN,
        'priority' => TaskPriorityEnum::HIGH,
        'responsible_id' => $this->userSamaTeam->id,
    ];

    // Send a POST request to create a task
    $response = $this->postJson(route('tasks.store'), $data);

    // Assert the response status and expected JSON structure
    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'New Task');

    // Verify the task exists in the database
    $this->assertDatabaseHas('tasks', [
        'title' => 'New Task',
        'description' => 'Task description',
        'building_id' => $this->building->id,
        'team_id' => $this->team->id,
        'status' => TaskStatusEnum::OPEN,
        'priority' => TaskPriorityEnum::HIGH,
    ]);
});
