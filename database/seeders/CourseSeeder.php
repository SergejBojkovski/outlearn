<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            ['name' => 'JavaScript Essentials', 'description' => "Beginner-friendly course that covers the fundamentals of JavaScript, including variables, functions, loops, and event handling. You'll learn how to write interactive web applications, manipulate the DOM, and understand core programming concepts. Perfect for aspiring developers looking to build a strong foundation in front-end development.", 'category_id' => '3'],
            ['name' => 'Python for Begginers', 'description' => ' Introductory course designed for those new to programming. You’ll learn the fundamentals of Python, including syntax, data types, loops, functions, and basic object-oriented programming (OOP). Through hands-on exercises and real-world examples, this course will help you build a strong foundation in coding.', 'category_id' => '5'],
            ['name' => 'Data Science Basics', 'description' => 'Introductory course designed for those new to programming. You’ll learn the fundamentals of Python, including syntax, data types, loops, functions, and basic object-oriented programming (OOP). Through hands-on exercises and real-world examples, this course will help you build a strong foundation in coding.', 'category_id' => '1'],
            ['name' => 'UX Design Principles', 'description' => 'Beginner-friendly course that covers the fundamentals of User Experience (UX) design. You’ll explore key concepts such as usability, user research, wireframing, prototyping, and accessibility. The course introduces essential tools like Figma, Adobe XD, and usability testing methods to help you create intuitive and user-friendly digital experiences.', 'category_id' => '7'], 
            ['name' => 'Machine Learning Intro', 'description' => 'Beginner-friendly course that introduces the fundamentals of machine learning. You’ll explore key concepts like supervised and unsupervised learning, algorithms, data preprocessing, and model evaluation. The course also covers practical applications and common tools like Python, Scikit-learn, and Jupyter Notebooks to help you get started with building machine learning models.', 'category_id' => '8'], 
            ['name' => 'Cloud Computing Fund.', 'description' => 'Beginner-friendly course that introduces the basics of cloud computing, including key concepts such as cloud service models (IaaS, PaaS, SaaS), deployment models, and the benefits of cloud-based solutions. The course covers popular cloud platforms like AWS, Microsoft Azure, and Google Cloud, with practical insights into how organizations leverage cloud services for scalability, security, and efficiency.', 'category_id' => '4'], 
        ];
    }
}
