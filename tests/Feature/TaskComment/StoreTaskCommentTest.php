<?php

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Team;
use App\Models\Building;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->otherClient = Client::factory()->create();

    $this->team = Team::factory()->create(['client_id' => $this->client->id]);
    $this->otherTeam = Team::factory()->create(['client_id' => $this->otherClient->id]);

    $this->owner = User::factory()->create([
        'client_id' => $this->client->id,
        'role' => UserRoleEnum::OWNER,
    ]);

    $this->responsibleUser = User::factory()->create([
        'client_id' => $this->client->id,
        'role' => UserRoleEnum::EMPLOYEE,
    ]);

    $this->unauthorizedUser = User::factory()->create([
        'client_id' => $this->client->id,
        'role' => UserRoleEnum::EMPLOYEE,
    ]);

    $this->otherClientUser = User::factory()->create([
        'client_id' => $this->otherClient->id,
        'role' => UserRoleEnum::OWNER,
    ]);

    $this->building = Building::factory()->create(['client_id' => $this->client->id]);

    $this->task = Task::factory()->create([
        'client_id' => $this->client->id,
        'team_id' => $this->team->id,
        'building_id' => $this->building->id,
        'created_by' => $this->owner->id,
        'responsible_id' => $this->responsibleUser->id,
    ]);
});

/**
 * Allows the task creator to create a comment.
 */
it('allows the task creator to create a comment', function () {
    $this->actingAs($this->owner);

    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => 'This is a valid comment.',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('task_comments', [
        'comment' => 'This is a valid comment.',
        'task_id' => $this->task->id,
        'user_id' => $this->owner->id,
    ]);
});

/**
 * Allows the responsible user to create a comment.
 */
it('allows the responsible user to create a comment', function () {
    $this->actingAs($this->responsibleUser);

    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => 'Responsible user comment.',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('task_comments', [
        'comment' => 'Responsible user comment.',
        'task_id' => $this->task->id,
        'user_id' => $this->responsibleUser->id,
    ]);
});

/**
 * Fails to create a task comment when the user is not authorized.
 */
it('fails to create a task comment when the user is not authorized', function () {
    $this->actingAs($this->unauthorizedUser);

    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => 'Unauthorized comment attempt.',
    ]);

    $response->assertStatus(403);
});

/**
 * Fails to create a task comment when the user belongs to another client.
 */
it('fails to create a task comment when the user belongs to another client', function () {
    $this->actingAs($this->otherClientUser);

    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => 'Cross-client access attempt.',
    ]);

    $response->assertStatus(403);
});

/**
 * Fails to create a task comment when the comment field is empty.
 */
it('fails to create a task comment when the comment field is empty', function () {
    $this->actingAs($this->owner);

    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['comment']);
});

/**
 * Fails to create a task comment when the comment exceeds the maximum length.
 */
it('fails to create a task comment when the comment exceeds the maximum length', function () {
    $this->actingAs($this->owner);

    $longComment = str_repeat('A', 256);

    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => $longComment,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['comment']);
});
