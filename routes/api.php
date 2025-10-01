<?php

use App\Http\Controllers\Api\DemoBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/demo/services', [DemoBookingController::class, 'services']);
Route::get('/demo/bookings', [DemoBookingController::class, 'calendar']);
Route::post('/demo/bookings', [DemoBookingController::class, 'store']);
