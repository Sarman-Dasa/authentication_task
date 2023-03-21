<?php

use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->group(function()
{
    Route::post('register','register');
    Route::get('/account-verify/{token}','verifyAccount');
    Route::post('login','login');
    Route::post('forgot-password','forgotPassword');
    Route::post('reset-password','resetPassword');
});

Route::middleware(['auth:api'])->group(function(){
    Route::controller(UserController::class)->group(function(){
        Route::post('list','list');
        Route::get('get/{id}','get');
        Route::get('logout','logout');
        Route::post('change-password','changePassword');
    });
});

