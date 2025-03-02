<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            ['user_id' => '1', 'name' => 'First Login', 'description' => 'Awarded for logging in for the first time.'],
            ['user_id' => '3', 'name' => 'Course Completion', 'description' => 'Awarded for completing your first course.'],
            ['user_id' => '1', 'name' => 'Quiz Master', 'description' => 'Awarded for achieving 90% or higher on a quiz.'],
            ['user_id' => '3', 'name' => 'Top Scorer', 'description' => 'Awarded for scoring the highest in a course exam.'],
            ['user_id' => '3', 'name' => 'Milestone Reached', 'description' => 'Awarded for completing 5 lessons in a single course.'],
            ['user_id' => '1', 'name' => 'Community Contributor', 'description' => 'Awarded for contributing to the forum with 10 helpful posts.'],
            ['user_id' => '1', 'name' => 'Advanced Learner', 'description' => 'Awarded for completing 10 advanced-level courses.'],
            ['user_id' => '3', 'name' => 'Early Adopter', 'description' => 'Awarded for joining the platform within the first month of launch.'],
            ['user_id' => '3', 'name' => 'Perfectionist', 'description' => 'Awarded for completing a course with a perfect score.'],
            ['user_id' => '1', 'name' => 'Certification Earned', 'description' => 'Awarded for earning a certificate in any course.'],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}
