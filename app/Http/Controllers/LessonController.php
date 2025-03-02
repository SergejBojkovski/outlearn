<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LessonController extends Controller
{
    protected $validationRules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'module_id' => 'required|exists:modules,id',
        'order' => 'nullable|integer|min:0',
        'duration' => 'nullable|integer|min:0',
    ];

    /**
     * Display a listing of the lessons.
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
        
        $query = Lesson::with('module.course');
        
        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        // Apply filter
        if ($filter) {
            if (isset($filter['module_id'])) {
                $query->where('module_id', $filter['module_id']);
            }
            if (isset($filter['course_id'])) {
                $query->whereHas('module', function ($q) use ($filter) {
                    $q->where('course_id', $filter['course_id']);
                });
            }
        }
        
        // Apply sorting
        if (in_array($sort, ['title', 'order', 'duration', 'created_at', 'updated_at'])) {
            $query->orderBy($sort, $direction);
        }
        
        $lessons = $query->paginate(10);
        $modules = Module::with('course')->get();
        
        return view('lessons.index', [
            'lessons' => $lessons,
            'modules' => $modules,
            'search' => $search,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new lesson.
     *
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $moduleId = $request->input('module_id');
        $modules = Module::with('course')->get();
        $selectedModule = null;
        
        if ($moduleId) {
            $selectedModule = Module::with('course')->findOrFail($moduleId);
        }
        
        return view('lessons.create', [
            'modules' => $modules,
            'selectedModule' => $selectedModule
        ]);
    }

    /**
     * Store a newly created lesson in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('lessons.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $lessonData = $request->all();
        
        // Set the order if not provided
        if (!isset($lessonData['order']) || $lessonData['order'] === null) {
            $lastLesson = Lesson::where('module_id', $lessonData['module_id'])
                ->orderBy('order', 'desc')
                ->first();
            $lessonData['order'] = $lastLesson ? $lastLesson->order + 1 : 0;
        }
        
        $lesson = Lesson::create($lessonData);
        
        return redirect()->route('lessons.show', $lesson->id)
            ->with('success', 'Lesson created successfully.');
    }

    /**
     * Display the specified lesson.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $lesson = Lesson::with(['module.course'])->findOrFail($id);
        $user = Auth::user();
        $completed = false;
        
        // Check if lesson is completed by the current user
        if ($user) {
            $completed = $user->completedLessons()->where('lesson_id', $id)->exists();
        }
        
        // Get next and previous lessons in the module
        $nextLesson = Lesson::where('module_id', $lesson->module_id)
            ->where('order', '>', $lesson->order)
            ->orderBy('order', 'asc')
            ->first();
            
        $previousLesson = Lesson::where('module_id', $lesson->module_id)
            ->where('order', '<', $lesson->order)
            ->orderBy('order', 'desc')
            ->first();
        
        return view('lessons.show', [
            'lesson' => $lesson,
            'completed' => $completed,
            'nextLesson' => $nextLesson,
            'previousLesson' => $previousLesson
        ]);
    }

    /**
     * Show the form for editing the specified lesson.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $lesson = Lesson::findOrFail($id);
        $modules = Module::with('course')->get();
        
        return view('lessons.edit', [
            'lesson' => $lesson,
            'modules' => $modules
        ]);
    }

    /**
     * Update the specified lesson in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'module_id' => 'sometimes|required|exists:modules,id',
            'order' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('lessons.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $lesson = Lesson::findOrFail($id);
        $lesson->update($request->all());
        
        return redirect()->route('lessons.show', $id)
            ->with('success', 'Lesson updated successfully.');
    }

    /**
     * Remove the specified lesson from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->delete();
        
        return redirect()->route('lessons.index')
            ->with('success', 'Lesson deleted successfully.');
    }
    
    /**
     * Get lessons for a specific module.
     *
     * @param int $moduleId
     * @return View
     */
    public function getByModule(int $moduleId)
    {
        $module = Module::with('course')->findOrFail($moduleId);
        $lessons = Lesson::where('module_id', $moduleId)
            ->orderBy('order')
            ->get();
            
        return view('lessons.by-module', [
            'lessons' => $lessons,
            'module' => $module
        ]);
    }
    
    /**
     * Get navigation information for a lesson.
     *
     * @param int $id
     * @return View
     */
    public function getNavigation(int $id)
    {
        $lesson = Lesson::with('module.course')->findOrFail($id);
        
        // Get previous and next lessons
        $previousLesson = Lesson::where('module_id', $lesson->module_id)
            ->where('order', '<', $lesson->order)
            ->orderBy('order', 'desc')
            ->first();
            
        $nextLesson = Lesson::where('module_id', $lesson->module_id)
            ->where('order', '>', $lesson->order)
            ->orderBy('order', 'asc')
            ->first();
            
        // If no next lesson in the current module, check if there's another module
        if (!$nextLesson) {
            $nextModule = Module::where('course_id', $lesson->module->course_id)
                ->where('order', '>', $lesson->module->order)
                ->orderBy('order', 'asc')
                ->first();
                
            if ($nextModule) {
                $nextLesson = Lesson::where('module_id', $nextModule->id)
                    ->orderBy('order', 'asc')
                    ->first();
            }
        }
        
        // If no previous lesson in the current module, check if there's a previous module
        if (!$previousLesson) {
            $previousModule = Module::where('course_id', $lesson->module->course_id)
                ->where('order', '<', $lesson->module->order)
                ->orderBy('order', 'desc')
                ->first();
                
            if ($previousModule) {
                $previousLesson = Lesson::where('module_id', $previousModule->id)
                    ->orderBy('order', 'desc')
                    ->first();
            }
        }
            
        return view('lessons.navigation', [
            'lesson' => $lesson,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson
        ]);
    }
    
    /**
     * Reorder lessons within a module.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lessons' => 'required|array',
            'lessons.*.id' => 'required|exists:lessons,id',
            'lessons.*.order' => 'required|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $lessons = $request->input('lessons');
        
        foreach ($lessons as $lessonData) {
            $lesson = Lesson::findOrFail($lessonData['id']);
            $lesson->order = $lessonData['order'];
            $lesson->save();
        }
        
        return redirect()->back()
            ->with('success', 'Lessons reordered successfully.');
    }
    
    /**
     * Mark a lesson as completed by the current user.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markCompleted(Request $request, int $id)
    {
        $lesson = Lesson::findOrFail($id);
        $user = Auth::user();
        
        // Check if the lesson is already completed
        if (!$user->completedLessons()->where('lesson_id', $id)->exists()) {
            $user->completedLessons()->attach($id, ['completed_at' => now()]);
        }
        
        return redirect()->back()->with('success', 'Lesson marked as completed.');
    }
    
    /**
     * Get completion status of a lesson for the current user.
     *
     * @param int $id
     * @return View
     */
    public function getCompletionStatus(int $id)
    {
        $lesson = Lesson::findOrFail($id);
        $user = Auth::user();
        $completed = false;
        $completedAt = null;
        
        if ($user) {
            $userLesson = $user->completedLessons()->where('lesson_id', $id)->first();
            $completed = $userLesson !== null;
            $completedAt = $userLesson ? $userLesson->pivot->completed_at : null;
        }
        
        return view('lessons.completion-status', [
            'lesson' => $lesson,
            'completed' => $completed,
            'completedAt' => $completedAt
        ]);
    }
    
    /**
     * Bulk delete lessons.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('lessons.index')
                ->with('error', 'No lessons selected for deletion.');
        }
        
        Lesson::whereIn('id', $ids)->delete();
        
        return redirect()->route('lessons.index')
            ->with('success', count($ids) . ' lessons deleted successfully.');
    }
    
    /**
     * Get lesson options for dropdowns.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $lessons = Lesson::select('id', 'title')->orderBy('title')->get();
        return response()->json($lessons);
    }
} 