<?php

namespace Database\Seeders;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Seeder;

class NewsletterSubscriberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NewsletterSubscriber::query()->firstOrCreate(
            ['email' => 'client1@example.com'],
            ['ip_address' => '127.0.0.1', 'subscribed_at' => now()]
        );
    }
}
