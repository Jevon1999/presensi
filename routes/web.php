<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProgressController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware(['web'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me']);

    // Protected routes - require session with auth_token
    Route::middleware('token')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Members
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Members/Index');
            })->name('index');
            Route::get('/{id}', [MemberController::class, 'show'])->name('show');
            Route::post('/', [MemberController::class, 'store'])->name('store');
            Route::put('/{id}', [MemberController::class, 'update'])->name('update');
            Route::delete('/{id}', [MemberController::class, 'destroy'])->name('destroy');
        });

        // Attendances
        Route::prefix('attendances')->name('attendances.')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Attendances/Index');
            })->name('index');
            Route::get('/{id}', [AttendanceController::class, 'show'])->name('show');
            Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
            Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
            Route::post('/{id}/reset', [AttendanceController::class, 'reset'])->name('reset');
        });

        // Progresses
        Route::prefix('progresses')->name('progresses.')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Progresses/Index');
            })->name('index');
            Route::get('/{id}', [ProgressController::class, 'show'])->name('show');
            Route::post('/', [ProgressController::class, 'store'])->name('store');
            Route::put('/{id}', [ProgressController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProgressController::class, 'destroy'])->name('destroy');
        });

        // Bot configuration
        Route::get('/bot/config', function () {
            return Inertia::render('Bot/Config');
        })->name('bot.config');

        // User management
        Route::get('/users', function () {
            return Inertia::render('Users/Index');
        })->name('users.index');
    });
});