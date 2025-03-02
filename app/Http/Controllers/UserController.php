<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class UserController extends Controller
{
    protected $validationRules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'role_id' => 'required|exists:roles,id',
    ];

    /**
     * Display a listing of the users.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        
        $query = User::with('role');
        
        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply filter
        if ($filter && isset($filter['role_id'])) {
            $query->where('role_id', $filter['role_id']);
        }
        
        // Apply sorting
        if (in_array($sort, ['name', 'email', 'created_at', 'updated_at'])) {
            $query->orderBy($sort, $direction);
        }
        
        $users = $query->paginate(10);
        $roles = Role::all();
        
        return view('users.index', [
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', ['roles' => $roles]);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('users.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $userData = $request->all();
        $userData['password'] = Hash::make($userData['password']);
        
        $user = User::create($userData);
        
        return redirect()->route('users.show', $user->id)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $user = User::with(['role', 'courses', 'professorCourses'])->findOrFail($id);
        return view('users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('users.edit', [
            'user' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'role_id' => 'sometimes|required|exists:roles,id',
        ];
        
        if ($request->has('password') && !empty($request->password)) {
            $rules['password'] = 'required|string|min:8';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->route('users.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $userData = $request->all();
        if ($request->has('password') && !empty($request->password)) {
            $userData['password'] = Hash::make($userData['password']);
        } else {
            unset($userData['password']);
        }
        
        $user->update($userData);
        
        return redirect()->route('users.show', $id)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
    
    /**
     * Get users by role
     *
     * @param int $roleId
     * @return View
     */
    public function getByRole(int $roleId)
    {
        $role = Role::findOrFail($roleId);
        $users = User::where('role_id', $roleId)->paginate(10);
        
        return view('users.by-role', [
            'users' => $users,
            'role' => $role
        ]);
    }
    
    /**
     * Get enrolled courses for a user
     *
     * @param int $id
     * @return View
     */
    public function getEnrolledCourses(int $id)
    {
        $user = User::findOrFail($id);
        $courses = $user->courses()->paginate(10);
        
        return view('users.enrolled-courses', [
            'user' => $user,
            'courses' => $courses
        ]);
    }
    
    /**
     * Get courses taught by a professor
     *
     * @param int $id
     * @return View
     */
    public function getTaughtCourses(int $id)
    {
        $user = User::findOrFail($id);
        $courses = $user->professorCourses()->paginate(10);
        
        return view('users.taught-courses', [
            'user' => $user,
            'courses' => $courses
        ]);
    }
    
    /**
     * Get current authenticated user
     *
     * @return View
     */
    public function me()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $role = $user->role;
        $enrolledCourses = $user->courses;
        $taughtCourses = $user->professorCourses;
        
        return view('users.profile', [
            'user' => $user,
            'role' => $role,
            'enrolledCourses' => $enrolledCourses,
            'taughtCourses' => $taughtCourses
        ]);
    }
    
    /**
     * Get user options for dropdowns
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $users = User::select('id', 'name')->orderBy('name')->get();
        return response()->json($users);
    }
} 