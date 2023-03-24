<?php

use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Models\Company;
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

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::get('/account-verify/{token}', 'verifyAccount');
    Route::post('login', 'login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
});

Route::middleware(['auth:api'])->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::post('list', 'list');
        Route::get('get/{id}', 'get');
        Route::get('logout', 'logout');
        Route::post('change-password', 'changePassword');
    });
    Route::middleware(['admin', 'employee'])->group(function () {

        /**
         * Company Module Crud 
         */
        Route::controller(CompanyController::class)->prefix('company')->group(function () {
            Route::post('list', 'list');
            Route::post('create', 'create');
            Route::post('update/{id}', 'update');
            Route::get('get/{id}', 'get');
            Route::delete('delete/{id}', 'destroy');
            Route::delete('force-delete/{id}', 'forceDelete');
            Route::get('restore-deleted-company/{id}', 'restoreDeletedCompany');
        });

        /**
         * Employee Module Crud
         */
        Route::controller(EmployeeController::class)->prefix('employee')->group(function () {
            Route::post('list', 'list');
            Route::post('create', 'create');
            Route::delete('delete/{id}', 'destroy');
            Route::delete('force-delete/{id}', 'forceDelete');
            Route::get('restore-deleted-employee/{id}', 'restoreDeletedEmployee');
            Route::post('export', 'export');
            Route::post('import', 'import');
        });

        /**
         * Employee Task Module Crud
         */
        Route::controller(TaskController::class)->prefix('task')->group(function () {
            Route::post('list', 'list');
            Route::post('create', 'create');
            Route::post('update/{id}', 'update');
            Route::get('get/{id}', 'get');
            Route::delete('delete/{id}', 'destroy');
            Route::delete('force-delete/{id}', 'forceDelete');
            Route::get('restore-deleted-employee/{id}', 'restoreDeletedTask');
        });

        /**
         * Job Module Crud
         */
        Route::controller(JobController::class)->prefix('job')->group(function () {
            Route::post('list', 'list');
            Route::post('create', 'create');
            Route::post('update/{id}', 'update');
            Route::get('get/{id}', 'get');
            Route::delete('delete/{id}', 'destroy');
            Route::delete('force-delete/{id}', 'forceDelete');
            Route::get('restore-deleted-employee/{id}', 'restoreDeletedJob');
        });

        Route::controller(CandidateController::class)->prefix('candidate')->group(function () {
            Route::post('list', 'list');
            Route::post('create', 'create')->withoutMiddleware(['auth:api', 'admin']);
            Route::post('change-position/{id}', 'update');
            Route::get('get/{id}', 'get');
            Route::delete('delete/{id}', 'destroy');
        });
    });

    /**
     * Employee Task Module Crud
     */
    Route::controller(TaskController::class)->middleware(['employee'])->prefix('task')->group(function () {
        Route::post('update/{id}', 'update');
        Route::get('get/{id}', 'get');
    });
});
