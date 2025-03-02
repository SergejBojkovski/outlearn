<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProfesorDataSeeder extends Seeder
{
    public function run()
    {
        DB::table('professors_data')->insert([
            [
                'user_id' => 2,
                'position' => 'Lecturer',
                'company' => 'University 6',
                'gender' => 'male',
                'birth_date' => '1980-05-06',
                'work_experience_years' => 27,
                'created_at' => Carbon::parse('2025-02-16T08:48:19'),
                'updated_at' => Carbon::parse('2023-09-30T21:42:33'),
            ],
            [
                'user_id' => 4,
                'position' => 'Professor',
                'company' => 'University 7',
                'gender' => 'female',
                'birth_date' => '1974-02-27',
                'work_experience_years' => 28,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
