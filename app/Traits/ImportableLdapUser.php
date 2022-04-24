<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

/**
 * Trait para importação de usuários do servidor LDAP.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 */
trait ImportableLdapUser
{
    /**
     * Importa para o database da aplicação o usuário do servidor LDAP e o
     * retorna como um usuário da aplicação.
     *
     * @param string $username usuário/samaccountname do servidor LDAP
     *
     * @return \App\Models\User|null
     */
    private function importLdapUser(string $username)
    {
        Artisan::call('ldap:import', [
            'provider' => 'users',
            '--no-interaction',
            '--filter' => "(samaccountname={$username})",
            '--attributes' => 'cn,samaccountname',
        ]);

        return User::where('username', $username)->first();
    }
}
