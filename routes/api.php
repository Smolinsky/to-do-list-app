<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [\App\Http\Controllers\Api\Auth\RegisterController::class, 'register'])
    ->name('api.register');

Route::post('/login', [\App\Http\Controllers\Api\Auth\AuthController::class, 'login'])
    ->name('api.login');
Route::post('/forgot-password', [\App\Http\Controllers\Api\Auth\ResetPasswordController::class, 'forgot'])
    ->name('api.forgot-password');
Route::post('/reset-password', [\App\Http\Controllers\Api\Auth\ResetPasswordController::class, 'reset'])
    ->name('api.reset-password');
Route::post('/logout', [\App\Http\Controllers\Api\Auth\AuthController::class, 'logout'])->name('api.logout')
    ->middleware('auth:sanctum');


Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'tasks'], function () {
    Route::get('', [\App\Http\Controllers\Api\TaskController::class, 'getTasks'])
        ->name('api.tasks');
    Route::post('', [\App\Http\Controllers\Api\TaskController::class, 'createTask'])
        ->name('api.tasks.create');
    Route::get('{task}', [\App\Http\Controllers\Api\TaskController::class, 'getTask'])
        ->name('api.tasks.show');
    Route::put('{task}', [\App\Http\Controllers\Api\TaskController::class, 'updateTask'])
        ->name('api.tasks.update');
    Route::delete('{task}', [\App\Http\Controllers\Api\TaskController::class, 'deleteTask'])
        ->name('api.tasks.delete');
});
