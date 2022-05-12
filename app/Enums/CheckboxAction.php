<?php

namespace App\Enums;

/*
 * Types of actions available for checkboxes.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/collections
 */
enum CheckboxAction: string
{
    case CheckAll = 'check-all';
    case UncheckAll = 'uncheck-all';
    case CheckAllPage = 'check-all-page';
    case UncheckAllPage = 'uncheck-all-page';
    /**
     * Display name of the action type for the checkbox.
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
     * All possible values for the enum value.
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
