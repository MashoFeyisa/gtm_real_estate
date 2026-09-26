<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Worker;
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

        $workers = [
            ['name' => 'Tadesse Bekele', 'email' => 'tadesse@example.com', 'department' => 'Sales', 'phone' => '+251911000001'],
            ['name' => 'Mekdes Ali', 'email' => 'mekdes@example.com', 'department' => 'Operations', 'phone' => '+251922000002'],
            ['name' => 'Yohannes Gebre', 'email' => 'yohannes@example.com', 'department' => 'Support', 'phone' => '+251933000003'],
        ];

        foreach ($workers as $worker) {
            Worker::query()->updateOrCreate(
                ['email' => $worker['email']],
                $worker
            );
        }
    }
}
