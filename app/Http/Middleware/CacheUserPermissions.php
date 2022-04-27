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
     *
     * O tempo de vida do cache é de 5 segundos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();

            $this->useCache();

            $this->cache(
                key: "{$user->username}-permissions",
                seconds: 5,
                callback: fn () => $user->permissions()
            );
        }

        return $next($request);
    }
}
