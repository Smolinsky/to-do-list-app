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
    Route::patch('{task}/move', [\App\Http\Controllers\Api\TaskController::class, 'moveTask'])
        ->name('api.tasks.move');
    Route::delete('{task}', [\App\Http\Controllers\Api\TaskController::class, 'deleteTask'])
        ->name('api.tasks.delete');

    Route::get('{task}/attachments', [\App\Http\Controllers\Api\TaskAttachmentController::class, 'getAttachments'])
        ->name('api.tasks.attachments');
    Route::post('{task}/attachments', [\App\Http\Controllers\Api\TaskAttachmentController::class, 'uploadAttachment'])
        ->name('api.tasks.attachments.create');
    Route::get('{task}/attachments/{attachment}/download', [\App\Http\Controllers\Api\TaskAttachmentController::class, 'downloadAttachment'])
        ->name('api.tasks.attachments.download');
    Route::delete('{task}/attachments/{attachment}', [\App\Http\Controllers\Api\TaskAttachmentController::class, 'deleteAttachment'])
        ->name('api.tasks.attachments.delete');
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'boards'], function () {
    Route::get('', [\App\Http\Controllers\Api\BoardController::class, 'getBoards'])
        ->name('api.boards');
    Route::post('', [\App\Http\Controllers\Api\BoardController::class, 'createBoard'])
        ->name('api.boards.create');
    Route::get('{board}', [\App\Http\Controllers\Api\BoardController::class, 'getBoard'])
        ->name('api.boards.show');
    Route::put('{board}', [\App\Http\Controllers\Api\BoardController::class, 'updateBoard'])
        ->name('api.boards.update');
    Route::delete('{board}', [\App\Http\Controllers\Api\BoardController::class, 'deleteBoard'])
        ->name('api.boards.delete');
});
