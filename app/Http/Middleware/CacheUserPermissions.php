<?php

namespace App\Http\Middleware;

use App\Traits\WithCaching;
use Closure;
use Illuminate\Http\Request;

/**
 * Permissões do usuário autenticado.
 *
 * @see https://laravel.com/docs/9.x/middleware
 */
class CacheUserPermissions
{
    use WithCaching;

    /**
     * Handle an incoming request.
     *
     * Armazena em cache as permissões do usuário autenticado. As permissões e
     * o cache são renovados a cada request.
     * Note que também tambem é verificado se o usuário é um superadmin.
     *
     * O tempo de vida dos caches é de 5 segundos.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            /** @var \App\Models\User */
            $user = auth()->user();

            $this->useCache();

            $this->cache(
                key: "{$user->username}-permissions",
                seconds: 5,
                callback: fn () => $user->permissions()
            );

            $this->cache(
                key: "{$user->username}-is-super-admin",
                seconds: 5,
                callback: fn () => $user->isSuperAdmin()
            );
        }

        return $next($request);
    }
}
