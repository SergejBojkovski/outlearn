<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CourseCollection;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = Course::class;
        $this->resourceClass = CourseResource::class;
        $this->collectionClass = CourseCollection::class;
        
        $this->storeRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ];
        
        $this->updateRules = [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
        ];
        
        $this->searchableFields = ['title', 'description'];
        $this->filterableFields = ['category_id'];
        $this->sortableFields = ['title', 'created_at', 'updated_at'];
        $this->defaultRelations = ['category', 'modules'];
    }
    
    /**
     * Get courses by category
     *
     * @param int $categoryId
     * @return CourseCollection
     */
    public function getByCategory(int $categoryId)
    {
        $courses = Course::where('category_id', $categoryId)
            ->with($this->defaultRelations)
            ->paginate(10);
            
        return new CourseCollection($courses);
    }
    
    /**
     * Get popular courses based on enrollment count
     *
     * @param Request $request
     * @return CourseCollection
     */
    public function getPopular(Request $request)
    {
        $limit = $request->input('limit', 5);
        
        $courses = Course::withCount('students')
            ->with($this->defaultRelations)
            ->orderByDesc('students_count')
            ->limit($limit)
            ->paginate($limit);
            
        return new CourseCollection($courses);
    }
    
    /**
     * Get courses for a specific user (either as student or professor)
     *
     * @param Request $request
     * @param int $userId
     * @return CourseCollection
     */
    public function getForUser(Request $request, int $userId)
    {
        $role = $request->input('role', 'student');
        $user = User::findOrFail($userId);
        
        if ($role === 'professor') {
            $courses = $user->professorCourses()->with($this->defaultRelations)->paginate(10);
        } else {
            $courses = $user->courses()->with($this->defaultRelations)->paginate(10);
        }
        
        return new CourseCollection($courses);
    }
    
    /**
     * Get recent courses
     *
     * @param Request $request
     * @return CourseCollection
     */
    public function getRecent(Request $request)
    {
        $limit = $request->input('limit', 5);
        
        $courses = Course::with($this->defaultRelations)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->paginate($limit);
            
        return new CourseCollection($courses);
    }
    
    /**
     * Enroll a user in a course
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function enrollUser(Request $request, int $id): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $course = Course::findOrFail($id);
        $userId = $request->input('user_id');
        
        // Check if user is already enrolled
        if ($course->students()->where('users.id', $userId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already enrolled in this course'
            ], 422);
        }
        
        $course->students()->attach($userId);
        
        return response()->json([
            'status' => 'success',
            'message' => 'User enrolled successfully'
        ]);
    }
    
    /**
     * Unenroll a user from a course
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function unenrollUser(Request $request, int $id): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $course = Course::findOrFail($id);
        $userId = $request->input('user_id');
        
        $course->students()->detach($userId);
        
        return response()->json([
            'status' => 'success',
            'message' => 'User unenrolled successfully'
        ]);
    }
} 