<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\KPIController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProyekManajerController;
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

Route::get('/', function () {
    return redirect()->route('employees.index');
});

//Login
Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'login_submit'])->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/kpi/async', [KPIController::class, 'async'])->name('employees.async');

    Route::resource('employees', EmployeeController::class);
    Route::resource('projects', ProjectController::class);
    Route::resource('kpi', KPIController::class);
    Route::resource('project-managers', ProyekManajerController::class);



    Route::post('/employees/sync-jibble', [KPIController::class, 'syncJibbleUsers'])->name('employees.sync-jibble');
});
