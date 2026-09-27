<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@realestate.com'],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'password' => Hash::make('secret123'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'user@realestate.com'],
            [
                'name' => 'Regular User',
                'role' => 'user',
                'password' => Hash::make('secret123'),
            ]
        );

        $agents = [
            ['name' => 'Tadesse Bekele', 'email' => 'tadesse.agent@example.com', 'phone' => '+251911000001', 'bio' => 'Senior property consultant', 'password' => 'agent123'],
            ['name' => 'Mekdes Ali', 'email' => 'mekdes.agent@example.com', 'phone' => '+251922000002', 'bio' => 'Investment advisor', 'password' => 'agent123'],
            ['name' => 'Yohannes Gebre', 'email' => 'yohannes.agent@example.com', 'phone' => '+251933000003', 'bio' => 'Sales manager', 'password' => 'agent123'],
        ];

        foreach ($agents as $agentData) {
            $agent = Agent::query()->updateOrCreate(
                ['email' => $agentData['email']],
                [
                    'name' => $agentData['name'],
                    'phone' => $agentData['phone'],
                    'bio' => $agentData['bio'],
                    'password' => $agentData['password'],
                ]
            );

            $account = User::query()->updateOrCreate(
                ['email' => $agentData['email']],
                [
                    'name' => $agentData['name'],
                    'role' => 'agent',
                    'password' => Hash::make($agentData['password']),
                ]
            );

            $agent->forceFill(['user_id' => $account->id])->save();
        }
    }
}
