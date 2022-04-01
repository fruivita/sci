<?php

use App\Enums\Policy;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Authorization\PermissionLivewireIndex;
use App\Http\Livewire\Authorization\PermissionLivewireShow;
use App\Http\Livewire\Authorization\PermissionLivewireUpdate;
use App\Http\Livewire\Authorization\RoleLivewireIndex;
use App\Http\Livewire\Authorization\RoleLivewireShow;
use App\Http\Livewire\Authorization\RoleLivewireUpdate;
use App\Http\Livewire\Authorization\UserLivewireIndex;
use App\Http\Livewire\Simulation\SimulationLivewireCreate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
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

    Route::prefix('autorizacao')->name('authorization.')->group(function () {
        Route::prefix('perfil')->name('roles.')->group(function () {
            Route::get('/', RoleLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Role::class);
            Route::get('show/{role_id}', RoleLivewireShow::class)->name('show')->can(Policy::View->value, Role::class);
            Route::get('edit/{role}', RoleLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Role::class);
        });

        Route::prefix('permissao')->name('permissions.')->group(function () {
            Route::get('/', PermissionLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Permission::class);
            Route::get('show/{permission_id}', PermissionLivewireShow::class)->name('show')->can(Policy::View->value, Permission::class);
            Route::get('edit/{permission}', PermissionLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Permission::class);
        });

        Route::prefix('usuario')->name('users.')->group(function () {
            Route::get('/', UserLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, User::class);
        });
    });

    Route::prefix('simulacao')->name('simulation.')->group(function () {
        Route::get('create', SimulationLivewireCreate::class)->name('create')->can(Policy::SimulationCreate->value);
        Route::delete('/', [SimulationLivewireCreate::class, 'destroy'])->name('destroy')->can(Policy::SimulationDelete->value);
    });
});
