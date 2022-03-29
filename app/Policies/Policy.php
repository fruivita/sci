<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\WithCaching;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Classe base para concentrar rotinas comuns às diversas policies.
 *
 * @see https://laravel.com/docs/9.x/authorization
 */
abstract class Policy
{
    use HandlesAuthorization, WithCaching;

    /**
     * Determina se o usuário possui a permissão informada.
     *
     * Utiliza cache de curta duração para armazenar a permissão evitando-se
     * queries repetitivas, em especial, em um mesmo request.
     *
     * @param \App\Models\User $user
     * @param int $permission
     *
     * @return bool
     */
    protected function hasPermissionWithCache(User $user, int $permission)
    {
        $this->useCache();

        return (bool) $this->cache(
            key: $user->username . $permission,
            seconds: 5,
            callback: function () use ($user, $permission) {
                return $user->hasPermission($permission);
            }
        );
    }
}
