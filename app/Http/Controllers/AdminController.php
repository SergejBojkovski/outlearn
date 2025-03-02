<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware is already applied in routes/web.php
        // No need to apply it here again
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $totalUsers = User::count();
        $totalCourses = Course::count();
        $totalCategories = Category::count();
        $totalLessons = Lesson::count();

        $recentUsers = User::latest()->take(5)->with('role')->get();
        $recentCourses = Course::latest()->take(5)->with('category')->get();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalCourses' => $totalCourses,
            'totalCategories' => $totalCategories,
            'totalLessons' => $totalLessons,
            'recentUsers' => $recentUsers,
            'recentCourses' => $recentCourses,
        ]);
    }

    /**
     * Show the users management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function users()
    {
        $users = User::with('role')->paginate(10);
        $roles = Role::all();
        
        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Show the user creation form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createUser()
    {
        $roles = Role::all();
        
        return view('admin.users.create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        // Handle role-specific data
        $role = Role::find($request->role_id);
        if ($role->name === 'student') {
            $user->studentData()->create([
                'student_number' => $request->student_number,
                'enrollment_date' => now(),
            ]);
        } elseif ($role->name === 'professor') {
            $user->professorData()->create([
                'department' => $request->department,
                'specialization' => $request->specialization,
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the user edit form.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editUser($id)
    {
        $user = User::with(['role', 'studentData', 'professorData'])->findOrFail($id);
        $roles = Role::all();
        
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
        ];
        
        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        
        $request->validate($rules);
        
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];
        
        // Only update password if it's provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        
        $user->update($userData);
        
        // Handle role-specific data
        $role = Role::find($request->role_id);
        if ($role->name === 'student') {
            // Delete professor data if exists
            if ($user->professorData) {
                $user->professorData->delete();
            }
            
            // Create or update student data
            if ($user->studentData) {
                $user->studentData->update([
                    'student_number' => $request->student_number,
                ]);
            } else {
                $user->studentData()->create([
                    'student_number' => $request->student_number,
                    'enrollment_date' => now(),
                ]);
            }
            
        } elseif ($role->name === 'professor') {
            // Delete student data if exists
            if ($user->studentData) {
                $user->studentData->delete();
            }
            
            // Create or update professor data
            if ($user->professorData) {
                $user->professorData->update([
                    'department' => $request->department,
                    'specialization' => $request->specialization,
                ]);
            } else {
                $user->professorData()->create([
                    'department' => $request->department,
                    'specialization' => $request->specialization,
                ]);
            }
        }
        
        return redirect()->route('admin.users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Don't allow deleting yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show the courses management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function courses()
    {
        $query = Course::with(['category', 'instructor']);
        
        // Apply filters if provided
        if (request('category')) {
            $query->where('category_id', request('category'));
        }
        
        if (request('status')) {
            $query->where('status', request('status'));
        }
        
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $courses = $query->latest()->paginate(10);
        $categories = Category::all();
        
        return view('admin.courses.index', [
            'courses' => $courses,
            'categories' => $categories,
        ]);
    }

    /**
     * Show the course creation form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createCourse()
    {
        $categories = Category::all();
        
        return view('admin.courses.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('admin.courses.show', $course->id)
            ->with('success', 'Course created successfully.');
    }

    /**
     * Show a specific course.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showCourse($id)
    {
        $course = Course::with(['category', 'modules' => function($query) {
            $query->orderBy('order', 'asc');
        }])->findOrFail($id);
        
        return view('admin.courses.show', [
            'course' => $course,
        ]);
    }

    /**
     * Show the course edit form.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editCourse($id)
    {
        $course = Course::findOrFail($id);
        $categories = Category::all();
        
        return view('admin.courses.edit', [
            'course' => $course,
            'categories' => $categories,
        ]);
    }

    /**
     * Update a course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCourse(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $course = Course::findOrFail($id);
        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('admin.courses.show', $course->id)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Delete a course.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteCourse($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        
        return redirect()->route('admin.courses')
            ->with('success', 'Course deleted successfully.');
    }

    /**
     * Show the categories management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function categories()
    {
        $categories = Category::withCount('courses')->paginate(10);
        
        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the achievements management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function achievements()
    {
        $achievements = Achievement::paginate(10);
        
        return view('admin.achievements.index', [
            'achievements' => $achievements,
        ]);
    }

    /**
     * Show the reports page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function reports()
    {
        $studentCount = User::whereHas('role', function($query) {
            $query->where('name', 'student');
        })->count();
        
        $professorCount = User::whereHas('role', function($query) {
            $query->where('name', 'professor');
        })->count();
        
        $completedLessonsCount = UserProgress::where('completed', true)->count();
        
        return view('admin.reports', [
            'studentCount' => $studentCount,
            'professorCount' => $professorCount,
            'completedLessonsCount' => $completedLessonsCount,
        ]);
    }

    /**
     * Show the module creation form.
     *
     * @param  int  $courseId
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createModule($courseId)
    {
        $course = Course::findOrFail($courseId);
        $moduleCount = $course->modules()->count();
        
        return view('admin.modules.create', [
            'course' => $course,
            'nextOrder' => $moduleCount + 1,
        ]);
    }

    /**
     * Store a new module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $courseId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeModule(Request $request, $courseId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
        ]);

        $course = Course::findOrFail($courseId);
        
        // If inserting in the middle, reorder other modules
        if ($request->order <= $course->modules()->count()) {
            $course->modules()
                ->where('order', '>=', $request->order)
                ->increment('order');
        }

        $module = new Module([
            'name' => $request->name,
            'order' => $request->order,
        ]);

        $course->modules()->save($module);

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', 'Module created successfully.');
    }

    /**
     * Show the module edit form.
     *
     * @param  int  $courseId
     * @param  int  $moduleId
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editModule($courseId, $moduleId)
    {
        $course = Course::findOrFail($courseId);
        $module = Module::findOrFail($moduleId);
        
        return view('admin.modules.edit', [
            'course' => $course,
            'module' => $module,
        ]);
    }

    /**
     * Update a module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $courseId
     * @param  int  $moduleId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateModule(Request $request, $courseId, $moduleId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
        ]);

        $course = Course::findOrFail($courseId);
        $module = Module::findOrFail($moduleId);
        
        $oldOrder = $module->order;
        $newOrder = $request->order;
        
        // Handle reordering if necessary
        if ($oldOrder != $newOrder) {
            if ($newOrder > $oldOrder) {
                // Moving down - shift modules in between old and new position up
                $course->modules()
                    ->where('order', '>', $oldOrder)
                    ->where('order', '<=', $newOrder)
                    ->where('id', '!=', $moduleId)
                    ->decrement('order');
            } else {
                // Moving up - shift modules in between new and old position down
                $course->modules()
                    ->where('order', '>=', $newOrder)
                    ->where('order', '<', $oldOrder)
                    ->where('id', '!=', $moduleId)
                    ->increment('order');
            }
        }

        $module->update([
            'name' => $request->name,
            'order' => $newOrder,
        ]);

        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Delete a module.
     *
     * @param  int  $courseId
     * @param  int  $moduleId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteModule($courseId, $moduleId)
    {
        $module = Module::findOrFail($moduleId);
        $order = $module->order;
        
        // Delete the module
        $module->delete();
        
        // Reorder remaining modules
        Module::where('course_id', $courseId)
            ->where('order', '>', $order)
            ->decrement('order');
        
        return redirect()->route('admin.courses.show', $courseId)
            ->with('success', 'Module deleted successfully.');
    }

    /**
     * Show the lesson creation form.
     *
     * @param  int  $courseId
     * @param  int  $moduleId
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createLesson($courseId, $moduleId)
    {
        $course = Course::findOrFail($courseId);
        $module = Module::findOrFail($moduleId);
        $lessonCount = $module->lessons()->count();
        
        return view('admin.lessons.create', [
            'course' => $course,
            'module' => $module,
            'nextOrder' => $lessonCount + 1,
        ]);
    }

    /**
     * Store a new lesson.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $courseId
     * @param  int  $moduleId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeLesson(Request $request, $courseId, $moduleId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'order_number' => 'required|integer|min:1',
        ]);

        $module = Module::findOrFail($moduleId);
        
        // If inserting in the middle, reorder other lessons
        if ($request->order_number <= $module->lessons()->count()) {
            $module->lessons()
                ->where('order_number', '>=', $request->order_number)
                ->increment('order_number');
        }

        $lesson = new Lesson([
            'title' => $request->title,
            'content' => $request->content,
            'video_url' => $request->video_url ?? '',
            'order_number' => $request->order_number,
        ]);

        $module->lessons()->save($lesson);

        return redirect()->route('admin.modules.show', [$courseId, $moduleId])
            ->with('success', 'Lesson created successfully.');
    }

    /**
     * Show a specific module with its lessons.
     *
     * @param  int  $courseId
     * @param  int  $moduleId
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showModule($courseId, $moduleId)
    {
        $course = Course::findOrFail($courseId);
        $module = Module::with(['lessons' => function($query) {
            $query->orderBy('order_number', 'asc');
        }])->findOrFail($moduleId);
        
        return view('admin.modules.show', [
            'course' => $course,
            'module' => $module,
        ]);
    }

    /**
     * Show a specific lesson.
     *
     * @param  int  $courseId
     * @param  int  $moduleId
     * @param  int  $lessonId
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showLesson($courseId, $moduleId, $lessonId)
    {
        $course = Course::findOrFail($courseId);
        $module = Module::findOrFail($moduleId);
        $lesson = Lesson::findOrFail($lessonId);
        
        return view('admin.lessons.show', [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
        ]);
    }

    /**
     * Show the lesson edit form.
     *
     * @param  int  $courseId
     * @param  int  $moduleId
     * @param  int  $lessonId
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function editLesson($courseId, $moduleId, $lessonId)
    {
        $course = Course::findOrFail($courseId);
        $module = Module::findOrFail($moduleId);
        $lesson = Lesson::findOrFail($lessonId);
        
        return view('admin.lessons.edit', [
            'course' => $course,
            'module' => $module,
            'lesson' => $lesson,
        ]);
    }

    /**
     * Update a lesson.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $courseId
     * @param  int  $moduleId
     * @param  int  $lessonId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateLesson(Request $request, $courseId, $moduleId, $lessonId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'order_number' => 'required|integer|min:1',
        ]);

        $module = Module::findOrFail($moduleId);
        $lesson = Lesson::findOrFail($lessonId);
        
        $oldOrder = $lesson->order_number;
        $newOrder = $request->order_number;
        
        // Handle reordering if necessary
        if ($oldOrder != $newOrder) {
            if ($newOrder > $oldOrder) {
                // Moving down - shift lessons in between old and new position up
                $module->lessons()
                    ->where('order_number', '>', $oldOrder)
                    ->where('order_number', '<=', $newOrder)
                    ->where('id', '!=', $lessonId)
                    ->decrement('order_number');
            } else {
                // Moving up - shift lessons in between new and old position down
                $module->lessons()
                    ->where('order_number', '>=', $newOrder)
                    ->where('order_number', '<', $oldOrder)
                    ->where('id', '!=', $lessonId)
                    ->increment('order_number');
            }
        }

        $lesson->update([
            'title' => $request->title,
            'content' => $request->content,
            'video_url' => $request->video_url ?? '',
            'order_number' => $newOrder,
        ]);

        return redirect()->route('admin.modules.show', [$courseId, $moduleId])
            ->with('success', 'Lesson updated successfully.');
    }

    /**
     * Delete a lesson.
     *
     * @param  int  $courseId
     * @param  int  $moduleId
     * @param  int  $lessonId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteLesson($courseId, $moduleId, $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $orderNumber = $lesson->order_number;
        
        // Delete the lesson
        $lesson->delete();
        
        // Reorder remaining lessons
        Lesson::where('module_id', $moduleId)
            ->where('order_number', '>', $orderNumber)
            ->decrement('order_number');
        
        return redirect()->route('admin.modules.show', [$courseId, $moduleId])
            ->with('success', 'Lesson deleted successfully.');
    }
} 