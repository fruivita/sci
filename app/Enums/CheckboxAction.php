<?php

namespace App\Enums;

/*
 * Tipos de actions disponíveis para os checkbox.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 */
enum CheckboxAction: string
{
    case CheckAll = 'check_all';
    case UncheckAll = 'uncheck_all';
    case CheckAllPage = 'check_all_page';
    case UncheckAllPage = 'uncheck_all_page';
    /**
     * Nome para exibição do tipo de ação para o checkbox.
     *
     * @return string
     */
    public function label()
    {
        return match ($this) {
            CheckboxAction::CheckAll => __('Check all'),
            CheckboxAction::UncheckAll => __('Uncheck all'),
            CheckboxAction::CheckAllPage => __('Check all on page'),
            CheckboxAction::UncheckAllPage => __('Uncheck all on page')
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
        collect(CheckboxAction::cases())
        ->transform(function ($type) {
            return $type->value;
        });
    }
}
