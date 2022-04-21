<?php

namespace App\Rules;

use function App\reportMaxDate;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

/**
 * Verifica se a data em validação é menor que a data máxima para geração dos
 * relatórios.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class DateMax implements Rule
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
        $max_date = reportMaxDate()->endOfDay();
        $date = Carbon::createFromFormat('d-m-Y', $value);

        return $date->lte($max_date);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.max.date', ['max' => reportMaxDate()->format('d-m-Y')]);
    }
}
