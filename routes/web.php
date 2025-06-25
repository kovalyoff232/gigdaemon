<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/invoices', [App\Http\Controllers\HomeController::class, 'invoices'])->name('invoices');
Route::get('/invoices/{invoice}', [App\Http\Controllers\HomeController::class, 'showInvoice'])->name('invoices.show');
Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'downloadPDF'])->name('invoices.download');

