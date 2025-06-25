<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\TimeEntryController; // <-- ВОТ ОН, НЕДОСТАЮЩИЙ КЛЮЧ!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    // Этот маршрут вернет данные текущего залогиненного пользователя
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // CRUD маршруты для клиентов и проектов
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('projects', ProjectController::class);
    
    // --- Маршруты для управления ВРЕМЕНЕМ ---

    // Получить все записи времени для конкретного проекта
    Route::get('/projects/{project}/time-entries', [TimeEntryController::class, 'index']);

    // Запустить новый таймер для проекта
    Route::post('/projects/{project}/time-entries/start', [TimeEntryController::class, 'start']);

    // Остановить активный таймер
    Route::patch('/time-entries/{time_entry}/stop', [TimeEntryController::class, 'stop']);

    // Обновить существующую запись времени (например, изменить описание)
    Route::put('/time-entries/{time_entry}', [TimeEntryController::class, 'update']);

    // Удалить запись времени
    Route::delete('/time-entries/{time_entry}', [TimeEntryController::class, 'destroy']);
    
    // Получить текущий активный таймер пользователя
    Route::get('/time-entries/active', [TimeEntryController::class, 'getActive']);
});