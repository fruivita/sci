<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Route;

/**
 * Verifica se a rota informada existe na aplicação, isto é, se é uma rota
 * válida.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
 */
class RouteExists implements Rule
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
        return Route::has($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Route not found');
    }
}
