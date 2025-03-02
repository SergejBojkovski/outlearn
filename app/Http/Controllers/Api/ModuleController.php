<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ModuleCollection;
use App\Http\Resources\ModuleResource;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = Module::class;
        $this->resourceClass = ModuleResource::class;
        $this->collectionClass = ModuleCollection::class;
        
        $this->storeRules = [
            'name' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
            'course_id' => 'required|exists:courses,id',
        ];
        
        $this->updateRules = [
            'name' => 'sometimes|required|string|max:255',
            'order' => 'sometimes|required|integer|min:1',
            'course_id' => 'sometimes|required|exists:courses,id',
        ];
        
        $this->searchableFields = ['name'];
        $this->filterableFields = ['course_id', 'order'];
        $this->sortableFields = ['name', 'order', 'created_at', 'updated_at'];
        $this->defaultRelations = ['lessons'];
    }
    
    /**
     * Get modules for a specific course with their lessons
     *
     * @param int $courseId
     * @return ModuleCollection
     */
    public function getByCourse(int $courseId)
    {
        $modules = Module::where('course_id', $courseId)
            ->with('lessons')
            ->orderBy('order')
            ->paginate(20);
            
        return new ModuleCollection($modules);
    }
    
    /**
     * Reorder modules within a course
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $modules = $request->input('modules');
        
        // Update each module with its new order
        foreach ($modules as $moduleData) {
            Module::find($moduleData['id'])->update(['order' => $moduleData['order']]);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Modules reordered successfully'
        ]);
    }
    
    /**
     * Get next and previous modules for navigation
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getNavigation(int $id): JsonResponse
    {
        $currentModule = Module::findOrFail($id);
        $courseId = $currentModule->course_id;
        
        // Get previous module (if any)
        $previousModule = Module::where('course_id', $courseId)
            ->where('order', '<', $currentModule->order)
            ->orderByDesc('order')
            ->first();
            
        // Get next module (if any)
        $nextModule = Module::where('course_id', $courseId)
            ->where('order', '>', $currentModule->order)
            ->orderBy('order')
            ->first();
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'previous' => $previousModule ? new ModuleResource($previousModule) : null,
                'next' => $nextModule ? new ModuleResource($nextModule) : null,
            ],
            'message' => 'Module navigation retrieved successfully'
        ]);
    }
} 