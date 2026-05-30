<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::redirect('/', '/admin/dashboard');

// Authentication routes
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Authenticated Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Licenses Management
    Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
    Route::put('/licenses/{license}', [LicenseController::class, 'update'])->name('licenses.update');
    Route::post('/licenses/{license}/toggle', [LicenseController::class, 'toggleStatus'])->name('licenses.toggle');
    Route::delete('/licenses/{license}', [LicenseController::class, 'destroy'])->name('licenses.destroy');

    // Logs
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('/logs/clear-old', [LogController::class, 'clearOld'])->name('logs.clear-old');
    Route::post('/logs/clear-all', [LogController::class, 'clearAll'])->name('logs.clear-all');

    // Settings & Password update
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('password.update');
});
