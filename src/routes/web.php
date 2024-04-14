<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HallDataController;

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

Route::get('/', [App\Http\Controllers\HallController::class, 'index']);
Route::get('halls/{id}/hall-data/event', [App\Http\Controllers\HallDataController::class, 'event']);
Route::get('halls/{id}/hall-data/detail', [App\Http\Controllers\HallDataController::class, 'detail']);
Route::resource('halls/{id}/hall-data', HallDataController::class);
