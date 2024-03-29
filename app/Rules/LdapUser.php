<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use LdapRecord\Models\ActiveDirectory\User;

/**
 * Checks if the samaccountname is valid i.e. if it exists in the LDAP server.
 *
 * @see https://laravel.com/docs/validation#custom-validation-rules
 */
class LdapUser implements Rule
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
        return User::findBy('samaccountname', $value)
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
        return __('validation.not_found.user');
    }
}
