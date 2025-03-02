<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ModuleController extends Controller
{
    protected $validationRules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'course_id' => 'required|exists:courses,id',
        'order' => 'nullable|integer|min:0',
    ];

    /**
     * Display a listing of the modules.
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
        
        $query = Module::with(['course', 'lessons']);
        
        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply filter
        if ($filter && isset($filter['course_id'])) {
            $query->where('course_id', $filter['course_id']);
        }
        
        // Apply sorting
        if (in_array($sort, ['title', 'order', 'created_at', 'updated_at'])) {
            $query->orderBy($sort, $direction);
        }
        
        $modules = $query->paginate(10);
        $courses = Course::all();
        
        return view('modules.index', [
            'modules' => $modules,
            'courses' => $courses,
            'search' => $search,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new module.
     *
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $courseId = $request->input('course_id');
        $courses = Course::all();
        $selectedCourse = null;
        
        if ($courseId) {
            $selectedCourse = Course::findOrFail($courseId);
        }
        
        return view('modules.create', [
            'courses' => $courses,
            'selectedCourse' => $selectedCourse
        ]);
    }

    /**
     * Store a newly created module in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('modules.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $moduleData = $request->all();
        
        // Set the order if not provided
        if (!isset($moduleData['order']) || $moduleData['order'] === null) {
            $lastModule = Module::where('course_id', $moduleData['course_id'])
                ->orderBy('order', 'desc')
                ->first();
            $moduleData['order'] = $lastModule ? $lastModule->order + 1 : 0;
        }
        
        $module = Module::create($moduleData);
        
        return redirect()->route('modules.show', $module->id)
            ->with('success', 'Module created successfully.');
    }

    /**
     * Display the specified module.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $module = Module::with(['course', 'lessons'])->findOrFail($id);
        return view('modules.show', ['module' => $module]);
    }

    /**
     * Show the form for editing the specified module.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $module = Module::findOrFail($id);
        $courses = Course::all();
        
        return view('modules.edit', [
            'module' => $module,
            'courses' => $courses
        ]);
    }

    /**
     * Update the specified module in storage.
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
            'course_id' => 'sometimes|required|exists:courses,id',
            'order' => 'nullable|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('modules.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $module = Module::findOrFail($id);
        $module->update($request->all());
        
        return redirect()->route('modules.show', $id)
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();
        
        return redirect()->route('modules.index')
            ->with('success', 'Module deleted successfully.');
    }
    
    /**
     * Get modules for a specific course.
     *
     * @param int $courseId
     * @return View
     */
    public function getByCourse(int $courseId)
    {
        $course = Course::findOrFail($courseId);
        $modules = Module::where('course_id', $courseId)
            ->orderBy('order')
            ->with('lessons')
            ->get();
            
        return view('modules.by-course', [
            'modules' => $modules,
            'course' => $course
        ]);
    }
    
    /**
     * Get navigation information for a module.
     *
     * @param int $id
     * @return View
     */
    public function getNavigation(int $id)
    {
        $module = Module::with(['course', 'lessons'])->findOrFail($id);
        
        // Get previous and next modules
        $previousModule = Module::where('course_id', $module->course_id)
            ->where('order', '<', $module->order)
            ->orderBy('order', 'desc')
            ->first();
            
        $nextModule = Module::where('course_id', $module->course_id)
            ->where('order', '>', $module->order)
            ->orderBy('order', 'asc')
            ->first();
            
        return view('modules.navigation', [
            'module' => $module,
            'previousModule' => $previousModule,
            'nextModule' => $nextModule
        ]);
    }
    
    /**
     * Reorder modules for a course.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $modules = $request->input('modules');
        
        foreach ($modules as $moduleData) {
            $module = Module::findOrFail($moduleData['id']);
            $module->order = $moduleData['order'];
            $module->save();
        }
        
        return redirect()->back()
            ->with('success', 'Modules reordered successfully.');
    }
    
    /**
     * Bulk delete modules.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('modules.index')
                ->with('error', 'No modules selected for deletion.');
        }
        
        Module::whereIn('id', $ids)->delete();
        
        return redirect()->route('modules.index')
            ->with('success', count($ids) . ' modules deleted successfully.');
    }
    
    /**
     * Get module options for dropdowns.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $modules = Module::select('id', 'title')->orderBy('title')->get();
        return response()->json($modules);
    }
} 