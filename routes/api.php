<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\BookmarkController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('upload', [FileUploadController::class, 'uploadImage'])->name('upload');

    Route::controller(UserController::class)->group(function () {
        Route::get('user', 'user')->name('user');
        Route::patch('user', 'update')->name('user.update');
    });

    Route::controller(StoryController::class)->group(function () {
        Route::post('stories', 'store')->name('stories.store');
        Route::put('stories/{story}', 'update')->name('stories.update');
        Route::delete('stories/{story}', 'destroy')->name('stories.destroy');
        Route::get('stories/my', 'my_stories')->name('stories.my');
    });

    Route::controller(BookmarkController::class)->group(function () {
        Route::get('stories/bookmarks', 'index')->name('bookmarks');
        Route::post('stories/bookmarks/{story}', 'store')->name('bookmarks.store');
    });
});

Route::controller(UserController::class)->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('logout', 'logout')->name('logout');
    Route::post('register', 'register')->name('register');
});

Route::get('categories', [CategoryController::class, 'index'])->name('categories');

Route::controller(StoryController::class)->group(function () {
    Route::get('stories', 'index')->name('stories');
    Route::get('stories/{slug}', 'show')->name('stories.show');
    Route::get('/stories/{slug}/similar', 'getSimilarStories')->name('stories.similar');
});
