<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class DelegationPolicy extends Policy
{
    /**
     * Determine whether the user can view any delegations.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return $this->hasPermissionWithCache($user, PermissionType::DelegationViewAny);
    }

    /**
     * Determine whether the user can delegate his role.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $delegated
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function create(User $user, User $delegated)
    {
        return
            ! empty($user->role_id)
            // usuário autenticado possui mais permissões que o destinatário
            && $user->role_id < $delegated->role_id
            // possuem a mesma lotação
            && $user->department_id == $delegated->department_id
            && $this->hasPermissionWithCache($user, PermissionType::DelegationCreate);
    }

    /**
     * Determine whether the user can revoke his delegation.
     *
     * @param \App\Models\User $user
     * @param \App\Models\User $delegated
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user, User $delegated)
    {
        return
            ! empty($delegated->role_granted_by)
            // usuário autenticado possui mais ou o mesmo nível de permissões que o destinatário
            && $user->role_id <= $delegated->role_id
            // possuem a mesma lotação
            && $user->department_id == $delegated->department_id;
    }
}
