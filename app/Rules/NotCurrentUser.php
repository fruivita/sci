<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * User informed is not the authenticated user.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
 */
class NotCurrentUser implements Rule
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
        return (auth()->user()
            && auth()->user()->username != $value)
                ? true
                : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.not_current_user');
    }
}
