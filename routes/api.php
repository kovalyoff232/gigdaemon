<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TimeEntryController;
use App\Http\Controllers\Api\V1\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Защищенные маршруты, доступные только после входа в систему
Route::middleware('auth:sanctum')->group(function () {
    // Пользователь
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // CRUD маршруты
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('invoices', InvoiceController::class);
    

    Route::get('/projects/{project}/time-entries', [TimeEntryController::class, 'index']);
    Route::post('/projects/{project}/time-entries/start', [TimeEntryController::class, 'start']);
    Route::patch('/time-entries/{time_entry}/stop', [TimeEntryController::class, 'stop']);
    Route::put('/time-entries/{time_entry}', [TimeEntryController::class, 'update']);
    Route::delete('/time-entries/{time_entry}', [TimeEntryController::class, 'destroy']);
    Route::get('/time-entries/active', [TimeEntryController::class, 'getActive']);
	Route::get('/invoices', [InvoiceController::class, 'index']);
	Route::post('/invoices', [InvoiceController::class, 'store']);
	Route::get('/clients/{client}/unbilled-entries', [InvoiceController::class, 'getUnbilledEntries']);
	Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
	Route::get('/dashboard-summary', [DashboardController::class, 'summary']);
	Route::patch('/invoices/{invoice}/status', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'updateStatus']);
	Route::post('/projects/{project}/time-entries/manual', [\App\Http\Controllers\Api\V1\TimeEntryController::class, 'storeManual']);
});