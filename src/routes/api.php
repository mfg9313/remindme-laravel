<?php

use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\AuthController;
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

Route::put('/session', [AuthController::class, 'refresh']);

// Rate throttling,only 10 login attempts per minute
Route::middleware(['throttle:10,1'])->post('/session', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum', 'token.expiry'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

   Route::get('/reminders', [ReminderController::class, 'index']);
   Route::post('/reminders', [ReminderController::class, 'store']);
   Route::get('/reminders/{id}', [ReminderController::class, 'show']);
   Route::put('/reminders/{id}', [ReminderController::class, 'update']);
   Route::delete('/reminders/{id}', [ReminderController::class, 'destroy']);
});
