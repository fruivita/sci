<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Classe base para concentrar rotinas comuns às diversas policies.
 *
 * @see https://laravel.com/docs/authorization
 */
abstract class Policy
{
    use HandlesAuthorization;

    /**
     * Verfica se o usuário possui uma das permissões informadas.
     *
     * @param \App\Models\User            $user
     * @param \App\Enums\PermissionType[] $permissions
     * @param bool                        $cache       pode usar o cache na
     *                                                 consulta?
     *
     * @return bool
     */
    protected function hasAnyPermission(User $user, array $permissions, bool $cache = true)
    {
        return $this
        ->permissions($user, $cache)
        ->filter(
            fn ($value) => in_array(PermissionType::tryFrom($value), $permissions, true)
        )->isNotEmpty();
    }

    /**
     * Todas as permissões do usuário.
     *
     * @param \App\Models\User $user
     * @param bool             $cache pode usar o cache na consulta?
     *
     * @return \Illuminate\Support\Collection
     */
    protected function permissions(User $user, bool $cache)
    {
        return ($cache === true)
        ? cache()->get("{$user->username}-permissions", fn () => $user->permissions())
        : $user->permissions();
    }
}
