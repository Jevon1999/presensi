<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware(['web'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected routes - require session with auth_token
    Route::middleware('token')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Members CRUD
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::post('/', [MemberController::class, 'store'])->name('store');
            Route::get('/{id}', [MemberController::class, 'show'])->name('show');
            Route::put('/{id}', [MemberController::class, 'update'])->name('update');
            Route::delete('/{id}', [MemberController::class, 'destroy'])->name('destroy');
        });

        // Attendances
        Route::prefix('attendances')->name('attendances.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
            Route::get('/export', [AttendanceController::class, 'exportReport'])->name('export');
            Route::get('/{id}', [AttendanceController::class, 'show'])->name('show');
            Route::post('/{id}/reset', [AttendanceController::class, 'reset'])->name('reset');
        });

        // Progresses CRUD
        Route::prefix('progresses')->name('progresses.')->group(function () {
            Route::get('/', [ProgressController::class, 'index'])->name('index');
            Route::post('/', [ProgressController::class, 'store'])->name('store');
            Route::get('/{id}', [ProgressController::class, 'show'])->name('show');
            Route::put('/{id}', [ProgressController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProgressController::class, 'destroy'])->name('destroy');
        });

        // Offices CRUD
        Route::prefix('offices')->name('offices.')->group(function () {
            Route::get('/', [OfficeController::class, 'index'])->name('index');
            Route::post('/', [OfficeController::class, 'store'])->name('store');
            Route::get('/{id}', [OfficeController::class, 'show'])->name('show');
            Route::put('/{id}', [OfficeController::class, 'update'])->name('update');
            Route::delete('/{id}', [OfficeController::class, 'destroy'])->name('destroy');
        });

        // Users CRUD (Admin Management)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        });

        // Bot Configuration (WAHA proxy)
        Route::prefix('bot')->name('bot.')->group(function () {
            Route::get('/config', [BotController::class, 'config'])->name('config');
            Route::put('/config', [BotController::class, 'updateConfig'])->name('config.update');
            Route::get('/status', [BotController::class, 'status'])->name('status');
            Route::post('/session/start', [BotController::class, 'startSession'])->name('session.start');
            Route::post('/session/stop', [BotController::class, 'stopSession'])->name('session.stop');
            Route::get('/qr-code', [BotController::class, 'qrCode'])->name('qr-code');
            Route::post('/send-message', [BotController::class, 'sendMessage'])->name('send-message');
            Route::post('/broadcast', [BotController::class, 'broadcastMessage'])->name('broadcast');
            Route::get('/screenshot', [BotController::class, 'screenshot'])->name('screenshot');
        });
    });
});