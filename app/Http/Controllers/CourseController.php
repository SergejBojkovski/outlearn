<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CourseController extends Controller
{
    protected $validationRules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
    ];

    /**
     * Display a listing of the courses.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $sort = $request->input('sort', 'title');
        $direction = $request->input('direction', 'asc');
        
        $query = Course::with(['category', 'modules']);
        
        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply filter
        if ($filter && isset($filter['category_id'])) {
            $query->where('category_id', $filter['category_id']);
        }
        
        // Apply sorting
        if (in_array($sort, ['title', 'created_at', 'updated_at'])) {
            $query->orderBy($sort, $direction);
        }
        
        $courses = $query->paginate(10);
        $categories = Category::all();
        
        return view('courses.index', [
            'courses' => $courses,
            'categories' => $categories,
            'search' => $search,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new course.
     *
     * @return View
     */
    public function create()
    {
        $categories = Category::all();
        return view('courses.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created course in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('courses.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $course = Course::create($request->all());
        
        return redirect()->route('courses.show', $course->id)
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $course = Course::with(['category', 'modules.lessons', 'professor'])->findOrFail($id);
        return view('courses.show', ['course' => $course]);
    }

    /**
     * Show the form for editing the specified course.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $categories = Category::all();
        return view('courses.edit', [
            'course' => $course,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified course in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('courses.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $course = Course::findOrFail($id);
        $course->update($request->all());
        
        return redirect()->route('courses.show', $id)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        
        return redirect()->route('courses.index')
            ->with('success', 'Course deleted successfully.');
    }
    
    /**
     * Get courses by category
     *
     * @param int $categoryId
     * @return View
     */
    public function getByCategory(int $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $courses = Course::where('category_id', $categoryId)
            ->with(['category', 'modules'])
            ->paginate(10);
            
        return view('courses.by-category', [
            'courses' => $courses,
            'category' => $category
        ]);
    }
    
    /**
     * Get popular courses based on enrollment count
     *
     * @param Request $request
     * @return View
     */
    public function getPopular(Request $request)
    {
        $limit = $request->input('limit', 5);
        
        $courses = Course::withCount('students')
            ->with(['category', 'modules'])
            ->orderByDesc('students_count')
            ->limit($limit)
            ->paginate($limit);
            
        return view('courses.popular', ['courses' => $courses]);
    }
    
    /**
     * Get courses for a specific user (either as student or professor)
     *
     * @param Request $request
     * @param int $userId
     * @return View
     */
    public function getForUser(Request $request, int $userId)
    {
        $role = $request->input('role', 'student');
        $user = User::findOrFail($userId);
        
        if ($role === 'professor') {
            $courses = $user->professorCourses()->with(['category', 'modules'])->paginate(10);
            return view('courses.taught', ['courses' => $courses, 'user' => $user]);
        } else {
            $courses = $user->courses()->with(['category', 'modules'])->paginate(10);
            return view('courses.enrolled', ['courses' => $courses, 'user' => $user]);
        }
    }
    
    /**
     * Get recent courses
     *
     * @param Request $request
     * @return View
     */
    public function getRecent(Request $request)
    {
        $limit = $request->input('limit', 5);
        
        $courses = Course::with(['category', 'modules'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->paginate($limit);
            
        return view('courses.recent', ['courses' => $courses]);
    }
    
    /**
     * Enroll a user in a course
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enrollUser(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $course = Course::findOrFail($id);
        $userId = $request->input('user_id');
        
        // Check if user is already enrolled
        if ($course->students()->where('users.id', $userId)->exists()) {
            return back()->with('error', 'User is already enrolled in this course');
        }
        
        $course->students()->attach($userId);
        
        return back()->with('success', 'User enrolled successfully');
    }
    
    /**
     * Unenroll a user from a course
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unenrollUser(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $course = Course::findOrFail($id);
        $userId = $request->input('user_id');
        
        $course->students()->detach($userId);
        
        return back()->with('success', 'User unenrolled successfully');
    }
    
    /**
     * Get course options for dropdowns
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $courses = Course::select('id', 'title')->orderBy('title')->get();
        return response()->json($courses);
    }
} 