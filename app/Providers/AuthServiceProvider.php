<?php

namespace App\Providers;

use App\Enums\Policy;
use App\Models\User;
use App\Policies\DelegationPolicy;
use App\Policies\ImportationPolicy;
use App\Policies\LogPolicy;
use App\Policies\SimulationPolicy;
use App\Traits\WithCaching;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Fortify;

class AuthServiceProvider extends ServiceProvider
{
    use WithCaching;

    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Configuration::class => \App\Policies\ConfigurationPolicy::class,
        \App\Models\Department::class => \App\Policies\DepartmentPolicy::class,
        \App\Models\Permission::class => \App\Policies\PermissionPolicy::class,
        \App\Models\Printer::class => \App\Policies\PrinterPolicy::class,
        \App\Models\Printing::class => \App\Policies\PrintingPolicy::class,
        \App\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Server::class => \App\Policies\ServerPolicy::class,
        \App\Models\Site::class => \App\Policies\SitePolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // checking for super admin
        Gate::before(function (User $user) {
            $this->useCache();

            $is_super_admin = $this->cache(
                key: "{$user->username}-is-super-admin",
                seconds: 5,
                callback: fn () => $user->isSuperAdmin()
            );

            if ($is_super_admin === true) {
                return true;
            }
        });

        Gate::define(Policy::DelegationViewAny->value, [DelegationPolicy::class, 'viewAny']);
        Gate::define(Policy::DelegationCreate->value, [DelegationPolicy::class, 'create']);
        Gate::define(Policy::DelegationDelete->value, [DelegationPolicy::class, 'delete']);
        Gate::define(Policy::ImportationCreate->value, [ImportationPolicy::class, 'create']);
        Gate::define(Policy::SimulationCreate->value, [SimulationPolicy::class, 'create']);
        Gate::define(Policy::SimulationDelete->value, [SimulationPolicy::class, 'delete']);
        Gate::define(Policy::LogViewAny->value, [LogPolicy::class, 'viewAny']);
        Gate::define(Policy::LogDelete->value, [LogPolicy::class, 'delete']);
        Gate::define(Policy::LogDownload->value, [LogPolicy::class, 'download']);

        // authentication
        Fortify::authenticateUsing(function ($request) {
            $validated = Auth::validate([
                'samaccountname' => $request->input('username'),
                'password' => $request->input('password'),
                'fallback' => [
                    'username' => $request->input('username'),
                    'password' => $request->input('password'),
                ],
            ]);

            return $validated ? Auth::getLastAttempted() : null;
        });
    }
}
