<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class RolePolicy extends Policy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $this->hasPermissionWithCache($user, Role::VIEWANY);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user)
    {
        return $this->hasPermissionWithCache($user, Role::VIEW);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user)
    {
        return $this->hasPermissionWithCache($user, Role::UPDATE);
    }
}
