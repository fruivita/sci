<?php

namespace App\Policies;

use App\Enums\PermissionType;
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
    use HandlesAuthorization;
    use WithCaching;

    /**
     * Determina se o usuário possui a permissão informada.
     *
     * Utiliza cache de curta duração para armazenar a permissão evitando-se
     * queries repetitivas, em especial, em um mesmo request.
     *
     * @param \App\Models\User $user
     * @param \App\Enums\PermissionType $permission
     *
     * @return bool
     */
    protected function hasPermissionWithCache(User $user, PermissionType $permission)
    {
        $this->useCache();

        return (bool) $this->cache(
            key: $user->username . $permission->value,
            seconds: 5,
            callback: function () use ($user, $permission) {
                return $user->hasPermission($permission);
            }
        );
    }

    /**
     * Determina se o usuário possui a permissão informada, sem armazenar em
     * cache o resultado.
     *
     * @param \App\Models\User $user
     * @param \App\Enums\PermissionType $permission
     *
     * @return bool
     */
    protected function hasPermissionWithoutCache(User $user, PermissionType $permission)
    {
        return (bool) $user->hasPermission($permission);
    }
}
