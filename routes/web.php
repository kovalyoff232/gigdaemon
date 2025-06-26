<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/invoices', [App\Http\Controllers\HomeController::class, 'invoices'])->name('invoices');
Route::get('/invoices/{invoice}', [App\Http\Controllers\HomeController::class, 'showInvoice'])->name('invoices.show');
Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'downloadPDF'])->name('invoices.download');


Route::get('/system/clear-cache/KJH234G5H6J3K4L', function() {
    Artisan::call('cache:clear');
    return "Application cache cleared successfully.";
});