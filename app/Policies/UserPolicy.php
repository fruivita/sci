<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class UserPolicy extends Policy
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
        return $this->hasPermissionWithCache($user, PermissionType::UserViewAny);
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
        return $this->hasPermissionWithCache($user, PermissionType::UserUpdate);
    }

    /**
     * Determine whether the user can delegate his role.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $delegated
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delegationCreate(User $user, User $delegated)
    {
        return
            ! empty($user->role_id)
            // usuário autenticado possui mais permissões que o destinatário
            && $user->role_id < $delegated->role_id
            // possuem a mesma lotação
            && $user->department_id == $delegated->department_id;
    }

    /**
     * Determine whether the user can revoke his delegation.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $delegated
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delegationDelete(User $user, User $delegated)
    {
        return
            ! empty($delegated->role_granted_by)
            // usuário autenticado possui mais ou o mesmo nível de permissões que o destinatário
            && $user->role_id <= $delegated->role_id
            // possuem a mesma lotação
            && $user->department_id == $delegated->department_id;
    }
}
