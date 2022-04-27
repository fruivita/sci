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
     * @param \App\Models\User          $user
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
                return $this->hasAnyPermission($user, [$permission]);
            }
        );
    }

    /**
     * Determina se o usuário possui uma das permissões informadas.
     *
     * Utiliza cache de curta duração para armazenar a permissão evitando-se
     * queries repetitivas, em especial, em um mesmo request.
     *
     * @param \App\Models\User            $user
     * @param \App\Enums\PermissionType[] $permissions
     * @param string                      $partial_key parte da chave que será concatenada ao nome
     *                                                 do usuário para gerar a chave do cache
     *
     * @return bool
     */
    protected function hasAnyPermissionWithCache(User $user, array $permissions, string $partial_key)
    {
        $this->useCache();

        return (bool) $this->cache(
            key: $user->username . $partial_key,
            seconds: 5,
            callback: function () use ($user, $permissions) {
                return $this->hasAnyPermission($user, $permissions);
            }
        );
    }

    /**
     * Determina se o usuário possui a permissão informada, sem armazenar em
     * cache o resultado.
     *
     * @param \App\Models\User          $user
     * @param \App\Enums\PermissionType $permission
     *
     * @return bool
     */
    protected function hasPermissionWithoutCache(User $user, PermissionType $permission)
    {
        return (bool) $user->hasPermission($permission);
    }

    /**
     * Verfica se o usuário possui uma das permissões informadas.
     *
     * @param \App\Models\User $user
     * @param \App\Enums\PermissionType[] $permissions
     *
     * @return bool
     */
    protected function hasAnyPermission(User $user, array $permissions)
    {
        $match = $this->allPermissionsWithCache($user)->filter(function ($value, $key) use ($permissions) {
            return in_array(PermissionType::tryFrom($value), $permissions, true);
        });

        return $match->isNotEmpty();
    }

    /**
     * Todas as permissões do usuário.
     *
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Support\Collection
     */
    protected function allPermissionsWithCache(User $user)
    {
        $this->useCache();

        return $this->cache(
            key: $user->username . '-all-permissions',
            seconds: 5,
            callback: function () use ($user) {
                return $user->permissions();
            }
        );
    }
}
