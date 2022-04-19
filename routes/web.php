<?php

use App\Enums\Policy;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Administration\ImportationLivewireCreate;
use App\Http\Livewire\Authorization\DelegationLivewireIndex;
use App\Http\Livewire\Authorization\PermissionLivewireIndex;
use App\Http\Livewire\Authorization\PermissionLivewireShow;
use App\Http\Livewire\Authorization\PermissionLivewireUpdate;
use App\Http\Livewire\Authorization\RoleLivewireIndex;
use App\Http\Livewire\Authorization\RoleLivewireShow;
use App\Http\Livewire\Authorization\RoleLivewireUpdate;
use App\Http\Livewire\Authorization\UserLivewireIndex;
use App\Http\Livewire\Printer\PrinterReportLivewire;
use App\Http\Livewire\Printing\PrintingReportLivewire;
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
        Route::prefix('delegacao')->name('delegations.')->group(function () {
            Route::get('/', DelegationLivewireIndex::class)->name('index')->can(Policy::DelegationViewAny->value);
        });

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

    Route::prefix('importacao')->name('importation.')->group(function () {
        Route::get('create', ImportationLivewireCreate::class)->name('create')->can(Policy::ImportationCreate->value);
    });

    Route::prefix('relatorio')->name('report.')->group(function () {
        Route::prefix('impressao')->name('printing.')->group(function () {
            Route::get('/', PrintingReportLivewire::class)->name('create');
        });

        Route::prefix('impressora')->name('printer.')->group(function () {
            Route::get('/', PrinterReportLivewire::class)->name('create');
        });
    });

    Route::prefix('simulacao')->name('simulation.')->group(function () {
        Route::get('create', SimulationLivewireCreate::class)->name('create')->can(Policy::SimulationCreate->value);
        Route::delete('/', [SimulationLivewireCreate::class, 'destroy'])->name('destroy')->can(Policy::SimulationDelete->value);
    });
});
