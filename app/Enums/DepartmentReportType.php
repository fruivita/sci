<?php

namespace App\Enums;

/*
 * Tipos de relatório por lotação.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/9.x/collections
 */
enum DepartmentReportType: string
{
    case Institutional = 'institutional';
    case Managerial = 'managerial';
    case Department = 'department';
    /**
     * Nome para exibição do tipo de relatório por lotação.
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
     * Todos os valores possíveis para o value do enum.
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
