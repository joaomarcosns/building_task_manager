<?php

use App\Enums\BuildingStatusEnum;
use App\Enums\TaskPriorityEnum;
use App\Models\Building;
use App\Models\Team;
use App\Models\User;
use App\Models\Client;
use App\Enums\TaskStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Set up the test environment before each test.
 */
beforeEach(function () {
    // Create two clients
    $this->client = Client::factory()->create([
        'name' => 'Client ' . uniqid()
    ]);

    $this->otherClient = Client::factory()->create([
        'name' => 'Client ' . uniqid()
    ]);

    // Create two teams
    $this->team = Team::factory()->create([
        'client_id' => $this->client->id
    ]);
    $this->otherTeam = Team::factory()->create([
        'client_id' => $this->otherClient->id
    ]);

    // Create users for each client
    $this->user = User::factory()->create([
        'client_id' => $this->client->id,
        'team_id' => $this->team->id,
        'role' => UserRoleEnum::OWNER
    ]);

    $this->otherUser = User::factory()->create([
        'client_id' => $this->otherClient->id,
        'team_id' => $this->otherTeam->id
    ]);
    $this->actingAs($this->user);

    // Create an active and inactive building
    $this->activeBuilding = Building::factory()->create([
        'client_id' => $this->client->id,
        'status' => BuildingStatusEnum::ACTIVE,
    ]);
    $this->inactiveBuilding = Building::factory()->create([
        'client_id' => $this->client->id,
        'status' => BuildingStatusEnum::INACTIVE,
    ]);

    // Create a task with OPEN status
    $this->task = Task::factory()->create([
        'client_id' => $this->client->id,
        'building_id' => $this->activeBuilding->id,
        'created_by' => $this->user->id,
        'status' => TaskStatusEnum::OPEN,
    ]);
});

/**
 * Test that only a user with the 'OWNER' role can create a task.
 */
it('fails to create a task if the user is not an owner', function () {
    // Authenticate as a user with the EMPLOYEE role
    $this->user->update([
        'role' => UserRoleEnum::EMPLOYEE
    ]);
    // Try to create a task with a user who does not have the 'OWNER' role
    $response = $this->putJson(route('tasks.update', $this->task), [
        'title' => 'Task with Inactive Building',
        'description' => 'Building is inactive, so task should not be created.',
        'team_id' => $this->team->id,
        'priority' => TaskPriorityEnum::HIGH,
    ]);
    // Verify the response is an error (forbidden) because the user does not have permission to create the task
    $response->assertStatus(403);
});

/**
 * Test if a user cannot update a task if client_id does not match.
 */
it('fails to update a task if client_id does not match', function () {
    // Create a task with client_id = $this->client->id
    $task = Task::factory()->create(['client_id' => $this->client->id]);

    // Try to update the task with a user from a different client_id
    $data = [
        'title' => 'Updated Task Title',
        'description' => 'Updated Task Description',
    ];

    // Authenticate with the other user (different client_id)
    $this->actingAs($this->otherUser);

    // Try to send a request to update the task
    $response = $this->putJson(route('tasks.update', $task), $data);

    // Ensure the response is 403 (Forbidden)
    $response->assertStatus(403);
});

/**
 * Tests if the task cannot be updated if the status is not OPEN.
 */
it('fails to update task if status is not OPEN', function () {
    // Updating the task to COMPLETED status (not allowed)
    $response = $this->patchJson(route('tasks.update', $this->task->id), [
        'status' => TaskStatusEnum::COMPLETED,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

/**
 * Tests if the task cannot be updated with a building from another client.
 */
it('fails to update task with building from another client', function () {
    // Creating another client and building for the other client
    $otherClient = Client::factory()->create();
    $otherBuilding = Building::factory()->create(['client_id' => $otherClient->id]);

    // Trying to update the task with the building from the other client
    $response = $this->patchJson(route('tasks.update', $this->task->id), [
        'building_id' => $otherBuilding->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['building_id']);
});

/**
 * Tests if the task cannot be updated with a building that has a false status.
 */
it('fails to update task with inactive building', function () {
    // Trying to update the task with the inactive building
    $response = $this->patchJson(route('tasks.update', $this->task->id), [
        'building_id' => $this->inactiveBuilding->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['building_id']);
});

/**
 * Test if a user can update a task if client_id matches.
 */
it('can update a task if client_id matches', function () {
    // Create a task with client_id = $this->client->id
    $task = Task::factory()->create([
        'client_id' => $this->client->id,
        'created_by' => $this->user->id
    ]);

    // Data to update the task
    $data = [
        'title' => 'Updated Task Title',
        'description' => 'Updated Task Description',
    ];

    // Authenticate as the correct user (same client_id)
    $this->actingAs($this->user);

    // Try to send a request to update the task
    $response = $this->putJson(route('tasks.update', $task), $data);

    // Ensure the response is 200 (OK)
    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated Task Title');

    // Verify if the task was updated in the database
    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'description' => 'Updated Task Description',
    ]);
});

/**
 * Test if a task cannot be updated with a non-existent building.
 */
it('fails to update task with a non-existent building', function () {
    // Trying to update the task with a non-existent building ID
    $response = $this->patchJson(route('tasks.update', $this->task->id), [
        'building_id' => 9999,  // Non-existent building ID
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['building_id']);
});


/**
 * Test if a user cannot update a task if the created_by does not match the logged-in user.
 */
it('fails to update a task if created_by does not match the logged-in user', function () {
    // Create a task with created_by different from the logged-in user
    $task = Task::factory()->create([
        'client_id' => $this->client->id,
        'created_by' => $this->otherUser->id, // Task created by another user
        'building_id' => $this->activeBuilding->id,
        'status' => TaskStatusEnum::OPEN,
    ]);

    // Data to update the task
    $data = [
        'title' => 'Updated Task Title',
        'description' => 'Updated task description.',
        'status' => TaskStatusEnum::IN_PROGRESS,
    ];

    // Try to update the task with the logged-in user
    $response = $this->putJson(route('tasks.update', $task->id), $data);

    // Check if the response is 403 (Forbidden), as the user is not the creator of the task
    $response->assertStatus(403)
        ->assertJson(['message' => 'This action is unauthorized.']);
});


/**
 * Test if a user cannot update a task if the task does not exist.
 */
it('fails to update a task if the task does not exist', function () {
    // Trying to update a non-existent task
    $response = $this->patchJson(route('tasks.update', 9999), [
        'title' => 'Updated Task Title',
        'description' => 'Updated Task Description',
    ]);

    $response->assertStatus(404);
});
