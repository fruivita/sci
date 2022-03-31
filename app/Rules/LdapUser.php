<?php

namespace App\Rules;

use LdapRecord\Models\ActiveDirectory\User;
use Illuminate\Contracts\Validation\Rule;

/**
 * Verifica se o samaccountname é válido, isto é, se existe no servidor LDAP.
 *
 * @see https://laravel.com/docs/9.x/validation#custom-validation-rules
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
        return __('User not found');
    }
}