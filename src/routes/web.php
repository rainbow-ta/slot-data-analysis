<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HallController;
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

// ホール
Route::get('/', [HallController::class, 'index']);
Route::get('halls/{hall}/edit', [HallController::class, 'edit'])->name('halls.edit');
Route::put('halls/{hall}', [HallController::class, 'update'])->name('halls.update');

// ホールデータ
Route::resource('halls.hall-data', HallDataController::class)->except(['show']);
Route::get('halls/{hall}/hall-data/detail', [HallDataController::class, 'detail'])->name('halls.hall-data.detail');
