<?php

namespace Database\Factories;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Building;
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
            'client_id' => function () {
                return User::factory()->create()->client_id; // Gerar com client_id do usuário criado
            },
            'building_id' => function () {
                return Building::factory()->create()->id;
            },
            'team_id' => function () {
                return Team::factory()->create()->id;
            },
        ];
    }
}
