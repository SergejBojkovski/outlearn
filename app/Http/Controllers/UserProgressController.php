<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserProgressController extends Controller
{
    /**
     * Display a dashboard with the current user's progress.
     *
     * @param Request $request
     * @return View
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get enrolled courses with progress
        $enrolledCourses = $user->enrolledCourses()
            ->with(['modules.lessons'])
            ->get();
            
        foreach ($enrolledCourses as $course) {
            // Calculate course progress
            $totalLessons = 0;
            $completedLessons = 0;
            
            foreach ($course->modules as $module) {
                $totalLessons += $module->lessons->count();
                $completedLessons += $user->completedLessons()
                    ->whereIn('lesson_id', $module->lessons->pluck('id'))
                    ->count();
            }
            
            $course->progress = $totalLessons > 0 
                ? round(($completedLessons / $totalLessons) * 100) 
                : 0;
        }
        
        // Get user achievements
        $userAchievements = $user->achievements()
            ->orderBy('pivot.awarded_at', 'desc')
            ->take(5)
            ->get();
            
        // Get recent activity
        $recentActivity = $user->completedLessons()
            ->with('lesson.module.course')
            ->orderBy('pivot.completed_at', 'desc')
            ->take(10)
            ->get();
            
        return view('progress.dashboard', [
            'user' => $user,
            'enrolledCourses' => $enrolledCourses,
            'userAchievements' => $userAchievements,
            'recentActivity' => $recentActivity
        ]);
    }
    
    /**
     * Display detailed progress for a specific course.
     *
     * @param int $courseId
     * @return View
     */
    public function courseProgress($courseId)
    {
        $user = Auth::user();
        $course = Course::with(['modules.lessons'])->findOrFail($courseId);
        
        // Check if user is enrolled in the course
        $isEnrolled = $user->enrolledCourses()->where('course_id', $courseId)->exists();
        
        if (!$isEnrolled) {
            return redirect()->route('courses.show', $courseId)
                ->with('error', 'You are not enrolled in this course.');
        }
        
        // Get completed lessons
        $completedLessonIds = $user->completedLessons()
            ->pluck('lesson_id')
            ->toArray();
            
        // Calculate course and module progress
        $totalLessons = 0;
        $totalCompletedLessons = 0;
        
        foreach ($course->modules as $module) {
            $moduleLessonCount = $module->lessons->count();
            $moduleCompletedCount = 0;
            
            foreach ($module->lessons as $lesson) {
                if (in_array($lesson->id, $completedLessonIds)) {
                    $lesson->completed = true;
                    $moduleCompletedCount++;
                } else {
                    $lesson->completed = false;
                }
            }
            
            $module->lessonCount = $moduleLessonCount;
            $module->completedCount = $moduleCompletedCount;
            $module->progress = $moduleLessonCount > 0 
                ? round(($moduleCompletedCount / $moduleLessonCount) * 100) 
                : 0;
                
            $totalLessons += $moduleLessonCount;
            $totalCompletedLessons += $moduleCompletedCount;
        }
        
        $courseProgress = $totalLessons > 0 
            ? round(($totalCompletedLessons / $totalLessons) * 100) 
            : 0;
            
        // Get course-related achievements
        $courseAchievements = Achievement::where('course_id', $courseId)
            ->get();
            
        $userAchievementIds = $user->achievements()->pluck('achievement_id')->toArray();
        
        foreach ($courseAchievements as $achievement) {
            $achievement->unlocked = in_array($achievement->id, $userAchievementIds);
        }
        
        return view('progress.course', [
            'user' => $user,
            'course' => $course,
            'courseProgress' => $courseProgress,
            'achievements' => $courseAchievements
        ]);
    }
    
    /**
     * Display progress for a specific module.
     *
     * @param int $moduleId
     * @return View
     */
    public function moduleProgress($moduleId)
    {
        $user = Auth::user();
        $module = Module::with(['lessons', 'course'])->findOrFail($moduleId);
        
        // Check if user is enrolled in the course
        $isEnrolled = $user->enrolledCourses()->where('course_id', $module->course_id)->exists();
        
        if (!$isEnrolled) {
            return redirect()->route('modules.show', $moduleId)
                ->with('error', 'You are not enrolled in this course.');
        }
        
        // Get completed lessons
        $completedLessonIds = $user->completedLessons()
            ->pluck('lesson_id')
            ->toArray();
            
        // Calculate module progress
        $totalLessons = $module->lessons->count();
        $completedLessons = 0;
        
        foreach ($module->lessons as $lesson) {
            if (in_array($lesson->id, $completedLessonIds)) {
                $lesson->completed = true;
                $completedLessons++;
            } else {
                $lesson->completed = false;
            }
            
            // Get completion date if available
            if ($lesson->completed) {
                $completionData = DB::table('lesson_user')
                    ->where('user_id', $user->id)
                    ->where('lesson_id', $lesson->id)
                    ->first();
                    
                $lesson->completedAt = $completionData ? $completionData->completed_at : null;
            }
        }
        
        $moduleProgress = $totalLessons > 0 
            ? round(($completedLessons / $totalLessons) * 100) 
            : 0;
            
        return view('progress.module', [
            'user' => $user,
            'module' => $module,
            'moduleProgress' => $moduleProgress,
            'completedLessons' => $completedLessons,
            'totalLessons' => $totalLessons
        ]);
    }
    
    /**
     * Mark a lesson as complete or incomplete for the current user.
     *
     * @param Request $request
     * @param int $lessonId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleLessonCompletion(Request $request, $lessonId)
    {
        $user = Auth::user();
        $lesson = Lesson::with('module.course')->findOrFail($lessonId);
        
        // Check if user is enrolled in the course
        $isEnrolled = $user->enrolledCourses()->where('course_id', $lesson->module->course_id)->exists();
        
        if (!$isEnrolled) {
            return redirect()->route('lessons.show', $lessonId)
                ->with('error', 'You are not enrolled in this course.');
        }
        
        $isCompleted = $user->completedLessons()->where('lesson_id', $lessonId)->exists();
        
        if ($isCompleted) {
            // Remove the completion record
            $user->completedLessons()->detach($lessonId);
            $message = 'Lesson marked as incomplete.';
        } else {
            // Add the completion record
            $user->completedLessons()->attach($lessonId, ['completed_at' => now()]);
            $message = 'Lesson marked as complete.';
            
            // Check for achievements that might be unlocked
            $this->checkForAchievements($user, $lesson);
        }
        
        if ($request->input('redirect') === 'module') {
            return redirect()->route('progress.module', $lesson->module_id)
                ->with('success', $message);
        } else {
            return redirect()->back()->with('success', $message);
        }
    }
    
    /**
     * Display a listing of achievements earned by the current user.
     *
     * @return View
     */
    public function achievements()
    {
        $user = Auth::user();
        $userAchievements = $user->achievements()
            ->orderBy('pivot.awarded_at', 'desc')
            ->get();
            
        // Group achievements by course
        $achievementsBySource = [];
        
        foreach ($userAchievements as $achievement) {
            if ($achievement->course_id) {
                $key = 'course_' . $achievement->course_id;
                $sourceName = $achievement->course ? $achievement->course->title : 'Unknown Course';
            } else {
                $key = 'general';
                $sourceName = 'General Achievements';
            }
            
            if (!isset($achievementsBySource[$key])) {
                $achievementsBySource[$key] = [
                    'source_name' => $sourceName,
                    'achievements' => []
                ];
            }
            
            $achievementsBySource[$key]['achievements'][] = $achievement;
        }
        
        return view('progress.achievements', [
            'user' => $user,
            'achievementsBySource' => $achievementsBySource
        ]);
    }
    
    /**
     * Display a report of all users' progress for admin purposes.
     *
     * @param Request $request
     * @return View
     */
    public function adminReport(Request $request)
    {
        // Only allow admin access
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return redirect()->route('home')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        $search = $request->input('search');
        $courseFilter = $request->input('course_id');
        
        $usersQuery = User::where('role', 'student');
        
        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $usersQuery->paginate(20);
        $courses = Course::orderBy('title')->get();
        
        // Get progress stats for each user
        foreach ($users as $user) {
            // Enrolled course count
            $user->enrolledCount = $user->enrolledCourses()->count();
            
            // Completed lesson count
            $user->completedLessonCount = $user->completedLessons()->count();
            
            // Achievement count
            $user->achievementCount = $user->achievements()->count();
            
            // Calculate overall progress across all courses
            if ($courseFilter) {
                // Calculate progress for a specific course
                $course = Course::with('modules.lessons')->find($courseFilter);
                
                if ($course && $user->enrolledCourses()->where('course_id', $courseFilter)->exists()) {
                    $totalLessons = 0;
                    $completedLessons = 0;
                    
                    foreach ($course->modules as $module) {
                        $totalLessons += $module->lessons->count();
                        $completedLessons += $user->completedLessons()
                            ->whereIn('lesson_id', $module->lessons->pluck('id'))
                            ->count();
                    }
                    
                    $user->progress = $totalLessons > 0 
                        ? round(($completedLessons / $totalLessons) * 100) 
                        : 0;
                } else {
                    $user->progress = 0;
                }
            } else {
                // Calculate average progress across all enrolled courses
                $totalProgress = 0;
                $enrolledCourses = $user->enrolledCourses()->with('modules.lessons')->get();
                
                if ($enrolledCourses->count() > 0) {
                    foreach ($enrolledCourses as $course) {
                        $totalLessons = 0;
                        $completedLessons = 0;
                        
                        foreach ($course->modules as $module) {
                            $totalLessons += $module->lessons->count();
                            $completedLessons += $user->completedLessons()
                                ->whereIn('lesson_id', $module->lessons->pluck('id'))
                                ->count();
                        }
                        
                        $courseProgress = $totalLessons > 0 
                            ? ($completedLessons / $totalLessons) * 100 
                            : 0;
                            
                        $totalProgress += $courseProgress;
                    }
                    
                    $user->progress = round($totalProgress / $enrolledCourses->count());
                } else {
                    $user->progress = 0;
                }
            }
        }
        
        return view('progress.admin-report', [
            'users' => $users,
            'courses' => $courses,
            'search' => $search,
            'courseFilter' => $courseFilter
        ]);
    }
    
    /**
     * Display detailed progress for a specific user (admin only).
     *
     * @param int $userId
     * @return View
     */
    public function userReport($userId)
    {
        // Only allow admin access
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return redirect()->route('home')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        $targetUser = User::findOrFail($userId);
        
        // Get enrolled courses with progress
        $enrolledCourses = $targetUser->enrolledCourses()
            ->with(['modules.lessons'])
            ->get();
            
        foreach ($enrolledCourses as $course) {
            // Calculate course progress
            $totalLessons = 0;
            $completedLessons = 0;
            
            foreach ($course->modules as $module) {
                $totalLessons += $module->lessons->count();
                $completedLessons += $targetUser->completedLessons()
                    ->whereIn('lesson_id', $module->lessons->pluck('id'))
                    ->count();
            }
            
            $course->progress = $totalLessons > 0 
                ? round(($completedLessons / $totalLessons) * 100) 
                : 0;
                
            $course->completedLessonCount = $completedLessons;
            $course->totalLessonCount = $totalLessons;
        }
        
        // Get user achievements
        $userAchievements = $targetUser->achievements()
            ->orderBy('pivot.awarded_at', 'desc')
            ->get();
            
        // Get recent activity
        $recentActivity = $targetUser->completedLessons()
            ->with('lesson.module.course')
            ->orderBy('pivot.completed_at', 'desc')
            ->take(20)
            ->get();
            
        return view('progress.user-report', [
            'targetUser' => $targetUser,
            'enrolledCourses' => $enrolledCourses,
            'userAchievements' => $userAchievements,
            'recentActivity' => $recentActivity
        ]);
    }
    
    /**
     * Get progress statistics for all courses.
     *
     * @return View
     */
    public function courseStats()
    {
        // Only allow admin access
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return redirect()->route('home')
                ->with('error', 'You do not have permission to access this page.');
        }
        
        $courses = Course::withCount('enrolledUsers')
            ->orderBy('title')
            ->get();
            
        foreach ($courses as $course) {
            // Calculate average completion percentage
            $enrolledUsers = $course->enrolledUsers()->get();
            $totalProgressPercentage = 0;
            
            foreach ($enrolledUsers as $user) {
                $totalLessons = 0;
                $completedLessons = 0;
                
                foreach ($course->modules as $module) {
                    $totalLessons += $module->lessons->count();
                    $completedLessons += $user->completedLessons()
                        ->whereIn('lesson_id', $module->lessons->pluck('id'))
                        ->count();
                }
                
                $userProgress = $totalLessons > 0 
                    ? ($completedLessons / $totalLessons) * 100 
                    : 0;
                    
                $totalProgressPercentage += $userProgress;
            }
            
            $course->averageCompletion = $enrolledUsers->count() > 0 
                ? round($totalProgressPercentage / $enrolledUsers->count()) 
                : 0;
                
            // Get number of lessons
            $course->lessonCount = DB::table('lessons')
                ->join('modules', 'lessons.module_id', '=', 'modules.id')
                ->where('modules.course_id', $course->id)
                ->count();
                
            // Get completion count (number of completed lesson records)
            $course->completionCount = DB::table('lesson_user')
                ->join('lessons', 'lesson_user.lesson_id', '=', 'lessons.id')
                ->join('modules', 'lessons.module_id', '=', 'modules.id')
                ->where('modules.course_id', $course->id)
                ->count();
        }
        
        return view('progress.course-stats', [
            'courses' => $courses
        ]);
    }
    
    /**
     * Check for any achievements that may have been unlocked by completing a lesson.
     *
     * @param User $user
     * @param Lesson $lesson
     * @return void
     */
    private function checkForAchievements(User $user, Lesson $lesson)
    {
        $course = $lesson->module->course;
        
        // Check for course completion achievement
        $totalLessons = 0;
        $completedLessons = 0;
        
        foreach ($course->modules as $module) {
            $totalLessons += $module->lessons->count();
            $completedLessons += $user->completedLessons()
                ->whereIn('lesson_id', $module->lessons->pluck('id'))
                ->count();
        }
        
        // If course is completed, check for achievement
        if ($totalLessons > 0 && $completedLessons >= $totalLessons) {
            $achievement = Achievement::where('type', 'course_completion')
                ->where('course_id', $course->id)
                ->first();
                
            if ($achievement && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                $user->achievements()->attach($achievement->id, ['awarded_at' => now()]);
            }
        }
        
        // Check for module completion achievement
        $module = $lesson->module;
        $moduleLessons = $module->lessons->count();
        $moduleCompleted = $user->completedLessons()
            ->whereIn('lesson_id', $module->lessons->pluck('id'))
            ->count();
            
        if ($moduleLessons > 0 && $moduleCompleted >= $moduleLessons) {
            $achievement = Achievement::where('type', 'module_completion')
                ->where('module_id', $module->id)
                ->first();
                
            if ($achievement && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                $user->achievements()->attach($achievement->id, ['awarded_at' => now()]);
            }
        }
        
        // Check for first lesson achievement
        if ($user->completedLessons()->count() == 1) {
            $achievement = Achievement::where('type', 'first_lesson')
                ->whereNull('course_id')
                ->first();
                
            if ($achievement && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                $user->achievements()->attach($achievement->id, ['awarded_at' => now()]);
            }
        }
        
        // Check for lesson count achievements (5, 10, 25, 50, 100)
        $lessonCounts = [5, 10, 25, 50, 100];
        $userLessonCount = $user->completedLessons()->count();
        
        foreach ($lessonCounts as $count) {
            if ($userLessonCount == $count) {
                $achievement = Achievement::where('type', 'lesson_count')
                    ->where('condition_value', $count)
                    ->first();
                    
                if ($achievement && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                    $user->achievements()->attach($achievement->id, ['awarded_at' => now()]);
                }
            }
        }
    }
} 