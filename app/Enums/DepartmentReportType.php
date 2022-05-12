<?php

namespace App\Enums;

/*
 * Report types by department.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/collections
 */
enum DepartmentReportType: string
{
    case Institutional = 'institutional';
    case Managerial = 'managerial';
    case Department = 'department';
    /**
     * Display name of the report type by department.
     *
     * @return string
     */
    public function label()
    {
        return match ($this) {
            DepartmentReportType::Institutional => __('Institutional'),
            DepartmentReportType::Managerial => __('Managerial'),
            DepartmentReportType::Department => __('Department')
        };
    }

    /**
     * All possible values for the enum value.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function values()
    {
        return
        collect(DepartmentReportType::cases())
        ->transform(function ($type) {
            return $type->value;
        });
    }
}
