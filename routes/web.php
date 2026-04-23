<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberApplyController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\HolidayController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes (login & register)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware(['web'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- Routes for any authenticated user (token only) ---
    Route::middleware('token')->group(function () {
        // Member application flow (role=user, no/rejected member)
        Route::get('/member/apply', [MemberApplyController::class, 'showApply'])->name('member.apply');
        Route::post('/member/apply', [MemberApplyController::class, 'submitApply']);
        Route::get('/member/pending', [MemberApplyController::class, 'showPending'])->name('member.pending');
    });

    // --- Admin routes (role=admin) ---
    Route::middleware(['token', 'admin'])->group(function () {
        // Internal proxy for badges/notifications
        Route::get('/internal/pending-members-count', function() {
            try {
                $apiUrl = rtrim(env('API_URL'), '/');
                $response = Illuminate\Support\Facades\Http::withToken(session('auth_token'))
                    ->timeout(10)
                    ->get($apiUrl . '/members/pending-count');
                
                if ($response->successful()) {
                    return response()->json(['count' => $response->json('count') ?? 0]);
                }
                return response()->json(['count' => 0, 'error' => 'API returned ' . $response->status()]);
            } catch (\Exception $e) {
                return response()->json(['count' => 0, 'error' => $e->getMessage()]);
            }
        });

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Members CRUD + approval
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::post('/', [MemberController::class, 'store'])->name('store');
            Route::get('/{id}', [MemberController::class, 'show'])->name('show');
            Route::put('/{id}', [MemberController::class, 'update'])->name('update');
            Route::delete('/{id}', [MemberController::class, 'destroy'])->name('destroy');
            Route::put('/{id}/approve', [MemberController::class, 'approve'])->name('approve');
            Route::put('/{id}/reject', [MemberController::class, 'reject'])->name('reject');
        });

        // Attendances
        Route::prefix('attendances')->name('attendances.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
            Route::get('/export', [AttendanceController::class, 'exportReport'])->name('export');
            Route::get('/export/pdf', [AttendanceController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/{id}', [AttendanceController::class, 'show'])->name('show');
            Route::post('/{id}/reset', [AttendanceController::class, 'reset'])->name('reset');
        });

        // Statistics
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');

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
            Route::get('/lookup-member', [BotController::class, 'lookupMember'])->name('lookup-member');
        });

        // Holidays — Hari Libur Nasional
        Route::prefix('holidays')->name('holidays.')->group(function () {
            Route::get('/', [HolidayController::class, 'index'])->name('index');
            Route::post('/', [HolidayController::class, 'store'])->name('store');
            Route::post('/sync', [HolidayController::class, 'sync'])->name('sync');
            Route::delete('/{id}', [HolidayController::class, 'destroy'])->name('destroy');
        });
    });

    // --- Member routes (role=user with approved member) ---
    Route::middleware(['token', 'member'])->prefix('member')->group(function () {
        Route::get('/dashboard', [MemberDashboardController::class, 'dashboard'])->name('member.dashboard');
        Route::get('/progress', [MemberDashboardController::class, 'progress'])->name('member.progress');
        Route::get('/report', [MemberDashboardController::class, 'report'])->name('member.report');
    });
});