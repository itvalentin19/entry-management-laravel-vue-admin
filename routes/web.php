<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Exclude specific routes for assets
Route::get('/{path}', function () {
    return view('application');
})->where('path', '^(?!avatars|css|js|images).*$'); // Exclude these directories

