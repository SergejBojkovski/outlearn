<?php

namespace App\Http\Controllers;

use App\Models\ProfessorData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProfessorDataController extends Controller
{
    protected $validationRules = [
        'user_id' => 'required|exists:users,id',
        'bio' => 'nullable|string',
        'qualifications' => 'nullable|string',
        'areas_of_expertise' => 'nullable|string',
        'teaching_philosophy' => 'nullable|string',
        'office_hours' => 'nullable|string',
        'contact_information' => 'nullable|string',
    ];

    /**
     * Display a listing of professor data records.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'users.name');
        $direction = $request->input('direction', 'asc');
        
        $query = ProfessorData::with('user');
        
        // Apply search
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhere('bio', 'like', "%{$search}%")
            ->orWhere('qualifications', 'like', "%{$search}%")
            ->orWhere('areas_of_expertise', 'like', "%{$search}%");
        }
        
        // Apply sorting
        if (in_array($sort, ['users.name', 'users.email', 'created_at', 'updated_at'])) {
            if (strpos($sort, 'users.') === 0) {
                $field = substr($sort, 6);
                $query->join('users', 'professor_data.user_id', '=', 'users.id')
                    ->orderBy($field, $direction)
                    ->select('professor_data.*');
            } else {
                $query->orderBy($sort, $direction);
            }
        }
        
        $professorData = $query->paginate(10);
        
        return view('professor-data.index', [
            'professorData' => $professorData,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new professor data record.
     *
     * @return View
     */
    public function create()
    {
        // Get users who don't already have professor data
        $users = User::whereDoesntHave('professorData')
            ->where('role', 'professor')
            ->orderBy('name')
            ->get();
            
        return view('professor-data.create', [
            'users' => $users
        ]);
    }

    /**
     * Store a newly created professor data record in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('professor-data.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if professor data already exists for this user
        $existingData = ProfessorData::where('user_id', $request->input('user_id'))->first();
        
        if ($existingData) {
            return redirect()->route('professor-data.create')
                ->with('error', 'Professor data already exists for this user.')
                ->withInput();
        }
        
        $professorData = ProfessorData::create($request->all());
        
        return redirect()->route('professor-data.show', $professorData->id)
            ->with('success', 'Professor data created successfully.');
    }

    /**
     * Display the specified professor data record.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $professorData = ProfessorData::with('user')->findOrFail($id);
        
        return view('professor-data.show', [
            'professorData' => $professorData
        ]);
    }

    /**
     * Show the form for editing the specified professor data record.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $professorData = ProfessorData::with('user')->findOrFail($id);
        
        return view('professor-data.edit', [
            'professorData' => $professorData
        ]);
    }

    /**
     * Update the specified professor data record in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'bio' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'areas_of_expertise' => 'nullable|string',
            'teaching_philosophy' => 'nullable|string',
            'office_hours' => 'nullable|string',
            'contact_information' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('professor-data.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $professorData = ProfessorData::findOrFail($id);
        $professorData->update($request->all());
        
        return redirect()->route('professor-data.show', $id)
            ->with('success', 'Professor data updated successfully.');
    }

    /**
     * Remove the specified professor data record from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $professorData = ProfessorData::findOrFail($id);
        $professorData->delete();
        
        return redirect()->route('professor-data.index')
            ->with('success', 'Professor data deleted successfully.');
    }
    
    /**
     * Get professor data for a specific user.
     *
     * @param int $userId
     * @return View
     */
    public function getByUser($userId)
    {
        $user = User::findOrFail($userId);
        $professorData = ProfessorData::where('user_id', $userId)->firstOrFail();
        
        return view('professor-data.by-user', [
            'user' => $user,
            'professorData' => $professorData
        ]);
    }
    
    /**
     * Show a form to create or update professor data for the current user.
     *
     * @param Request $request
     * @return View
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'professor') {
            return redirect()->route('home')
                ->with('error', 'Only professors can access professor profiles.');
        }
        
        $professorData = ProfessorData::where('user_id', $user->id)->first();
        
        return view('professor-data.profile', [
            'user' => $user,
            'professorData' => $professorData
        ]);
    }
    
    /**
     * Update professor data for the current user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'professor') {
            return redirect()->route('home')
                ->with('error', 'Only professors can update professor profiles.');
        }
        
        $validator = Validator::make($request->all(), [
            'bio' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'areas_of_expertise' => 'nullable|string',
            'teaching_philosophy' => 'nullable|string',
            'office_hours' => 'nullable|string',
            'contact_information' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('professor-data.profile')
                ->withErrors($validator)
                ->withInput();
        }
        
        $professorData = ProfessorData::where('user_id', $user->id)->first();
        
        if ($professorData) {
            $professorData->update($request->all());
        } else {
            $data = $request->all();
            $data['user_id'] = $user->id;
            ProfessorData::create($data);
        }
        
        return redirect()->route('professor-data.profile')
            ->with('success', 'Professor profile updated successfully.');
    }
} 