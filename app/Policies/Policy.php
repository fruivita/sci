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
     * Checks if the user has one of the given permissions.
     *
     * @param \App\Models\User            $user
     * @param \App\Enums\PermissionType[] $permissions
     * @param bool                        $cache       can use cache in query?
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
     * All user permissions.
     *
     * @param \App\Models\User $user
     * @param bool             $cache can use cache in query?
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
