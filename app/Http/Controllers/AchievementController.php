<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AchievementController extends Controller
{
    protected $validationRules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'points' => 'required|integer|min:0',
        'image_url' => 'nullable|string|max:255',
    ];

    /**
     * Display a listing of the achievements.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        
        $query = Achievement::query();
        
        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        if (in_array($sort, ['name', 'points', 'created_at', 'updated_at'])) {
            $query->orderBy($sort, $direction);
        }
        
        $achievements = $query->paginate(10);
        
        return view('achievements.index', [
            'achievements' => $achievements,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new achievement.
     *
     * @return View
     */
    public function create()
    {
        return view('achievements.create');
    }

    /**
     * Store a newly created achievement in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->validationRules);
        
        if ($validator->fails()) {
            return redirect()->route('achievements.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $achievement = Achievement::create($request->all());
        
        return redirect()->route('achievements.show', $achievement->id)
            ->with('success', 'Achievement created successfully.');
    }

    /**
     * Display the specified achievement.
     *
     * @param int $id
     * @return View
     */
    public function show($id)
    {
        $achievement = Achievement::with('users')->findOrFail($id);
        return view('achievements.show', ['achievement' => $achievement]);
    }

    /**
     * Show the form for editing the specified achievement.
     *
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $achievement = Achievement::findOrFail($id);
        return view('achievements.edit', ['achievement' => $achievement]);
    }

    /**
     * Update the specified achievement in storage.
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
            'points' => 'sometimes|required|integer|min:0',
            'image_url' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('achievements.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        $achievement = Achievement::findOrFail($id);
        $achievement->update($request->all());
        
        return redirect()->route('achievements.show', $id)
            ->with('success', 'Achievement updated successfully.');
    }

    /**
     * Remove the specified achievement from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $achievement = Achievement::findOrFail($id);
        $achievement->delete();
        
        return redirect()->route('achievements.index')
            ->with('success', 'Achievement deleted successfully.');
    }
    
    /**
     * Award an achievement to a user.
     *
     * @param int $id Achievement ID
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function awardToUser(int $id, Request $request)
    {
        $achievement = Achievement::findOrFail($id);
        $userId = $request->input('user_id');
        $user = User::findOrFail($userId);
        
        // Check if user already has this achievement
        if (!$user->achievements()->where('achievement_id', $id)->exists()) {
            $user->achievements()->attach($id);
            return redirect()->back()->with('success', 'Achievement awarded to user successfully.');
        }
        
        return redirect()->back()->with('error', 'User already has this achievement.');
    }
    
    /**
     * Remove an achievement from a user.
     *
     * @param int $id Achievement ID
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeFromUser(int $id, Request $request)
    {
        $achievement = Achievement::findOrFail($id);
        $userId = $request->input('user_id');
        $user = User::findOrFail($userId);
        
        $user->achievements()->detach($id);
        
        return redirect()->back()->with('success', 'Achievement removed from user successfully.');
    }
    
    /**
     * Get achievements for a specific user.
     *
     * @param int $userId
     * @return View
     */
    public function getUserAchievements(int $userId)
    {
        $user = User::with('achievements')->findOrFail($userId);
        return view('achievements.user', ['user' => $user]);
    }
    
    /**
     * Get leaderboard based on achievement points.
     *
     * @param Request $request
     * @return View
     */
    public function getLeaderboard(Request $request)
    {
        $limit = $request->input('limit', 10);
        
        $users = User::withCount(['achievements as total_points' => function ($query) {
                $query->select(\DB::raw('sum(points)'));
            }])
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();
            
        return view('achievements.leaderboard', ['users' => $users]);
    }
    
    /**
     * Get a list of achievements for bulk operations.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('achievements.index')
                ->with('error', 'No achievements selected for deletion.');
        }
        
        Achievement::whereIn('id', $ids)->delete();
        
        return redirect()->route('achievements.index')
            ->with('success', count($ids) . ' achievements deleted successfully.');
    }
    
    /**
     * Get achievement options for dropdowns.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        $achievements = Achievement::select('id', 'name')->orderBy('name')->get();
        return response()->json($achievements);
    }
} 