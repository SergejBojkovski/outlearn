<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewsletterSeeder extends Seeder
{
    public function run()
    {
        DB::table('newsletter_subscription')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'email' => 'user1@example.com',
                'subscribed_at' => Carbon::parse('2024-07-02T14:03:31'),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'email' => 'user2@example.com',
                'subscribed_at' => Carbon::parse('2023-03-29T14:42:05'),
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'email' => 'user3@example.com',
                'subscribed_at' => Carbon::parse('2023-08-03T07:20:47'),
            ]
        ]);
    }
}
