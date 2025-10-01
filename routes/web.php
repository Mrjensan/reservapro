<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'index'])->name('home');
Route::get('/api/bookings', [BookingController::class, 'calendar'])->name('bookings.calendar');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('services', ServiceController::class)->except(['show']);

    Route::get('bookings/export', [AdminBookingController::class, 'export'])->name('bookings.export');
    Route::post('bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');
    Route::resource('bookings', AdminBookingController::class)->only(['index', 'show', 'destroy']);
});

Route::get('/demo', function () {
    return view('demo');
})->name('demo');
