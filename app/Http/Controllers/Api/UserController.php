<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\ProfessorData;
use App\Models\Role;
use App\Models\StudentData;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = User::class;
        $this->resourceClass = UserResource::class;
        $this->collectionClass = UserCollection::class;
        
        $this->storeRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id'
        ];
        
        $this->updateRules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email',
            'password' => 'sometimes|required|string|min:8',
            'role_id' => 'sometimes|required|exists:roles,id'
        ];
        
        $this->searchableFields = ['name', 'email'];
        $this->filterableFields = ['role_id'];
        $this->sortableFields = ['name', 'email', 'created_at', 'updated_at'];
        $this->defaultRelations = ['role', 'studentData', 'professorData'];
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->storeRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        
        $model = $this->modelClass::create($data);
        
        // Create role-specific data
        $roleId = $request->input('role_id');
        $role = Role::find($roleId);
        
        if ($role && $role->name === 'student') {
            StudentData::create([
                'user_id' => $model->id,
                'student_number' => $request->input('student_number'),
                'enrollment_date' => now(),
            ]);
        } elseif ($role && $role->name === 'professor') {
            ProfessorData::create([
                'user_id' => $model->id,
                'department' => $request->input('department'),
                'specialization' => $request->input('specialization'),
            ]);
        }
        
        $model->load($this->defaultRelations);
        
        $resourceClass = $this->resourceClass;
        
        return response()->json([
            'status' => 'success',
            'data' => new $resourceClass($model),
            'message' => 'Resource created successfully'
        ], 201);
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
        
        // Modify unique email validation rule to ignore current user
        $rules = $this->updateRules;
        if ($request->has('email') && $request->email !== $model->email) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $id;
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $data = $request->all();
        
        // Hash password if it's being updated
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $model->update($data);
        
        // Update role-specific data
        if ($request->has('role_id')) {
            $roleId = $request->input('role_id');
            $role = Role::find($roleId);
            
            if ($role && $role->name === 'student') {
                StudentData::updateOrCreate(
                    ['user_id' => $model->id],
                    [
                        'student_number' => $request->input('student_number', null),
                        'enrollment_date' => $request->input('enrollment_date', now()),
                    ]
                );
                
                // Delete professor data if exists
                ProfessorData::where('user_id', $model->id)->delete();
                
            } elseif ($role && $role->name === 'professor') {
                ProfessorData::updateOrCreate(
                    ['user_id' => $model->id],
                    [
                        'department' => $request->input('department', null),
                        'specialization' => $request->input('specialization', null),
                    ]
                );
                
                // Delete student data if exists
                StudentData::where('user_id', $model->id)->delete();
            }
        }
        
        $model->load($this->defaultRelations);
        
        $resourceClass = $this->resourceClass;
        
        return response()->json([
            'status' => 'success',
            'data' => new $resourceClass($model),
            'message' => 'Resource updated successfully'
        ]);
    }
    
    /**
     * Get users by role
     *
     * @param int $roleId
     * @return UserCollection
     */
    public function getByRole(int $roleId)
    {
        $users = User::where('role_id', $roleId)
            ->with($this->defaultRelations)
            ->paginate(20);
            
        return new UserCollection($users);
    }
    
    /**
     * Get enrolled courses for a user
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getEnrolledCourses(int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $courses = $user->enrolledCourses()->with(['category'])->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $courses,
            'message' => 'Enrolled courses retrieved successfully'
        ]);
    }
    
    /**
     * Get taught courses for a professor
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getTaughtCourses(int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        
        // Check if user is a professor
        $role = $user->role;
        if (!$role || $role->name !== 'professor') {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a professor'
            ], 400);
        }
        
        $courses = Course::where('professor_id', $userId)
            ->with(['category'])
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $courses,
            'message' => 'Taught courses retrieved successfully'
        ]);
    }
    
    /**
     * Login user and return token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        $user->load($this->defaultRelations);
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'message' => 'Login successful'
        ]);
    }
    
    /**
     * Logout user (revoke token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }
    
    /**
     * Get authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load($this->defaultRelations);
        
        return response()->json([
            'status' => 'success',
            'data' => new UserResource($user),
            'message' => 'User retrieved successfully'
        ]);
    }
} 