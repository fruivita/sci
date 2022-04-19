<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;

/**
 * @see https://laravel.com/docs/9.x/authorization
 */
class ServerPolicy extends Policy
{
    /**
     * Determine whether the user can generate server report.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function report(User $user)
    {
        return $this->hasPermissionWithCache($user, PermissionType::ServerReport);
    }

    /**
     * Determine whether the user can generated PDF server report.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function pdfReport(User $user)
    {
        return $this->hasPermissionWithCache($user, PermissionType::ServerPDFReport);
    }
}
