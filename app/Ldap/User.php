<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User as ADUser;

/**
 * @see https://ldaprecord.com/docs/laravel/v2/usage/
 */
class User extends ADUser
{
    /**
     * Retorna o username do usuário autenticado.
     *
     * @return string
     */
    public function username()
    {
        return $this->getFirstAttribute('samaccountname');
    }

    /**
     * Retorna o usuário autenticado para exibição em tela.
     *
     * @return string
     */
    public function forHumans()
    {
        return $this->username();
    }
}
