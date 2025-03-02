<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CourseCollection;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = Category::class;
        $this->resourceClass = CategoryResource::class;
        $this->collectionClass = CategoryCollection::class;
        
        $this->storeRules = [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string'
        ];
        
        $this->updateRules = [
            'name' => 'sometimes|required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string'
        ];
        
        $this->searchableFields = ['name', 'description'];
        $this->filterableFields = [];
        $this->sortableFields = ['name', 'created_at', 'updated_at'];
        $this->defaultRelations = [];
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $model = $this->modelClass::findOrFail($id);
        
        // Modify unique name validation rule to ignore current category
        $rules = $this->updateRules;
        if ($request->has('name') && $request->name !== $model->name) {
            $rules['name'] = 'required|string|max:255|unique:categories,name,' . $id;
        }
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $model->update($request->all());
        
        $resourceClass = $this->resourceClass;
        
        return response()->json([
            'status' => 'success',
            'data' => new $resourceClass($model),
            'message' => 'Resource updated successfully'
        ]);
    }
    
    /**
     * Get courses by category
     *
     * @param int $id
     * @return CourseCollection
     */
    public function getCourses(int $id)
    {
        $category = Category::findOrFail($id);
        $courses = Course::where('category_id', $id)
            ->with(['professor'])
            ->paginate(10);
            
        return new CourseCollection($courses);
    }
    
    /**
     * Get categories with course counts
     *
     * @return JsonResponse
     */
    public function getWithCourseCounts(): JsonResponse
    {
        $categories = Category::withCount('courses')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => CategoryResource::collection($categories),
            'message' => 'Categories with course counts retrieved successfully'
        ]);
    }
    
    /**
     * Get popular categories (those with the most courses)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPopular(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);
        
        $categories = Category::withCount('courses')
            ->orderBy('courses_count', 'desc')
            ->limit($limit)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => CategoryResource::collection($categories),
            'message' => 'Popular categories retrieved successfully'
        ]);
    }
} 