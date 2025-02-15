<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 0; // Using a counter to ensure uniqueness
        $teamNameFake = [
            'janitor',
            'electrician',
            'doorman',
        ];

        return [
            'name' => $teamNameFake[$counter % count($teamNameFake)], // Cycle through the names
            'client_id' => Client::factory(),
        ];
    }
}
