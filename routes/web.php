<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

