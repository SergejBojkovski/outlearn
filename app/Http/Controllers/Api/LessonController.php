<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\LessonCollection;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = Lesson::class;
        $this->resourceClass = LessonResource::class;
        $this->collectionClass = LessonCollection::class;
        
        $this->storeRules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'order_number' => 'required|integer|min:1',
            'module_id' => 'required|exists:modules,id'
        ];
        
        $this->updateRules = [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'video_url' => 'nullable|url',
            'order_number' => 'sometimes|required|integer|min:1',
            'module_id' => 'sometimes|required|exists:modules,id'
        ];
        
        $this->searchableFields = ['title', 'content'];
        $this->filterableFields = ['module_id', 'order_number'];
        $this->sortableFields = ['title', 'order_number', 'created_at', 'updated_at'];
        $this->defaultRelations = ['module', 'module.course'];
    }
    
    /**
     * Get lessons for a specific module
     *
     * @param int $moduleId
     * @return LessonCollection
     */
    public function getByModule(int $moduleId)
    {
        $lessons = Lesson::where('module_id', $moduleId)
            ->with($this->defaultRelations)
            ->orderBy('order_number')
            ->paginate(20);
            
        return new LessonCollection($lessons);
    }
    
    /**
     * Reorder lessons within a module
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|exists:lessons,id',
            'lessons.*.order_number' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $lessons = $request->input('lessons');
        
        // Update each lesson with its new order
        foreach ($lessons as $lessonData) {
            Lesson::find($lessonData['id'])->update(['order_number' => $lessonData['order_number']]);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Lessons reordered successfully'
        ]);
    }
    
    /**
     * Get navigation links (previous and next lesson)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getNavigation(int $id): JsonResponse
    {
        $currentLesson = Lesson::findOrFail($id);
        $moduleId = $currentLesson->module_id;
        
        // Get previous lesson (if any)
        $previousLesson = Lesson::where('module_id', $moduleId)
            ->where('order_number', '<', $currentLesson->order_number)
            ->orderByDesc('order_number')
            ->first();
            
        // Get next lesson (if any)
        $nextLesson = Lesson::where('module_id', $moduleId)
            ->where('order_number', '>', $currentLesson->order_number)
            ->orderBy('order_number')
            ->first();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'previous' => $previousLesson ? new LessonResource($previousLesson) : null,
                'next' => $nextLesson ? new LessonResource($nextLesson) : null,
            ],
            'message' => 'Lesson navigation retrieved successfully'
        ]);
    }
    
    /**
     * Mark a lesson as completed for a user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function markCompleted(Request $request, int $id): JsonResponse
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
        
        $lesson = Lesson::findOrFail($id);
        $userId = $request->input('user_id');
        
        // Create or update progress
        UserProgress::updateOrCreate(
            ['user_id' => $userId, 'lesson_id' => $id],
            ['completed' => true, 'completion_date' => now()]
        );
        
        return response()->json([
            'status' => 'success',
            'message' => 'Lesson marked as completed'
        ]);
    }
    
    /**
     * Get completed status of a lesson for a user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getCompletionStatus(Request $request, int $id): JsonResponse
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
        
        $userId = $request->input('user_id');
        
        $progress = UserProgress::where('user_id', $userId)
            ->where('lesson_id', $id)
            ->first();
            
        $isCompleted = $progress && $progress->completed;
        $completionDate = $progress ? $progress->completion_date : null;
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'completed' => $isCompleted,
                'completion_date' => $completionDate,
            ],
            'message' => 'Lesson completion status retrieved successfully'
        ]);
    }
} 