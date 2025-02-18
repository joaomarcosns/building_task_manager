<?php

declare(strict_types=1);

use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Team;
use App\Models\Building;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Set up the test environment before each test.
 */
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
 * Test that an unauthorized user cannot create a task comment.
 */
it('fails to create a task comment when the user is not authorized', function () {
    $this->actingAs($this->unauthorizedUser);

    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => 'Unauthorized comment attempt.',
    ]);

    $response->assertStatus(403);
});

/**
 * Test that a user from another client cannot create a task comment.
 */
it('fails to create a task comment when the user belongs to another client', function () {
    $this->actingAs($this->otherClientUser);

    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => 'Cross-client access attempt.',
    ]);

    $response->assertStatus(403);
});

/**
 * Test that a comment cannot be created when the comment field is empty.
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
 * Test that a comment cannot exceed the maximum length.
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

/**
 * Test that the task creator can create a comment.
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
 * Test that the responsible user can create a comment.
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
 * Test that the task status changes to IN_PROGRESS when the first comment is made.
 */
it('changes task status to IN_PROGRESS when the first comment is made', function () {
    // Garantir que a tarefa comece com um status diferente de IN_PROGRESS
    $this->task->update(['status' => TaskStatusEnum::OPEN]);

    // Agir como o criador da tarefa
    $this->actingAs($this->owner);

    // Fazer o primeiro comentário
    $response = $this->postJson(route('comments.store', $this->task), [
        'comment' => 'First comment, status should change to IN_PROGRESS.',
    ]);

    // Verificar se a resposta foi bem-sucedida
    $response->assertStatus(201);

    // Verificar se o status da tarefa foi atualizado para IN_PROGRESS
    $this->assertDatabaseHas('tasks', [
        'id' => $this->task->id,
        'status' => TaskStatusEnum::IN_PROGRESS,
    ]);

    $this->assertDatabaseHas('task_comments', [
        'comment' => 'First comment, status should change to IN_PROGRESS.',
        'task_id' => $this->task->id,
        'user_id' => $this->owner->id,
    ]);
});
