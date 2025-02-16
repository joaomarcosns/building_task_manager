<?php

namespace Database\Factories;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Building;
use App\Models\Client;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->text,
            'priority' => $this->faker->randomElement(TaskPriorityEnum::cases())->value,
            'status' => $this->faker->randomElement(TaskStatusEnum::cases())->value,
            'client_id' => $clientId = Client::factory()->create()->id,
            'building_id' => Building::factory()->create(['client_id' => $clientId])->id,
            'team_id' => $team_id = Team::factory()->create(['client_id' => $clientId])->id,
            'created_by' => User::factory()->create(['client_id' => $clientId, 'role' => UserRoleEnum::OWNER])->id,
            'responsible_id' => User::factory()->create(['client_id' => $clientId, 'team_id' => $team_id])->id,
        ];
    }
}
