<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::factory()
            ->hasTeams(3)
            ->hasBuildings(5)
            ->create();

        $client = Client::first();

        $teams = $client->teams;

        foreach ($teams as $team) {
            User::factory()
                ->count(3)
                ->create([
                    'team_id' => $team->id,
                    'client_id' => $client->id,
                ]);
        }

        $user = User::first();

        $user->update([
            'email' => 'teste@email.com',
            'password' => bcrypt('123456'),
            'role' => UserRoleEnum::OWNER,
            'team_id' => null,
        ]);
    }
}
