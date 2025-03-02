<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Validator;

abstract class BaseApiController extends Controller
{
    /**
     * The model class for this controller.
     *
     * @var string
     */
    protected string $modelClass;

    /**
     * The resource class for this controller.
     *
     * @var string
     */
    protected string $resourceClass;

    /**
     * The collection class for this controller.
     *
     * @var string
     */
    protected string $collectionClass;

    /**
     * Validation rules for store method.
     *
     * @var array
     */
    protected array $storeRules = [];

    /**
     * Validation rules for update method.
     *
     * @var array
     */
    protected array $updateRules = [];

    /**
     * Default relationships to load with the model.
     *
     * @var array
     */
    protected array $defaultRelations = [];

    /**
     * Valid search fields for the model.
     *
     * @var array
     */
    protected array $searchableFields = [];

    /**
     * Valid filter fields for the model.
     *
     * @var array
     */
    protected array $filterableFields = [];

    /**
     * Valid sort fields for the model.
     *
     * @var array
     */
    protected array $sortableFields = [];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ResourceCollection
     */
    public function index(Request $request)
    {
        $query = $this->modelClass::query();
        
        // Apply search
        if ($request->has('search') && !empty($this->searchableFields)) {
            $searchTerm = $request->input('search');
            $query->where(function (Builder $q) use ($searchTerm) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$searchTerm}%");
                }
            });
        }
        
        // Apply filters
        foreach ($this->filterableFields as $field) {
            if ($request->has($field)) {
                $query->where($field, $request->input($field));
            }
        }
        
        // Apply sorting
        if ($request->has('sort_by') && in_array($request->input('sort_by'), $this->sortableFields)) {
            $direction = $request->input('sort_direction', 'asc');
            $query->orderBy($request->input('sort_by'), $direction);
        }
        
        // Load relationships
        if (!empty($this->defaultRelations)) {
            $query->with($this->defaultRelations);
        }
        
        // Custom "with" relations
        if ($request->has('with')) {
            $relations = explode(',', $request->input('with'));
            $validRelations = array_intersect($relations, $this->defaultRelations);
            if (!empty($validRelations)) {
                $query->with($validRelations);
            }
        }
        
        $perPage = $request->input('per_page', 10);
        $items = $query->paginate($perPage);
        $collectionClass = $this->collectionClass;
        
        return new $collectionClass($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|JsonResource
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->storeRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $item = $this->modelClass::create($validator->validated());
        
        // Load relationships if needed
        if (!empty($this->defaultRelations)) {
            $item->load($this->defaultRelations);
        }
        
        $resourceClass = $this->resourceClass;

        return (new $resourceClass($item))
            ->additional([
                'status' => 'success',
                'message' => class_basename($this->modelClass) . ' created successfully'
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Model $model
     * @return JsonResource
     */
    public function show(Request $request, Model $model)
    {
        // Load default relationships or requested relationships
        if ($request->has('with')) {
            $relations = explode(',', $request->input('with'));
            $validRelations = array_intersect($relations, $this->defaultRelations);
            if (!empty($validRelations)) {
                $model->load($validRelations);
            }
        } else if (!empty($this->defaultRelations)) {
            $model->load($this->defaultRelations);
        }
        
        $resourceClass = $this->resourceClass;
        
        return (new $resourceClass($model))
            ->additional([
                'status' => 'success',
                'message' => class_basename($this->modelClass) . ' retrieved successfully'
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Model $model
     * @return JsonResponse|JsonResource
     */
    public function update(Request $request, Model $model)
    {
        // Replace ID placeholder in unique validation rules
        $rules = $this->updateRules;
        foreach ($rules as $key => $rule) {
            if (is_string($rule)) {
                $rules[$key] = str_replace('{id}', $model->id, $rule);
            }
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $model->update($validator->validated());
        
        // Load relationships if needed
        if (!empty($this->defaultRelations)) {
            $model->load($this->defaultRelations);
        }
        
        $resourceClass = $this->resourceClass;

        return (new $resourceClass($model))
            ->additional([
                'status' => 'success',
                'message' => class_basename($this->modelClass) . ' updated successfully'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Model $model
     * @return JsonResponse
     */
    public function destroy(Model $model): JsonResponse
    {
        $model->delete();

        return response()->json([
            'status' => 'success',
            'message' => class_basename($this->modelClass) . ' deleted successfully'
        ]);
    }
    
    /**
     * Get all available options for dropdown lists.
     *
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        $query = $this->modelClass::query();
        
        // Only select id and a descriptive field (assuming 'name' or 'title' exists)
        $descriptiveField = $this->getDescriptiveField();
        
        $options = $query->select(['id', $descriptiveField])
            ->orderBy($descriptiveField)
            ->get()
            ->map(function ($item) use ($descriptiveField) {
                return [
                    'value' => $item->id,
                    'label' => $item->{$descriptiveField}
                ];
            });
            
        return response()->json([
            'status' => 'success',
            'data' => $options,
            'message' => class_basename($this->modelClass) . ' options retrieved successfully'
        ]);
    }
    
    /**
     * Bulk delete multiple resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:' . $this->getTable() . ',id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $count = $this->modelClass::whereIn('id', $request->input('ids'))->delete();

        return response()->json([
            'status' => 'success',
            'count' => $count,
            'message' => $count . ' ' . class_basename($this->modelClass) . '(s) deleted successfully'
        ]);
    }
    
    /**
     * Get the descriptive field for the model (name or title).
     *
     * @return string
     */
    protected function getDescriptiveField(): string
    {
        $model = new $this->modelClass;
        $columns = $model->getFillable();
        
        // First try name, then title, if neither use first string field
        if (in_array('name', $columns)) {
            return 'name';
        } elseif (in_array('title', $columns)) {
            return 'title';
        }
        
        // If no name or title, use the first string field
        foreach ($columns as $column) {
            return $column;
        }
        
        // Fallback to id if nothing else
        return 'id';
    }
    
    /**
     * Get the table name for the model.
     *
     * @return string
     */
    protected function getTable(): string
    {
        return (new $this->modelClass)->getTable();
    }
} 