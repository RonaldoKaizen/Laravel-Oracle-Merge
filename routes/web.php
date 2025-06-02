<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

// Rutas de autenticación (login/out + register)
Route::get('/login',   [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
Route::get('/register',[AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas (requieren estar autenticado)
Route::middleware('auth')->group(function () {
    Route::get('/',             [HomeController::class, 'index'])->name('home');
    Route::post('/sync-alumno', [HomeController::class, 'syncAlumno'])->name('alumno.sync');
});