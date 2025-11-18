<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectPermissionController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;


// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // Rate limit: 5 attempts per minute

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('tasks', [TaskController::class, 'allTasks']);

    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{id}/archive', [ProjectController::class, 'archive']);
    Route::post('projects/{id}/restore', [ProjectController::class, 'restore']);

    Route::get('projects/{projectId}/permissions', [ProjectPermissionController::class, 'index']);
    Route::post('projects/{projectId}/permissions', [ProjectPermissionController::class, 'store']);
    Route::delete('projects/{projectId}/permissions/{id}', [ProjectPermissionController::class, 'destroy']);

    Route::get('projects/{projectId}/tasks', [TaskController::class, 'index']);
    Route::post('projects/{projectId}/tasks', [TaskController::class, 'store']);
    Route::apiResource('tasks', TaskController::class)->except(['index', 'store']);
    Route::post('tasks/{id}/assign', [TaskController::class, 'assign']);

    Route::get('tasks/{taskId}/comments', [CommentController::class, 'index']);
    Route::post('tasks/{taskId}/comments', [CommentController::class, 'store']);
    Route::put('comments/{id}', [CommentController::class, 'update']);
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);

    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{id}/members', [TeamController::class, 'addMember']);
    Route::delete('teams/{id}/members/{userId}', [TeamController::class, 'removeMember']);
});
