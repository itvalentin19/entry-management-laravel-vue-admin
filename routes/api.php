<?php

use App\Http\Controllers\EntityController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\UserController;
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

Route::prefix('v1')->group(function () {
    Route::post('login', [UserController::class, 'login'])->name('user.login');
    Route::post('register', [UserController::class, 'register']);
    Route::post('reset-password', [UserController::class, 'resetUserPassword']);
    Route::post('forgot-password', [UserController::class, 'forgotPassword']);
    Route::middleware('auth:sanctum')->group(function () {
        // Authenticate for current user
        Route::get('user', [UserController::class, 'index']);
        Route::post('logout', [UserController::class, 'logout']);
        Route::post('update-account', [UserController::class, 'update']);
        Route::post('update-password', [UserController::class, 'updatePassword']);

        // User Management
        Route::get('users', [UserController::class, 'getUsers']);
        Route::post('user', [UserController::class, 'store']);
        Route::get('user/{id}', [UserController::class, 'getUser']);
        Route::post('user/{id}', [UserController::class, 'updateUser']);
        Route::delete('user/{id}', [UserController::class, 'delete']);

        // Entity Owner Management
        Route::get('owners', [OwnerController::class, 'list']);
        Route::post('owner', [OwnerController::class, 'store']);
        Route::get('owner/{id}', [OwnerController::class, 'index']);
        Route::post('owner/{id}', [OwnerController::class, 'update']);
        Route::delete('owner/{id}', [OwnerController::class, 'delete']);

        // Entity Management
        Route::get('entities', [EntityController::class, 'list']);
        Route::get('entities/props', [EntityController::class, 'getProps']);
        Route::post('entity', [EntityController::class, 'store']);
        Route::post('entity/bulk-upload', [EntityController::class, 'bulkUpload']);
        Route::get('entity/{id}', [EntityController::class, 'index']);
        Route::post('entity/{id}', [EntityController::class, 'update']);
        Route::delete('entity/{id}', [EntityController::class, 'delete']);

        // Stats for Dashboard
        Route::get('stats', [UserController::class, 'stats']);
    });
});
