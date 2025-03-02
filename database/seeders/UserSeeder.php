<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                "name" => "John Doe",
                "email" => "john@example.com",
                "password" => Hash::make("password"),
                "role_id" => 2,
                "profile_picture" => ""
            ],
            [
                "name" => "Jane Doe",
                "email" => "jane@example.com",
                "password" => Hash::make("password"),
                "role_id" => 3,
                "profile_picture" => ""
            ],
            [
                "name" => "Alice Doe",
                "email" => "alice@example.com",
                "password" => Hash::make("password"),
                "role_id" => 2,
                "profile_picture" => ""
            ],
            [
                "name" => "Mike Burton",
                "email" => "mike@example.com",
                "password" => Hash::make("password"),
                "role_id" => 3,
                "profile_picture" => ""
            ]
        ]);
    }
}
