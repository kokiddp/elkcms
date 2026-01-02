<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// Admin Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Content management routes will be added here later
// Route::resource('content/{model}', ContentController::class);

// Translation management routes will be added here later
// Route::resource('translations', TranslationController::class);
