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

Route::get('/', [App\Http\Controllers\HallDataController::class, 'index']);
Route::get('hall-data/{id}/event', [App\Http\Controllers\HallDataController::class, 'event']);
Route::get('hall-data/{id}/detail', [App\Http\Controllers\HallDataController::class, 'detail']);
