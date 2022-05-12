<?php

namespace App\Rules;

use function App\reportMinDate;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

/**
 * Checks if the date is greater than the minimum date for generating reports.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
 */
class DateMin implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $min_date = reportMinDate()->startOfDay();
        $date = Carbon::createFromFormat('d-m-Y', $value);

        return $date->gte($min_date);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.min.date', ['min' => reportMinDate()->format('d-m-Y')]);
    }
}
