<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Route;

/**
 * Checks if the route exists in the application, that is, if it is a valid
 * route.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
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
        return __('validation.not_found.route');
    }
}
