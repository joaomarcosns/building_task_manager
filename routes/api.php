<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\TaskCommentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\UserController;
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

Route::get('/', function () {
    return response()->json(['message' => 'Hello World!']);
});

// Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::apiResource('clients', ClientController::class)->only(['index']);
Route::apiResource('buildings', BuildingController::class)->only(['index']);
Route::apiResource('users', UserController::class)->only(['index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function (Request $request) {
        return auth()->user();
    });

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('tasks/{task}/comments', TaskCommentController::class)->except(['index', 'show']);
});
