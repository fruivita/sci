<?php

use App\Http\Controllers\HomeController;
use App\Http\Livewire\Authorization\RoleLivewire;
use App\Models\Role;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('login');
    })->name('login');

    Route::post('/', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['throttle:login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('home', [HomeController::class, 'index'])->name('home');

    Route::prefix('autorização')->name('authorization.')->group(function () {
        Route::prefix('perfil')->name('roles.')->group(function () {
            Route::get('/', RoleLivewire::class)->name('index')->can('viewAny', Role::class);
        });
    });
});
