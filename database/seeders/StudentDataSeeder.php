<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('student_data')->insert([
            [
                "user_id" => 1,
                'gender' => 'male',
                'birth_date' => '2008-06-30',
                'school_year' => '11th Grade',
                'field_of_study' => 'Literature',
                'current_school' => 'High School 80',
                'created_at' => Carbon::parse('2024-09-20T00:54:04'),
                'updated_at' => Carbon::parse('2023-08-11T17:03:53'),
            ],
            [
                "user_id" => 3,
                'gender' => 'male',
                'birth_date' => '2004-08-02',
                'school_year' => '11th Grade',
                'field_of_study' => 'Literature',
                'current_school' => 'High School 24',
                'created_at' => Carbon::parse('2024-12-22T05:36:55'),
                'updated_at' => Carbon::parse('2023-08-20T11:19:11'),
            ]
        ]);
    }
}
