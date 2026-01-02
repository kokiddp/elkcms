<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Login routes
Route::get('/elk-login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/elk-login', [LoginController::class, 'login']);

// Registration routes  
Route::get('/elk-register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/elk-register', [RegisterController::class, 'register']);

// Logout route
Route::post('/elk-logout', [LoginController::class, 'logout'])->name('logout');
