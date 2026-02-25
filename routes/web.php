<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ProgressController;

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

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['web'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    //auth endpoint
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    //attendance 
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::get('/attendances/{id}', [AttendanceController::class, 'show']);
    Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut']);
    Route::post('/attendances/{id}/reset', [AttendanceController::class, 'reset']);

    //member
    Route::get('/members', [MemberController::class, 'index']);
    Route::get('/members/{id}', [MemberController::class, 'show']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::put('/members/{id}', [MemberController::class, 'update']);
    Route::delete('/members/{id}', [MemberController::class, 'destroy']);

    //office
    Route::get('/offices', [OfficeController::class, 'index']);
    Route::get('/offices/{id}', [OfficeController::class, 'show']);
    Route::post('/offices', [OfficeController::class, 'store']);
    Route::put('/offices/{id}', [OfficeController::class, 'update']);
    Route::delete('/offices/{id}', [OfficeController::class, 'destroy']);

    //progresses
    Route::get('/progresses', [ProgressController::class, 'index']);
    Route::get('/progresses/{id}', [ProgressController::class, 'show']);
    Route::post('/progresses', [ProgressController::class, 'store']);
    Route::put('/progresses/{id}', [ProgressController::class, 'update']);
    Route::delete('/progresses/{id}', [ProgressController::class, 'destroy']);

});