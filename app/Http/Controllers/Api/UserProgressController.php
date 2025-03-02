<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserProgressCollection;
use App\Http\Resources\UserProgressResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProgressController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = UserProgress::class;
        $this->resourceClass = UserProgressResource::class;
        $this->collectionClass = UserProgressCollection::class;
        
        $this->storeRules = [
            'user_id' => 'required|exists:users,id',
            'lesson_id' => 'required|exists:lessons,id',
            'completed' => 'required|boolean',
            'completion_date' => 'nullable|date'
        ];
        
        $this->updateRules = [
            'user_id' => 'sometimes|required|exists:users,id',
            'lesson_id' => 'sometimes|required|exists:lessons,id',
            'completed' => 'sometimes|required|boolean',
            'completion_date' => 'nullable|date'
        ];
        
        $this->searchableFields = [];
        $this->filterableFields = ['user_id', 'lesson_id', 'completed'];
        $this->sortableFields = ['completion_date', 'created_at', 'updated_at'];
        $this->defaultRelations = ['user', 'lesson', 'lesson.module', 'lesson.module.course'];
    }
    
    /**
     * Get progress for a specific user
     *
     * @param int $userId
     * @return UserProgressCollection
     */
    public function getByUser(int $userId)
    {
        $progress = UserProgress::where('user_id', $userId)
            ->with($this->defaultRelations)
            ->paginate(20);
            
        return new UserProgressCollection($progress);
    }
    
    /**
     * Get progress for a specific user in a specific course
     *
     * @param int $userId
     * @param int $courseId
     * @return JsonResponse
     */
    public function getUserCourseProgress(int $userId, int $courseId): JsonResponse
    {
        // Get all modules in the course
        $modules = Module::where('course_id', $courseId)->get();
        $moduleIds = $modules->pluck('id')->toArray();
        
        // Get all lessons in these modules
        $lessons = Lesson::whereIn('module_id', $moduleIds)->get();
        $lessonIds = $lessons->pluck('id')->toArray();
        $totalLessons = count($lessonIds);
        
        // Get completed lessons for this user in this course
        $completedLessons = UserProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->where('completed', true)
            ->count();
            
        // Calculate progress percentage
        $progressPercentage = $totalLessons > 0 
            ? round(($completedLessons / $totalLessons) * 100) 
            : 0;
            
        // Get last accessed lesson
        $lastAccessed = UserProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->orderBy('updated_at', 'desc')
            ->with(['lesson'])
            ->first();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'progress_percentage' => $progressPercentage,
                'last_accessed' => $lastAccessed ? new UserProgressResource($lastAccessed) : null,
            ],
            'message' => 'User course progress retrieved successfully'
        ]);
    }
    
    /**
     * Get progress summary for a user across all courses
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserProgressSummary(int $userId): JsonResponse
    {
        // Verify user exists
        $user = User::findOrFail($userId);
        
        // Get all courses
        $courses = Course::all();
        $courseSummaries = [];
        
        foreach ($courses as $course) {
            // Get all modules in the course
            $modules = Module::where('course_id', $course->id)->get();
            $moduleIds = $modules->pluck('id')->toArray();
            
            // Get all lessons in these modules
            $lessons = Lesson::whereIn('module_id', $moduleIds)->get();
            $lessonIds = $lessons->pluck('id')->toArray();
            $totalLessons = count($lessonIds);
            
            // Get completed lessons for this user in this course
            $completedLessons = UserProgress::where('user_id', $userId)
                ->whereIn('lesson_id', $lessonIds)
                ->where('completed', true)
                ->count();
                
            // Calculate progress percentage
            $progressPercentage = $totalLessons > 0 
                ? round(($completedLessons / $totalLessons) * 100) 
                : 0;
                
            // Add to summary if there's any progress or if we want to show all courses
            if ($completedLessons > 0) {
                $courseSummaries[] = [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'progress_percentage' => $progressPercentage,
                ];
            }
        }
        
        // Calculate overall progress
        $totalCompletedLessons = UserProgress::where('user_id', $userId)
            ->where('completed', true)
            ->count();
            
        $totalLessonsCount = Lesson::count();
        $overallProgress = $totalLessonsCount > 0 
            ? round(($totalCompletedLessons / $totalLessonsCount) * 100) 
            : 0;
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'overall_progress' => $overallProgress,
                'total_completed_lessons' => $totalCompletedLessons,
                'course_summaries' => $courseSummaries,
            ],
            'message' => 'User progress summary retrieved successfully'
        ]);
    }
    
    /**
     * Reset progress for a user in a course
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetProgress(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $userId = $request->input('user_id');
        $courseId = $request->input('course_id');
        
        // Get all modules in the course
        $modules = Module::where('course_id', $courseId)->get();
        $moduleIds = $modules->pluck('id')->toArray();
        
        // Get all lessons in these modules
        $lessons = Lesson::whereIn('module_id', $moduleIds)->get();
        $lessonIds = $lessons->pluck('id')->toArray();
        
        // Delete all progress records for this user in this course
        UserProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->delete();
            
        return response()->json([
            'status' => 'success',
            'message' => 'Progress reset successfully'
        ]);
    }
} 