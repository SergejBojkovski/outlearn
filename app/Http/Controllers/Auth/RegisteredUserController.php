<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Models\StudentData;
use App\Models\ProfessorData;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'profile_picture' => 'default.jpg', // Default profile picture
        ]);

        // Create role-specific data
        $roleId = $request->role_id;
        if ($roleId == 2) { // Student
            StudentData::create([
                'user_id' => $user->id,
                'student_number' => 'STU' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'enrollment_date' => now(),
            ]);
        } elseif ($roleId == 3) { // Professor
            ProfessorData::create([
                'user_id' => $user->id,
                'department' => 'General',
                'specialization' => 'General',
            ]);
        }

        event(new Registered($user));

        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
                'message' => 'Registration successful'
            ], 201);
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
