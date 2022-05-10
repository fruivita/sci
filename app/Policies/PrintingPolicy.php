<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;

/**
 * @see https://laravel.com/docs/authorization
 */
class PrintingPolicy extends Policy
{
    /**
     * Determine whether the user can generate printing report.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function report(User $user)
    {
        return $this->hasAnyPermission($user, [PermissionType::PrintingReport]);
    }
}
