<?php

namespace App\Enums;

/*
 * Monthly grouping types (number of months) for the general print report.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 */
enum MonthlyGroupingType: int
{
    case Monthly = 1;
    case Bimonthly = 2;
    case Trimonthly = 3;
    case Quadrimester = 4;
    case Semiannual = 6;
    case Yearly = 12;
    /**
     * Display name of the monthly grouping.
     *
     * @return string
     */
    public function label()
    {
        return match ($this) {
            MonthlyGroupingType::Monthly => __('Monthly'),
            MonthlyGroupingType::Bimonthly => __('Bimonthly'),
            MonthlyGroupingType::Trimonthly => __('Trimonthly'),
            MonthlyGroupingType::Quadrimester => __('Quadrimester'),
            MonthlyGroupingType::Semiannual => __('Semiannual'),
            MonthlyGroupingType::Yearly => __('Yearly')
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
        collect(MonthlyGroupingType::cases())
        ->transform(function ($type) {
            return $type->value;
        });
    }
}
