<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class LogPolicy extends Policy
{
    /**
     * Determine whether the user can view any log files.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function viewAny(User $user)
    {
        return $this->hasAnyPermission($user, [PermissionType::LogViewAny]);
    }

    /**
     * Determine whether the user can delete any log files.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function delete(User $user)
    {
        return $this->hasAnyPermission($user, [PermissionType::LogDelete]);
    }

    /**
     * Determine whether the user can download any log file.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function download(User $user)
    {
        return $this->hasAnyPermission($user, [PermissionType::LogDownload]);
    }
}
