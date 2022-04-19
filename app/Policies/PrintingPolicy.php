<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;

/**
 * @see https://laravel.com/docs/9.x/authorization
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
        return $this->hasPermissionWithCache($user, PermissionType::PrintingReport);
    }

    /**
     * Determine whether the user can generated PDF printing report.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function pdfReport(User $user)
    {
        return $this->hasPermissionWithCache($user, PermissionType::PrintingPDFReport);
    }
}
