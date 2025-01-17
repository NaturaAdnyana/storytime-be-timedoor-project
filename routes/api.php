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
    Route::get('user', [UserController::class, 'user'])->name('user');
    Route::patch('user', [UserController::class, 'update'])->name('user.update');
    Route::post('logout', [UserController::class, 'logout'])->name('logout');

    Route::post('upload', [FileUploadController::class, 'upload_image']);

    Route::post('stories', [StoryController::class, 'store'])->name('stories.store');
    Route::put('stories/{slug}', [StoryController::class, 'update'])->name('stories.update');
    Route::delete('stories/{id}', [StoryController::class, 'destroy'])->name('stories.destroy');

    Route::get('stories/my', [StoryController::class, 'my_stories'])->name('stories.my');

    Route::get('stories/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks');
    Route::post('stories/bookmarks/{id}', [BookmarkController::class, 'store'])->name('bookmarks.store');
});

Route::post('login', [UserController::class, 'login'])->name('login');
Route::post('register', [UserController::class, 'register'])->name('register');

Route::get('categories', [CategoryController::class, 'index'])->name('categories');

Route::get('stories', [StoryController::class, 'index'])->name('stories');
Route::get('stories/{slug}', [StoryController::class, 'show'])->name('stories.show');
