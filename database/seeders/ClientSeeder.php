<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\Client;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /** Run the database seeds. */
    public function run(): void
    {
        // Create two clients
        foreach (range(1, 2) as $index) {
            $this->createClientWithTeamsAndUsers();
        }
    }

    private function createClientWithTeamsAndUsers(): void
    {
        // Create a client
        $client = Client::factory()->hasBuildings(5)->create();

        if (!$client) {
            $this->command->error('No client was created.');
            return;
        }

        // Create predefined teams
        $teams = collect(['Electrician', 'Plumber', 'Doorman'])
            ->map(fn ($name) => Team::create(['name' => $name, 'client_id' => $client->id]));

        // Create users for each team
        $teams->each(fn ($team) => User::factory()->count(3)->create([
            'team_id' => $team->id,
            'client_id' => $client->id,
        ]));

        // Set a user as OWNER (without a team)
        if ($firstUser = User::where('client_id', $client->id)->first()) {
            $firstUser->update([
                'email' => "teste@email{$client->id}.com",
                'password' => Hash::make('123456'),
                'role' => UserRoleEnum::OWNER,
                'team_id' => null,
            ]);
        }
    }
}
