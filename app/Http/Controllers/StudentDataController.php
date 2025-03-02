<?php

namespace App\Http\Controllers;

use App\Models\StudentData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class StudentDataController extends Controller
{
    protected $validationRules = [
        'user_id' => 'required|exists:users,id',
        'bio' => 'nullable|string',
        'academic_level' => 'nullable|string',
        'interests' => 'nullable|string',
        'learning_goals' => 'nullable|string',
        'educational_background' => 'nullable|string',
    ];

    /**
     * Display a listing of student data records.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $sort = $request->input('sort', 'users.name');
        $direction = $request->input('direction', 'asc');
        
        $query = StudentData::with('user');
        
        // Apply search
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhere('bio', 'like', "%{$search}%")
            ->orWhere('academic_level', 'like', "%{$search}%")
            ->orWhere('interests', 'like', "%{$search}%");
        }
        
        // Apply filter
        if ($filter) {
            if (isset($filter['academic_level']) && $filter['academic_level']) {
                $query->where('academic_level', $filter['academic_level']);
            }
        }
        
        // Apply sorting
        if (in_array($sort, ['users.name', 'users.email', 'academic_level', 'created_at', 'updated_at'])) {
            if (strpos($sort, 'users.') === 0) {
                $field = substr($sort, 6);
                $query->join('users', 'student_data.user_id', '=', 'users.id')
                    ->orderBy($field, $direction)
                    ->select('student_data.*');
            } else {
                $query->orderBy($sort, $direction);
            }
        }
        
        $studentData = $query->paginate(10);
        
        // Get unique academic levels for filtering
        $academicLevels = StudentData::distinct('academic_level')
            ->whereNotNull('academic_level')
            ->pluck('academic_level')
            ->toArray();
        
        return view('student-data.index', [
            'studentData' => $studentData,
            'academicLevels' => $academicLevels,
            'search' => $search,
            'filter' => $filter,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new student data record.
     *
     * @return View
     */
    public function create()
    {
        // Get users who don't already have student data
        $users = User::whereDoesntHave('studentData')
            ->where('role', 'student')
            ->orderBy('name')
            ->get();
            
        return view('student-data.create', [
            'users' => $users
        ]);
    }

    /**
     * Store a newly created student data record in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('student-data.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if student data already exists for this user
        $existingData = StudentData::where('user_id', $request->input('user_id'))->first();
        
        if ($existingData) {
            return redirect()->route('student-data.create')
                ->with('error', 'Student data already exists for this user.')
                ->withInput();
        }
        
        $studentData = StudentData::create($request->all());
        
        return redirect()->route('student-data.show', $studentData->id)
            ->with('success', 'Student data created successfully.');
    }

    /**
     * Display the specified student data record.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $studentData = StudentData::with('user')->findOrFail($id);
        
        return view('student-data.show', [
            'studentData' => $studentData
        ]);
    }

    /**
     * Show the form for editing the specified student data record.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $studentData = StudentData::with('user')->findOrFail($id);
        
        return view('student-data.edit', [
            'studentData' => $studentData
        ]);
    }

    /**
     * Update the specified student data record in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bio' => 'nullable|string',
            'academic_level' => 'nullable|string',
            'interests' => 'nullable|string',
            'learning_goals' => 'nullable|string',
            'educational_background' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('student-data.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $studentData = StudentData::findOrFail($id);
        $studentData->update($request->all());
        
        return redirect()->route('student-data.show', $id)
            ->with('success', 'Student data updated successfully.');
    }

    /**
     * Remove the specified student data record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $studentData = StudentData::findOrFail($id);
        $studentData->delete();
        
        return redirect()->route('student-data.index')
            ->with('success', 'Student data deleted successfully.');
    }
    
    /**
     * Get student data for a specific user.
     *
     * @param int $userId
     * @return View
     */
    public function getByUser($userId)
    {
        $user = User::findOrFail($userId);
        $studentData = StudentData::where('user_id', $userId)->firstOrFail();
        
        return view('student-data.by-user', [
            'user' => $user,
            'studentData' => $studentData
        ]);
    }
    
    /**
     * Show a form to create or update student data for the current user.
     *
     * @param Request $request
     * @return View
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'student') {
            return redirect()->route('home')
                ->with('error', 'Only students can access student profiles.');
        }
        
        $studentData = StudentData::where('user_id', $user->id)->first();
        
        return view('student-data.profile', [
            'user' => $user,
            'studentData' => $studentData
        ]);
    }
    
    /**
     * Update student data for the current user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'student') {
            return redirect()->route('home')
                ->with('error', 'Only students can update student profiles.');
        }
        
        $validator = Validator::make($request->all(), [
            'bio' => 'nullable|string',
            'academic_level' => 'nullable|string',
            'interests' => 'nullable|string',
            'learning_goals' => 'nullable|string',
            'educational_background' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('student-data.profile')
                ->withErrors($validator)
                ->withInput();
        }
        
        $studentData = StudentData::where('user_id', $user->id)->first();
        
        if ($studentData) {
            $studentData->update($request->all());
        } else {
            $data = $request->all();
            $data['user_id'] = $user->id;
            StudentData::create($data);
        }
        
        return redirect()->route('student-data.profile')
            ->with('success', 'Student profile updated successfully.');
    }
    
    /**
     * Get students by academic level.
     *
     * @param string $level
     * @return View
     */
    public function getByAcademicLevel($level)
    {
        $students = StudentData::where('academic_level', $level)
            ->with('user')
            ->paginate(20);
            
        return view('student-data.by-level', [
            'students' => $students,
            'level' => $level
        ]);
    }
    
    /**
     * Bulk delete student data records.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('student-data.index')
                ->with('error', 'No student data records selected for deletion.');
        }
        
        StudentData::whereIn('id', $ids)->delete();
        
        return redirect()->route('student-data.index')
            ->with('success', count($ids) . ' student data records deleted successfully.');
    }
} 