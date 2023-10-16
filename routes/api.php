<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;
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

Route::group(['prefix' => 'auth'], function () {
    Route::post('signup', [AuthController::class, 'signup'])->name('auth.signup');
    Route::post('signin', [AuthController::class, 'signin'])->name('auth.signin');

    Route::group(['middleware' => 'jwt.auth'], function () {
        Route::get('refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::get('signout', [AuthController::class, 'signout'])->name('auth.signout');
        Route::get('current-user', [AuthController::class, 'currentUser'])->name('auth.current-user');
    });
});

Route::group([], function () {
    Route::get('posts', [PostController::class, 'list'])->name('posts.list');
    Route::get('posts/{id}', [PostController::class, 'one'])->name('posts.one');

    Route::group(['middleware' => 'jwt.auth'], function () {
        Route::post('posts', [PostController::class, 'add'])->name('posts.add');
        Route::patch('posts/{id}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('posts/{id}', [PostController::class, 'delete'])->name('posts.delete');
    });
});
