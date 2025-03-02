<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AchievementCollection;
use App\Http\Resources\AchievementResource;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->modelClass = Achievement::class;
        $this->resourceClass = AchievementResource::class;
        $this->collectionClass = AchievementCollection::class;
        
        $this->storeRules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string',
            'points' => 'required|integer|min:0',
            'requirements' => 'required|string'
        ];
        
        $this->updateRules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'icon' => 'nullable|string',
            'points' => 'sometimes|required|integer|min:0',
            'requirements' => 'sometimes|required|string'
        ];
        
        $this->searchableFields = ['name', 'description', 'requirements'];
        $this->filterableFields = ['points'];
        $this->sortableFields = ['name', 'points', 'created_at', 'updated_at'];
        $this->defaultRelations = [];
    }
    
    /**
     * Get achievements for a specific user
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserAchievements(int $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $achievements = $user->achievements()->get();
        
        return response()->json([
            'status' => 'success',
            'data' => AchievementResource::collection($achievements),
            'message' => 'User achievements retrieved successfully'
        ]);
    }
    
    /**
     * Award an achievement to a user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function awardToUser(Request $request, int $id): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $achievement = Achievement::findOrFail($id);
        $userId = $request->input('user_id');
        $user = User::findOrFail($userId);
        
        // Check if user already has this achievement
        if ($user->achievements()->where('achievement_id', $id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already has this achievement'
            ], 400);
        }
        
        // Award achievement to user
        $user->achievements()->attach($id, ['awarded_at' => now()]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Achievement awarded to user successfully'
        ]);
    }
    
    /**
     * Remove an achievement from a user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function removeFromUser(Request $request, int $id): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }
        
        $achievement = Achievement::findOrFail($id);
        $userId = $request->input('user_id');
        $user = User::findOrFail($userId);
        
        // Check if user has this achievement
        if (!$user->achievements()->where('achievement_id', $id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not have this achievement'
            ], 400);
        }
        
        // Remove achievement from user
        $user->achievements()->detach($id);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Achievement removed from user successfully'
        ]);
    }
    
    /**
     * Get leaderboard based on achievement points
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        
        $leaderboard = User::select('users.id', 'users.name', 'users.email')
            ->join('achievement_user', 'users.id', '=', 'achievement_user.user_id')
            ->join('achievements', 'achievement_user.achievement_id', '=', 'achievements.id')
            ->selectRaw('SUM(achievements.points) as total_points')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $leaderboard,
            'message' => 'Leaderboard retrieved successfully'
        ]);
    }
} 