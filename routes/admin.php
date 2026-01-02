<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// Admin Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Content Management
Route::prefix('content/{modelType}')->name('content.')->group(function () {
    Route::get('/', [ContentController::class, 'index'])->name('index');
    Route::get('/create', [ContentController::class, 'create'])->name('create');
    Route::post('/', [ContentController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ContentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ContentController::class, 'update'])->name('update');
    Route::delete('/{id}', [ContentController::class, 'destroy'])->name('destroy');
});

// Translation management routes will be added here later
// Route::resource('translations', TranslationController::class);
