<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\User;

/**
 * @see https://laravel.com/docs/authorization
 */
class DepartmentPolicy extends Policy
{
    /**
     * Determine whether the user can generate any department report.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function reportAny(User $user)
    {
        return $this->hasAnyPermission(
            $user,
            [
                PermissionType::DepartmentReport,
                PermissionType::ManagerialReport,
                PermissionType::InstitutionalReport,
            ]
        );
    }

    /**
     * Determine whether the user can generate department report.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function departmentReport(User $user)
    {
        return $this->hasAnyPermission($user, [PermissionType::DepartmentReport]);
    }

    /**
     * Determine whether the user can generate department (Managerial) report.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function managerialReport(User $user)
    {
        return $this->hasAnyPermission($user, [PermissionType::ManagerialReport]);
    }

    /**
     * Determine whether the user can generate department (Institutional) report.
     *
     * @param \App\Models\User $user
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function institutionalReport(User $user)
    {
        return $this->hasAnyPermission($user, [PermissionType::InstitutionalReport]);
    }
}
