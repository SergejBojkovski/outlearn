<?php

use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ProfessorDataController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StudentDataController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserProgressController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::post('reset-password', [NewPasswordController::class, 'store']);
    
    // Verification routes - accessible without auth for email link clicks
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store']);
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy']);
});

// API Resources and custom routes
// Achievements
Route::get('achievements/options', [AchievementController::class, 'options']);
Route::post('achievements/bulk-delete', [AchievementController::class, 'bulkDelete']);
Route::get('achievements/user/{userId}', [AchievementController::class, 'getUserAchievements']);
Route::post('achievements/{id}/award', [AchievementController::class, 'awardToUser']);
Route::post('achievements/{id}/remove', [AchievementController::class, 'removeFromUser']);
Route::get('achievements/leaderboard', [AchievementController::class, 'getLeaderboard']);
Route::apiResource('achievements', AchievementController::class);

// Categories
Route::get('categories/options', [CategoryController::class, 'options']);
Route::post('categories/bulk-delete', [CategoryController::class, 'bulkDelete']);
Route::get('categories/{id}/courses', [CategoryController::class, 'getCourses']);
Route::get('categories/with-course-counts', [CategoryController::class, 'getWithCourseCounts']);
Route::get('categories/popular', [CategoryController::class, 'getPopular']);
Route::apiResource('categories', CategoryController::class);

// Courses
Route::get('courses/options', [CourseController::class, 'options']);
Route::post('courses/bulk-delete', [CourseController::class, 'bulkDelete']);
Route::get('courses/category/{categoryId}', [CourseController::class, 'getByCategory']);
Route::get('courses/popular', [CourseController::class, 'getPopular']);
Route::get('courses/user/{userId}', [CourseController::class, 'getForUser']);
Route::get('courses/recent', [CourseController::class, 'getRecent']);
Route::post('courses/{id}/enroll', [CourseController::class, 'enrollUser']);
Route::post('courses/{id}/unenroll', [CourseController::class, 'unenrollUser']);
Route::apiResource('courses', CourseController::class);

// Lessons
Route::get('lessons/options', [LessonController::class, 'options']);
Route::post('lessons/bulk-delete', [LessonController::class, 'bulkDelete']);
Route::get('lessons/module/{moduleId}', [LessonController::class, 'getByModule']);
Route::get('lessons/{id}/navigation', [LessonController::class, 'getNavigation']);
Route::post('lessons/reorder', [LessonController::class, 'reorder']);
Route::post('lessons/{id}/mark-completed', [LessonController::class, 'markCompleted']);
Route::get('lessons/{id}/completion-status', [LessonController::class, 'getCompletionStatus']);
Route::apiResource('lessons', LessonController::class);

// Modules
Route::get('modules/options', [ModuleController::class, 'options']);
Route::post('modules/bulk-delete', [ModuleController::class, 'bulkDelete']);
Route::get('modules/course/{courseId}', [ModuleController::class, 'getByCourse']);
Route::get('modules/{id}/navigation', [ModuleController::class, 'getNavigation']);
Route::post('modules/reorder', [ModuleController::class, 'reorder']);
Route::apiResource('modules', ModuleController::class);

// Professor Data
Route::get('professor-data/options', [ProfessorDataController::class, 'options']);
Route::post('professor-data/bulk-delete', [ProfessorDataController::class, 'bulkDelete']);
Route::apiResource('professor-data', ProfessorDataController::class);

// Roles
Route::get('roles/options', [RoleController::class, 'options']);
Route::post('roles/bulk-delete', [RoleController::class, 'bulkDelete']);
Route::apiResource('roles', RoleController::class);

// Student Data
Route::get('student-data/options', [StudentDataController::class, 'options']);
Route::post('student-data/bulk-delete', [StudentDataController::class, 'bulkDelete']);
Route::apiResource('student-data', StudentDataController::class);

// Users
Route::get('users/options', [UserController::class, 'options']);
Route::post('users/bulk-delete', [UserController::class, 'bulkDelete']);
Route::get('users/role/{roleId}', [UserController::class, 'getByRole']);
Route::get('users/{id}/enrolled-courses', [UserController::class, 'getEnrolledCourses']);
Route::get('users/{id}/taught-courses', [UserController::class, 'getTaughtCourses']);
Route::get('users/me', [UserController::class, 'me'])->middleware('auth:sanctum');
Route::apiResource('users', UserController::class);

// User Progress
Route::get('user-progress/options', [UserProgressController::class, 'options']);
Route::post('user-progress/bulk-delete', [UserProgressController::class, 'bulkDelete']);
Route::get('user-progress/user/{userId}', [UserProgressController::class, 'getByUser']);
Route::get('user-progress/user/{userId}/course/{courseId}', [UserProgressController::class, 'getUserCourseProgress']);
Route::get('user-progress/user/{userId}/summary', [UserProgressController::class, 'getUserProgressSummary']);
Route::post('user-progress/reset', [UserProgressController::class, 'resetProgress']);
Route::apiResource('user-progress', UserProgressController::class); 