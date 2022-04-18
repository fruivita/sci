<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

/**
 * Verifica se a data em validação é maior que a data mínima para geração dos
 * relatórios.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
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
        $min_date = config('app.min_date')->startOfDay();
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
        return __('validation.min.date', ['min' => config('app.min_date')->format('d-m-Y')]);
    }
}
