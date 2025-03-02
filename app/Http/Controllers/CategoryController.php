<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected $validationRules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
    ];

    /**
     * Display a listing of the categories.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        
        $query = Category::withCount('courses');
        
        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        if (in_array($sort, ['name', 'created_at', 'updated_at', 'courses_count'])) {
            $query->orderBy($sort, $direction);
        }
        
        $categories = $query->paginate(10);
        
        return view('categories.index', [
            'categories' => $categories,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new category.
     *
     * @return View
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('categories.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $category = Category::create($request->all());
        
        return redirect()->route('categories.show', $category->id)
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $category = Category::with('courses')->findOrFail($id);
        return view('categories.show', ['category' => $category]);
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('categories.edit', ['category' => $category]);
    }

    /**
     * Update the specified category in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('categories.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $category = Category::findOrFail($id);
        $category->update($request->all());
        
        return redirect()->route('categories.show', $id)
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        
        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
    
    /**
     * Get courses for a specific category
     *
     * @param int $id
     * @return View
     */
    public function getCourses(int $id)
    {
        $category = Category::with('courses')->findOrFail($id);
        return view('categories.courses', ['category' => $category]);
    }
    
    /**
     * Get categories with course counts
     *
     * @return View
     */
    public function getWithCourseCounts()
    {
        $categories = Category::withCount('courses')->orderBy('name')->get();
        return view('categories.with-course-counts', ['categories' => $categories]);
    }
    
    /**
     * Get popular categories
     *
     * @param Request $request
     * @return View
     */
    public function getPopular(Request $request)
    {
        $limit = $request->input('limit', 5);
        
        $categories = Category::withCount('courses')
            ->orderByDesc('courses_count')
            ->limit($limit)
            ->get();
            
        return view('categories.popular', ['categories' => $categories]);
    }
    
    /**
     * Get category options for dropdowns
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return response()->json($categories);
    }
} 