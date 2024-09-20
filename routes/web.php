<?php

use App\Http\Controllers\UserController;
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

Route::get('/', function () {
    return view('welcome');
});


Route::post('user-registration', [UserController::class, 'user_registration']);
Route::post('user/login', [UserController::class, 'user_login']);
Route::get('user/profile', [UserController::class, 'user_profile'])->middleware('auth:sanctum');

Route::get('/logout', [UserController::class, 'user_logout'])->middleware('auth:sanctum');
Route::post('/profile/update', [UserController::class, 'update_profile'])->middleware('auth:sanctum');
Route::post('/otp', [UserController::class, 'otp']);
Route::post('/otp/verify', [UserController::class, 'otp_verify']);
Route::post('/reset/password', [UserController::class, 'reset_password']);




