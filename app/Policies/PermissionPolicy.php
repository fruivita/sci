<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Permission;
use App\Models\User;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class PermissionPolicy extends Policy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return $this->hasPermissionWithCache($user, PermissionType::PermissionViewAny);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function view(User $user)
    {
        return $this->hasPermissionWithCache($user, PermissionType::PermissionView);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function update(User $user)
    {
        return $this->hasPermissionWithCache($user, PermissionType::PermissionUpdate);
    }
}
