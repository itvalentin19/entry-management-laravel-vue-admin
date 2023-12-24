<?php

use App\Http\Controllers\EntityController;
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
Route::get('entity/report', function () {
    return view('pdf.report');
});
Route::get('reset-password', function () {
    return view('application');
})->name('password.reset');
Route::get('welcome', function () {
    return view('emails.welcome');
})->name('welcome');
Route::get('entity/report/{id}', [EntityController::class, 'getReport']);

Route::get('/{path}', function () {
    return view('application');
})->where('path', '^(?!avatars|css|js|images|report).*$'); // Exclude these directories

