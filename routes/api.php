<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Защищенные маршруты
Route::middleware('auth:sanctum')->group(function () {
    // Этот маршрут вернет данные текущего залогиненного пользователя
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // А это - полный набор CRUD маршрутов для наших клиентов
    Route::apiResource('clients', ClientController::class);
	Route::apiResource('projects', ProjectController::class);
});