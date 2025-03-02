<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class RoleController extends Controller
{
    protected $validationRules = [
        'name' => 'required|string|max:255|unique:roles',
        'description' => 'nullable|string',
        'permissions' => 'nullable|array',
        'permissions.*' => 'string',
    ];

    /**
     * Display a listing of roles.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        
        $query = Role::query();
        
        // Apply search
        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        
        // Apply sorting
        if (in_array($sort, ['name', 'created_at', 'updated_at'])) {
            $query->orderBy($sort, $direction);
        }
        
        $roles = $query->paginate(10);
        
        return view('roles.index', [
            'roles' => $roles,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new role.
     *
     * @return View
     */
    public function create()
    {
        // Get all available permissions (this is a placeholder - adjust based on your permissions structure)
        $availablePermissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_courses',
            'create_courses',
            'edit_courses',
            'delete_courses',
            'view_modules',
            'create_modules',
            'edit_modules',
            'delete_modules',
            'view_lessons',
            'create_lessons',
            'edit_lessons',
            'delete_lessons',
            'view_achievements',
            'create_achievements',
            'edit_achievements',
            'delete_achievements',
            'manage_roles',
            'view_reports',
            'manage_settings',
        ];
        
        return view('roles.create', [
            'availablePermissions' => $availablePermissions
        ]);
    }

    /**
     * Store a newly created role in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('roles.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $roleData = $request->all();
        $roleData['permissions'] = $request->input('permissions', []);
        
        $role = Role::create($roleData);
        
        return redirect()->route('roles.show', $role->id)
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);
        
        return view('roles.show', [
            'role' => $role
        ]);
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        
        // Get all available permissions (this is a placeholder - adjust based on your permissions structure)
        $availablePermissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_courses',
            'create_courses',
            'edit_courses',
            'delete_courses',
            'view_modules',
            'create_modules',
            'edit_modules',
            'delete_modules',
            'view_lessons',
            'create_lessons',
            'edit_lessons',
            'delete_lessons',
            'view_achievements',
            'create_achievements',
            'edit_achievements',
            'delete_achievements',
            'manage_roles',
            'view_reports',
            'manage_settings',
        ];
        
        return view('roles.edit', [
            'role' => $role,
            'availablePermissions' => $availablePermissions
        ]);
    }

    /**
     * Update the specified role in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('roles.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $roleData = $request->all();
        $roleData['permissions'] = $request->input('permissions', []);
        
        $role->update($roleData);
        
        return redirect()->route('roles.show', $id)
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Check if users are assigned to this role
        if ($role->users && $role->users->count() > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role because it is assigned to users.');
        }
        
        $role->delete();
        
        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
    
    /**
     * Get all available roles for dropdowns.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $roles = Role::select('id', 'name')->orderBy('name')->get();
        return response()->json($roles);
    }
    
    /**
     * Assign a role to a user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $userId = $request->input('user_id');
        $roleId = $request->input('role_id');
        
        // Assuming you have a user-role relationship in your models
        $user = \App\Models\User::findOrFail($userId);
        $user->role_id = $roleId;
        $user->save();
        
        return redirect()->back()
            ->with('success', 'Role assigned successfully.');
    }
    
    /**
     * Bulk delete roles.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('roles.index')
                ->with('error', 'No roles selected for deletion.');
        }
        
        // Check if any roles have users assigned
        $rolesWithUsers = Role::whereIn('id', $ids)
            ->has('users')
            ->pluck('name')
            ->toArray();
            
        if (!empty($rolesWithUsers)) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete roles: ' . implode(', ', $rolesWithUsers) . ' because they are assigned to users.');
        }
        
        Role::whereIn('id', $ids)->delete();
        
        return redirect()->route('roles.index')
            ->with('success', count($ids) . ' roles deleted successfully.');
    }
} 