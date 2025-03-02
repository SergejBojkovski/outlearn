<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfessorDataController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentDataController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProgressController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

// Admin Authentication Routes
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    // Guest routes for admin
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
        Route::get('/register', [AdminAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register']);
    });
    
    // Admin authenticated routes
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth');
});

// Add a general logout route that points to the admin logout
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth');

// User Dashboard - Protected Routes
Route::get('/admin/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// User Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // Course Management
    Route::get('/courses', [AdminController::class, 'courses'])->name('courses');
    Route::get('/courses/index', [AdminController::class, 'courses'])->name('courses.index');
    Route::get('/courses/create', [AdminController::class, 'createCourse'])->name('courses.create');
    Route::post('/courses', [AdminController::class, 'storeCourse'])->name('courses.store');
    Route::get('/courses/{id}', [AdminController::class, 'showCourse'])->name('courses.show');
    Route::get('/courses/{id}/edit', [AdminController::class, 'editCourse'])->name('courses.edit');
    Route::put('/courses/{id}', [AdminController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{id}', [AdminController::class, 'deleteCourse'])->name('courses.delete');
    
    // Module Management
    Route::get('/courses/{courseId}/modules/create', [AdminController::class, 'createModule'])->name('modules.create');
    Route::post('/courses/{courseId}/modules', [AdminController::class, 'storeModule'])->name('modules.store');
    Route::get('/courses/{courseId}/modules/{moduleId}', [AdminController::class, 'showModule'])->name('modules.show');
    Route::get('/courses/{courseId}/modules/{moduleId}/edit', [AdminController::class, 'editModule'])->name('modules.edit');
    Route::put('/courses/{courseId}/modules/{moduleId}', [AdminController::class, 'updateModule'])->name('modules.update');
    Route::delete('/courses/{courseId}/modules/{moduleId}', [AdminController::class, 'deleteModule'])->name('modules.delete');
    
    // Lesson Management
    Route::get('/courses/{courseId}/modules/{moduleId}/lessons/create', [AdminController::class, 'createLesson'])->name('lessons.create');
    Route::post('/courses/{courseId}/modules/{moduleId}/lessons', [AdminController::class, 'storeLesson'])->name('lessons.store');
    Route::get('/courses/{courseId}/modules/{moduleId}/lessons/{lessonId}', [AdminController::class, 'showLesson'])->name('lessons.show');
    Route::get('/courses/{courseId}/modules/{moduleId}/lessons/{lessonId}/edit', [AdminController::class, 'editLesson'])->name('lessons.edit');
    Route::put('/courses/{courseId}/modules/{moduleId}/lessons/{lessonId}', [AdminController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/courses/{courseId}/modules/{moduleId}/lessons/{lessonId}', [AdminController::class, 'deleteLesson'])->name('lessons.delete');
    
    // Category Management
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    
    // Achievement Management
    Route::get('/achievements', [AdminController::class, 'achievements'])->name('achievements');
    
    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
    
    // Verification routes - accessible without auth for email link clicks
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::middleware('auth')->group(function () {
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // User Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Achievements
    Route::get('achievements/options', [AchievementController::class, 'options'])->name('achievements.options');
    Route::post('achievements/bulk-delete', [AchievementController::class, 'bulkDelete'])->name('achievements.bulk-delete');
    Route::get('achievements/user/{userId}', [AchievementController::class, 'getUserAchievements'])->name('achievements.user');
    Route::post('achievements/{id}/award', [AchievementController::class, 'awardToUser'])->name('achievements.award');
    Route::post('achievements/{id}/remove', [AchievementController::class, 'removeFromUser'])->name('achievements.remove');
    Route::get('achievements/leaderboard', [AchievementController::class, 'getLeaderboard'])->name('achievements.leaderboard');
    Route::resource('achievements', AchievementController::class);
    
    // Categories
    Route::get('categories/options', [CategoryController::class, 'options'])->name('categories.options');
    Route::post('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
    Route::get('categories/{id}/courses', [CategoryController::class, 'getCourses'])->name('categories.courses');
    Route::get('categories/with-course-counts', [CategoryController::class, 'getWithCourseCounts'])->name('categories.with-course-counts');
    Route::get('categories/popular', [CategoryController::class, 'getPopular'])->name('categories.popular');
    Route::resource('categories', CategoryController::class);
    
    // Courses
    Route::get('courses/options', [CourseController::class, 'options'])->name('courses.options');
    Route::post('courses/bulk-delete', [CourseController::class, 'bulkDelete'])->name('courses.bulk-delete');
    Route::get('courses/category/{categoryId}', [CourseController::class, 'getByCategory'])->name('courses.by-category');
    Route::get('courses/popular', [CourseController::class, 'getPopular'])->name('courses.popular');
    Route::get('courses/user/{userId}', [CourseController::class, 'getForUser'])->name('courses.for-user');
    Route::get('courses/recent', [CourseController::class, 'getRecent'])->name('courses.recent');
    Route::post('courses/{id}/enroll', [CourseController::class, 'enrollUser'])->name('courses.enroll');
    Route::post('courses/{id}/unenroll', [CourseController::class, 'unenrollUser'])->name('courses.unenroll');
    Route::resource('courses', CourseController::class);
    
    // Lessons
    Route::get('lessons/options', [LessonController::class, 'options'])->name('lessons.options');
    Route::post('lessons/bulk-delete', [LessonController::class, 'bulkDelete'])->name('lessons.bulk-delete');
    Route::get('lessons/module/{moduleId}', [LessonController::class, 'getByModule'])->name('lessons.by-module');
    Route::get('lessons/{id}/navigation', [LessonController::class, 'getNavigation'])->name('lessons.navigation');
    Route::post('lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');
    Route::post('lessons/{id}/mark-completed', [LessonController::class, 'markCompleted'])->name('lessons.mark-completed');
    Route::get('lessons/{id}/completion-status', [LessonController::class, 'getCompletionStatus'])->name('lessons.completion-status');
    Route::resource('lessons', LessonController::class);
    
    // Modules
    Route::get('modules/options', [ModuleController::class, 'options'])->name('modules.options');
    Route::post('modules/bulk-delete', [ModuleController::class, 'bulkDelete'])->name('modules.bulk-delete');
    Route::get('modules/course/{courseId}', [ModuleController::class, 'getByCourse'])->name('modules.by-course');
    Route::get('modules/{id}/navigation', [ModuleController::class, 'getNavigation'])->name('modules.navigation');
    Route::post('modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');
    Route::resource('modules', ModuleController::class);
    
    // Professor Data
    Route::get('professor-data/options', [ProfessorDataController::class, 'options'])->name('professor-data.options');
    Route::post('professor-data/bulk-delete', [ProfessorDataController::class, 'bulkDelete'])->name('professor-data.bulk-delete');
    Route::resource('professor-data', ProfessorDataController::class);
    
    // Roles
    Route::get('roles/options', [RoleController::class, 'options'])->name('roles.options');
    Route::post('roles/bulk-delete', [RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');
    Route::resource('roles', RoleController::class);
    
    // Student Data
    Route::get('student-data/options', [StudentDataController::class, 'options'])->name('student-data.options');
    Route::post('student-data/bulk-delete', [StudentDataController::class, 'bulkDelete'])->name('student-data.bulk-delete');
    Route::resource('student-data', StudentDataController::class);
    
    // Users
    Route::get('users/options', [UserController::class, 'options'])->name('users.options');
    Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::get('users/role/{roleId}', [UserController::class, 'getByRole'])->name('users.by-role');
    Route::get('users/{id}/enrolled-courses', [UserController::class, 'getEnrolledCourses'])->name('users.enrolled-courses');
    Route::get('users/{id}/taught-courses', [UserController::class, 'getTaughtCourses'])->name('users.taught-courses');
    Route::get('users/me', [UserController::class, 'me'])->name('users.me');
    Route::resource('users', UserController::class);
    
    // User Progress
    Route::get('user-progress/options', [UserProgressController::class, 'options'])->name('user-progress.options');
    Route::post('user-progress/bulk-delete', [UserProgressController::class, 'bulkDelete'])->name('user-progress.bulk-delete');
    Route::get('user-progress/user/{userId}', [UserProgressController::class, 'getByUser'])->name('user-progress.by-user');
    Route::get('user-progress/user/{userId}/course/{courseId}', [UserProgressController::class, 'getUserCourseProgress'])->name('user-progress.course');
    Route::get('user-progress/user/{userId}/summary', [UserProgressController::class, 'getUserProgressSummary'])->name('user-progress.summary');
    Route::post('user-progress/reset', [UserProgressController::class, 'resetProgress'])->name('user-progress.reset');
    Route::resource('user-progress', UserProgressController::class);
});

