<?php

use App\Enums\Policy;
use App\Http\Controllers\HomeController;
use App\Http\Livewire\Administration\Configuration\ConfigurationLivewireShow;
use App\Http\Livewire\Administration\Configuration\ConfigurationLivewireUpdate;
use App\Http\Livewire\Administration\Importation\ImportationLivewireCreate;
use App\Http\Livewire\Administration\Log\LogLivewireIndex;
use App\Http\Livewire\Administration\Server\ServerLivewireIndex;
use App\Http\Livewire\Administration\Server\ServerLivewireShow;
use App\Http\Livewire\Administration\Server\ServerLivewireUpdate;
use App\Http\Livewire\Administration\Site\SiteLivewireCreate;
use App\Http\Livewire\Administration\Site\SiteLivewireIndex;
use App\Http\Livewire\Administration\Site\SiteLivewireShow;
use App\Http\Livewire\Administration\Site\SiteLivewireUpdate;
use App\Http\Livewire\Authorization\Delegation\DelegationLivewireIndex;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireIndex;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireShow;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireUpdate;
use App\Http\Livewire\Authorization\Role\RoleLivewireIndex;
use App\Http\Livewire\Authorization\Role\RoleLivewireShow;
use App\Http\Livewire\Authorization\Role\RoleLivewireUpdate;
use App\Http\Livewire\Authorization\User\UserLivewireIndex;
use App\Http\Livewire\Report\Department\DepartmentReportLivewire;
use App\Http\Livewire\Report\Printer\PrinterReportLivewire;
use App\Http\Livewire\Report\Printing\PrintingReportLivewire;
use App\Http\Livewire\Report\Server\ServerReportLivewire;
use App\Http\Livewire\Test\Simulation\SimulationLivewireCreate;
use App\Models\Configuration;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Printer;
use App\Models\Printing;
use App\Models\Role;
use App\Models\Server;
use App\Models\Site;
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

        Route::prefix('perfil')->name('role.')->group(function () {
            Route::get('/', RoleLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Role::class);
            Route::get('show/{role}', RoleLivewireShow::class)->name('show')->can(Policy::View->value, Role::class);
            Route::get('edit/{role}', RoleLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Role::class);
        });

        Route::prefix('permissao')->name('permission.')->group(function () {
            Route::get('/', PermissionLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Permission::class);
            Route::get('show/{permission}', PermissionLivewireShow::class)->name('show')->can(Policy::View->value, Permission::class);
            Route::get('edit/{permission}', PermissionLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Permission::class);
        });

        Route::prefix('usuario')->name('user.')->group(function () {
            Route::get('/', UserLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, User::class);
        });
    });

    Route::prefix('administracao')->name('administration.')->group(function () {
        Route::prefix('configuracao')->name('configuration.')->group(function () {
            Route::get('show', ConfigurationLivewireShow::class)->name('show')->can(Policy::View->value, Configuration::class);
            Route::get('edit', ConfigurationLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Configuration::class);
        });

        Route::prefix('importacao')->name('importation.')->group(function () {
            Route::get('create', ImportationLivewireCreate::class)->name('create')->can(Policy::ImportationCreate->value);
        });

        Route::prefix('localidade')->name('site.')->group(function () {
            Route::get('/', SiteLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Site::class);
            Route::get('create', SiteLivewireCreate::class)->name('create')->can(Policy::Create->value, Site::class);
            Route::get('show/{site}', SiteLivewireShow::class)->name('show')->can(Policy::View->value, Site::class);
            Route::get('edit/{site}', SiteLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Site::class);
        });

        Route::prefix('log')->name('log.')->group(function () {
            Route::get('/', LogLivewireIndex::class)->name('index')->can(Policy::LogViewAny->value);
        });

        Route::prefix('servidor')->name('server.')->group(function () {
            Route::get('/', ServerLivewireIndex::class)->name('index')->can(Policy::ViewAny->value, Server::class);
            Route::get('show/{server}', ServerLivewireShow::class)->name('show')->can(Policy::View->value, Server::class);
            Route::get('edit/{server}', ServerLivewireUpdate::class)->name('edit')->can(Policy::Update->value, Server::class);
        });
    });

    Route::prefix('relatorio')->name('report.')->group(function () {
        Route::prefix('impressao')->name('printing.')->group(function () {
            Route::get('/', PrintingReportLivewire::class)->name('create')->can(Policy::Report->value, Printing::class);
        });

        Route::prefix('impressora')->name('printer.')->group(function () {
            Route::get('/', PrinterReportLivewire::class)->name('create')->can(Policy::Report->value, Printer::class);
        });

        Route::prefix('lotacao')->name('department.')->group(function () {
            Route::get('/', DepartmentReportLivewire::class)->name('create')->can(Policy::ReportAny->value, Department::class);
        });

        Route::prefix('servidor')->name('server.')->group(function () {
            Route::get('/', ServerReportLivewire::class)->name('create')->can(Policy::Report->value, Server::class);
        });
    });

    Route::prefix('teste')->name('test.')->group(function () {
        Route::prefix('simulacao')->name('simulation.')->group(function () {
            Route::get('create', SimulationLivewireCreate::class)->name('create')->can(Policy::SimulationCreate->value);
            Route::delete('/', [SimulationLivewireCreate::class, 'destroy'])->name('destroy')->can(Policy::SimulationDelete->value);
        });
    });
});
